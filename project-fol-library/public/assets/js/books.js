//Thành phần tìm kiếm và lọc tài liệu
const catalogForm = document.querySelector('#catalog-search')
const catalogInput = catalogForm?.querySelector('input')
const catalogItems = document.querySelectorAll('.catalog-item')
const catalogCount = document.querySelector('#catalog-count')
const catalogEmpty = document.querySelector('#catalog-empty')
const catalogFilters = document.querySelectorAll('[data-filter')
const catalogReset = document.querySelector('#catalog-reset')

//Chuẩn hóa chữ hoa, chữ thường và dấu Tiếng Việt
function normalizeText(value) {
    return value
        .normalize('NFD')
        .replace(/[u300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .toLowerCase()
        .trim()
}

//Kết hợp từ khóa với tất cả bộ lọc đang chọn
function applyFilters() {
    const keyword = normalizeText(catalogInput.value)
    let matchedCount = 0

    catalogItems.forEach((item) => {
        const matchesKeyword = normalizeText (
            item.dataset.search ?? ''
        ).includes(keyword)

        const matchesFilters = Array.from(catalogFilters).every((Select) => {
            const field = Select.dataset.filter
            const selectedValue = Select.value

            return selectedValue === '' || item.dataset[field] === selectedValue
        })

        const matches = matchesKeyword && matchesFilters
        item.hidden = !matches

        if (matches) {
            matchedCount += 1
        }
    })

    catalogCount.textContent = `${matchedCount} tài liệu phù hợp`
    catalogEmpty.hidden = matchedCount !== 0
}

//Đăng kí tương tác khi các thành phần đã tồn tại
if (catalogForm && catalogInput && catalogCount && catalogEmpty) {
    catalogForm.addEventListener('submit', (event) => {
        event.preventDefault()
        applyFilters()
    })

    catalogFilters.forEach((select) => {
        select.addEventListener('change', applyFilters)
    })

    //Đưa danh sách về trạng thái ban đầu
    catalogReset?.addEventListener('click', () => {
        catalogInput.value = ''

        catalogFilters.forEach((select) => {
            select.value = ''
        })

        applyFilters()
    })
}