// Các thành phần báo cáo.
const reportForm = document.querySelector('#report-form')
const periodInput = document.querySelector('#report-period')
const dateInput = document.querySelector('#report-date')
const reportRows = document.querySelectorAll('[data-report-row]')
const rangeText = document.querySelector('#report-range')
const borrowedTotal = document.querySelector('#report-borrowed')
const returnedTotal = document.querySelector('#report-returned')
const daysTotal = document.querySelector('#report-days')
const emptyReport = document.querySelector('#report-empty')
const exportButton = document.querySelector('#export-report')
const reportFeedback = document.querySelector('#report-feedback')

let displayedRows = []
let displayedRange = null

// Lịch và dropdown báo cáo đồng bộ giao diện thư viện.
let syncPeriodDropdown = () => {}
let closePeriodDropdown = () => {}
const periodField = periodInput?.parentElement
let periodDropdown = null
let periodTrigger = null
let periodCurrent = null
let periodOptions = []

if (periodInput && periodField) {
  periodDropdown = document.createElement('div')
  periodDropdown.className = 'filter-dropdown report-period-dropdown'
  periodTrigger = document.createElement('button')
  periodTrigger.type = 'button'
  periodTrigger.className = 'filter-dropdown-trigger'
  periodTrigger.id = 'report-period-trigger'
  periodTrigger.setAttribute('aria-haspopup', 'listbox')
  periodTrigger.setAttribute('aria-expanded', 'false')
  periodCurrent = document.createElement('span')
  periodCurrent.className = 'filter-dropdown-current'
  const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  chevron.setAttribute('viewBox', '0 0 24 24')
  chevron.setAttribute('aria-hidden', 'true')
  chevron.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>'
  periodTrigger.append(periodCurrent, chevron)
  const menu = document.createElement('div')
  menu.className = 'filter-dropdown-menu'
  menu.setAttribute('role', 'listbox')
  menu.setAttribute('aria-label', 'Kỳ thống kê')
  periodOptions = Array.from(periodInput.options).map((option, index) => {
    const item = document.createElement('button')
    item.type = 'button'
    item.className = 'filter-dropdown-option'
    item.setAttribute('role', 'option')
    item.textContent = option.textContent.trim()
    item.addEventListener('click', () => {
      periodInput.selectedIndex = index
      syncPeriodDropdown()
      closePeriodDropdown()
      periodInput.dispatchEvent(new Event('change', { bubbles: true }))
    })
    menu.append(item)
    return item
  })
  syncPeriodDropdown = () => {
    periodCurrent.textContent = periodInput.options[periodInput.selectedIndex]?.textContent.trim() || ''
    periodTrigger.setAttribute('aria-label', `Kỳ thống kê: ${periodCurrent.textContent}`)
    periodOptions.forEach((item, index) => {
      const selected = periodInput.options[index].value === periodInput.value
      item.classList.toggle('is-selected', selected)
      item.setAttribute('aria-selected', selected ? 'true' : 'false')
    })
  }
  const openMenu = () => {
    periodField.classList.add('filter-group', 'filter-dropdown-active')
    periodDropdown.classList.add('is-open')
    periodTrigger.setAttribute('aria-expanded', 'true')
  }
  closePeriodDropdown = () => {
    periodDropdown.classList.remove('is-open')
    periodField.classList.remove('filter-dropdown-active')
    periodTrigger.setAttribute('aria-expanded', 'false')
  }
  periodInput.style.display = 'none'
  periodInput.setAttribute('aria-hidden', 'true')
  periodInput.tabIndex = -1
  periodField.querySelector('label')?.setAttribute('for', periodTrigger.id)
  periodField.insertBefore(periodDropdown, periodInput)
  periodDropdown.append(periodTrigger, menu, periodInput)
  periodTrigger.addEventListener('pointerenter', openMenu)
  periodDropdown.addEventListener('pointerleave', () => window.setTimeout(() => {
    if (!periodDropdown.matches(':hover')) closePeriodDropdown()
  }, 160))
  periodTrigger.addEventListener('click', openMenu)
  periodTrigger.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      openMenu()
      periodOptions[Math.max(0, periodInput.selectedIndex)]?.focus()
    } else if (event.key === 'Escape') closePeriodDropdown()
  })
  document.addEventListener('click', (event) => {
    if (!periodDropdown.contains(event.target)) closePeriodDropdown()
  })
  periodInput.addEventListener('change', syncPeriodDropdown)
  syncPeriodDropdown()
}

let reportDatePicker = null
let reportDateButton = null
let reportDateLabel = null
let reportCalendarTitle = null
let reportCalendarDays = null
let syncReportCalendar = () => {}
let closeReportCalendar = () => {}
let openReportCalendar = () => {}
const reportDateField = dateInput?.parentElement

if (dateInput && reportDateField) {
  reportDatePicker = document.createElement('div')
  reportDatePicker.className = 'counter-date-picker report-date-picker'
  reportDateLabel = document.createElement('span')
  reportDateButton = document.createElement('button')
  reportDateButton.type = 'button'
  reportDateButton.className = 'counter-date-trigger'
  reportDateButton.id = 'report-date-trigger'
  reportDateButton.setAttribute('aria-haspopup', 'dialog')
  reportDateButton.setAttribute('aria-expanded', 'false')
  reportDateButton.setAttribute('aria-controls', 'report-calendar')
  reportDateButton.setAttribute('aria-required', 'true')
  const calendarIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  calendarIcon.setAttribute('viewBox', '0 0 24 24')
  calendarIcon.setAttribute('aria-hidden', 'true')
  calendarIcon.innerHTML = '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'
  reportDateButton.append(calendarIcon)
  reportDatePicker.append(reportDateLabel, reportDateButton)
  const calendar = document.createElement('div')
  calendar.className = 'counter-calendar'
  calendar.id = 'report-calendar'
  calendar.setAttribute('role', 'dialog')
  calendar.setAttribute('aria-label', 'Chọn ngày thuộc kỳ báo cáo')
  const header = document.createElement('div')
  header.className = 'counter-calendar-header'
  const previous = document.createElement('button')
  previous.type = 'button'
  previous.className = 'counter-calendar-nav'
  previous.setAttribute('aria-label', 'Tháng trước')
  previous.textContent = '‹'
  reportCalendarTitle = document.createElement('strong')
  const next = document.createElement('button')
  next.type = 'button'
  next.className = 'counter-calendar-nav'
  next.setAttribute('aria-label', 'Tháng sau')
  next.textContent = '›'
  header.append(previous, reportCalendarTitle, next)
  const weekdays = document.createElement('div')
  weekdays.className = 'counter-calendar-grid counter-calendar-weekdays'
  for (const day of ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']) {
    const label = document.createElement('span')
    label.textContent = day
    weekdays.append(label)
  }
  reportCalendarDays = document.createElement('div')
  reportCalendarDays.className = 'counter-calendar-grid counter-calendar-days'
  calendar.append(header, weekdays, reportCalendarDays)
  const fromIso = (value) => {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(year, month - 1, day)
  }
  const toIso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  let shownMonth = dateInput.value ? fromIso(dateInput.value) : new Date()
  shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1)
  const renderCalendar = () => {
    reportCalendarTitle.textContent = new Intl.DateTimeFormat('vi-VN', { month: 'long', year: 'numeric' }).format(shownMonth)
    reportCalendarDays.replaceChildren()
    const offset = (new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1).getDay() + 6) % 7
    for (let i = 0; i < offset; i += 1) {
      const spacer = document.createElement('span')
      spacer.className = 'counter-calendar-spacer'
      reportCalendarDays.append(spacer)
    }
    const count = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 0).getDate()
    for (let day = 1; day <= count; day += 1) {
      const date = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), day)
      const iso = toIso(date)
      const button = document.createElement('button')
      button.type = 'button'
      button.className = 'counter-calendar-day'
      button.textContent = String(day)
      button.setAttribute('aria-pressed', iso === dateInput.value ? 'true' : 'false')
      if (iso === dateInput.value) button.classList.add('is-selected')
      button.addEventListener('click', () => {
        dateInput.value = iso
        dateInput.dispatchEvent(new Event('change', { bubbles: true }))
        syncReportCalendar()
        closeReportCalendar()
      })
      reportCalendarDays.append(button)
    }
  }
  syncReportCalendar = () => {
    const date = dateInput.value ? fromIso(dateInput.value) : null
    reportDateLabel.textContent = date
      ? `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getFullYear()).slice(-2)}`
      : 'dd/mm/yy'
    reportDateButton.setAttribute('aria-label', date ? `Ngày thuộc kỳ cần xem: ${reportDateLabel.textContent}` : 'Chọn ngày thuộc kỳ cần xem')
    renderCalendar()
  }
  openReportCalendar = () => {
    reportDatePicker.classList.add('is-open')
    reportDateButton.setAttribute('aria-expanded', 'true')
  }
  closeReportCalendar = () => {
    reportDatePicker.classList.remove('is-open')
    reportDateButton.setAttribute('aria-expanded', 'false')
  }
  previous.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() - 1, 1)
    renderCalendar()
  })
  next.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 1)
    renderCalendar()
  })
  reportDateButton.addEventListener('click', () => reportDatePicker.classList.contains('is-open') ? closeReportCalendar() : openReportCalendar())
  document.addEventListener('click', (event) => {
    if (!reportDatePicker.contains(event.target)) closeReportCalendar()
  })
  dateInput.style.display = 'none'
  dateInput.required = false
  dateInput.setAttribute('aria-hidden', 'true')
  dateInput.tabIndex = -1
  reportDateField.querySelector('label')?.setAttribute('for', reportDateButton.id)
  reportDateField.insertBefore(reportDatePicker, dateInput)
  reportDatePicker.append(calendar, dateInput)
  syncReportCalendar()
}
// Chuyển ngày UTC sang YYYY-MM-DD để so sánh ngày lịch.
function dateKey(date) {
  return date.toISOString().slice(0, 10)
}

function displayDate(value) {
  return value.split('-').reverse().join('/')
}

// Xác định đầu và cuối kỳ chứa ngày được chọn.
function getReportRange(value, period) {
  const selected = new Date(`${value}T00:00:00Z`)
  const year = selected.getUTCFullYear()
  const month = selected.getUTCMonth()

  let start = new Date(selected)
  let end = new Date(selected)

  if (period === 'week') {
    const offset = (selected.getUTCDay() + 6) % 7
    start.setUTCDate(start.getUTCDate() - offset)
    end = new Date(start)
    end.setUTCDate(end.getUTCDate() + 6)
  }

  if (period === 'month') {
    start = new Date(Date.UTC(year, month, 1))
    end = new Date(Date.UTC(year, month + 1, 0))
  }

  if (period === 'quarter') {
    const firstMonth = Math.floor(month / 3) * 3
    start = new Date(Date.UTC(year, firstMonth, 1))
    end = new Date(Date.UTC(year, firstMonth + 3, 0))
  }

  if (period === 'year') {
    start = new Date(Date.UTC(year, 0, 1))
    end = new Date(Date.UTC(year, 11, 31))
  }

  return { start: dateKey(start), end: dateKey(end) }
}

// Lọc bảng và tổng hợp các dòng đang hiển thị.
function applyReport() {
  if (!dateInput.value) {
    reportDateButton?.focus()
    openReportCalendar()
    return
  }
  if (!reportForm.reportValidity()) return

  const range = getReportRange(dateInput.value, periodInput.value)
  let borrowed = 0
  let returned = 0

  displayedRows = []
  displayedRange = range

  reportRows.forEach((row) => {
    const matches =
      row.dataset.date >= range.start &&
      row.dataset.date <= range.end

    row.hidden = !matches

    if (!matches) return

    borrowed += Number(row.dataset.borrowed)
    returned += Number(row.dataset.returned)
    displayedRows.push(row)
  })

  displayedRows.sort((a, b) =>
    a.dataset.date.localeCompare(b.dataset.date)
  )

  borrowedTotal.textContent = borrowed
  returnedTotal.textContent = returned
  daysTotal.textContent = displayedRows.length

  rangeText.textContent =
    `Kỳ đang hiển thị: ${displayDate(range.start)} – ` +
    `${displayDate(range.end)} · Dữ liệu mẫu`

  emptyReport.hidden = displayedRows.length !== 0
  exportButton.disabled = displayedRows.length === 0
  reportFeedback.textContent = ''
}

reportForm.addEventListener('submit', (event) => {
  event.preventDefault()
  applyReport()
})

// Tự cập nhật khi đổi kỳ hoặc ngày.
periodInput.addEventListener('change', applyReport)
dateInput.addEventListener('change', applyReport)

// Xuất đúng dữ liệu đang được hiển thị, có BOM để đọc tiếng Việt.
exportButton.addEventListener('click', () => {
  if (!displayedRows.length || !displayedRange) return

  const rows = [
    ['Ngày', 'Lượt mượn', 'Lượt trả'],
    ...displayedRows.map((row) => [
      displayDate(row.dataset.date),
      row.dataset.borrowed,
      row.dataset.returned,
    ]),
    [
      'Tổng',
      borrowedTotal.textContent,
      returnedTotal.textContent,
    ],
  ]

  const csv = '\uFEFF' + rows.map((row) =>
    row.map((value) =>
      `"${String(value).replace(/"/g, '""')}"`
    ).join(',')
  ).join('\r\n')

  const blob = new Blob([csv], {
    type: 'text/csv;charset=utf-8',
  })

  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download =
    `bao-cao-mau-${displayedRange.start}-${displayedRange.end}.csv`

  document.body.append(link)
  link.click()
  link.remove()

  setTimeout(() => URL.revokeObjectURL(url), 1000)

  reportFeedback.textContent =
    'Đã tạo tệp CSV từ dữ liệu mẫu đang hiển thị.'
})

// Báo cáo mặc định là ngày hiện tại.
applyReport()