//Các thành phần tìm kiếm sách mẫu
const searchForm = document.querySelector('#search')
const searchInput = searchForm?.querySelector('input')
const bookCards = document.querySelectorAll('.book-card')
const searchSummary = document.querySelector('#search-summary')
const searchEmpty = document.querySelector('#search-empty')
const featuredSection = document.querySelector('.featured')

//Chuẩn hóa để tìm kiếm không phân biệt chữ hoa và dấu tiếng Việt
function normalizeText(value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .toLowerCase()
        .trim()
}

//Lọc theo tên sách, tác giả và chuyên ngành trong dữ liệu mẫu
if (searchForm && searchInput && searchSummary && searchEmpty) {
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault()

        const keyword = normalizeText(searchInput.value)
        let matchedCount = 0

        bookCards.forEach((card) => {
            const searchableText = normalizeText(card.dataset.search ?? '')
            const matches = searchableText.includes(keyword)

            card.hidden = !matches

            if (matches) {
                matchedCount += 1
            }
        })

        searchSummary.hidden = false
        searchSummary.textContent = keyword
            ? `Tìm thấy ${matchedCount} sách trong danh sách mẫu.`
            : `Đang hiển thị toàn bộ ${matchedCount} sách mẫu.`

        searchEmpty.hidden = matchedCount !== 0

        featuredSection?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    })
}