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

//Khóa lưu danh sách yêu thích mẫu trên trình duyệt
const favoriteStorageKey = 'fol-demo-favorites-v1'

//Đọc danh sách mã sách đã lưu
function readFavorite() {
  const stored = JSON.parse(
    localStorage.getItem(favoriteStorageKey) ?? '[]'
  )

  return Array.isArray(stored)
    ? [...new Set(stored.filter(Number.isInteger))]
    : []
}

//Đồng bộ trạng thái nút với danh sách đã lưu
function updateFavoriteButton(selected) {
  favoriteButton.setAttribute('aria-pressed', String(selected))
  favoriteButton.textContent = selected
    ? 'Đã thêm vào yêu thích'
    : 'Thêm vào yêu thích'
}

//Lưu hoặc bỏ mã sách, không lưu thông tin cá nhân
if (favoriteButton) {
  const bookId = Number(favoriteButton.dataset.bookId)

  if (Number.isInteger(bookId) && bookId > 0) {
    try {
      updateFavoriteButton(readFavorite().includes(bookId))
    } catch {
      if (feedback) {
        feedback.textContent = 'Chưa đọc được danh sách yêu thích trên trình duyệt'
      }
    }

    favoriteButton.addEventListener('click', () => {
      try {
        const favorites = readFavorite()
        const selected = favorites.includes(bookId)

        const nextFavorites = selected
          ? favorites.filter((id) => id !== bookId)
          : [...favorites, bookId]

        localStorage.setItem(
          favoriteStorageKey,
          JSON.stringify(nextFavorites)
        )

        updateFavoriteButton(!selected)

        if (feedback) {
          feedback.textContent = 'Đã cập nhật yêu thích trên trình duyệt này'
        }
      } catch {
        if (feedback) {
          feedback.textContent = 'Không lưu được yêu thích. Hãy kiểm tra quyền lưu trữ của trình duyệt'
        }
      }
    })
  }
}