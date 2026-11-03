// Các thành phần giao diện tại quầy.
const counterForm = document.querySelector('#counter-form')
const studentInput = document.querySelector('#counter-student')
const studentOptions = document.querySelectorAll('#counter-students option')
const bookSelect = document.querySelector('#counter-book')
const dueInput = document.querySelector('#counter-due')
const counterFeedback = document.querySelector('#counter-feedback')
const counterRows = document.querySelector('#counter-loans')
const counterEmpty = document.querySelector('#counter-empty')

// Dropdown chọn sách cùng giao diện với bộ lọc Tra cứu sách.
let syncBookDropdown = () => {}
let closeBookDropdown = () => {}
const bookField = bookSelect?.closest('.counter-field')
let bookDropdown = null
let bookDropdownTrigger = null
let bookDropdownText = null
let bookDropdownOptions = []

if (bookSelect && bookField) {
  bookDropdown = document.createElement('div')
  bookDropdown.className = 'filter-dropdown counter-book-dropdown'
  bookDropdownTrigger = document.createElement('button')
  bookDropdownTrigger.type = 'button'
  bookDropdownTrigger.className = 'filter-dropdown-trigger'
  bookDropdownTrigger.id = 'counter-book-dropdown-trigger'
  bookDropdownTrigger.setAttribute('aria-required', 'true')
  bookDropdownTrigger.setAttribute('aria-controls', 'counter-book-dropdown-menu')
  bookDropdownTrigger.setAttribute('aria-haspopup', 'listbox')
  bookDropdownTrigger.setAttribute('aria-expanded', 'false')
  bookDropdownText = document.createElement('span')
  bookDropdownText.className = 'filter-dropdown-current'
  const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  chevron.setAttribute('viewBox', '0 0 24 24')
  chevron.setAttribute('aria-hidden', 'true')
  chevron.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>'
  bookDropdownTrigger.append(bookDropdownText, chevron)

  const menu = document.createElement('div')
  menu.className = 'filter-dropdown-menu'
  menu.id = 'counter-book-dropdown-menu'
  menu.setAttribute('role', 'listbox')
  menu.setAttribute('aria-label', 'Sách cần mượn')
  bookDropdownOptions = Array.from(bookSelect.options).map((option, index) => {
    const item = document.createElement('button')
    item.type = 'button'
    item.className = 'filter-dropdown-option'
    item.setAttribute('role', 'option')
    item.textContent = option.textContent.trim().replace(/\s+/g, ' ')
    item.disabled = option.disabled
    item.addEventListener('click', () => {
      bookSelect.selectedIndex = index
      bookSelect.dispatchEvent(new Event('change', { bubbles: true }))
      syncBookDropdown()
      closeBookDropdown()
    })
    item.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault()
        const step = event.key === 'ArrowDown' ? 1 : -1
        bookDropdownOptions[(index + step + bookDropdownOptions.length) % bookDropdownOptions.length]?.focus()
      } else if (event.key === 'Escape') {
        event.preventDefault()
        closeBookDropdown()
        bookDropdownTrigger.focus()
      }
    })
    menu.append(item)
    return item
  })

  bookSelect.style.display = 'none'
  bookSelect.setAttribute('aria-hidden', 'true')
  bookSelect.tabIndex = -1
  bookSelect.required = false
  bookField.querySelector('label')?.setAttribute('for', bookDropdownTrigger.id)
  bookField.classList.add('filter-group')
  bookField.insertBefore(bookDropdown, bookSelect)
  bookDropdown.append(bookDropdownTrigger, menu, bookSelect)

  syncBookDropdown = () => {
    const selected = bookSelect.options[bookSelect.selectedIndex]
    bookDropdownText.textContent = selected ? selected.textContent.trim().replace(/\s+/g, ' ') : ''
    bookDropdownTrigger.setAttribute('aria-label', `Sách cần mượn: ${bookDropdownText.textContent}`)
    bookDropdownOptions.forEach((item, index) => {
      const option = bookSelect.options[index]
      item.textContent = option.textContent.trim().replace(/\s+/g, ' ')
      item.disabled = option.disabled
      item.setAttribute('aria-selected', option.value === bookSelect.value ? 'true' : 'false')
      item.classList.toggle('is-selected', option.value === bookSelect.value)
    })
  }
  const openMenu = () => {
    bookField.classList.add('filter-dropdown-active')
    bookDropdown.classList.add('is-open')
    bookDropdownTrigger.setAttribute('aria-expanded', 'true')
  }
  closeBookDropdown = () => {
    bookDropdown.classList.remove('is-open')
    bookField.classList.remove('filter-dropdown-active')
    bookDropdownTrigger.setAttribute('aria-expanded', 'false')
  }

  bookDropdownTrigger.addEventListener('pointerenter', openMenu)
  bookDropdown.addEventListener('pointerleave', (event) => {
    if (event.relatedTarget && bookDropdown.contains(event.relatedTarget)) return
    window.setTimeout(() => {
      if (!bookDropdown.matches(':hover')) closeBookDropdown()
    }, 160)
  })
  bookDropdownTrigger.addEventListener('click', openMenu)
  bookDropdownTrigger.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault()
      openMenu()
      bookDropdownOptions[Math.max(0, bookSelect.selectedIndex)]?.focus()
    } else if (event.key === 'Escape') {
      closeBookDropdown()
    }
  })
  document.addEventListener('click', (event) => {
    if (!bookDropdown.contains(event.target)) closeBookDropdown()
  })
  bookSelect.addEventListener('change', syncBookDropdown)
  counterForm?.addEventListener('reset', () => window.setTimeout(syncBookDropdown, 0))
  syncBookDropdown()
}
// Lịch chọn hạn trả dùng màu giao diện thư viện.
let syncDueDatePicker = () => {}
let openDueDatePicker = () => {}
let closeDueDatePicker = () => {}
let dueDateButton = null
let dueDatePicker = null
let dueDateMonthLabel = null
let dueDateGrid = null

if (dueInput) {
  const dueField = dueInput.closest('.counter-field')
  dueDatePicker = document.createElement('div')
  dueDatePicker.className = 'counter-date-picker'
  dueDateButton = document.createElement('button')
  dueDateButton.type = 'button'
  dueDateButton.className = 'counter-date-trigger'
  dueDateButton.id = 'counter-due-date-trigger'
  dueDateButton.setAttribute('aria-haspopup', 'dialog')
  dueDateButton.setAttribute('aria-expanded', 'false')
  dueDateButton.setAttribute('aria-controls', 'counter-due-calendar')
  dueDateButton.setAttribute('aria-required', 'true')
  const dueDateText = document.createElement('span')
  const calendarIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  calendarIcon.setAttribute('viewBox', '0 0 24 24')
  calendarIcon.setAttribute('aria-hidden', 'true')
  calendarIcon.innerHTML = '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'
  dueDatePicker.append(dueDateText)
  dueDateButton.append(calendarIcon)

  const calendar = document.createElement('div')
  calendar.className = 'counter-calendar'
  calendar.id = 'counter-due-calendar'
  calendar.setAttribute('role', 'dialog')
  calendar.setAttribute('aria-label', 'Chọn hạn trả')
  const calendarHeader = document.createElement('div')
  calendarHeader.className = 'counter-calendar-header'
  const previousMonth = document.createElement('button')
  previousMonth.type = 'button'
  previousMonth.className = 'counter-calendar-nav'
  previousMonth.setAttribute('aria-label', 'Tháng trước')
  previousMonth.textContent = '‹'
  dueDateMonthLabel = document.createElement('strong')
  const nextMonth = document.createElement('button')
  nextMonth.type = 'button'
  nextMonth.className = 'counter-calendar-nav'
  nextMonth.setAttribute('aria-label', 'Tháng sau')
  nextMonth.textContent = '›'
  calendarHeader.append(previousMonth, dueDateMonthLabel, nextMonth)

  const weekdays = document.createElement('div')
  weekdays.className = 'counter-calendar-grid counter-calendar-weekdays'
  for (const day of ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']) {
    const weekday = document.createElement('span')
    weekday.textContent = day
    weekdays.append(weekday)
  }
  dueDateGrid = document.createElement('div')
  dueDateGrid.className = 'counter-calendar-grid counter-calendar-days'
  calendar.append(calendarHeader, weekdays, dueDateGrid)

  let shownMonth = new Date()
  if (dueInput.value) {
    const [year, month] = dueInput.value.split('-').map(Number)
    shownMonth = new Date(year, month - 1, 1)
  } else {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1)
  }

  const toIsoDate = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  const fromIsoDate = (value) => {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(year, month - 1, day)
  }
  const minimumDate = dueInput.min ? fromIsoDate(dueInput.min) : new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate())

  const renderDueCalendar = () => {
    dueDateMonthLabel.textContent = new Intl.DateTimeFormat('vi-VN', { month: 'long', year: 'numeric' }).format(shownMonth)
    dueDateGrid.replaceChildren()
    const firstDay = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1)
    const offset = (firstDay.getDay() + 6) % 7
    for (let blank = 0; blank < offset; blank += 1) {
      const spacer = document.createElement('span')
      spacer.className = 'counter-calendar-spacer'
      dueDateGrid.append(spacer)
    }
    const daysInMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 0).getDate()
    for (let day = 1; day <= daysInMonth; day += 1) {
      const date = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), day)
      const iso = toIsoDate(date)
      const button = document.createElement('button')
      button.type = 'button'
      button.className = 'counter-calendar-day'
      button.textContent = String(day)
      button.disabled = date < minimumDate
      button.setAttribute('aria-pressed', iso === dueInput.value ? 'true' : 'false')
      if (iso === dueInput.value) button.classList.add('is-selected')
      button.addEventListener('click', () => {
        dueInput.value = iso
        dueInput.dispatchEvent(new Event('input', { bubbles: true }))
        dueInput.dispatchEvent(new Event('change', { bubbles: true }))
        syncDueDatePicker()
        closeDueDatePicker()
      })
      dueDateGrid.append(button)
    }
  }

  syncDueDatePicker = () => {
    const chosenDate = dueInput.value ? fromIsoDate(dueInput.value) : null
    dueDateText.textContent = chosenDate
      ? `${String(chosenDate.getDate()).padStart(2, "0")}/${String(chosenDate.getMonth() + 1).padStart(2, "0")}/${String(chosenDate.getFullYear()).slice(-2)}`
      : 'dd/mm/yy'
    dueDateButton.setAttribute('aria-label', dueInput.value ? `Hạn trả: ${dueDateText.textContent}` : 'Chọn hạn trả')
    renderDueCalendar()
  }
  openDueDatePicker = () => {
    dueDatePicker.classList.add('is-open')
    dueDateButton.setAttribute('aria-expanded', 'true')
  }
  closeDueDatePicker = () => {
    dueDatePicker.classList.remove('is-open')
    dueDateButton.setAttribute('aria-expanded', 'false')
  }
  previousMonth.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() - 1, 1)
    renderDueCalendar()
  })
  nextMonth.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 1)
    renderDueCalendar()
  })
  dueDateButton.addEventListener('click', () => {
    if (dueDatePicker.classList.contains('is-open')) closeDueDatePicker()
    else openDueDatePicker()
  })
  dueDateButton.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeDueDatePicker()
  })
  document.addEventListener('click', (event) => {
    if (!dueDatePicker.contains(event.target)) closeDueDatePicker()
  })
  dueInput.style.display = 'none'
  dueInput.required = false
  dueInput.setAttribute('aria-hidden', 'true')
  dueInput.tabIndex = -1
  dueField.querySelector('label')?.setAttribute('for', dueDateButton.id)
  dueField.insertBefore(dueDatePicker, dueInput)
  dueDatePicker.append(dueDateButton, calendar, dueInput)
  counterForm?.addEventListener('reset', () => window.setTimeout(syncDueDatePicker, 0))
  syncDueDatePicker()
}
// Phiếu mượn thực tế từ CSDL Thư viện FoL
const counterLoans = Array.isArray(window.__INITIAL_COUNTER_LOANS__) ? [...window.__INITIAL_COUNTER_LOANS__] : []
let loanSequence = counterLoans.length + 1

// Định dạng ngày từ giá trị YYYY-MM-DD.
function formatCounterDate(value) {
  return value.split('-').reverse().join('/')
}

// Hiển thị phiếu và thao tác trả/mất sách.
function renderCounterLoans() {
  counterRows.replaceChildren()
  counterEmpty.hidden = counterLoans.length !== 0

  counterLoans.forEach((loan) => {
    const row = document.createElement('tr')

    const values = [
      loan.code,
      `${loan.studentCode} · ${loan.studentName}`,
      loan.title,
      formatCounterDate(loan.due),
      loan.status,
    ]

    values.forEach((value, index) => {
      const cell = document.createElement(index === 0 ? 'th' : 'td')

      if (index === 0) cell.scope = 'row'

      cell.textContent = value
      row.append(cell)
    })

    const actions = document.createElement('td')

    if (!loan.closed) {
      const group = document.createElement('div')
      group.className = 'staff-book-actions'

      for (const [mode, label] of [
        ['returned', 'Trả sách'],
        ['lost', 'Báo mất'],
      ]) {
        const button = document.createElement('button')
        button.type = 'button'
        button.className = 'staff-action-button'
        button.textContent = label

        button.addEventListener('click', () => {
          openReturnDialog(loan.code, mode)
        })

        group.append(button)
      }

      actions.append(group)
    } else {
      actions.textContent =
        `Phí mẫu: ${loan.totalFee.toLocaleString('vi-VN')}đ`
    }

    row.append(actions)
    counterRows.append(row)
  })
}

// Kiểm tra mã sinh viên từ danh sách mẫu.
function findCounterStudent() {
  const code = studentInput.value.trim().toUpperCase()

  return Array.from(studentOptions).find(
    (option) => option.value === code
  )
}

studentInput.addEventListener('input', () => {
  studentInput.setCustomValidity('')
  counterFeedback.textContent = ''
})

// Tạo phiếu và trừ số bản trong giao diện mẫu.
counterForm.addEventListener('submit', (event) => {
  event.preventDefault()

  if (!dueInput.value) {
    counterFeedback.textContent = 'Vui lòng chọn hạn trả.'
    dueDateButton?.focus()
    openDueDatePicker()
    return
  }

  if (!bookSelect.value) {
    counterFeedback.textContent = 'Vui lòng chọn sách cần mượn.'
    bookDropdownTrigger?.focus()
    return
  }

  const student = findCounterStudent()

  studentInput.setCustomValidity(
    student ? '' : 'Không tìm thấy mã sinh viên trong dữ liệu mẫu.'
  )

  if (!counterForm.reportValidity()) return

  const option = bookSelect.selectedOptions[0]
  const available = Number(option.dataset.available)

  if (!Number.isInteger(available) || available <= 0) {
    counterFeedback.textContent = 'Sách không còn bản có thể mượn.'
    return
  }

  // Chặn tạo trùng cùng sách cho cùng sinh viên trong phiên thử.
  const duplicate = counterLoans.some(
    (loan) =>
        loan.studentCode === student.value &&
        loan.bookId === option.value &&
        !loan.closed
  )

  if (duplicate) {
    counterFeedback.textContent =
      'Sinh viên đã có phiếu mượn cuốn sách này trong phiên thử.'
    return
  }

  const loan = {
    code: `PM-MOCK-${loanSequence++}`,
    studentCode: student.value,
    studentName: student.dataset.studentName,
    bookId: option.value,
    title: option.dataset.title,
    due: dueInput.value,
    closed: false,
    status: 'Đang mượn mẫu',
  }

  counterLoans.push(loan)

  // Chỉ cập nhật số bản trên danh sách chọn hiện tại.
  const remaining = available - 1
  option.dataset.available = String(remaining)
  option.textContent =
    `BK${loan.bookId} · ${loan.title} · Còn ${remaining} bản`
  option.disabled = remaining === 0
  syncBookDropdown()

  renderCounterLoans()
  counterForm.reset()
  studentInput.setCustomValidity('')

  counterFeedback.textContent =
    `Đã tạo phiếu mẫu ${loan.code}. Chưa lưu vào hệ thống.`

  studentInput.focus()
})

// Xóa thông báo kiểm tra khi nhập lại.
counterForm.addEventListener('reset', () => {
  studentInput.setCustomValidity('')
  counterFeedback.textContent = ''
})

// Thành phần xử lý trả hoặc mất sách.
const returnDialog = document.querySelector('#return-dialog')
const returnForm = document.querySelector('#return-form')
const returnTitle = document.querySelector('#return-title')
const returnDescription = document.querySelector('#return-description')
const returnDate = document.querySelector('#return-date')
const compensationField = document.querySelector('#compensation-field')
const compensationInput = document.querySelector('#return-compensation')
const returnFee = document.querySelector('#return-fee')
const cancelReturn = document.querySelector('#cancel-return')

let processingCode = null
let processingMode = null

// Ngày hiện tại theo giờ Việt Nam.
function counterToday() {
  const parts = new Intl.DateTimeFormat('en-US', {
    timeZone: 'Asia/Ho_Chi_Minh',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(new Date())

  const part = (type) => parts.find((item) => item.type === type).value

  return `${part('year')}-${part('month')}-${part('day')}`
}

// So sánh ngày lịch, không cộng thêm phí trong chính ngày đến hạn.
function calculateReturnFee(loan) {
  const due = Date.parse(`${loan.due}T00:00:00Z`)
  const processed = Date.parse(`${returnDate.value}T00:00:00Z`)

  if (!Number.isFinite(due) || !Number.isFinite(processed)) {
    return null
  }

  const days = Math.max(0, Math.round((processed - due) / 86400000))
  const overdueFee = days * 5000
  const compensation = processingMode === 'lost'
    ? Number(compensationInput.value)
    : 0

  if (!Number.isInteger(compensation) || compensation < 0) {
    return null
  }

  return {
    days,
    overdueFee,
    compensation,
    total: overdueFee + compensation,
  }
}

// Cập nhật chi tiết phí trên hộp.
function updateReturnFee() {
  const loan = counterLoans.find((item) => item.code === processingCode)
  if (!loan) return

  const fee = calculateReturnFee(loan)

  if (!fee) {
    returnFee.textContent = 'Hãy nhập ngày và số tiền hợp lệ.'
    return
  }

  returnFee.textContent =
    `Quá hạn: ${fee.days} ngày · ` +
    `Phí quá hạn: ${fee.overdueFee.toLocaleString('vi-VN')}đ · ` +
    `Bồi thường: ${fee.compensation.toLocaleString('vi-VN')}đ · ` +
    `Tổng phí mẫu: ${fee.total.toLocaleString('vi-VN')}đ`
}

// Chuẩn bị xử lý một phiếu đang mở.
function openReturnDialog(code, mode) {
  const loan = counterLoans.find((item) => item.code === code)
  if (!loan || loan.closed) return

  processingCode = code
  processingMode = mode

  returnForm.reset()
  returnDate.value = counterToday()
  returnDate.min = counterToday()

  compensationField.hidden = mode !== 'lost'
  compensationInput.required = mode === 'lost'
  compensationInput.disabled = mode !== 'lost'

  returnTitle.textContent = mode === 'lost'
    ? 'Ghi nhận sách mất'
    : 'Xác nhận trả sách'

  returnDescription.textContent =
    `${loan.code} · ${loan.title} · ${loan.studentCode}`

  updateReturnFee()
  returnDialog.showModal()
}

returnForm.addEventListener('input', updateReturnFee)

cancelReturn.addEventListener('click', () => {
  returnDialog.close()
})

// Đóng phiếu, hoàn lại số bản nếu sách được trả.
returnForm.addEventListener('submit', (event) => {
  event.preventDefault()
  if (!returnForm.reportValidity()) return

  const loan = counterLoans.find((item) => item.code === processingCode)
  if (!loan || loan.closed) return

  const fee = calculateReturnFee(loan)
  if (!fee) return

  loan.closed = true
  loan.status = processingMode === 'lost'
    ? 'Đã ghi nhận mất'
    : 'Đã trả'
  loan.totalFee = fee.total
  loan.processedDate = returnDate.value

  // Sách mất không được cộng lại vào số bản có thể mượn.
  if (processingMode === 'returned') {
    const option = Array.from(bookSelect.options).find(
      (item) => item.value === loan.bookId
    )

    if (option) {
      const available = Number(option.dataset.available) + 1

      option.dataset.available = String(available)
      option.disabled = false
      option.textContent =
        `BK${loan.bookId} · ${loan.title} · Còn ${available} bản`
      syncBookDropdown()
    }
  }

  renderCounterLoans()
  returnDialog.close()

  counterFeedback.textContent =
    `${loan.code}: ${loan.status}. ` +
    `Tổng phí mẫu ${fee.total.toLocaleString('vi-VN')}đ. ` +
    'Chưa lưu hoặc thu tiền thật.'

  counterFeedback.setAttribute('tabindex', '-1')
  counterFeedback.focus()
})

// Đóng bằng Esc không cập nhật phiếu.
returnDialog.addEventListener('close', () => {
  processingCode = null
  processingMode = null
})

// Khởi tạo hiển thị phiếu mượn thời gian thực từ CSDL
renderCounterLoans()