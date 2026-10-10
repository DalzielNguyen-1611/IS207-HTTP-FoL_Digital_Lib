/**
 * TechCare Unified Authentication & Session Guard Module (auth.js)
 * Quản lý phiên làm việc tập trung (JWT / Token Expiration / RBAC Guard)
 */

const Auth = {
  TOKEN_KEY: 'techcare_token',
  USER_KEY: 'techcare_user',
  EXPIRES_AT_KEY: 'techcare_expires_at',

  /**
   * Lấy token hiện tại
   */
  getToken() {
    return localStorage.getItem(this.TOKEN_KEY);
  },

  /**
   * Lấy thông tin user hiện tại
   */
  getUser() {
    const str = localStorage.getItem(this.USER_KEY);
    if (!str) return null;
    try {
      return JSON.parse(str);
    } catch (e) {
      return null;
    }
  },

  /**
   * Lấy thời điểm hết hạn (Timestamp mili-giây)
   */
  getExpiresAt() {
    const val = localStorage.getItem(this.EXPIRES_AT_KEY);
    return val ? parseInt(val, 10) : 0;
  },

  /**
   * Lưu phiên đăng nhập mới
   */
  saveSession(authData) {
    if (!authData || !authData.token) return;

    localStorage.setItem(this.TOKEN_KEY, authData.token);
    if (authData.user) {
      localStorage.setItem(this.USER_KEY, JSON.stringify(authData.user));
    }

    let expMs;
    if (authData.expires_at_ts) {
      expMs = authData.expires_at_ts * 1000;
    } else if (authData.expires_in) {
      expMs = Date.now() + (authData.expires_in * 1000);
    } else {
      // Mặc định 120 phút nếu backend không trả
      expMs = Date.now() + (120 * 60 * 1000);
    }

    localStorage.setItem(this.EXPIRES_AT_KEY, expMs.toString());
  },

  /**
   * Xóa sạch phiên làm việc
   */
  clearSession() {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    localStorage.removeItem(this.EXPIRES_AT_KEY);
  },

  /**
   * Kiểm tra người dùng đã đăng nhập và phiên còn hạn hay không
   */
  isLoggedIn() {
    const token = this.getToken();
    const user = this.getUser();
    const exp = this.getExpiresAt();

    if (!token || !user) {
      return false;
    }

    // Kiểm tra thời gian hết hạn của phiên
    if (exp > 0 && Date.now() >= exp) {
      this.clearSession();
      return false;
    }

    return true;
  },

  /**
   * Tính thời gian còn lại của phiên làm việc
   */
  getTimeRemaining() {
    const exp = this.getExpiresAt();
    if (!exp) return { totalSeconds: 0, minutes: 0, seconds: 0, formatted: '00:00' };

    const diff = Math.max(0, Math.floor((exp - Date.now()) / 1000));
    const hours = Math.floor(diff / 3600);
    const minutes = Math.floor((diff % 3600) / 60);
    const seconds = diff % 60;

    const pad = (n) => String(n).padStart(2, '0');
    let formatted;
    if (hours > 0) {
      formatted = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    } else {
      formatted = `${pad(minutes)}:${pad(seconds)}`;
    }

    return {
      totalSeconds: diff,
      hours,
      minutes,
      seconds,
      formatted,
      isExpired: diff <= 0,
      isExpiringSoon: diff > 0 && diff < 300 // Dưới 5 phút
    };
  },

  /**
   * Đăng xuất người dùng và chuyển về trang chủ
   */
  logout(redirectUrl = 'index.html') {
    this.clearSession();
    window.location.href = redirectUrl;
  },

  /**
   * Auth Guard chặn trang (Dùng ở đầu mỗi trang HTML)
   * 
   * @param {string|string[]} allowedRoles Danh sách vai trò được phép truy cập
   */
  checkGuard(allowedRoles = []) {
    const roles = Array.isArray(allowedRoles) ? allowedRoles : [allowedRoles];
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const currentQuery = window.location.search || '';
    const fullRedirect = encodeURIComponent(currentPage + currentQuery);

    const token = this.getToken();
    const exp = this.getExpiresAt();

    // 1. Kiểm tra hết hạn phiên
    if (token && exp > 0 && Date.now() >= exp) {
      this.clearSession();
      window.location.replace(`index.html?reason=expired&redirect=${fullRedirect}`);
      return false;
    }

    // 2. Kiểm tra chưa đăng nhập
    if (!this.isLoggedIn()) {
      window.location.replace(`index.html?reason=login_required&redirect=${fullRedirect}`);
      return false;
    }

    const user = this.getUser();
    if (!user) {
      this.clearSession();
      window.location.replace(`index.html?reason=login_required&redirect=${fullRedirect}`);
      return false;
    }

    // 3. Kiểm tra vai trò (admin luôn được truy cập mọi trang)
    if (roles.length > 0 && user.role !== 'admin' && !roles.includes(user.role)) {
      alert(`Tài khoản vai trò [${user.role}] không có quyền truy cập trang này. Đang chuyển hướng về không gian làm việc của bạn.`);
      if (user.role === 'dispatcher') {
        window.location.replace('quan_ly.html');
      } else if (user.role === 'technician') {
        window.location.replace('ky_thuat_vien.html');
      } else {
        window.location.replace('khach_hang.html');
      }
      return false;
    }

    return true;
  },

  /**
   * Wrapper gọi Fetch có tự động đính kèm Token và xử lý khi phiên hết hạn (401)
   */
  async fetch(url, options = {}) {
    const headers = { ...(options.headers || {}) };
    const token = this.getToken();

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      headers['X-Auth-Token'] = token;
    }

    try {
      const response = await fetch(url, {
        ...options,
        headers
      });

      // Nếu máy chủ trả về 401 Unauthorized
      if (response.status === 401) {
        let errData = null;
        try {
          errData = await response.clone().json();
        } catch (e) {}

        if (errData && errData.expired) {
          this.clearSession();
          alert('Phiên làm việc của bạn đã hết hạn! Vui lòng đăng nhập lại để tiếp tục.');
          const currentPage = window.location.pathname.split('/').pop() || 'index.html';
          window.location.href = `index.html?reason=expired&redirect=${encodeURIComponent(currentPage)}`;
          return response;
        }
      }

      return response;
    } catch (err) {
      throw err;
    }
  },

  /**
   * Giám sát thời gian phiên làm việc chạy ngầm (Background Session Watcher)
   * Tự động điều hướng khi hết hạn phiên mà không cần hiển thị đồng hồ lên UI
   * 
   * @param {string|null} timerElementId Tùy chọn ID thẻ nếu cần fallback
   * @param {function|null} onExpire Callback tùy biến khi phiên hết hạn
   */
  initSessionTimer(timerElementId = null, onExpire = null) {
    const checkExpiration = () => {
      if (!this.isLoggedIn()) return;
      const remaining = this.getTimeRemaining();

      if (timerElementId) {
        const currentEl = document.getElementById(timerElementId);
        if (currentEl) {
          currentEl.textContent = remaining.formatted;
        }
      }

      if (remaining.isExpired) {
        this.clearSession();
        if (onExpire) {
          onExpire();
        } else {
          window.location.href = 'index.html?reason=expired';
        }
      }
    };

    checkExpiration();
    return setInterval(checkExpiration, 5000);
  },

  /**
   * Render widget thông tin người dùng trên Header
   */
  renderHeaderUserWidget(containerId = 'header-user-widget') {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!this.isLoggedIn()) {
      container.innerHTML = `
        <a href="index.html#booking-login-gate" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold shadow-xs transition-colors">
          <span class="material-symbols-outlined text-[16px]">login</span>
          <span>Đăng Nhập</span>
        </a>
      `;
      return;
    }

    const user = this.getUser();
    const roleLabels = {
      admin: { name: 'Quản Trị Viên', color: 'bg-purple-500/20 text-purple-300 border-purple-500/30' },
      dispatcher: { name: 'Điều Phối Viên', color: 'bg-blue-500/20 text-blue-300 border-blue-500/30' },
      technician: { name: 'Kỹ Thuật Viên', color: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' },
      customer: { name: 'Khách Hàng', color: 'bg-amber-500/20 text-amber-300 border-amber-500/30' }
    };

    const roleInfo = roleLabels[user.role] || { name: user.role, color: 'bg-slate-700 text-slate-200 border-slate-600' };

    container.innerHTML = `
      <div class="flex items-center gap-2.5 sm:gap-3 bg-slate-800/80 backdrop-blur-xs border border-slate-700/60 rounded-xl px-2.5 py-1.5 text-xs text-white">
        <!-- User Avatar & Info -->
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-slate-700 flex items-center justify-center font-bold text-[11px] text-white shrink-0">
            ${user.full_name ? user.full_name.charAt(0).toUpperCase() : 'U'}
          </div>
          <div class="hidden sm:flex flex-col text-left leading-tight">
            <span class="font-bold text-slate-100 truncate max-w-[120px]">${user.full_name || user.username}</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded border ${roleInfo.color} font-medium inline-block w-fit mt-0.5">${roleInfo.name}</span>
          </div>
        </div>

        <!-- Logout Button -->
        <button type="button" onclick="Auth.logout()" class="p-1 rounded-lg hover:bg-rose-500/20 text-slate-400 hover:text-rose-300 transition-colors ml-0.5" title="Đăng xuất khỏi hệ thống">
          <span class="material-symbols-outlined text-[18px]">logout</span>
        </button>
      </div>
    `;

    this.initSessionTimer();
  }
};

// Đưa ra phạm vi toàn cục để tất cả các trang web sử dụng
window.Auth = Auth;
