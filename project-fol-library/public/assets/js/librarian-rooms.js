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

// Lịch và dropdown đồng bộ giao diện thư viện.
const dateField = roomDate?.parentElement
let roomDatePicker = null
let roomDateButton = null
let roomDateLabel = null
let roomCalendarTitle = null
let roomCalendarDays = null
let syncRoomCalendar = () => {}
let closeRoomCalendar = () => {}
let openRoomCalendar = () => {}

if (roomDate && dateField) {
  roomDatePicker = document.createElement('div')
  roomDatePicker.className = 'counter-date-picker staff-room-date-picker'
  roomDateLabel = document.createElement('span')
  roomDateButton = document.createElement('button')
  roomDateButton.type = 'button'
  roomDateButton.className = 'counter-date-trigger'
  roomDateButton.id = 'staff-room-date-trigger'
  roomDateButton.setAttribute('aria-haspopup', 'dialog')
  roomDateButton.setAttribute('aria-expanded', 'false')
  roomDateButton.setAttribute('aria-controls', 'staff-room-calendar')
  const calendarIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  calendarIcon.setAttribute('viewBox', '0 0 24 24')
  calendarIcon.setAttribute('aria-hidden', 'true')
  calendarIcon.innerHTML = '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'
  roomDateButton.append(calendarIcon)
  roomDatePicker.append(roomDateLabel, roomDateButton)

  const calendar = document.createElement('div')
  calendar.className = 'counter-calendar'
  calendar.id = 'staff-room-calendar'
  calendar.setAttribute('role', 'dialog')
  calendar.setAttribute('aria-label', 'Chọn ngày theo dõi')
  const header = document.createElement('div')
  header.className = 'counter-calendar-header'
  const previous = document.createElement('button')
  previous.type = 'button'
  previous.className = 'counter-calendar-nav'
  previous.setAttribute('aria-label', 'Tháng trước')
  previous.textContent = '‹'
  roomCalendarTitle = document.createElement('strong')
  const next = document.createElement('button')
  next.type = 'button'
  next.className = 'counter-calendar-nav'
  next.setAttribute('aria-label', 'Tháng sau')
  next.textContent = '›'
  header.append(previous, roomCalendarTitle, next)
  const weekdays = document.createElement('div')
  weekdays.className = 'counter-calendar-grid counter-calendar-weekdays'
  for (const day of ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']) {
    const label = document.createElement('span')
    label.textContent = day
    weekdays.append(label)
  }
  roomCalendarDays = document.createElement('div')
  roomCalendarDays.className = 'counter-calendar-grid counter-calendar-days'
  calendar.append(header, weekdays, roomCalendarDays)
  const parseRoomDate = (value) => {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(year, month - 1, day)
  }
  let shownMonth = roomDate.value ? parseRoomDate(roomDate.value) : new Date()
  shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1)
  const fromRoomIso = (value) => {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(year, month - 1, day)
  }
  const toRoomIso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  const renderRoomCalendar = () => {
    roomCalendarTitle.textContent = new Intl.DateTimeFormat('vi-VN', { month: 'long', year: 'numeric' }).format(shownMonth)
    roomCalendarDays.replaceChildren()
    const offset = (new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1).getDay() + 6) % 7
    for (let i = 0; i < offset; i += 1) {
      const spacer = document.createElement('span')
      spacer.className = 'counter-calendar-spacer'
      roomCalendarDays.append(spacer)
    }
    const count = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 0).getDate()
    for (let day = 1; day <= count; day += 1) {
      const date = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), day)
      const iso = toRoomIso(date)
      const button = document.createElement('button')
      button.type = 'button'
      button.className = 'counter-calendar-day'
      button.textContent = String(day)
      button.setAttribute('aria-pressed', iso === roomDate.value ? 'true' : 'false')
      if (iso === roomDate.value) button.classList.add('is-selected')
      button.addEventListener('click', () => {
        roomDate.value = iso
        roomDate.dispatchEvent(new Event('change', { bubbles: true }))
        syncRoomCalendar()
        closeRoomCalendar()
      })
      roomCalendarDays.append(button)
    }
  }
  syncRoomCalendar = () => {
    const date = roomDate.value ? fromRoomIso(roomDate.value) : null
    roomDateLabel.textContent = date
      ? `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getFullYear()).slice(-2)}`
      : 'dd/mm/yy'
    roomDateButton.setAttribute('aria-label', date ? `Ngày theo dõi: ${roomDateLabel.textContent}` : 'Chọn ngày theo dõi')
    renderRoomCalendar()
  }
  openRoomCalendar = () => {
    roomDatePicker.classList.add('is-open')
    roomDateButton.setAttribute('aria-expanded', 'true')
  }
  closeRoomCalendar = () => {
    roomDatePicker.classList.remove('is-open')
    roomDateButton.setAttribute('aria-expanded', 'false')
  }
  previous.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() - 1, 1)
    renderRoomCalendar()
  })
  next.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 1)
    renderRoomCalendar()
  })
  roomDateButton.addEventListener('click', () => roomDatePicker.classList.contains('is-open') ? closeRoomCalendar() : openRoomCalendar())
  document.addEventListener('click', (event) => {
    if (!roomDatePicker.contains(event.target)) closeRoomCalendar()
  })
  roomDate.style.display = 'none'
  roomDate.required = false
  roomDate.setAttribute('aria-hidden', 'true')
  roomDate.tabIndex = -1
  dateField.querySelector('label')?.setAttribute('for', roomDateButton.id)
  dateField.insertBefore(roomDatePicker, roomDate)
  roomDatePicker.append(calendar, roomDate)
  syncRoomCalendar()
}

if (roomShift) {
  const shiftField = roomShift.parentElement
  const dropdown = document.createElement('div')
  dropdown.className = 'filter-dropdown staff-room-shift-dropdown'
  const trigger = document.createElement('button')
  trigger.type = 'button'
  trigger.className = 'filter-dropdown-trigger'
  trigger.id = 'staff-room-shift-trigger'
  trigger.setAttribute('aria-haspopup', 'listbox')
  trigger.setAttribute('aria-expanded', 'false')
  const current = document.createElement('span')
  current.className = 'filter-dropdown-current'
  const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  chevron.setAttribute('viewBox', '0 0 24 24')
  chevron.setAttribute('aria-hidden', 'true')
  chevron.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>'
  trigger.append(current, chevron)
  const menu = document.createElement('div')
  menu.className = 'filter-dropdown-menu'
  menu.setAttribute('role', 'listbox')
  menu.setAttribute('aria-label', 'Ca đọc')
  const options = Array.from(roomShift.options).map((option, index) => {
    const item = document.createElement('button')
    item.type = 'button'
    item.className = 'filter-dropdown-option'
    item.setAttribute('role', 'option')
    item.textContent = option.textContent.trim()
    item.addEventListener('click', () => {
      roomShift.selectedIndex = index
      syncRoomShift()
      closeRoomShift()
      roomShift.dispatchEvent(new Event('change', { bubbles: true }))
    })
    menu.append(item)
    return item
  })
  const syncRoomShift = () => {
    current.textContent = roomShift.options[roomShift.selectedIndex]?.textContent.trim() || ''
    trigger.setAttribute('aria-label', `Ca đọc: ${current.textContent}`)
    options.forEach((item, index) => {
      const selected = roomShift.options[index].value === roomShift.value
      item.classList.toggle('is-selected', selected)
      item.setAttribute('aria-selected', selected ? 'true' : 'false')
    })
  }
  const openRoomShift = () => {
    shiftField.classList.add('filter-group', 'filter-dropdown-active')
    dropdown.classList.add('is-open')
    trigger.setAttribute('aria-expanded', 'true')
  }
  closeRoomShift = () => {
    dropdown.classList.remove('is-open')
    shiftField.classList.remove('filter-dropdown-active')
    trigger.setAttribute('aria-expanded', 'false')
  }
  roomShift.style.display = 'none'
  roomShift.setAttribute('aria-hidden', 'true')
  roomShift.tabIndex = -1
  shiftField.querySelector('label')?.setAttribute('for', trigger.id)
  shiftField.insertBefore(dropdown, roomShift)
  dropdown.append(trigger, menu, roomShift)
  trigger.addEventListener('pointerenter', openRoomShift)
  dropdown.addEventListener('pointerleave', () => window.setTimeout(() => {
    if (!dropdown.matches(':hover')) closeRoomShift()
  }, 160))
  trigger.addEventListener('click', openRoomShift)
  trigger.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      openRoomShift()
      options[Math.max(0, roomShift.selectedIndex)]?.focus()
    } else if (event.key === 'Escape') closeRoomShift()
  })
  document.addEventListener('click', (event) => {
    if (!dropdown.contains(event.target)) closeRoomShift()
  })
  roomShift.addEventListener('change', syncRoomShift)
  syncRoomShift()
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