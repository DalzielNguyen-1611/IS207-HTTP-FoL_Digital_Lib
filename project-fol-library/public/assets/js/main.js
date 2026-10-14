/**
 * THƯ VIỆN TRUNG TÂM - ĐẠI HỌC QUỐC GIA TP.HCM
 * Client-side script cho tương tác Tab, Bộ sưu tập và Tìm kiếm
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Xử lý chuyển đổi Tab "Tài liệu nổi bật"
  const filterTabs = document.querySelectorAll('.filter-tab');
  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  // 2. Xử lý chấm trượt Carousel "Danh sách bộ sưu tập"
  const dots = document.querySelectorAll('.carousel-dot');
  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      dots.forEach(d => d.classList.remove('active'));
      dot.classList.add('active');
    });
  });

  // 3. Xử lý nút tìm kiếm
  const searchBtn = document.querySelector('.search-btn');
  const searchInput = document.querySelector('.search-input');
  if (searchBtn && searchInput) {
    searchBtn.addEventListener('click', () => {
      const query = searchInput.value.trim();
      if (query) {
        console.log('Tìm kiếm từ khóa:', query);
      } else {
        searchInput.focus();
      }
    });

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        searchBtn.click();
      }
    });
  }

  // 4. Xử lý mở rộng / thu gọn danh mục đơn vị bộ sưu tập (Accordion Tree)
  const communityHeaders = document.querySelectorAll('.community-item-header');
  communityHeaders.forEach(header => {
    header.addEventListener('click', (e) => {
      // Nếu không nhấp trực tiếp vào liên kết bên trong, toggle trạng thái
      const item = header.closest('.community-item');
      if (item) {
        item.classList.toggle('open');
      }
    });
  });
});

