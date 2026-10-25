// Thành phần bảng và tìm kiếm.
const searchForm = document.querySelector('#staff-book-search')
const searchInput = document.querySelector('#staff-book-keyword')
const resultCount = document.querySelector('#staff-book-count')
const emptyMessage = document.querySelector('#staff-book-empty')
const tableBody = document.querySelector('.staff-table tbody')

// Thành phần hộp thêm/sửa.
const bookDialog = document.querySelector('#edit-book-dialog')
const bookForm = document.querySelector('#edit-book-form')
const dialogTitle = document.querySelector('#edit-book-title')
const saveButton = document.querySelector('#save-book-button')
const addButton = document.querySelector('#add-book-button')
const cancelButton = document.querySelector('#cancel-edit-book')
const feedback = document.querySelector('#staff-edit-feedback')

const titleInput = document.querySelector('#edit-title')
const authorInput = document.querySelector('#edit-author')
const availableInput = document.querySelector('#edit-available')
const shelfInput = document.querySelector('#edit-shelf')
const cellInput = document.querySelector('#edit-cell')

let editingRow = null
let openingButton = null
let newBookSequence = 1

// Chuẩn hóa tìm kiếm tiếng Việt.
function normalizeText(value) {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[đĐ]/g, 'd')
    .toLowerCase()
    .trim()
}

// Lấy danh sách mới mỗi lần để bao gồm các sách vừa thêm.
function filterBooks() {
  const keyword = normalizeText(searchInput.value)
  let count = 0

  tableBody.querySelectorAll('[data-book-row]').forEach((row) => {
    const matches = normalizeText(row.dataset.search ?? '')
      .includes(keyword)

    row.hidden = !matches
    if (matches) count += 1
  })

  resultCount.textContent = `${count} tài liệu phù hợp`
  emptyMessage.hidden = count !== 0
}

// Kiểm tra tên sách và tác giả.
function validateText() {
  titleInput.setCustomValidity(
    titleInput.value.trim() ? '' : 'Bạn hãy nhập tên sách.'
  )

  authorInput.setCustomValidity(
    authorInput.value.trim() ? '' : 'Bạn hãy nhập tác giả.'
  )
}

// Mở hộp dùng chung, không thay đổi bảng cho tới khi lưu.
function openBookForm(row, button) {
  editingRow = row
  openingButton = button

  bookForm.reset()
  titleInput.setCustomValidity('')
  authorInput.setCustomValidity('')

  dialogTitle.textContent = row ? 'Chỉnh sửa sách' : 'Thêm sách mới'
  saveButton.textContent = row ? 'Lưu thay đổi mẫu' : 'Thêm sách mẫu'

  if (row) {
    titleInput.value = row
      .querySelector('[data-field="title"]').textContent.trim()

    authorInput.value = row
      .querySelector('[data-field="author"]').textContent.trim()

    availableInput.value = parseInt(
      row.querySelector('[data-field="available"]').textContent,
      10
    )

    const position = row
      .querySelector('[data-field="location"]')
      .textContent.match(/\d+/g)

    shelfInput.value = position?.[0] ?? ''
    cellInput.value = position?.[1] ?? ''
  } else {
    availableInput.value = '1'
    shelfInput.value = '1'
    cellInput.value = '1'
  }

  bookDialog.showModal()
  titleInput.focus()
}

// Tạo hàng mới bằng DOM, không đưa nội dung ô nhập vào innerHTML.
function createBookRow() {
  const row = document.createElement('tr')
  row.setAttribute('data-book-row', '')

  const codeCell = document.createElement('th')
  codeCell.scope = 'row'
  codeCell.textContent = `MOCK-${newBookSequence++}`
  row.append(codeCell)

  for (const field of ['title', 'author', 'available', 'location']) {
    const cell = document.createElement('td')
    cell.dataset.field = field
    row.append(cell)
  }

  // Sách mới chưa có trang chi tiết vì chưa được lưu trong dữ liệu PHP.
  const actionsCell = document.createElement('td')
  const actions = document.createElement('div')
  actions.className = 'staff-book-actions'

  const viewButton = document.createElement('button')
  viewButton.type = 'button'
  viewButton.className = 'staff-action-button'
  viewButton.textContent = 'Xem sách'
  viewButton.disabled = true
  viewButton.title = 'Sách thêm mẫu chưa có trang chi tiết'

  const editButton = document.createElement('button')
  editButton.type = 'button'
  editButton.className = 'staff-action-button'
  editButton.textContent = 'Sửa'
  editButton.setAttribute('data-edit-book', '')

  actions.append(viewButton, editButton)
  actionsCell.append(actions)
  row.append(actionsCell)
  tableBody.append(row)

  return row
}

// Tìm kiếm.
searchForm.addEventListener('submit', (event) => {
  event.preventDefault()
  filterBooks()
})

// Mở hộp thêm.
addButton.addEventListener('click', () => {
  openBookForm(null, addButton)
})

// Dùng sự kiện trên bảng để hỗ trợ cả hàng mới thêm.
tableBody.addEventListener('click', (event) => {
  const button = event.target.closest('[data-edit-book]')
  if (!button) return

  openBookForm(button.closest('[data-book-row]'), button)
})

// Hủy hoặc nhấn Esc sẽ đóng hộp mà không cập nhật bảng.
cancelButton.addEventListener('click', () => {
  bookDialog.close()
})

bookForm.addEventListener('input', validateText)

// Thêm hoặc sửa dữ liệu hiển thị.
bookForm.addEventListener('submit', (event) => {
  event.preventDefault()
  validateText()

  if (!bookForm.reportValidity()) return

  const creating = editingRow === null
  const row = editingRow ?? createBookRow()
  const title = titleInput.value.trim()
  const author = authorInput.value.trim()
  const available = Number(availableInput.value)
  const shelf = Number(shelfInput.value)
  const cell = Number(cellInput.value)

  row.querySelector('[data-field="title"]').textContent = title
  row.querySelector('[data-field="author"]').textContent = author
  row.querySelector('[data-field="available"]').textContent =
    `${available} bản`
  row.querySelector('[data-field="location"]').textContent =
    `Kệ ${String(shelf).padStart(2, '0')} · Ô ${String(cell).padStart(2, '0')}`

  const code = row.querySelector('th').textContent.trim()
  row.dataset.search = `${code} ${title} ${author}`

  // Xóa tìm kiếm sau khi thêm để nhìn thấy sách mới.
  if (creating) searchInput.value = ''

  filterBooks()

  feedback.textContent =
    `${creating ? 'Đã thêm' : 'Đã sửa'} mẫu “${title}”. ` +
    'Chỉ cập nhật bảng này; tải lại sẽ khôi phục dữ liệu. ' +
    'Trang chi tiết vẫn dùng dữ liệu PHP ban đầu.'

  bookDialog.close()
})

// Khôi phục tiêu điểm khi đóng.
bookDialog.addEventListener('close', () => {
  const rowWasHidden = editingRow?.hidden

  if (rowWasHidden) {
    searchInput.focus()
  } else {
    openingButton?.focus()
  }

  editingRow = null
  openingButton = null
})