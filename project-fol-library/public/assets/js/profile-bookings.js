const bookingsList = document.querySelector('#bookings-list')
const bookingsEmpty = document.querySelector('#bookings-empty')
const bookingsFeedback = document.querySelector('#bookings-feedback')
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

function formatBookingDate(date) {
    const parsedDate = new Date(`${date}T00:00:00+07:00`)
    return new Intl.DateTimeFormat('vi-VN', {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'Asia/Ho_Chi_Minh',
    }).format(parsedDate)
}

function bookingStatus(booking) {
    if (booking.status === 'Huy') return 'Đã hủy'
    if (booking.status === 'DaNhanCho') return 'Đã check-in'
    if (booking.status === 'QuaHanKhongDen') return 'Không đến'

    const startsAt = Date.parse(booking.starts_at)
    return Number.isFinite(startsAt) && startsAt <= Date.now()
        ? 'Đã qua ca đọc'
        : 'Đã đặt'
}

async function loadBookings() {
    const response = await fetch('/api/reading-room/bookings', {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    const result = await response.json()

    if (!response.ok || result.success !== true || !Array.isArray(result.bookings)) {
        throw new Error(result.error ?? 'Không tải được lịch phòng đọc.')
    }

    return result.bookings
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
        throw new Error(result.error ?? 'Không thể hủy lượt đặt.')
    }
}

async function renderBookings() {
    if (!bookingsList || !bookingsEmpty || !bookingsFeedback) return

    bookingsList.replaceChildren()

    try {
        const bookings = await loadBookings()
        bookingsEmpty.hidden = bookings.length !== 0

        bookings.forEach((booking) => {
            const card = document.createElement('article')
            card.className = 'profile-booking'

            const title = document.createElement('h3')
            title.textContent = `${booking.shift} · ${formatBookingDate(booking.date)}`

            const details = document.createElement('p')
            details.textContent = `${booking.start.slice(0, 5)}–${booking.end.slice(0, 5)} · Ghế ${booking.seat}`

            const status = document.createElement('p')
            status.textContent = bookingStatus(booking)

            card.append(title, details, status)

            if (booking.status === 'DaDat') {
                const cancelButton = document.createElement('button')
                cancelButton.type = 'button'
                cancelButton.className = 'profile-reset'
                cancelButton.textContent = 'Hủy lịch'
                cancelButton.disabled = !booking.can_cancel
                cancelButton.addEventListener('click', async () => {
                    if (!window.confirm('Bạn muốn hủy lịch phòng đọc này?')) return

                    cancelButton.disabled = true
                    try {
                        await cancelBooking(Number(booking.id))
                        bookingsFeedback.textContent = 'Đã hủy lượt đặt chỗ.'
                        await renderBookings()
                    } catch (error) {
                        bookingsFeedback.textContent = error instanceof Error
                            ? error.message
                            : 'Không thể hủy lượt đặt chỗ.'
                        cancelButton.disabled = false
                    }
                })
                card.append(cancelButton)
                if (!booking.can_cancel) {
                    const note = document.createElement('p')
                    note.textContent = 'Chỉ có thể hủy trước ca đọc ít nhất 24 giờ.'
                    card.append(note)
                }
            }

            bookingsList.append(card)
        })
    } catch (error) {
        bookingsEmpty.hidden = true
        bookingsFeedback.textContent = error instanceof Error
            ? error.message
            : 'Không tải được lịch phòng đọc.'
    }
}

window.addEventListener('pageshow', renderBookings)
renderBookings()
