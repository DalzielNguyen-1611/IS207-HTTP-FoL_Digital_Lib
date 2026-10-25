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