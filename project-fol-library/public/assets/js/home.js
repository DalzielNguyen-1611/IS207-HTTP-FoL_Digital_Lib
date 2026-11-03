// Thanh tìm kiếm trang chủ chuyển truy vấn sang danh mục sách thật.
const searchForm = document.querySelector('#search')
if (searchForm) {
    searchForm.action = '/books'
    searchForm.method = 'get'
}