<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Digital Library :: Contact Statement</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/png" href="assets/images/logo.png">
</head>
<body>

  <!-- ==============================================================================
       1. TOP BAR
       ============================================================================== -->
  <div class="top-bar">
    <div class="top-bar-right">
      <a href="#login" class="login-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
        <span>Đăng nhập</span>
      </a>
      <div class="flag-group">
        <!-- Vietnam Flag SVG -->
        <svg class="flag-icon" viewBox="0 0 30 20" title="Tiếng Việt">
          <rect width="30" height="20" fill="#da251d"/>
          <polygon points="15,4 16.5,8.8 21.5,8.8 17.5,11.8 19,16.5 15,13.5 11,16.5 12.5,11.8 8.5,8.8 13.5,8.8" fill="#ffff00"/>
        </svg>
        <!-- UK Flag SVG -->
        <svg class="flag-icon" viewBox="0 0 60 30" title="English">
          <clipPath id="t-contact"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/></clipPath>
          <path d="M0,0 v30 h60 v-30 z" fill="#012169"/>
          <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
          <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t-contact)" stroke="#c8102e" stroke-width="4"/>
          <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
          <path d="M30,0 v30 M0,15 h60" stroke="#c8102e" stroke-width="6"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- ==============================================================================
       2. HEADER & MAIN NAVIGATION (CONTACT ACTIVE)
       ============================================================================== -->
  <header class="main-header">
    <a href="/" class="brand-wrapper">
      <img src="assets/images/logo.png" alt="Khoa Văn học - USSH - VNU" class="brand-logo-img">
      <div class="brand-text-group">
        <span class="brand-title-sub">ĐẠI HỌC QUỐC GIA TP.HCM</span>
        <span class="brand-title-main">THƯ VIỆN KHOA VĂN HỌC</span>
      </div>
    </a>

    <nav>
      <ul class="nav-links">
        <li class="nav-item">
          <a href="/collections" class="nav-link">Bộ sưu tập</a>
        </li>
        <li class="nav-item">
          <a href="#entities" class="nav-link">
            <span>Thực thể</span>
            <span class="caret-icon">▾</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#browse" class="nav-link">
            <span>Duyệt theo</span>
            <span class="caret-icon">▾</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#help" class="nav-link">Trợ giúp</a>
        </li>
        <li class="nav-item">
          <!-- Active Nav Link: Liên hệ with blue underline -->
          <a href="/contact" class="nav-link active">Liên hệ</a>
        </li>
        <li class="nav-item">
          <!-- Search icon button on right of navbar -->
          <button class="nav-search-btn" title="Tìm kiếm" aria-label="Tìm kiếm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="11" cy="11" r="7"/>
              <line x1="21" y1="21" x2="16.5" y2="16.5"/>
            </svg>
          </button>
        </li>
      </ul>
    </nav>
  </header>

  <!-- Green horizontal accent strip below header -->
  <div class="header-green-bar"></div>

  <!-- ==============================================================================
       3. BREADCRUMB
       ============================================================================== -->
  <div class="breadcrumb-section">
    <div class="breadcrumb-container">
      <a href="/" class="breadcrumb-link">Trang chủ</a>
      <span class="breadcrumb-separator">•</span>
      <span class="breadcrumb-current">Thông tin liên hệ</span>
    </div>
  </div>

  <!-- ==============================================================================
       4. THÔNG TIN LIÊN HỆ (CONTACT STATEMENT CONTENT)
       ============================================================================== -->
  <main class="contact-section">
    <div class="contact-container">
      <h1 class="contact-org-title">Thư viện Trung tâm, Đại học Quốc gia TP. Hồ Chí Minh</h1>

      <ul class="contact-list">
        <li>
          <strong>Địa chỉ:</strong> Đường Võ Trường Toản, Khu đô thị ĐHQG-HCM, Khu phố 9, Phường Linh Xuân, TP.HCM
        </li>
        <li>
          <strong>Điện thoại:</strong> (84-28) 37242160 (2311)
        </li>
        <li>
          <strong>Email:</strong> <a href="mailto:cenlib@vnuhcm.edu.vn" class="contact-email-link">cenlib@vnuhcm.edu.vn</a>
        </li>
      </ul>
    </div>
  </main>

  <!-- ==============================================================================
       5. FOOTER
       ============================================================================== -->
  <footer class="main-footer">
    <div class="footer-container">
      <div class="footer-info">
        <div class="footer-org-name">THƯ VIỆN TRUNG TÂM, ĐẠI HỌC QUỐC GIA TP. HỒ CHÍ MINH</div>
        <div class="footer-line">
          <strong>Địa chỉ:</strong> Đường Võ Trường Toản, Khu đô thị ĐHQG-HCM, Khu phố 6, Phường Linh Trung, TP. Thủ Đức, TP.HCM
        </div>
        <div class="footer-line">
          <strong>Điện thoại:</strong> (028) 37242160 (2311)
        </div>
        <div class="footer-line">
          <strong>Email:</strong> centralib@vnuhcm.edu.vn
        </div>
      </div>

      <div class="footer-right-logo">
        <svg class="footer-logo-svg" viewBox="0 0 140 90" fill="none">
          <path d="M20 22 L65 5 L65 80 L20 63 Z" fill="#67e8f9" opacity="0.9"/>
          <path d="M30 30 L55 20 L55 72 L30 62 Z" fill="#ffffff" opacity="0.8"/>
          <path d="M75 5 L120 22 L120 63 L75 80 Z" fill="#67e8f9" opacity="0.9"/>
          <path d="M85 20 L110 30 L110 62 L85 72 Z" fill="#ffffff" opacity="0.8"/>
          <rect x="36" y="37" width="14" height="3" fill="#137036"/>
          <rect x="36" y="45" width="14" height="3" fill="#137036"/>
          <rect x="36" y="53" width="14" height="3" fill="#137036"/>
          <rect x="90" y="37" width="14" height="3" fill="#137036"/>
          <rect x="90" y="45" width="14" height="3" fill="#137036"/>
          <rect x="90" y="53" width="14" height="3" fill="#137036"/>
        </svg>
      </div>
    </div>
  </footer>

  <script src="assets/js/main.js"></script>
</body>
</html>
