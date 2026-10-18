//Thành phần giao diện đăng nhập mẫu
const loginForm = document.querySelector('#login-form')
const roleSelect = document.querySelector('#login-role')
const passwordInput = document.querySelector('#login-password')
const togglePassword = document.querySelector('#toggle-password')
const loginFeedback = document.querySelector('#login-feedback')

//Hiện hoặc ẩn mật khẩu
if (passwordInput && togglePassword) {
    togglePassword.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password'

        passwordInput.type = showPassword ? 'text' : 'password'
        togglePassword.textContent = showPassword ? 'Ẩn' : 'Hiện'
        togglePassword.setAttribute('aria-pressed', String(showPassword))
    })
}

//Minh họa thao tác đăng nhập, không xác thực hoặc lưu dữ liệu
if (loginForm && passwordInput && loginFeedback) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault()

        const roleLabel = roleSelect.selectedOptions[0].textContent.trim()

        loginFeedback.textContent = `Đã thử biểu mẫu cho vai trò ${roleLabel}.` + `Chưa xác thực tài khoản hoặc cấp quyền truy cập.`

        passwordInput.value = ''
        passwordInput.type = 'password'

        if (togglePassword) {
            togglePassword.textContent = 'Hiện'
            togglePassword.setAttribute('aria-pressed', 'false')
        }
    })
}