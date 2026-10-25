// Thành phần tìm kiếm và lọc người dùng.
const userSearchForm = document.querySelector('#user-search-form')
const userKeyword = document.querySelector('#user-keyword')
const userRoleFilter = document.querySelector('#user-role-filter')
const userStatusFilter = document.querySelector('#user-status-filter')
const resetUserFilters = document.querySelector('#reset-user-filters')
const userRows = document.querySelectorAll('[data-user-row]')
const userCount = document.querySelector('#user-count')
const userEmpty = document.querySelector('#user-empty')

// Chuẩn hóa tìm kiếm không phân biệt hoa/thường và dấu.
function normalizeUserText(value) {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[đĐ]/g, 'd')
    .toLowerCase()
    .trim()
}

// Kết hợp từ khóa, vai trò và trạng thái.
function filterUsers() {
  const keyword = normalizeUserText(userKeyword.value)
  let count = 0

  userRows.forEach((row) => {
    const matchesKeyword = normalizeUserText(
      row.dataset.search ?? ''
    ).includes(keyword)

    const matchesRole =
      userRoleFilter.value === '' ||
      row.dataset.role === userRoleFilter.value

    const matchesStatus =
      userStatusFilter.value === '' ||
      row.dataset.status === userStatusFilter.value

    const matches = matchesKeyword && matchesRole && matchesStatus

    row.hidden = !matches
    if (matches) count += 1
  })

  userCount.textContent = `${count} tài khoản phù hợp`
  userEmpty.hidden = count !== 0
}

userSearchForm.addEventListener('submit', (event) => {
  event.preventDefault()
  filterUsers()
})

userRoleFilter.addEventListener('change', filterUsers)
userStatusFilter.addEventListener('change', filterUsers)

// Khôi phục toàn bộ danh sách.
resetUserFilters.addEventListener('click', () => {
  userKeyword.value = ''
  userRoleFilter.value = ''
  userStatusFilter.value = ''
  filterUsers()
})

filterUsers()

// Thành phần chỉnh sửa tài khoản.
const userDialog = document.querySelector('#edit-user-dialog')
const userEditForm = document.querySelector('#edit-user-form')
const userCodeText = document.querySelector('#edit-user-code')
const userNameInput = document.querySelector('#edit-user-name')
const userEmailInput = document.querySelector('#edit-user-email')
const userRoleInput = document.querySelector('#edit-user-role')
const cancelUserEdit = document.querySelector('#cancel-edit-user')
const userFeedback = document.querySelector('#user-feedback')

let editingUserRow = null
let userOpeningButton = null

// Kiểm tra họ tên không chỉ chứa khoảng trắng.
function validateUserName() {
  userNameInput.setCustomValidity(
    userNameInput.value.trim() ? '' : 'Bạn hãy nhập họ và tên.'
  )
}

// Mở biểu mẫu từ hàng được chọn.
document.querySelectorAll('[data-edit-user]').forEach((button) => {
  button.addEventListener('click', () => {
    editingUserRow = button.closest('[data-user-row]')
    userOpeningButton = button

    userNameInput.setCustomValidity('')

    userCodeText.textContent =
      editingUserRow.querySelector('th').textContent.trim()

    userNameInput.value = editingUserRow
      .querySelector('[data-user-field="name"]').textContent.trim()

    userEmailInput.value = editingUserRow
      .querySelector('[data-user-field="email"]').textContent.trim()

    userRoleInput.value = editingUserRow.dataset.role
    userRoleInput.disabled =
      editingUserRow.dataset.status === 'suspended'

    userDialog.showModal()
    userNameInput.focus()
  })
})

userNameInput.addEventListener('input', validateUserName)

// Hủy hoặc Esc không thay đổi bảng.
cancelUserEdit.addEventListener('click', () => {
  userDialog.close()
})

// Lưu thay đổi vào giao diện hiện tại.
userEditForm.addEventListener('submit', (event) => {
  event.preventDefault()
  validateUserName()

  if (!userEditForm.reportValidity() || !editingUserRow) return

  const row = editingUserRow
  const name = userNameInput.value.trim()
  const email = userEmailInput.value.trim()

  // Giữ vai trò cũ nếu tài khoản đang đình chỉ.
  const role = row.dataset.status === 'suspended'
    ? row.dataset.role
    : userRoleInput.value

  row.querySelector('[data-user-field="name"]').textContent = name
  row.querySelector('[data-user-field="email"]').textContent = email

  row.dataset.role = role
  row.querySelector('[data-user-field="role"]').textContent =
    role === 'student' ? 'Sinh viên' : 'Cán bộ'

  const code = row.querySelector('th').textContent.trim()
  row.dataset.search = `${code} ${name} ${email}`

  filterUsers()
  refreshAccountActions()

  userFeedback.textContent =
    `Đã chỉnh sửa mẫu ${code}. ` +
    'Chưa lưu vào hệ thống; tải lại trang sẽ khôi phục dữ liệu.'

  userDialog.close()
})

// Trả tiêu điểm về nút mở hoặc ô tìm kiếm nếu hàng đã bị lọc ẩn.
userDialog.addEventListener('close', () => {
  if (editingUserRow?.hidden) {
    userKeyword.focus()
  } else {
    userOpeningButton?.focus()
  }

  editingUserRow = null
  userOpeningButton = null
})

// Ngày kết thúc đình chỉ mẫu: sau 30 ngày lịch theo giờ Việt Nam.
function suspensionEndDate() {
  const parts = new Intl.DateTimeFormat('en-US', {
    timeZone: 'Asia/Ho_Chi_Minh',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(new Date())

  const part = (type) =>
    Number(parts.find((item) => item.type === type).value)

  const end = new Date(Date.UTC(
    part('year'),
    part('month') - 1,
    part('day') + 30
  ))

  return [
    String(end.getUTCDate()).padStart(2, '0'),
    String(end.getUTCMonth() + 1).padStart(2, '0'),
    end.getUTCFullYear(),
  ].join('/')
}

// Đồng bộ nút với vai trò hiện tại.
function refreshAccountActions() {
  userRows.forEach((row) => {
    const button = row.querySelector('[data-toggle-suspension]')
    if (!button) return

    const isStudent = row.dataset.role === 'student'
    const suspended = row.dataset.status === 'suspended'

    button.disabled = !isStudent
    button.textContent = !isStudent
      ? 'Không áp dụng'
      : suspended
        ? 'Mở lại tài khoản'
        : 'Đình chỉ 30 ngày'

    button.title = isStudent
      ? ''
      : 'Thao tác đình chỉ 30 ngày mẫu chỉ áp dụng cho sinh viên'
  })
}

// Đình chỉ hoặc mở lại sau khi xác nhận.
document.querySelectorAll('[data-toggle-suspension]').forEach((button) => {
  button.addEventListener('click', () => {
    const row = button.closest('[data-user-row]')

    if (!row || row.dataset.role !== 'student') return

    const code = row.querySelector('th').textContent.trim()
    const suspended = row.dataset.status === 'suspended'
    const until = suspended ? '' : suspensionEndDate()

    const question = suspended
      ? `Mở lại tài khoản mẫu ${code}?`
      : `Đình chỉ tài khoản mẫu ${code} trong 30 ngày, đến ${until}?`

    if (!window.confirm(question)) return

    row.dataset.status = suspended ? 'active' : 'suspended'

    const statusBadge = row.querySelector('.admin-user-status')
    statusBadge.textContent = suspended ? 'Hoạt động' : 'Đình chỉ'
    statusBadge.classList.toggle('is-suspended', !suspended)

    row.querySelector('[data-user-field="until"]').textContent =
      suspended ? '—' : until

    refreshAccountActions()
    filterUsers()

    userFeedback.textContent = suspended
      ? `Đã mở lại tài khoản mẫu ${code}.`
      : `Đã đình chỉ tài khoản mẫu ${code} đến ${until}.`

    userFeedback.textContent +=
      ' Chưa thay đổi quyền đăng nhập thật; tải lại sẽ khôi phục dữ liệu.'

    // Giữ tiêu điểm hợp lý nếu hàng bị bộ lọc ẩn đi.
    if (row.hidden) {
      userKeyword.focus()
    } else {
      button.focus()
    }
  })
})

// Trạng thái ban đầu của các nút.
refreshAccountActions()