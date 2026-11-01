const slotButtons = document.querySelectorAll('.room-slot')
const cancelButtons = document.querySelectorAll('.room-booking-cancel')
const bookingDialog = document.querySelector('#booking-dialog')
const bookingDescription = document.querySelector('#booking-description')
const roomFeedback = document.querySelector('#room-feedback')
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
const canBook = document.querySelector('main[data-can-book]')?.dataset.canBook === 'true'
const isBookableWeek = document.querySelector('main[data-bookable-week]')?.dataset.bookableWeek === 'true'
const weekStart = document.querySelector('main[data-week-start]')?.dataset.weekStart ?? ''

let selectedSlot = null

function setSlotState(slot, { booked = false, free = Number(slot.dataset.free) } = {}) {
  const expired = Date.parse(slot.dataset.start) <= Date.now()
  const capacity = Number(slot.dataset.capacity)
  const occupied = Math.max(0, capacity - free)
  slot.disabled = booked || free <= 0 || expired || !canBook || !isBookableWeek
  slot.classList.toggle('is-booked', booked)

  slot.querySelector('strong').textContent =
    `${occupied} / ${capacity} chỗ đã đặt`

  slot.querySelector('span').textContent = booked
    ? 'Bạn đã đặt ca này'
    : free <= 0
      ? 'Hết chỗ'
      : expired
      ? 'Chọn ca khác'
      : !isBookableWeek
        ? 'Chỉ đặt được tuần kế tiếp'
      : canBook
        ? 'Đặt chỗ +'
        : 'Đăng nhập để đặt'

  slot.setAttribute(
    'aria-label',
    `${slot.dataset.label} · ${slot.querySelector('strong').textContent}`
  )
}

async function loadUserBookings() {
  const response = await fetch(
    `/api/reading-room/bookings?week=${encodeURIComponent(weekStart)}`,
    {
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    }
  )
  const result = await response.json()

  if (
    !response.ok ||
    result.success !== true ||
    !Array.isArray(result.bookings) ||
    !Array.isArray(result.schedule?.days) ||
    !Array.isArray(result.schedule?.shifts)
  ) {
    throw new Error(result.error ?? 'Không tải được lịch đặt chỗ.')
  }

  return result
}

async function refreshRoomSlots() {
  slotButtons.forEach((slot) => setSlotState(slot))
  if (!canBook) return

  try {
    const { bookings, schedule } = await loadUserBookings()
    const dayIndexes = new Map(
      schedule.days.map((day, index) => [day.date, index])
    )
    const shifts = new Map(
      schedule.shifts.map((shift) => [String(shift.id_ca_doc), shift])
    )

    slotButtons.forEach((slot) => {
      const dayIndex = dayIndexes.get(slot.dataset.date)
      const shift = shifts.get(slot.dataset.shiftId)
      if (dayIndex !== undefined && shift) {
        slot.dataset.free = String(shift.free[dayIndex] ?? 0)
        slot.dataset.capacity = String(schedule.capacity)
      }

      const ownBooking = bookings.find(
        (booking) =>
          booking.date === slot.dataset.date &&
          String(booking.shift_id) === slot.dataset.shiftId &&
          ['DaDat', 'DaNhanCho'].includes(booking.status)
      )
      setSlotState(slot, { booked: Boolean(ownBooking) })

      const cancelButton = [...cancelButtons].find(
        (button) =>
          button.dataset.date === slot.dataset.date &&
          button.dataset.shiftId === slot.dataset.shiftId
      )
      if (cancelButton) {
        const canCancel = ownBooking?.status === 'DaDat'
        cancelButton.hidden = !canCancel
        cancelButton.disabled = canCancel && !ownBooking.can_cancel
        cancelButton.title = cancelButton.disabled
          ? 'Chỉ có thể hủy trước ca đọc ít nhất 24 giờ.'
          : ''
        if (cancelButton.disabled) {
          cancelButton.setAttribute(
            'aria-label',
            `Không thể hủy ${slot.dataset.label}; cần hủy trước ca ít nhất 24 giờ`
          )
        } else {
          cancelButton.setAttribute(
            'aria-label',
            `Hủy đặt chỗ ${slot.dataset.label}`
          )
        }
      }
    })
  } catch (error) {
    roomFeedback.textContent = error instanceof Error
      ? error.message
      : 'Không tải được lịch đặt chỗ.'
  }
}

async function createBooking(slot) {
  const response = await fetch('/api/reading-room/bookings/create', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': csrfToken,
    },
    body: JSON.stringify({
      date: slot.dataset.date,
      shift_id: Number(slot.dataset.shiftId),
    }),
  })
  const result = await response.json()

  if (!response.ok || result.success !== true) {
    throw new Error(result.error ?? 'Không thể đặt chỗ.')
  }

  return result.booking
}

async function cancelBooking(bookingId) {
  const response = await fetch('/api/reading-room/bookings/cancel', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': csrfToken,
    },
    body: JSON.stringify({ booking_id: bookingId }),
  })
  const result = await response.json()

  if (!response.ok || result.success !== true) {
    throw new Error(result.error ?? 'Không thể hủy lượt đặt chỗ.')
  }
}

if (bookingDialog && bookingDescription && roomFeedback) {
  slotButtons.forEach((slot) => {
    slot.addEventListener('click', () => {
      if (!canBook) {
        roomFeedback.textContent = 'Vui lòng đăng nhập bằng tài khoản sinh viên để đặt chỗ.'
        roomFeedback.setAttribute('tabindex', '-1')
        roomFeedback.focus()
        return
      }

      if (!isBookableWeek) {
        roomFeedback.textContent = 'Chỉ có thể đặt chỗ cho tuần kế tiếp.'
        roomFeedback.setAttribute('tabindex', '-1')
        roomFeedback.focus()
        return
      }

      if (slot.disabled) return

      selectedSlot = slot
      bookingDialog.returnValue = ''
      bookingDescription.textContent = slot.dataset.label
      bookingDialog.showModal()
    })
  })

  cancelButtons.forEach((button) => {
    button.addEventListener('click', async () => {
      if (button.disabled || button.hidden) return
      if (!window.confirm('Bạn muốn hủy lượt đặt chỗ này?')) return

      button.disabled = true
      try {
        const { bookings } = await loadUserBookings()
        const booking = bookings.find(
          (item) =>
            item.date === button.dataset.date &&
            String(item.shift_id) === button.dataset.shiftId &&
            item.status === 'DaDat'
        )
        if (!booking) {
          throw new Error('Không tìm thấy lượt đặt còn hiệu lực để hủy.')
        }

        await cancelBooking(Number(booking.id))
        await refreshRoomSlots()
        roomFeedback.textContent = 'Đã hủy lượt đặt chỗ. Ghế đã được mở lại.'
      } catch (error) {
        roomFeedback.textContent = error instanceof Error
          ? error.message
          : 'Không thể hủy lượt đặt chỗ. Vui lòng thử lại.'
        await refreshRoomSlots()
      } finally {
        roomFeedback.setAttribute('tabindex', '-1')
        roomFeedback.focus()
      }
    })
  })

  bookingDialog.addEventListener('close', async () => {
    const slot = selectedSlot
    selectedSlot = null

    if (!slot || bookingDialog.returnValue !== 'confirm') {
      slot?.focus()
      return
    }

    const confirmButton = bookingDialog.querySelector('.room-confirm')
    if (confirmButton) confirmButton.disabled = true

    try {
      const booking = await createBooking(slot)
      await refreshRoomSlots()
      roomFeedback.textContent = `Đặt chỗ thành công. Ghế của bạn: ${booking.seat}.`
    } catch (error) {
      roomFeedback.textContent = error instanceof Error
        ? error.message
        : 'Không thể đặt chỗ. Vui lòng thử lại.'
      await refreshRoomSlots()
    } finally {
      if (confirmButton) confirmButton.disabled = false
      roomFeedback.setAttribute('tabindex', '-1')
      roomFeedback.focus()
    }
  })

  window.addEventListener('pageshow', refreshRoomSlots)
  refreshRoomSlots()
}
