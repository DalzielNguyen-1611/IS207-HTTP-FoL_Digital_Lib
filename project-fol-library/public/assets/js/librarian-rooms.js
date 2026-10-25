// Các thành phần quản lý phòng đọc mẫu.
const roomDate = document.querySelector('#staff-room-date')
const roomShift = document.querySelector('#staff-room-shift')
const roomRows = document.querySelectorAll('[data-room-row]')
const roomCount = document.querySelector('#staff-room-count')
const roomEmpty = document.querySelector('#staff-room-empty')
const roomFeedback = document.querySelector('#staff-room-feedback')

// Lọc theo ngày và ca, đồng thời thống kê các hàng đang hiển thị.
function filterStaffRooms() {
  let total = 0
  let checkedIn = 0
  let noShow = 0
  let violations = 0

  roomRows.forEach((row) => {
    const matches =
      row.dataset.date === roomDate.value &&
      (roomShift.value === '' || row.dataset.shift === roomShift.value)

    row.hidden = !matches

    if (!matches) return

    total += 1

    const attendance = row.querySelector('[data-attendance]').value

    if (attendance === 'checked-in') checkedIn += 1
    if (attendance === 'no-show') noShow += 1
    if (row.querySelector('[data-violation]').checked) violations += 1
  })

  roomEmpty.hidden = total !== 0

  roomCount.textContent =
    `${total} lịch · ${checkedIn} đã check-in · ` +
    `${noShow} no-show · ${violations} đánh dấu vi phạm`
}

// Lọc khi thay đổi lựa chọn.
roomDate.addEventListener('change', () => {
  roomFeedback.textContent = ''
  filterStaffRooms()
})

roomShift.addEventListener('change', () => {
  roomFeedback.textContent = ''
  filterStaffRooms()
})

// Thông báo thay đổi trạng thái mẫu.
roomRows.forEach((row) => {
  const attendance = row.querySelector('[data-attendance]')
  const violation = row.querySelector('[data-violation]')

  attendance.addEventListener('change', () => {
    filterStaffRooms()

    roomFeedback.textContent =
      `${row.dataset.bookingCode}: ` +
      `${attendance.selectedOptions[0].textContent.trim()}. ` +
      'Chỉ cập nhật giao diện mẫu.'
  })

  violation.addEventListener('change', () => {
    filterStaffRooms()

    roomFeedback.textContent =
      `${row.dataset.bookingCode}: ` +
      `${violation.checked ? 'đã đánh dấu' : 'đã bỏ đánh dấu'} vi phạm. ` +
      'Chưa áp dụng chế tài.'
  })
})

// Hiển thị kết quả ngày hiện tại ngay khi mở trang.
filterStaffRooms()