//Các nút điều hướng và nội dung tương ứng
const sectionButtons = document.querySelectorAll('[data-section]')
const profilePanels = document.querySelectorAll('.profile-panel')

//Hiển thị mục được chọn, mặc định về thông tin nếu mã không hợp lệ
function showProfileSection(section) {
    const validSection = Array.from(sectionButtons).some(
        (button) => button.dataset.section === section
    )

    const selectedSection = validSection ? section : 'information'

    sectionButtons.forEach((button) => {
        const selected = button.dataset.section === selectedSection

        if (selected) {
            button.setAttribute('aria-current', 'true')
        } else {
            button.removeAttribute('aria-current')
        }
    })

    profilePanels.forEach((panel) => {
        panel.hidden = panel.id !== `panel-${selectedSection}`
    })
}

//Đổi tab và cập nhật địa chỉ mà không tải lại hoặc cuộn trang
sectionButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const section = button.dataset.section

        history.replaceState(
            history.state,
            '',
            `${location.pathname}${location.search}#${section}`
        )

        showProfileSection(section)
    })
})

//Khôi phục tab từ địa chỉ khi mở hoặc tải lại trang
showProfileSection(location.hash.slice(1))

//Cập nhật nếu phần hash của địa chỉ thay đổi
window.addEventListener('hashchange', () => {
    showProfileSection(location.hash.slice(1))
})

// Thành phần biểu mẫu hồ sơ.
const profileForm = document.querySelector('#profile-form')
const profileFeedback = document.querySelector('#profile-feedback')
const editableTextInputs = document.querySelectorAll(
  '#profile-name, #profile-major'
)

// Không chấp nhận nội dung chỉ chứa khoảng trắng.
function validateProfile() {
  editableTextInputs.forEach((input) => {
    input.setCustomValidity(
      input.value.trim() ? '' : 'Bạn hãy nhập thông tin.'
    )
  })
}

if (profileForm && profileFeedback) {
  profileForm.addEventListener('input', () => {
    validateProfile()
    profileFeedback.textContent = ''
  })

  // Mô phỏng lưu, không gửi hoặc lưu dữ liệu.
  profileForm.addEventListener('submit', (event) => {
    event.preventDefault()
    validateProfile()

    if (!profileForm.reportValidity()) return

    profileFeedback.textContent =
      'Thông tin hợp lệ trong bản thử. Chưa lưu thay đổi vào hệ thống.'
  })

  // Khôi phục giá trị ban đầu.
  profileForm.addEventListener('reset', () => {
    editableTextInputs.forEach((input) => {
      input.setCustomValidity('')
    })

    profileFeedback.textContent = ''
  })
}

// Danh sách yêu thích đồng bộ với trang chi tiết sách qua localStorage.
const favoritesContainer = document.querySelector('.profile-favorites')
const favoritesEmpty = document.querySelector('#favorites-empty')
const favoritesFeedback = document.querySelector('#favorites-feedback')
const profileFavoriteKey = 'fol-demo-favorites-v1'

function readProfileFavorites() {
    const stored = JSON.parse(localStorage.getItem(profileFavoriteKey) ?? '[]')
    if (!Array.isArray(stored)) return []

    const unique = new Map()
    stored.forEach((item) => {
        const book = item && typeof item === 'object'
            ? { id: String(item.id ?? '').trim(), title: String(item.title ?? ''), author: String(item.author ?? ''), cover: String(item.cover ?? '') }
            : { id: String(item ?? '').trim(), title: '', author: '' }
        if (book.id) unique.set(book.id, book)
    })
    return [...unique.values()]
}

function createLocalFavoriteCard(book) {
    const article = document.createElement('article')
    article.className = 'catalog-item profile-favorite'
    article.dataset.favoriteId = book.id
    article.dataset.localFavoriteCard = 'true'

    const coverBox = document.createElement('div')
    coverBox.className = 'catalog-cover-box'
    const cover = document.createElement('img')
    cover.src = book.cover || '/assets/images/default-book-cover.jpg'
    cover.alt = `Bìa sách ${book.title}`
    cover.loading = 'lazy'
    cover.addEventListener('error', () => { cover.src = '/assets/images/default-book-cover.jpg' }, { once: true })
    coverBox.append(cover)

    const info = document.createElement('div')
    info.className = 'catalog-main-info'
    const heading = document.createElement('h2')
    heading.className = 'book-title'
    const link = document.createElement('a')
    link.className = 'catalog-title-link'
    link.href = `/book-detail?id=${encodeURIComponent(book.id)}`
    link.textContent = book.title || book.id
    heading.append(link)
    info.append(heading)

    const author = document.createElement('p')
    author.className = 'book-author'
    author.textContent = book.author || 'Chưa rõ tác giả'
    info.append(author)

    const footer = document.createElement('div')
    footer.className = 'catalog-card-footer'
    const meta = document.createElement('div')
    meta.className = 'book-meta'
    const code = document.createElement('span')
    code.textContent = book.id
    const separator = document.createElement('span')
    separator.className = 'meta-dot'
    separator.textContent = '·'
    const status = document.createElement('span')
    status.textContent = 'Sách yêu thích'
    meta.append(code, separator, status)
    footer.append(meta)
    info.append(footer)

    const remove = document.createElement('button')
    remove.className = 'profile-reset'
    remove.type = 'button'
    remove.dataset.removeFavorite = book.id
    remove.setAttribute('aria-label', `Bỏ yêu thích ${book.title || book.id}`)
    remove.textContent = 'Bỏ yêu thích'

    article.append(coverBox, info, remove)
    return article
}

function renderProfileFavorites() {
    if (!favoritesContainer || !favoritesEmpty || !favoritesFeedback) return

    try {
        const favorites = readProfileFavorites()
        const favoriteIds = new Set(favorites.map((book) => book.id))
        const serverCards = favoritesContainer.querySelectorAll('.profile-favorite:not([data-local-favorite-card])')
        const availableIds = new Set()
        let visibleCount = 0

        serverCards.forEach((card) => {
            const id = String(card.dataset.favoriteId || '')
            const selected = favoriteIds.has(id)
            card.hidden = !selected
            if (selected) {
                visibleCount += 1
                availableIds.add(id)
            }
        })

        favoritesContainer.querySelectorAll('[data-local-favorite-card]').forEach((card) => card.remove())
        favorites.forEach((book) => {
            if (!book.title || availableIds.has(book.id)) return
            favoritesContainer.append(createLocalFavoriteCard(book))
            availableIds.add(book.id)
            visibleCount += 1
        })

        favoritesEmpty.hidden = visibleCount !== 0
    } catch {
        favoritesContainer.querySelectorAll('.profile-favorite').forEach((card) => { card.hidden = true })
        favoritesEmpty.hidden = true
        favoritesFeedback.textContent = 'Không đọc được danh sách yêu thích trên trình duyệt'
    }
}

favoritesContainer?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-remove-favorite]')
    if (!button) return

    try {
        const bookId = String(button.dataset.removeFavorite || '')
        const nextFavorites = readProfileFavorites().filter((book) => book.id !== bookId)
        localStorage.setItem(profileFavoriteKey, JSON.stringify(nextFavorites))
        renderProfileFavorites()
        favoritesFeedback.textContent = 'Đã bỏ sách khỏi yêu thích'
        favoritesFeedback.setAttribute('tabindex', '-1')
        favoritesFeedback.focus()
    } catch {
        favoritesFeedback.textContent = 'Không cập nhật được yêu thích'
    }
})

renderProfileFavorites()
window.addEventListener('pageshow', renderProfileFavorites)
window.addEventListener('storage', (event) => {
    if (event.key === profileFavoriteKey || event.key === null) renderProfileFavorites()
})