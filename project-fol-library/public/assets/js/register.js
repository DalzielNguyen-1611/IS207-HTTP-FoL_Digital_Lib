// Các thành phần biểu mẫu đăng ký.
const registerForm = document.querySelector('#register-form')
const nameInput = document.querySelector('#register-name')
const codeInput = document.querySelector('#register-code')
const passwordInput = document.querySelector('#register-password')
const confirmInput = document.querySelector('#register-confirm')
const toggleButton = document.querySelector('#register-toggle')
const feedback = document.querySelector('#register-feedback')

// Hiện hoặc ẩn mật khẩu.
if (passwordInput && toggleButton) {
  toggleButton.addEventListener('click', () => {
    const showPassword = passwordInput.type === 'password'

    passwordInput.type = showPassword ? 'text' : 'password'
    toggleButton.textContent = showPassword ? 'Ẩn' : 'Hiện'
    toggleButton.setAttribute('aria-pressed', String(showPassword))
  })
}

// Kiểm tra các trường chỉ chứa khoảng trắng và mật khẩu xác nhận.
function validateRegistration() {
  nameInput.setCustomValidity(
    nameInput.value.trim() ? '' : 'Bạn hãy nhập họ và tên.'
  )

  codeInput.setCustomValidity(
    codeInput.value.trim() ? '' : 'Bạn hãy nhập mã sinh viên.'
  )

  confirmInput.setCustomValidity(
    confirmInput.value === passwordInput.value
      ? ''
      : 'Mật khẩu nhập lại chưa khớp.'
  )
}

// Chỉ minh họa biểu mẫu, không gửi hoặc lưu thông tin.
if (
  registerForm &&
  nameInput &&
  codeInput &&
  passwordInput &&
  confirmInput &&
  feedback
) {
  registerForm.addEventListener('input', () => {
    validateRegistration()
    feedback.textContent = ''
  })

  registerForm.addEventListener('submit', (event) => {
    event.preventDefault()
    validateRegistration()

    if (!registerForm.reportValidity()) {
      return
    }

    feedback.textContent =
      'Biểu mẫu hợp lệ trong bản thử. Chưa tạo tài khoản ' +
      'hoặc xác minh email sinh viên.'

    passwordInput.value = ''
    confirmInput.value = ''
    confirmInput.setCustomValidity('')
    passwordInput.type = 'password'

    if (toggleButton) {
      toggleButton.textContent = 'Hiện'
      toggleButton.setAttribute('aria-pressed', 'false')
    }
  })
}