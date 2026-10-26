// Thành phần giao diện đăng nhập mẫu
const loginForm = document.querySelector('#login-form')
const roleSelect = document.querySelector('#login-role')
const passwordInput = document.querySelector('#login-password')
const togglePassword = document.querySelector('#toggle-password')
const loginFeedback = document.querySelector('#login-feedback')

// Ánh xạ vai trò tới đường dẫn tương ứng
const roleDestinations = {
    student: '/profile',
    librarian: '/librarian',
    admin: '/admin/settings'
}

// Chuyển hướng tới giao diện theo vai trò
function navigateToRole(role) {
    const destination = roleDestinations[role] || '/profile'
    window.location.href = destination
}

// Tự động chuyển trang khi chọn vai trò trong danh sách
if (roleSelect) {
    roleSelect.addEventListener('change', () => {
        const selectedRole = roleSelect.value
        if (selectedRole && roleDestinations[selectedRole]) {
            navigateToRole(selectedRole)
        }
    })
}

// Xử lý khi nhấn nút "Vào xem giao diện ngay"
if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault()
        const selectedRole = roleSelect ? roleSelect.value : 'student'
        navigateToRole(selectedRole)
    })
}

// Hiện hoặc ẩn mật khẩu nếu người dùng nhập
if (passwordInput && togglePassword) {
    togglePassword.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password'

        passwordInput.type = showPassword ? 'text' : 'password'
        togglePassword.textContent = showPassword ? 'Ẩn' : 'Hiện'
        togglePassword.setAttribute('aria-pressed', String(showPassword))
    })
}