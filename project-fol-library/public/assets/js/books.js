/**
 * Thư viện Khoa Văn học - USSH VNU-HANOI
 * Quản lý tương tác Tra cứu tài liệu (Server-Side Pagination & Filtering)
 */

document.addEventListener('DOMContentLoaded', () => {
    const catalogContent = document.getElementById('catalog-content')
    const searchForm = document.getElementById('catalog-search')
    const searchInput = searchForm?.querySelector('input[name="q"]')
    const filtersForm = document.getElementById('catalog-filters-form')
    const filterSelects = filtersForm?.querySelectorAll('select') || []
    const resetBtn = document.getElementById('catalog-reset')
    const paginationLinks = document.querySelectorAll('.catalog-pagination .pagination-btn')

    // Bật hiệu ứng chờ tải dữ liệu (Loading indicator)
    function showLoading() {
        if (catalogContent) {
            catalogContent.classList.add('is-loading')
        }
    }

    // 1. Tự động gửi yêu cầu lên Server khi thay đổi bất kỳ dropdown bộ lọc nào
    filterSelects.forEach((select) => {
        select.addEventListener('change', () => {
            showLoading()
            // Loại bỏ các input rỗng để URL gọn gàng hơn
            const formData = new FormData(filtersForm)
            const params = new URLSearchParams()
            
            for (const [key, value] of formData.entries()) {
                if (value && value.trim() !== '' && !(key === 'sort' && value === 'id_asc')) {
                    params.set(key, value.trim())
                }
            }

            window.location.href = '/books' + (params.toString() ? '?' + params.toString() : '')
        })
    })

    // Dropdown tùy chỉnh cho bộ lọc, mở khi rê chuột và vẫn đồng bộ với select gửi lên server.
    filterSelects.forEach((select) => {
        const group = select.closest('.filter-group')
        if (!group || select.dataset.customDropdown === 'true') return

        select.dataset.customDropdown = 'true'
        select.style.display = 'none'
        select.setAttribute('aria-hidden', 'true')
        select.tabIndex = -1

        const label = Array.from(group.querySelectorAll('label')).find((item) => item.htmlFor === select.id)
        const wrapper = document.createElement('div')
        wrapper.className = 'filter-dropdown'

        const trigger = document.createElement('button')
        trigger.type = 'button'
        trigger.className = 'filter-dropdown-trigger'
        trigger.id = `${select.id}-dropdown-trigger`
        trigger.setAttribute('aria-haspopup', 'listbox')
        trigger.setAttribute('aria-expanded', 'false')
        trigger.setAttribute('aria-controls', `${select.id}-dropdown-menu`)

        const triggerText = document.createElement('span')
        triggerText.className = 'filter-dropdown-current'
        const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
        chevron.setAttribute('viewBox', '0 0 24 24')
        chevron.setAttribute('aria-hidden', 'true')
        chevron.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>'
        trigger.append(triggerText, chevron)

        const menu = document.createElement('div')
        menu.className = 'filter-dropdown-menu'
        menu.id = `${select.id}-dropdown-menu`
        menu.setAttribute('role', 'listbox')
        if (label) {
            menu.setAttribute('aria-label', label.textContent.trim())
            label.htmlFor = trigger.id
        }

        const optionButtons = []
        const syncCurrentOption = () => {
            const selected = select.options[select.selectedIndex]
            triggerText.textContent = selected ? selected.textContent.trim().replace(/\s+/g, ' ') : ''
            trigger.setAttribute('aria-label', `${label ? label.textContent.trim() + ': ' : ''}${triggerText.textContent}`)
            optionButtons.forEach((button, index) => {
                const active = select.options[index].value === select.value
                button.setAttribute('aria-selected', active ? 'true' : 'false')
                button.classList.toggle('is-selected', active)
            })
        }

        const openMenu = () => {
            group.classList.add('filter-dropdown-active')
            wrapper.classList.add('is-open')
            trigger.setAttribute('aria-expanded', 'true')
        }
        const closeMenu = () => {
            wrapper.classList.remove('is-open')
            group.classList.remove('filter-dropdown-active')
            trigger.setAttribute('aria-expanded', 'false')
        }

        Array.from(select.options).forEach((option, index) => {
            const item = document.createElement('button')
            item.type = 'button'
            item.className = 'filter-dropdown-option'
            item.setAttribute('role', 'option')
            item.setAttribute('aria-selected', option.selected ? 'true' : 'false')
            item.textContent = option.textContent.trim().replace(/\s+/g, ' ')
            item.disabled = option.disabled
            item.addEventListener('click', () => {
                select.selectedIndex = index
                syncCurrentOption()
                closeMenu()
                select.dispatchEvent(new Event('change', { bubbles: true }))
            })
            item.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault()
                    const direction = event.key === 'ArrowDown' ? 1 : -1
                    optionButtons[(index + direction + optionButtons.length) % optionButtons.length].focus()
                } else if (event.key === 'Escape') {
                    event.preventDefault()
                    closeMenu()
                    trigger.focus()
                }
            })
            optionButtons.push(item)
            menu.appendChild(item)
        })

        select.parentNode.insertBefore(wrapper, select)
        wrapper.append(trigger, menu, select)
        syncCurrentOption()

        trigger.addEventListener('pointerenter', openMenu)
        wrapper.addEventListener('pointerleave', (event) => {
            if (event.relatedTarget && wrapper.contains(event.relatedTarget)) return
            window.setTimeout(() => {
                if (!wrapper.matches(':hover')) closeMenu()
            }, 160)
        })
        trigger.addEventListener('click', openMenu)
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault()
                openMenu()
                const selectedIndex = Math.max(0, select.selectedIndex)
                optionButtons[selectedIndex]?.focus()
            } else if (event.key === 'Escape') {
                closeMenu()
            }
        })
    })

    document.addEventListener('click', (event) => {
        document.querySelectorAll('.filter-dropdown.is-open').forEach((wrapper) => {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove('is-open')
                wrapper.closest('.filter-group')?.classList.remove('filter-dropdown-active')
                wrapper.querySelector('.filter-dropdown-trigger')?.setAttribute('aria-expanded', 'false')
            }
        })
    })
    // 2. Gửi yêu cầu tìm kiếm lên Server khi nhấn Tìm kiếm hoặc Enter
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault()
            showLoading()

            const formData = new FormData(searchForm)
            const params = new URLSearchParams()

            for (const [key, value] of formData.entries()) {
                if (value && value.trim() !== '' && !(key === 'sort' && value === 'id_asc')) {
                    params.set(key, value.trim())
                }
            }

            window.location.href = '/books' + (params.toString() ? '?' + params.toString() : '')
        })
    }

    // 3. Hiệu ứng loading khi chuyển trang
    paginationLinks.forEach((link) => {
        link.addEventListener('click', () => {
            showLoading()
        })
    })

    // 4. Hiệu ứng loading khi làm mới bộ lọc
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            showLoading()
        })
    }
})