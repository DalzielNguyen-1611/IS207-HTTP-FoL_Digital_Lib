// Các thành phần cấu hình mẫu.
const settingsForm = document.querySelector('#settings-form')
const shelvesInput = document.querySelector('#settings-shelves')
const cellsInput = document.querySelector('#settings-cells')
const fineInput = document.querySelector('#settings-fine')
const capacityText = document.querySelector('#settings-capacity')
const emailEnabled = document.querySelector('#settings-email-enabled')
const emailFields = document.querySelector('#settings-email-fields')
const senderName = document.querySelector('#settings-sender-name')
const settingsFeedback = document.querySelector('#settings-feedback')

// Xem trước tổng số ô theo cấu hình đang nhập.
function updateCapacityPreview() {
  if (!shelvesInput.validity.valid || !cellsInput.validity.valid) {
    capacityText.textContent = 'Nhập số kệ và số ô hợp lệ để xem tổng.'
    return
  }

  const shelves = Number(shelvesInput.value)
  const cells = Number(cellsInput.value)

  capacityText.textContent =
    `Sơ đồ mẫu: ${shelves} kệ × ${cells} ô = ${shelves * cells} ô.`
}

// Bật/tắt các trường cấu hình email.
function updateEmailFields() {
  emailFields.disabled = !emailEnabled.checked
}

// Không nhận tên người gửi chỉ gồm khoảng trắng.
function validateSenderName() {
  senderName.setCustomValidity(
    emailEnabled.checked && !senderName.value.trim()
      ? 'Bạn hãy nhập tên người gửi.'
      : ''
  )
}

// Cập nhật biểu mẫu khi nhập.
settingsForm.addEventListener('input', () => {
  updateCapacityPreview()
  updateEmailFields()
  validateSenderName()
  settingsFeedback.textContent = ''
})

// Lưu chỉ hiển thị kết quả thử, không gọi backend hoặc gửi email.
settingsForm.addEventListener('submit', (event) => {
  event.preventDefault()
  validateSenderName()

  if (!settingsForm.reportValidity()) return

  const fine = Number(fineInput.value).toLocaleString('vi-VN')
  const emailStatus = emailEnabled.checked ? 'bật' : 'tắt'

  settingsFeedback.textContent =
    `Biểu mẫu hợp lệ: ${shelvesInput.value} kệ, ` +
    `${cellsInput.value} ô/kệ, phí ${fine}đ/ngày, ` +
    `email ${emailStatus}. ` +
    'Chưa lưu cấu hình thật hoặc gửi email.'
})

// Chờ trình duyệt reset giá trị rồi cập nhật phần xem trước.
settingsForm.addEventListener('reset', () => {
  senderName.setCustomValidity('')

  setTimeout(() => {
    updateCapacityPreview()
    updateEmailFields()
    settingsFeedback.textContent = 'Đã khôi phục các giá trị mẫu ban đầu.'
  }, 0)
})

// Trạng thái khi mở trang.
updateCapacityPreview()
updateEmailFields()