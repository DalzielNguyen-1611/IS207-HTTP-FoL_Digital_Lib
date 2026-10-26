// Các thành phần lọc nhật ký.
const auditForm = document.querySelector('#audit-search-form')
const auditKeyword = document.querySelector('#audit-keyword')
const auditDate = document.querySelector('#audit-date')
const auditAction = document.querySelector('#audit-action')
const auditReset = document.querySelector('#audit-reset')
const auditRows = document.querySelectorAll('[data-audit-row]')
const auditCount = document.querySelector('#audit-count')
const auditEmpty = document.querySelector('#audit-empty')

// Chuẩn hóa chữ hoa/thường và dấu tiếng Việt.
function normalizeAuditText(value) {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[đĐ]/g, 'd')
    .toLowerCase()
    .trim()
}

// Kết hợp tất cả điều kiện lọc.
function filterAudit() {
  const keyword = normalizeAuditText(auditKeyword.value)
  let count = 0

  auditRows.forEach((row) => {
    const matchesKeyword = normalizeAuditText(
      row.dataset.search ?? ''
    ).includes(keyword)

    const matchesDate =
      auditDate.value === '' ||
      row.dataset.date === auditDate.value

    const matchesAction =
      auditAction.value === '' ||
      row.dataset.action === auditAction.value

    const matches = matchesKeyword && matchesDate && matchesAction

    row.hidden = !matches
    if (matches) count += 1
  })

  auditCount.textContent = `${count} bản ghi phù hợp`
  auditEmpty.hidden = count !== 0
}

// Tìm kiếm theo biểu mẫu.
auditForm.addEventListener('submit', (event) => {
  event.preventDefault()
  filterAudit()
})

auditDate.addEventListener('change', filterAudit)
auditAction.addEventListener('change', filterAudit)

// Khôi phục toàn bộ danh sách mẫu.
auditReset.addEventListener('click', () => {
  auditKeyword.value = ''
  auditDate.value = ''
  auditAction.value = ''
  filterAudit()
})

filterAudit()