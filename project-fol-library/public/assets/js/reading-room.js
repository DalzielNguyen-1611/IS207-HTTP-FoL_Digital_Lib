// Các thành phần của lịch và hộp xác nhận.
const slotButtons = document.querySelectorAll('.room-slot')
const bookingDialog = document.querySelector('#booking-dialog')
const bookingDescription = document.querySelector('#booking-description')
const roomFeedback = document.querySelector('#room-feedback')

let selectedSlot = null

if (bookingDialog && bookingDescription && roomFeedback) {
  // Mở xác nhận cho ca còn chỗ.
  slotButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (button.disabled) return

      selectedSlot = button
      bookingDialog.returnValue = ''
      bookingDescription.textContent = button.dataset.label
      bookingDialog.showModal()
    })
  })

  // Chỉ cập nhật số ghế khi người dùng xác nhận.
  bookingDialog.addEventListener('close', () => {
    const slot = selectedSlot
    selectedSlot = null

    if (!slot || bookingDialog.returnValue !== 'confirm') {
      slot?.focus()
      return
    }

    const freeSeats = Number(slot.dataset.free)

    if (!Number.isInteger(freeSeats) || freeSeats <= 0 || slot.disabled) {
      return
    }

    const remaining = freeSeats - 1

    slot.dataset.free = String(remaining)
    slot.classList.add('is-booked')
    slot.disabled = true
    slot.querySelector('strong').textContent = 'Đã đặt thử'
    slot.querySelector('span').textContent = `Còn ${remaining} chỗ`
    slot.setAttribute('aria-label', `${slot.dataset.label} · Đã đặt thử`)

    roomFeedback.textContent =
      `Đã mô phỏng đặt chỗ: ${slot.dataset.label}. ` +
      'Tải lại trang sẽ đặt lại dữ liệu mẫu.'

    // Chuyển tiêu điểm tới thông báo vì ca đã đặt bị vô hiệu hóa.
    roomFeedback.setAttribute('tabindex', '-1')
    roomFeedback.focus()
  })
}