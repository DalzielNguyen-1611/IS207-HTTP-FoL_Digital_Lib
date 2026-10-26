// Các thành phần giao diện tại quầy.
const counterForm = document.querySelector('#counter-form')
const studentInput = document.querySelector('#counter-student')
const studentOptions = document.querySelectorAll('#counter-students option')
const bookSelect = document.querySelector('#counter-book')
const dueInput = document.querySelector('#counter-due')
const counterFeedback = document.querySelector('#counter-feedback')
const counterRows = document.querySelector('#counter-loans')
const counterEmpty = document.querySelector('#counter-empty')

// Phiếu mẫu chỉ giữ trong bộ nhớ của trang.
const counterLoans = []
let loanSequence = 1

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