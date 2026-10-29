// Xử lý Giao diện và Biểu mẫu Đăng nhập Xác thực Thật
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('#login-form')
    const emailInput = document.querySelector('#login-email')
    const passwordInput = document.querySelector('#login-password')
    const togglePassword = document.querySelector('#toggle-password')
    const loginFeedback = document.querySelector('#login-feedback')
    const sampleRoleButtons = document.querySelectorAll('.sample-role-btn')

    // Tự động điền tài khoản mẫu khi bấm vào các nút gợi ý
    sampleRoleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const email = button.getAttribute('data-email')
            const password = button.getAttribute('data-password')

            if (emailInput && passwordInput) {
                emailInput.value = email
                passwordInput.value = password

                // Hiệu ứng phản hồi trực quan
                emailInput.style.borderColor = '#005baa'
                passwordInput.style.borderColor = '#005baa'
                setTimeout(() => {
                    emailInput.style.borderColor = ''
                    passwordInput.style.borderColor = ''
                }, 800)

                if (loginFeedback) {
                    loginFeedback.textContent = `Đã chọn: ${email}. Nhấn "Đăng nhập hệ thống" để xác thực qua Backend.`
                }
            }
        })
    })

    // Hiện / ẩn mật khẩu
    if (passwordInput && togglePassword) {
        togglePassword.addEventListener('click', () => {
            const showPassword = passwordInput.type === 'password'
            passwordInput.type = showPassword ? 'text' : 'password'
            togglePassword.textContent = showPassword ? 'Ẩn' : 'Hiện'
            togglePassword.setAttribute('aria-pressed', String(showPassword))
        })
    }

    // Khi gửi biểu mẫu: hiển thị trạng thái đang xử lý xác thực
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const submitBtn = document.querySelector('#submit-login-btn')
            if (submitBtn) {
                submitBtn.disabled = true
                submitBtn.textContent = 'Đang xác thực qua máy chủ...'
            }
        })
    }
})