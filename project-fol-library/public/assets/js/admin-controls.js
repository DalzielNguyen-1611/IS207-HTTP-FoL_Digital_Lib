// Giao diện dropdown và lịch dùng chung cho các trang quản trị.
document.querySelectorAll('body.admin-ui select').forEach((select, selectIndex) => {
  const field = select.parentElement
  if (!field || select.dataset.adminDropdown === 'true') return

  select.dataset.adminDropdown = 'true'
  const wrapper = document.createElement('div')
  wrapper.className = 'filter-dropdown admin-select-dropdown'
  const trigger = document.createElement('button')
  trigger.type = 'button'
  trigger.className = 'filter-dropdown-trigger'
  trigger.id = `${select.id || `admin-select-${selectIndex}`}-trigger`
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
  menu.id = `${trigger.id}-menu`
  menu.setAttribute('role', 'listbox')
  const label = Array.from(field.querySelectorAll('label')).find((item) => item.htmlFor === select.id)
  if (label) {
    menu.setAttribute('aria-label', label.textContent.trim())
    label.htmlFor = trigger.id
  }
  trigger.setAttribute('aria-controls', menu.id)

  const options = Array.from(select.options).map((option, index) => {
    const item = document.createElement('button')
    item.type = 'button'
    item.className = 'filter-dropdown-option'
    item.setAttribute('role', 'option')
    item.textContent = option.textContent.trim().replace(/\s+/g, ' ')
    item.addEventListener('click', () => {
      select.selectedIndex = index
      select.dispatchEvent(new Event('change', { bubbles: true }))
      sync()
      close()
    })
    item.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault()
        const step = event.key === 'ArrowDown' ? 1 : -1
        options[(index + step + options.length) % options.length]?.focus()
      } else if (event.key === 'Escape') {
        event.preventDefault()
        close()
        trigger.focus()
      }
    })
    menu.append(item)
    return item
  })

  const sync = () => {
    const selected = select.options[select.selectedIndex]
    current.textContent = selected ? selected.textContent.trim().replace(/\s+/g, ' ') : ''
    trigger.setAttribute('aria-label', `${label ? label.textContent.trim() + ': ' : ''}${current.textContent}`)
    trigger.disabled = select.disabled
    options.forEach((item, index) => {
      const option = select.options[index]
      const active = option.value === select.value
      item.disabled = option.disabled || select.disabled
      item.classList.toggle('is-selected', active)
      item.setAttribute('aria-selected', active ? 'true' : 'false')
    })
    if (select.disabled) close()
  }
  const open = () => {
    if (select.disabled) return
    field.classList.add('filter-group', 'filter-dropdown-active')
    wrapper.classList.add('is-open')
    trigger.setAttribute('aria-expanded', 'true')
  }
  const close = () => {
    wrapper.classList.remove('is-open')
    field.classList.remove('filter-dropdown-active')
    trigger.setAttribute('aria-expanded', 'false')
  }

  select.style.display = 'none'
  select.setAttribute('aria-hidden', 'true')
  select.tabIndex = -1
  field.insertBefore(wrapper, select)
  wrapper.append(trigger, menu, select)
  trigger.addEventListener('pointerenter', open)
  wrapper.addEventListener('pointerleave', () => window.setTimeout(() => {
    if (!wrapper.matches(':hover')) close()
  }, 160))
  trigger.addEventListener('click', () => wrapper.classList.contains('is-open') ? close() : open())
  trigger.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault()
      open()
      options[Math.max(0, select.selectedIndex)]?.focus()
    } else if (event.key === 'Escape') close()
  })
  document.addEventListener('click', (event) => {
    if (!wrapper.contains(event.target)) close()
  })
  select.addEventListener('change', sync)
  select.form?.addEventListener('reset', () => window.setTimeout(sync, 0))
  new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['disabled'] })
  sync()
})

// Lịch nhỏ màu kem-xanh, mở riêng khi bấm biểu tượng.
document.querySelectorAll('body.admin-ui input[type="date"]').forEach((input) => {
  const field = input.parentElement
  if (!field) return
  const picker = document.createElement('div')
  picker.className = 'counter-date-picker admin-date-picker'
  const dateLabel = document.createElement('span')
  const trigger = document.createElement('button')
  trigger.type = 'button'
  trigger.className = 'counter-date-trigger'
  trigger.id = `${input.id || 'admin-date'}-trigger`
  trigger.setAttribute('aria-haspopup', 'dialog')
  trigger.setAttribute('aria-expanded', 'false')
  const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  icon.setAttribute('viewBox', '0 0 24 24')
  icon.setAttribute('aria-hidden', 'true')
  icon.innerHTML = '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'
  trigger.append(icon)
  picker.append(dateLabel, trigger)

  const calendar = document.createElement('div')
  calendar.className = 'counter-calendar'
  calendar.id = `${trigger.id}-calendar`
  calendar.setAttribute('role', 'dialog')
  calendar.setAttribute('aria-label', 'Chọn ngày')
  trigger.setAttribute('aria-controls', calendar.id)
  const header = document.createElement('div')
  header.className = 'counter-calendar-header'
  const previous = document.createElement('button')
  previous.type = 'button'
  previous.className = 'counter-calendar-nav'
  previous.setAttribute('aria-label', 'Tháng trước')
  previous.textContent = '‹'
  const monthLabel = document.createElement('strong')
  const next = document.createElement('button')
  next.type = 'button'
  next.className = 'counter-calendar-nav'
  next.setAttribute('aria-label', 'Tháng sau')
  next.textContent = '›'
  header.append(previous, monthLabel, next)
  const weekdays = document.createElement('div')
  weekdays.className = 'counter-calendar-grid counter-calendar-weekdays'
  for (const day of ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']) {
    const item = document.createElement('span')
    item.textContent = day
    weekdays.append(item)
  }
  const days = document.createElement('div')
  days.className = 'counter-calendar-grid counter-calendar-days'
  calendar.append(header, weekdays, days)

  const fromIso = (value) => {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(year, month - 1, day)
  }
  const toIso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  let shownMonth = input.value ? fromIso(input.value) : new Date()
  shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1)
  const render = () => {
    monthLabel.textContent = new Intl.DateTimeFormat('vi-VN', { month: 'long', year: 'numeric' }).format(shownMonth)
    days.replaceChildren()
    const offset = (new Date(shownMonth.getFullYear(), shownMonth.getMonth(), 1).getDay() + 6) % 7
    for (let i = 0; i < offset; i += 1) {
      const blank = document.createElement('span')
      blank.className = 'counter-calendar-spacer'
      days.append(blank)
    }
    const count = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 0).getDate()
    for (let number = 1; number <= count; number += 1) {
      const date = new Date(shownMonth.getFullYear(), shownMonth.getMonth(), number)
      const iso = toIso(date)
      const item = document.createElement('button')
      item.type = 'button'
      item.className = 'counter-calendar-day'
      item.textContent = String(number)
      item.setAttribute('aria-pressed', iso === input.value ? 'true' : 'false')
      if (iso === input.value) item.classList.add('is-selected')
      item.addEventListener('click', () => {
        input.value = iso
        input.dispatchEvent(new Event('change', { bubbles: true }))
        syncDate()
        closeDate()
      })
      days.append(item)
    }
  }
  const syncDate = () => {
    const date = input.value ? fromIso(input.value) : null
    dateLabel.textContent = date
      ? `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getFullYear()).slice(-2)}`
      : 'dd/mm/yy'
    trigger.setAttribute('aria-label', date ? `Ngày: ${dateLabel.textContent}` : 'Chọn ngày')
    render()
  }
  const openDate = () => {
    picker.classList.add('is-open')
    trigger.setAttribute('aria-expanded', 'true')
  }
  const closeDate = () => {
    picker.classList.remove('is-open')
    trigger.setAttribute('aria-expanded', 'false')
  }
  previous.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() - 1, 1)
    render()
  })
  next.addEventListener('click', () => {
    shownMonth = new Date(shownMonth.getFullYear(), shownMonth.getMonth() + 1, 1)
    render()
  })
  trigger.addEventListener('click', () => picker.classList.contains('is-open') ? closeDate() : openDate())
  document.addEventListener('click', (event) => {
    if (!picker.contains(event.target)) closeDate()
  })
  input.style.display = 'none'
  input.required = false
  input.setAttribute('aria-hidden', 'true')
  input.tabIndex = -1
  field.querySelector('label')?.setAttribute('for', trigger.id)
  field.insertBefore(picker, input)
  picker.append(calendar, input)
  input.addEventListener('change', syncDate)
  input.form?.addEventListener('reset', () => window.setTimeout(syncDate, 0))
  syncDate()
})