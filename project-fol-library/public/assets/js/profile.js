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

//Các thành phần danh sách yêu thích mẫu
const favoriteCards = document.querySelectorAll('[data-favorite-id]')
const favoritesEmpty = document.querySelector('#favorites-empty')
const favoritesFeedback = document.querySelector('#favorites-feedback')
const profileFavoriteKey = 'fol-demo-favorites-v1'

//Đọc mã sách yêu thích đã lưu
function readProfileFavorites() {
    const stored = JSON.parse(
        localStorage.getItem(profileFavoriteKey) ?? '[]'
    )

    return Array.isArray(stored)
        ? [...new Set(stored.filter(Number.isInteger))]
        : []
}

//Hiển thị những danh sách có mã trong danh sách yêu thích
function renderProfileFavorites() {
    if (!favoritesEmpty || !favoritesFeedback) return

    try {
        const favorites = readProfileFavorites()
        let  visibleCount = 0

        favoriteCards.forEach((card) => {
            const selected = favorites.includes(
                Number(card.dataset.favoriteId)
            )

            card.hidden = !selected

            if (selected) visibleCount += 1
        })

        favoritesEmpty.hidden = visibleCount !== 0
    } catch {
        favoriteCards.forEach((card) => {
            card.hidden = true
        })

        favoritesEmpty.hidden = true
        favoritesFeedback.textContent = 'Không đọc được danh sách yêu thích trên trình duyệt'
    }
}

//Bỏ một sách khỏi danh sách
document.querySelectorAll('[data-remove-favorite]').forEach((button) => {
    button.addEventListener('click', () => {
        try {
            const bookId = Number(button.dataset.removeFavorite)
            const nextFavorites = readProfileFavorites().filter (
                (id) => id !== bookId
            )

            localStorage.setItem(
                profileFavoriteKey,
                JSON.stringify(nextFavorites)
            )

            renderProfileFavorites()

            if (favoritesFeedback) {
                favoritesFeedback.textContent = 'Đã bỏ sách khỏi yêu thích'
                favoritesFeedback.setAttribute('tabindex', '-1')
                favoritesFeedback.focus()
            }
        } catch {
            if (favoritesFeedback) {
                favoritesFeedback.textContent = 'Không cập nhật được yêu thích'
            }
        }
    })
})

//Đọc lần đầu và cập nhật khi quay lại từ trang khác
renderProfileFavorites()
window.addEventListener('pageshow', renderProfileFavorites)

//Đồng bộ khi danh sách thay đổi ở tab trình duyệt khác
window.addEventListener('storage', (event) => {
    if (event.key === profileFavoriteKey || event.key === null) {
        renderProfileFavorites()
    }
})