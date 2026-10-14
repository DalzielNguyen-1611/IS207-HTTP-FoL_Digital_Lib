<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bộ Sưu Tập Số — Thư Viện Trung Tâm ĐHQG-HCM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/png" href="assets/images/logo.png">
</head>
<body>

  <!-- ==============================================================================
       1. TOP BAR (Thanh tiện ích trên cùng)
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
          <clipPath id="t"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/></clipPath>
          <path d="M0,0 v30 h60 v-30 z" fill="#012169"/>
          <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
          <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t)" stroke="#c8102e" stroke-width="4"/>
          <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
          <path d="M30,0 v30 M0,15 h60" stroke="#c8102e" stroke-width="6"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- ==============================================================================
       2. HEADER & MAIN NAVIGATION
       ============================================================================== -->
  <header class="main-header">
    <a href="/" class="brand-wrapper">
      <!-- Faculty of Literature Official Logo -->
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
          <a href="/contact" class="nav-link">Liên hệ</a>
        </li>
      </ul>
    </nav>
  </header>

  <!-- ==============================================================================
       3. HERO BANNER: BỘ SƯU TẬP SỐ
       ============================================================================== -->
  <section class="hero-banner">
    <!-- Authentic left graphic from official repository -->
    <img src="assets/images/banner_art_left.png" class="banner-side-art banner-art-left" alt="E-Book Lesson">

    <div class="hero-container">
      
      <!-- Center Hero: Title & Search Bar -->
      <div class="hero-center-content">
        <div class="hero-sub-badge">
          <svg class="hero-vnu-logo-mini" viewBox="0 0 30 24" fill="none">
            <path d="M3 8 L13 3 L13 22 L3 17 Z" fill="#005baa"/>
            <path d="M17 3 L27 8 L27 17 L17 22 Z" fill="#005baa"/>
          </svg>
          <span class="hero-sub-text">ĐẠI HỌC QUỐC GIA TP.HCM &nbsp;|&nbsp; HỆ THỐNG THƯ VIỆN</span>
        </div>

        <h1 class="hero-main-title">BỘ SƯU TẬP SỐ</h1>

        <!-- Search Bar Component -->
        <div class="search-box-wrapper">
          <input type="text" class="search-input" placeholder="Nhập từ khoá cần tìm kiếm" aria-label="Tìm kiếm tài liệu">
          <select class="search-dropdown" aria-label="Phạm vi tìm kiếm">
            <option value="all">Chọn</option>
            <option value="title">Tiêu đề</option>
            <option value="author">Tác giả</option>
            <option value="subject">Chủ đề</option>
            <option value="isbn">Mã số tài liệu</option>
          </select>
          <button class="search-btn" aria-label="Thực hiện tìm kiếm">
            <svg class="search-icon-svg" viewBox="0 0 24 24" fill="none">
              <circle cx="11" cy="11" r="7"/>
              <line x1="21" y1="21" x2="16.5" y2="16.5"/>
            </svg>
          </button>
        </div>
      </div>

    </div>

    <!-- Authentic right graphic from official repository -->
    <img src="assets/images/banner_art_right.png" class="banner-side-art banner-art-right" alt="Digital Library">
  </section>

  <!-- ==============================================================================
       4. TÀI LIỆU NỔI BẬT (FEATURED DOCUMENTS SECTION)
       ============================================================================== -->
  <main class="featured-section">
    <h2 class="section-title">Tài liệu nổi bật</h2>

    <!-- Filter Buttons (Tabs) -->
    <div class="filter-tabs-wrapper">
      <button class="filter-tab active" data-tab="recent">Bổ sung gần đây</button>
      <button class="filter-tab" data-tab="most-viewed">Xem nhiều nhất</button>
      <button class="filter-tab" data-tab="most-downloaded">Tải xuống nhiều nhất</button>
    </div>

    <!-- 6 Document Cards Grid (Chuẩn hiển thị 100% zoom) -->
    <div class="documents-grid">

      <!-- CARD 1 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="Vietnam's human resources through 40 years of Doi Moi">
            Vietnam's human resources through 40 years of Doi Moi
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Ma, Ngoc Nga; Nguyen,...</a>
          </div>
          <p class="doc-excerpt">
            This article employs a qualitative research method to synthesize and analyze documents...
          </p>
        </div>
      </article>

      <!-- CARD 2 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="The private economic sector in Vietnam's socialist-oriented...">
            The private economic sector in Vietnam's socialist-oriented...
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Tran, Dinh Nuoi; Nguyen,...</a>
          </div>
          <p class="doc-excerpt">
            This article analyzes the role and development of the private economic sector...
          </p>
        </div>
      </article>

      <!-- CARD 3 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="Economic growth based on science, technology, and...">
            Economic growth based on science, technology, and...
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Ly, Dai Hung</a>
          </div>
          <p class="doc-excerpt">
            In Vietnam, the contribution of total factor productivity to economic growth...
          </p>
        </div>
      </article>

      <!-- CARD 4 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="Economic institutional renovation in Vietnam from 1986 to the...">
            Economic institutional renovation in Vietnam from 1986 to the...
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Nguyen, Dinh Ngan</a>
          </div>
          <p class="doc-excerpt">
            The article analyzes the process of economic institutional reform in Vietnam...
          </p>
        </div>
      </article>

      <!-- CARD 5 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="Economic corridors in Vietnam">
            Economic corridors in Vietnam
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Nguyen, Thu Phuong</a>
          </div>
          <p class="doc-excerpt">
            Developing economic corridors is a modern approach to regional economic connectivity...
          </p>
        </div>
      </article>

      <!-- CARD 6 -->
      <article class="doc-card">
        <div class="doc-thumbnail-wrap">
          <svg class="doc-cover-art" viewBox="0 0 100 130" fill="none">
            <rect width="100" height="130" fill="#94a3b8"/>
            <rect x="8" y="8" width="84" height="114" fill="#cbd5e1" rx="2"/>
            <polygon points="15,8 30,8 30,35 22.5,28 15,35" fill="#1e3a8a"/>
            <text x="50" y="65" font-size="10" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">TÀI LIỆU</text>
            <line x1="30" y1="74" x2="70" y2="74" stroke="#64748b" stroke-width="1.5"/>
            <line x1="35" y1="80" x2="65" y2="80" stroke="#94a3b8" stroke-width="1"/>
          </svg>
        </div>
        <div class="doc-content-wrap">
          <span class="doc-badge">Ấn phẩm</span>
          <h3 class="doc-title" title="Nghiên cứu chế biến kẹo dẻo từ cơm dừa non (Cocos nucifera L.)">
            Nghiên cứu chế biến kẹo dẻo từ cơm dừa non (Cocos nucifera L.)
          </h3>
          <div class="doc-meta">
            <span class="doc-year">(2026)</span>
            <a href="#author" class="doc-author">Huỳnh, Minh Hiếu; Nguyễn...</a>
          </div>
          <p class="doc-excerpt">
            Nghiên cứu chế biến kẹo dẻo từ cơm dừa non, khảo sát ảnh hưởng của nồng độ chất tạo đông...
          </p>
        </div>
      </article>

    </div>
  </main>

  <!-- ==============================================================================
       5. DANH SÁCH BỘ SƯU TẬP (COLLECTION CAROUSEL - 3 CARDS AT 100% ZOOM)
       ============================================================================== -->
  <section class="collection-section">
    <div class="collection-container">
      <h2 class="collection-title">Danh sách bộ sưu tập</h2>

      <div class="collection-carousel">

        <!-- Card 1: Central Library -->
        <div class="collection-card">
          <h4 class="collection-card-title">01. Thư viện Trung tâm ĐHQG-HCM (Central Library)</h4>
          <div class="collection-logo-box">
            <!-- Central Library Logo SVG -->
            <svg class="collection-logo-img" viewBox="0 0 120 70" fill="none">
              <path d="M20 18 L60 5 L60 65 L20 52 Z" fill="#005baa"/>
              <path d="M28 24 L52 16 L52 58 L28 50 Z" fill="#ffffff"/>
              <path d="M68 5 L108 18 L108 52 L68 65 Z" fill="#005baa"/>
              <path d="M76 16 L100 24 L100 50 L76 58 Z" fill="#ffffff"/>
              <rect x="32" y="29" width="16" height="3" fill="#005baa"/>
              <rect x="32" y="36" width="16" height="3" fill="#005baa"/>
              <rect x="32" y="43" width="16" height="3" fill="#005baa"/>
              <rect x="80" y="29" width="16" height="3" fill="#005baa"/>
              <rect x="80" y="36" width="16" height="3" fill="#005baa"/>
              <rect x="80" y="43" width="16" height="3" fill="#005baa"/>
            </svg>
          </div>
        </div>

        <!-- Card 2: UEL -->
        <div class="collection-card">
          <h4 class="collection-card-title">02. Trường Đại học Kinh tế - Luật (University of Economics...</h4>
          <div class="collection-logo-box">
            <!-- UEL Logo SVG -->
            <svg class="collection-logo-img" viewBox="0 0 100 100" fill="none">
              <circle cx="50" cy="50" r="44" stroke="#005baa" stroke-width="4" fill="#ffffff"/>
              <circle cx="50" cy="50" r="36" stroke="#0284c7" stroke-width="1.5" stroke-dasharray="3 3"/>
              <text x="50" y="58" font-size="20" font-weight="900" fill="#005baa" text-anchor="middle" font-family="sans-serif">UEL</text>
              <path d="M20 50 A30 30 0 0 1 80 50" stroke="#0284c7" stroke-width="1.5" fill="none"/>
              <path d="M25 50 A25 15 0 0 0 75 50" stroke="#0284c7" stroke-width="1.5" fill="none"/>
            </svg>
          </div>
        </div>

        <!-- Card 3: UIT -->
        <div class="collection-card">
          <h4 class="collection-card-title">03. Trường Đại học Công nghệ Thông tin (University of...</h4>
          <div class="collection-logo-box">
            <!-- UIT Logo SVG matching Image 2 -->
            <svg class="collection-logo-img" viewBox="0 0 100 100" fill="none">
              <!-- Globe structure with orbital tech ring -->
              <circle cx="50" cy="50" r="36" stroke="#005baa" stroke-width="3" fill="#ffffff"/>
              <!-- Grid lines on globe -->
              <ellipse cx="50" cy="50" rx="20" ry="36" stroke="#0284c7" stroke-width="2" fill="none"/>
              <line x1="14" y1="50" x2="86" y2="50" stroke="#0284c7" stroke-width="2"/>
              <line x1="20" y1="34" x2="80" y2="34" stroke="#0284c7" stroke-width="1.5"/>
              <line x1="20" y1="66" x2="80" y2="66" stroke="#0284c7" stroke-width="1.5"/>
              <!-- Dynamic orbital swoosh ring -->
              <ellipse cx="50" cy="50" rx="46" ry="16" transform="rotate(-30 50 50)" stroke="#005baa" stroke-width="3.5" fill="none"/>
              <circle cx="85" cy="30" r="4.5" fill="#005baa"/>
            </svg>
          </div>
        </div>

      </div>

      <!-- Carousel Pagination Dots -->
      <div class="carousel-dots">
        <span class="carousel-dot active"></span>
        <span class="carousel-dot"></span>
      </div>
    </div>
  </section>

  <!-- ==============================================================================
       6. FOOTER
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
        <!-- Stylized Central Library Emblem Vector in Cyan/White on Green -->
        <svg class="footer-logo-svg" viewBox="0 0 140 90" fill="none">
          <path d="M20 22 L65 5 L65 80 L20 63 Z" fill="#67e8f9" opacity="0.9"/>
          <path d="M30 30 L55 20 L55 72 L30 62 Z" fill="#ffffff" opacity="0.8"/>
          <path d="M75 5 L120 22 L120 63 L75 80 Z" fill="#67e8f9" opacity="0.9"/>
          <path d="M85 20 L110 30 L110 62 L85 72 Z" fill="#ffffff" opacity="0.8"/>
          <!-- Page lines -->
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
