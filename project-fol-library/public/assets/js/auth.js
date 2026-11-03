// Xử lý Giao diện và Biểu mẫu Đăng nhập Xác thực Thật
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('#login-form')
    const passwordInput = document.querySelector('#login-password')


    // Khi gửi biểu mẫu: hiển thị trạng thái đang xử lý xác thực
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const submitBtn = document.querySelector('#submit-login-btn')
            if (submitBtn) {
                submitBtn.disabled = true
                submitBtn.textContent = 'Đang đăng nhập...'
            }
        })
    }
})