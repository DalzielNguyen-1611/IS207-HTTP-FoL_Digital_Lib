//Các thành phần lịch đặt phòng trong profile
const bookingsKey = 'fol-demo-bookings-v1'
const bookingsList = document.querySelector('#bookings-list')
const bookingsEmpty = document.querySelector('#bookings-empty')
const bookingsFeedback = document.querySelector('#bookings-feedback')
const cancellationWindow = 72 * 60 * 60 * 1000

//Đọc lịch mẫu
function readBookings() {
    const records = JSON.parse(
        localStorage.getItem(bookingsKey) ?? '[]'
    )

    if (!Array.isArray(records)) {
        throw new Error('Dữ liệu lịch không hợp lệ')
    }

    return records
}

//Hiển thị lịch bằng textContent để không chèn HTML từ dữ liệu lưu trữ
function renderBookings() {
    if (!bookingsList || !bookingsEmpty || !bookingsFeedback) return

    bookingsList.replaceChildren()

    try {
        const bookings = readBookings()
        bookingsEmpty.hidden = bookings.length !== 0

        bookings.forEach((booking, index) => {
            const startsAt = Date.parse(booking.start)
            const cancelled = booking.status === 'cancelled'
            const validDate = Number.isFinite(startsAt)
            const completed = validDate && startsAt <= Date.now()
            const canCancel = 
                !cancelled &&
                booking.status === 'active' &&
                validDate &&
                startsAt - Date.now() >= cancellationWindow
            
            const card = document.createElement('article')
            card.className = 'profile-booking'

            const title = document.createElement('h3')
            title.textContent = String(booking.label ?? 'Lịch đọc')
            
            const status = document.createElement('p')
            status.textContent = cancelled
                ? 'Đã hủy'
                : !validDate
                    ? 'Ngày ca đọc không hợp lệ'
                    : completed
                        ? 'Ca đã kết thúc thời gian bắt đầu'
                        : 'Đã đặt mẫu'
            
            card.append(title, status)

            if (!cancelled && !completed && validDate) {
                const cancelButton = document.createElement('button')
                cancelButton.type = 'button'
                cancelButton.className = 'profile-reset'
                cancelButton.textContent = 'Hủy lịch'
                cancelButton.disabled = !canCancel

                cancelButton.addEventListener('click', () => {
                    cancelBooking(index)
                })

                card.append(cancelButton)

                if (!canCancel) {
                    const note = document.createElement('p')
                    note.textContent = 'Không thể hủy vì còn dưới 72 giờ trước ca đọc'
                    card.append(note)
                }
            }

            bookingsList.append(card)
        })
    } catch {
        bookingsEmpty.hidden = true
        bookingsFeedback.textContent = 'Không đọc được lịch phòng đọc trên trình duyệt'
    }
}

//Kiểm tra lại thời gian ngay lúc này
function cancelBooking(index) {
    if (!window.confirm('Bạn muốn hủy lịch phòng đọc này?')) return

    try {
        const bookings = readBookings()
        const booking = bookings[index]
        const startsAt = Date.parse(booking?.start)

        if (
            !booking ||
            booking.status !== 'active' ||
            !Number.isFinite(startsAt) ||
            startsAt - Date.now() < cancellationWindow
        ) {
            bookingsFeedback.textContent = 'Lịch không còn đủ điều kiện hủy'
            renderBookings()
            return
        }

        booking.status = 'cancelled'
        booking.cancelledAt = new Date().toISOString()

        localStorage.setItem(bookingsKey, JSON.stringify(bookings))
        renderBookings()
        bookingsFeedback.textContent = 'Đã hủy lịch mẫu'
    } catch {
        bookingsFeedback.textContent = 'Không cập nhật được lịch'
    }
}

//Khôi phục dữ liệu và đồng bộ giữa các tab
window.addEventListener('pageshow', renderBookings)
window.addEventListener('storage', (event) => {
    if (event.key === bookingsKey || event.key === null) {
        renderBookings()
    }
})

renderBookings()