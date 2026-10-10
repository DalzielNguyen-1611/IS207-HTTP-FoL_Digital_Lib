/**
 * TechCare Frontend API Client
 * Thư viện JavaScript Client kết nối Presentation Tier với Backend PHP REST API
 */

const API_BASE_URL = (() => {
  if (!window.location.origin.includes('localhost') && !window.location.origin.includes('127.0.0.1')) {
    return '../backend/api';
  }
  if (window.location.pathname.includes('/mini-project-techCare-platform/')) {
    const prefix = window.location.pathname.startsWith('/mini_project/') ? '/mini_project' : '';
    return `${prefix}/mini-project-techCare-platform/backend/api`;
  }
  return window.location.port === '8000' || window.location.port === '5000' 
    ? '/backend/api' 
    : '/mini_project/backend/api';
})();

const TechCareAPI = {
  /**
   * Cấu hình URL gốc
   */
  baseUrl: API_BASE_URL,

  /**
   * Gọi API tổng quát qua Fetch
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}/${endpoint.replace(/^\//, '')}`;
    const defaultHeaders = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          ...defaultHeaders,
          ...(options.headers || {}),
        },
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.warn(`[TechCareAPI] Lỗi kết nối đến ${url}, chuyển sang chế độ offline:`, error);
      return {
        success: false,
        message: 'Không thể kết nối đến máy chủ backend PHP.',
        error: error.message,
      };
    }
  },

  // 1. AUTH API
  auth: {
    async login(username, password) {
      return TechCareAPI.request('auth.php?action=login', {
        method: 'POST',
        body: JSON.stringify({ username, password }),
      });
    },
    async register(userData) {
      return TechCareAPI.request('auth.php?action=register', {
        method: 'POST',
        body: JSON.stringify(userData),
      });
    },
    async getProfile() {
      return TechCareAPI.request('auth.php?action=profile');
    },
  },

  // 2. BOOKINGS API
  bookings: {
    async getAll(filters = {}) {
      const query = new URLSearchParams(filters).toString();
      return TechCareAPI.request(`bookings.php${query ? '?' + query : ''}`);
    },
    async getById(id) {
      return TechCareAPI.request(`bookings.php?id=${encodeURIComponent(id)}`);
    },
    async create(bookingData) {
      return TechCareAPI.request('bookings.php', {
        method: 'POST',
        body: JSON.stringify(bookingData),
      });
    },
    async updateStatus(id, status) {
      return TechCareAPI.request(`bookings.php?id=${encodeURIComponent(id)}&action=status`, {
        method: 'POST',
        body: JSON.stringify({ status }),
      });
    },
  },

  // 3. DISPATCH API
  dispatch: {
    async getDashboard() {
      return TechCareAPI.request('dispatch.php?action=dashboard');
    },
    async assignTechnician(bookingId, technicianId) {
      return TechCareAPI.request('dispatch.php?action=assign', {
        method: 'POST',
        body: JSON.stringify({ booking_id: bookingId, technician_id: technicianId }),
      });
    },
    async getLiveTechnicians() {
      return TechCareAPI.request('dispatch.php?action=technicians-live');
    },
  },

  // 4. TECHNICIAN API
  technician: {
    async getSchedule(techId = 'TECH-204') {
      return TechCareAPI.request(`technicians.php?action=schedule&tech_id=${encodeURIComponent(techId)}`);
    },
    async getWallet(techId = 'TECH-204') {
      return TechCareAPI.request(`technicians.php?action=wallet&tech_id=${encodeURIComponent(techId)}`);
    },
    async completeJob(bookingId, completionData) {
      return TechCareAPI.request(`technicians.php?action=complete&booking_id=${encodeURIComponent(bookingId)}`, {
        method: 'POST',
        body: JSON.stringify(completionData),
      });
    },
    async withdraw(techId, amount) {
      return TechCareAPI.request(`technicians.php?action=withdraw&tech_id=${encodeURIComponent(techId)}`, {
        method: 'POST',
        body: JSON.stringify({ amount }),
      });
    },
  },
};

// Đưa ra window để dùng trực tiếp trong console hoặc inline script
if (typeof window !== 'undefined') {
  window.TechCareAPI = TechCareAPI;
}
