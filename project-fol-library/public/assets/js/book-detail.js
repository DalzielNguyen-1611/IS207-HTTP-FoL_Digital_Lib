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

// Đọc sách yêu thích, chấp nhận cả mã cũ dạng chuỗi và dữ liệu sách đầy đủ.
function readFavorite() {
  const stored = JSON.parse(
    localStorage.getItem(favoriteStorageKey) ?? '[]'
  )

  if (!Array.isArray(stored)) return []

  const unique = new Map()
  stored.forEach((item) => {
    const book = item && typeof item === 'object'
      ? { id: String(item.id ?? '').trim(), title: String(item.title ?? ''), author: String(item.author ?? ''), cover: String(item.cover ?? '') }
      : { id: String(item ?? '').trim(), title: '', author: '', cover: '' }
    if (book.id) unique.set(book.id, book)
  })
  return [...unique.values()]
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
  const bookId = String(favoriteButton.dataset.bookId || '').trim()
  const currentBook = {
    id: bookId,
    title: favoriteButton.dataset.bookTitle || '',
    author: favoriteButton.dataset.bookAuthor || '',
    cover: favoriteButton.dataset.bookCover || '/assets/images/default-book-cover.jpg',
  }

  if (bookId !== '') {
    try {
      updateFavoriteButton(readFavorite().some((book) => book.id === bookId))
    } catch {
      if (feedback) {
        feedback.textContent = 'Chưa đọc được danh sách yêu thích trên trình duyệt'
      }
    }

    favoriteButton.addEventListener('click', () => {
      try {
        const favorites = readFavorite()
        const selected = favorites.some((book) => book.id === bookId)
        const nextFavorites = selected
          ? favorites.filter((book) => book.id !== bookId)
          : [...favorites.filter((book) => book.id !== bookId), currentBook]

        localStorage.setItem(favoriteStorageKey, JSON.stringify(nextFavorites))
        updateFavoriteButton(!selected)

        if (feedback) {
          feedback.textContent = !selected
            ? 'Đã thêm sách vào yêu thích trên trình duyệt này'
            : 'Đã bỏ sách khỏi yêu thích trên trình duyệt này'
        }
      } catch {
        if (feedback) {
          feedback.textContent = 'Không lưu được yêu thích. Hãy kiểm tra quyền lưu trữ của trình duyệt'
        }
      }
    })
  }
}