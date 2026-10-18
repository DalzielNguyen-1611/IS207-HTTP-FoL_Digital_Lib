// Các nút thao tác trên trang chi tiết.
const borrowButton = document.querySelector('#borrow-demo')
const favoriteButton = document.querySelector('#favorite-demo')
const feedback = document.querySelector('#detail-feedback')

// Minh họa thao tác mượn, không tạo phiếu thật.
borrowButton?.addEventListener('click', () => {
  if (!feedback) return

  feedback.textContent =
    'Bạn vừa thử thao tác đăng ký mượn. Chưa có phiếu mượn nào được tạo.'
})

// Minh họa bật/tắt yêu thích, chỉ giữ trong lần mở trang hiện tại.
favoriteButton?.addEventListener('click', () => {
  const selected = favoriteButton.getAttribute('aria-pressed') === 'true'
  const nextSelected = !selected

  favoriteButton.setAttribute('aria-pressed', String(nextSelected))
  favoriteButton.textContent = nextSelected
    ? 'Đã chọn yêu thích'
    : 'Thêm vào yêu thích'

  if (feedback) {
    feedback.textContent =
      'Trạng thái yêu thích chỉ là bản thử và sẽ đặt lại khi tải lại trang.'
  }
})