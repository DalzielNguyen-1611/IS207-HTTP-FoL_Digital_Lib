// Quản lý sách Thư viện FoL - Client Script
// Kết nối trực tiếp với Backend API qua AJAX & Prepared Statements

document.addEventListener('DOMContentLoaded', () => {
  // Thành phần tìm kiếm và bảng
  const searchForm = document.querySelector('#staff-book-search')
  const searchInput = document.querySelector('#staff-book-keyword')
  const tableBody = document.querySelector('#staff-book-tbody')
  const feedback = document.querySelector('#staff-edit-feedback')

  // Thành phần hộp thoại Thêm/Sửa
  const bookDialog = document.querySelector('#edit-book-dialog')
  const bookForm = document.querySelector('#edit-book-form')
  const dialogTitle = document.querySelector('#edit-book-title')
  const saveButton = document.querySelector('#save-book-button')
  const addButton = document.querySelector('#add-book-button')
  const cancelButton = document.querySelector('#cancel-edit-book')
  const formError = document.querySelector('#dialog-form-error')

  // Các ô nhập liệu trong form
  const idInput = document.querySelector('#edit-id')
  const prefixSelect = document.querySelector('#edit-prefix-select')
  const btnGenerateId = document.querySelector('#btn-generate-id')
  const titleInput = document.querySelector('#edit-title')
  const authorInput = document.querySelector('#edit-author')
  const yearInput = document.querySelector('#edit-year')
  const copiesInput = document.querySelector('#edit-copies')
  const labelCopies = document.querySelector('#label-copies')
  const noteCopies = document.querySelector('#note-copies')
  const descInput = document.querySelector('#edit-description')

  // Thành phần hộp thoại xem bản sách
  const copiesDialog = document.querySelector('#view-copies-dialog')
  const closeCopiesBtn = document.querySelector('#close-copies-dialog')
  const copiesBookInfo = document.querySelector('#copies-book-info')
  const copiesTableBody = document.querySelector('#copies-table-body')

  let isEditing = false
  let currentEditingId = null

  // Hiển thị thông báo phản hồi
  function showFeedback(message, isSuccess = true) {
    if (!feedback) return
    feedback.textContent = message
    feedback.style.color = isSuccess ? '#15803d' : '#b91c1c'
    feedback.style.background = isSuccess ? '#dcfce7' : '#fee2e2'
    feedback.style.padding = '10px 14px'
    feedback.style.borderRadius = '8px'
    feedback.style.display = 'block'

    setTimeout(() => {
      feedback.style.display = 'none'
    }, 6000)
  }

  // Tự động lấy mã sách tiếp theo từ Backend
  async function fetchNextId(prefix) {
    try {
      const res = await fetch(`/librarian/books/next-id?prefix=${encodeURIComponent(prefix)}`)
      const data = await res.json()
      if (data.success && data.next_id) {
        idInput.value = data.next_id
      }
    } catch (err) {
      console.error('Lỗi khi lấy mã tiếp theo:', err)
    }
  }

  // Mở modal Thêm sách mới
  if (addButton) {
    addButton.addEventListener('click', async () => {
      isEditing = false
      currentEditingId = null
      bookForm.reset()
      formError.style.display = 'none'

      dialogTitle.textContent = 'Thêm đầu sách mới'
      saveButton.textContent = 'Lưu vào CSDL'

      prefixSelect.style.display = 'inline-block'
      btnGenerateId.style.display = 'inline-block'
      idInput.readOnly = false

      labelCopies.textContent = 'Số bản sách ban đầu'
      noteCopies.textContent = 'Các cuốn sách vật lý sẽ được tạo tự động (.1, .2...).'
      copiesInput.value = '1'
      copiesInput.min = '1'

      await fetchNextId(prefixSelect.value)

      bookDialog.showModal()
      titleInput.focus()
    })
  }

  // Thay đổi tiền tố chuyên mục -> tự động đổi mã sách gợi ý
  if (prefixSelect) {
    prefixSelect.addEventListener('change', () => {
      if (!isEditing) {
        fetchNextId(prefixSelect.value)
      }
    })
  }

  // Nút tự sinh mã
  if (btnGenerateId) {
    btnGenerateId.addEventListener('click', () => {
      if (!isEditing) {
        fetchNextId(prefixSelect.value)
      }
    })
  }

  // Mở modal Sửa sách
  async function openEditDialog(bookId) {
    isEditing = true
    currentEditingId = bookId
    bookForm.reset()
    formError.style.display = 'none'

    dialogTitle.textContent = `Chỉnh sửa đầu sách: ${bookId}`
    saveButton.textContent = 'Lưu thay đổi'

    prefixSelect.style.display = 'none'
    btnGenerateId.style.display = 'none'
    idInput.value = bookId
    idInput.readOnly = true

    labelCopies.textContent = 'Bổ sung thêm bản sách'
    noteCopies.textContent = 'Nhập số lượng nếu muốn in thêm cuốn sách vật lý (mặc định 0).'
    copiesInput.value = '0'
    copiesInput.min = '0'

    try {
      const res = await fetch(`/librarian/books/detail?id=${encodeURIComponent(bookId)}`)
      const data = await res.json()

      if (!data.success || !data.data) {
        alert(data.message || 'Không thể tải thông tin sách.')
        return
      }

      const book = data.data
      titleInput.value = book.title || ''
      authorInput.value = book.authorNames || ''
      yearInput.value = book.publishYear || ''
      descInput.value = book.description || ''

      bookDialog.showModal()
      titleInput.focus()
    } catch (err) {
      alert('Lỗi kết nối khi tải dữ liệu sách: ' + err.message)
    }
  }

  // Mở modal Xem các bản sách vật lý
  async function openCopiesDialog(bookId) {
    copiesDialog.showModal()
    copiesBookInfo.textContent = `Đầu sách: ${bookId}`
    copiesTableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:20px;">Đang tải danh sách bản sách...</td></tr>'

    try {
      const res = await fetch(`/librarian/books/detail?id=${encodeURIComponent(bookId)}`)
      const data = await res.json()

      if (!data.success || !data.data) {
        copiesTableBody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#b91c1c;">${data.message || 'Lỗi khi tải dữ liệu'}</td></tr>`
        return
      }

      const book = data.data
      copiesBookInfo.innerHTML = `<strong>${escapeHtml(book.title)}</strong> (Mã: <code>${escapeHtml(book.id)}</code>) · Tổng số: <strong>${book.totalQuantity}</strong> bản (Khả dụng: <strong>${book.availableQuantity}</strong>)`

      const copies = book.copies || []
      if (copies.length === 0) {
        copiesTableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b;">Chưa có bản sách vật lý nào trong kho.</td></tr>'
        return
      }

      let rowsHtml = ''
      copies.forEach((copy) => {
        const status = copy.trang_thai || 'SanSang'
        let badgeClass = 'badge-available'
        let statusText = 'Sẵn sàng'

        if (status === 'DangMuon') {
          badgeClass = 'badge-count'
          statusText = 'Đang mượn'
        } else if (status === 'Mat') {
          badgeClass = 'badge-empty'
          statusText = 'Báo mất'
        } else if (status === 'Hong') {
          badgeClass = 'badge-empty'
          statusText = 'Hỏng'
        }

        rowsHtml += `
          <tr>
            <th scope="row"><code>${escapeHtml(copy.id_cuon_sach)}</code></th>
            <td>${escapeHtml(copy.ma_vach || copy.id_cuon_sach)}</td>
            <td><span class="badge-count ${badgeClass}">${statusText}</span></td>
            <td style="color:#64748b;font-size:11px;">${escapeHtml(copy.ngay_nhap_kho || '—')}</td>
          </tr>
        `
      })

      copiesTableBody.innerHTML = rowsHtml
    } catch (err) {
      copiesTableBody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#b91c1c;">Lỗi: ${escapeHtml(err.message)}</td></tr>`
    }
  }

  // Đóng dialog bản sách
  if (closeCopiesBtn) {
    closeCopiesBtn.addEventListener('click', () => {
      copiesDialog.close()
    })
  }

  // Xóa sách
  async function deleteBook(bookId, bookTitle) {
    const confirmed = confirm(`Bạn có chắc chắn muốn xóa đầu sách này khỏi hệ thống?\n\n• Mã sách: ${bookId}\n• Tên sách: "${bookTitle}"\n\nLưu ý: Chỉ xóa được nếu không có bản sách nào đang được mượn.`)
    if (!confirmed) return

    try {
      const res = await fetch('/librarian/books/delete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ id_dau_sach: bookId }),
      })

      const data = await res.json()
      if (data.success) {
        showFeedback(data.message || 'Xóa đầu sách thành công!', true)
        // Xóa hàng trên giao diện hoặc reload
        const row = document.querySelector(`tr[data-id="${bookId}"]`)
        if (row) {
          row.style.transition = 'opacity 0.3s ease'
          row.style.opacity = '0'
          setTimeout(() => row.remove(), 300)
        } else {
          window.location.reload()
        }
      } else {
        alert(data.message || 'Không thể xóa đầu sách.')
      }
    } catch (err) {
      alert('Lỗi kết nối khi xóa sách: ' + err.message)
    }
  }

  // Lắng nghe sự kiện click trên bảng sách
  if (tableBody) {
    tableBody.addEventListener('click', (event) => {
      // 1. Nút Xem bản sách
      const copiesBtn = event.target.closest('[data-view-copies]')
      if (copiesBtn) {
        const bookId = copiesBtn.dataset.viewCopies
        if (bookId) openCopiesDialog(bookId)
        return
      }

      // 2. Nút Sửa sách
      const editBtn = event.target.closest('[data-edit-book]')
      if (editBtn) {
        const bookId = editBtn.dataset.editBook
        if (bookId) openEditDialog(bookId)
        return
      }

      // 3. Nút Xóa sách
      const deleteBtn = event.target.closest('[data-delete-book]')
      if (deleteBtn) {
        const bookId = deleteBtn.dataset.deleteBook
        const bookTitle = deleteBtn.dataset.title || ''
        if (bookId) deleteBook(bookId, bookTitle)
      }
    })
  }

  // Hủy đóng form
  if (cancelButton) {
    cancelButton.addEventListener('click', () => {
      bookDialog.close()
    })
  }

  // Submit Form Thêm / Sửa sách
  if (bookForm) {
    bookForm.addEventListener('submit', async (event) => {
      event.preventDefault()
      formError.style.display = 'none'

      const title = titleInput.value.trim()
      const author = authorInput.value.trim()
      const year = yearInput.value.trim()
      const desc = descInput.value.trim()
      const id = idInput.value.trim()

      if (!title || !author) {
        formError.textContent = 'Vui lòng nhập đầy đủ tên sách và tác giả.'
        formError.style.display = 'block'
        return
      }

      saveButton.disabled = true
      saveButton.textContent = 'Đang xử lý...'

      try {
        let endpoint = '/librarian/books/create'
        let payload = {
          id_dau_sach: id,
          tieu_de: title,
          tac_gia: author,
          nam_xuat_ban: year ? parseInt(year, 10) : null,
          mo_ta: desc,
          so_luong: parseInt(copiesInput.value, 10) || 1,
        }

        if (isEditing) {
          endpoint = '/librarian/books/update'
          payload = {
            id_dau_sach: currentEditingId,
            tieu_de: title,
            tac_gia: author,
            nam_xuat_ban: year ? parseInt(year, 10) : null,
            mo_ta: desc,
            additional_copies: parseInt(copiesInput.value, 10) || 0,
          }
        }

        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(payload),
        })

        const data = await res.json()

        if (data.success) {
          bookDialog.close()
          showFeedback(data.message || 'Thao tác thành công!', true)
          // Tải lại trang để đồng bộ danh sách và số lượng
          setTimeout(() => {
            window.location.reload()
          }, 600)
        } else {
          formError.textContent = data.message || (data.errors ? Object.values(data.errors).join(', ') : 'Đã có lỗi xảy ra.')
          formError.style.display = 'block'
        }
      } catch (err) {
        formError.textContent = 'Lỗi kết nối tới máy chủ: ' + err.message
        formError.style.display = 'block'
      } finally {
        saveButton.disabled = false
        saveButton.textContent = isEditing ? 'Lưu thay đổi' : 'Lưu vào CSDL'
      }
    })
  }

  function escapeHtml(str) {
    if (!str) return ''
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;')
  }
})