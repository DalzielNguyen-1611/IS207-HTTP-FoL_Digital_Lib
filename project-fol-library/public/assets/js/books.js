/**
 * Thư viện Khoa Văn học - USSH VNU-HANOI
 * Quản lý tương tác Tra cứu tài liệu (Server-Side Pagination & Filtering)
 */

document.addEventListener('DOMContentLoaded', () => {
    const catalogContent = document.getElementById('catalog-content')
    const searchForm = document.getElementById('catalog-search')
    const searchInput = searchForm?.querySelector('input[name="q"]')
    const filtersForm = document.getElementById('catalog-filters-form')
    const filterSelects = filtersForm?.querySelectorAll('select') || []
    const resetBtn = document.getElementById('catalog-reset')
    const paginationLinks = document.querySelectorAll('.catalog-pagination .pagination-btn')

    // Bật hiệu ứng chờ tải dữ liệu (Loading indicator)
    function showLoading() {
        if (catalogContent) {
            catalogContent.classList.add('is-loading')
        }
    }

    // 1. Tự động gửi yêu cầu lên Server khi thay đổi bất kỳ dropdown bộ lọc nào
    filterSelects.forEach((select) => {
        select.addEventListener('change', () => {
            showLoading()
            // Loại bỏ các input rỗng để URL gọn gàng hơn
            const formData = new FormData(filtersForm)
            const params = new URLSearchParams()
            
            for (const [key, value] of formData.entries()) {
                if (value && value.trim() !== '' && !(key === 'sort' && value === 'id_asc')) {
                    params.set(key, value.trim())
                }
            }

            window.location.href = '/books' + (params.toString() ? '?' + params.toString() : '')
        })
    })

    // 2. Gửi yêu cầu tìm kiếm lên Server khi nhấn Tìm kiếm hoặc Enter
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault()
            showLoading()

            const formData = new FormData(searchForm)
            const params = new URLSearchParams()

            for (const [key, value] of formData.entries()) {
                if (value && value.trim() !== '' && !(key === 'sort' && value === 'id_asc')) {
                    params.set(key, value.trim())
                }
            }

            window.location.href = '/books' + (params.toString() ? '?' + params.toString() : '')
        })
    }

    // 3. Hiệu ứng loading khi chuyển trang
    paginationLinks.forEach((link) => {
        link.addEventListener('click', () => {
            showLoading()
        })
    })

    // 4. Hiệu ứng loading khi làm mới bộ lọc
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            showLoading()
        })
    }
})