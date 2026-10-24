// Lưu trữ đặt chỗ mẫu, chưa gắn với tài khoản.
const roomStorageKey = 'fol-demo-bookings-v1'
const slotButtons = document.querySelectorAll('.room-slot')
const bookingDialog = document.querySelector('#booking-dialog')
const bookingDescription = document.querySelector('#booking-description')
const roomFeedback = document.querySelector('#room-feedback')

let selectedSlot = null

// Đọc các lịch đã đặt.
function readRoomBookings() {
  const records = JSON.parse(
    localStorage.getItem(roomStorageKey) ?? '[]'
  )

  if (!Array.isArray(records)) {
    throw new Error('Dữ liệu lịch không hợp lệ')
  }

  return records
}

// Khôi phục số ghế và trạng thái từ dữ liệu mẫu.
function refreshRoomSlots() {
  try {
    const bookings = readRoomBookings()

    slotButtons.forEach((slot) => {
      const booked = bookings.some(
        (booking) =>
          booking.id === slot.dataset.slot &&
          booking.status === 'active'
      )

      const free = Number(slot.dataset.free)
      const expired = Date.parse(slot.dataset.start) <= Date.now()

      slot.disabled = booked || free <= 0 || expired
      slot.classList.toggle('is-booked', booked)

      slot.querySelector('strong').textContent = booked
        ? 'Đã đặt thử'
        : expired
          ? 'Đã qua giờ'
          : free > 0
            ? `${free} / 40 chỗ`
            : 'Hết chỗ'

      slot.querySelector('span').textContent = booked
        ? `Còn ${Math.max(0, free - 1)} chỗ`
        : expired || free <= 0
          ? 'Chọn ca khác'
          : 'Đặt chỗ +'

      slot.setAttribute(
        'aria-label',
        `${slot.dataset.label} · ${slot.querySelector('strong').textContent}`
      )
    })
  } catch {
    roomFeedback.textContent =
      'Không đọc được lịch mẫu. Chưa thể đặt thêm chỗ.'
    slotButtons.forEach((slot) => {
      slot.disabled = true
    })
  }
}

if (bookingDialog && bookingDescription && roomFeedback) {
  // Mở hộp xác nhận.
  slotButtons.forEach((slot) => {
    slot.addEventListener('click', () => {
      if (slot.disabled) return

      selectedSlot = slot
      bookingDialog.returnValue = ''
      bookingDescription.textContent = slot.dataset.label
      bookingDialog.showModal()
    })
  })

  // Lưu lịch khi xác nhận.
  bookingDialog.addEventListener('close', () => {
    const slot = selectedSlot
    selectedSlot = null

    if (!slot || bookingDialog.returnValue !== 'confirm') {
      slot?.focus()
      return
    }

    try {
      const bookings = readRoomBookings()
      const alreadyBooked = bookings.some(
        (booking) =>
          booking.id === slot.dataset.slot &&
          booking.status === 'active'
      )

      const startsAt = Date.parse(slot.dataset.start)

      if (
        alreadyBooked ||
        !Number.isFinite(startsAt) ||
        startsAt <= Date.now() ||
        Number(slot.dataset.free) <= 0
      ) {
        roomFeedback.textContent =
          'Ca này không còn đủ điều kiện đặt.'
        refreshRoomSlots()
        return
      }

      bookings.push({
        id: slot.dataset.slot,
        label: slot.dataset.label,
        start: slot.dataset.start,
        status: 'active',
        createdAt: new Date().toISOString(),
      })

      localStorage.setItem(roomStorageKey, JSON.stringify(bookings))
      refreshRoomSlots()

      roomFeedback.textContent =
        'Đã lưu lịch mẫu. Bạn có thể xem tại Cá nhân → Phòng đọc.'
    } catch {
      roomFeedback.textContent =
        'Không lưu được lịch trên trình duyệt.'
    }

    roomFeedback.setAttribute('tabindex', '-1')
    roomFeedback.focus()
  })

  // Khôi phục lịch khi mở trang hoặc quay lại từ profile.
  window.addEventListener('pageshow', refreshRoomSlots)
  window.addEventListener('storage', (event) => {
    if (event.key === roomStorageKey || event.key === null) {
      refreshRoomSlots()
    }
  })

  refreshRoomSlots()
}