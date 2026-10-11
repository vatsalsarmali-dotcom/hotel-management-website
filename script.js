const bookingForm = document.getElementById('bookingForm');
const bookingTableBody = document.getElementById('bookingTableBody');

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
};

const addBookingToTable = (guestName, roomType, checkIn, checkOut) => {
  const newRow = document.createElement('tr');
  newRow.innerHTML = `
    <td>${guestName}</td>
    <td>${roomType}</td>
    <td>${formatDate(checkIn)}</td>
    <td>${formatDate(checkOut)}</td>
    <td><span class="status confirmed">Confirmed</span></td>
  `;

  bookingTableBody.prepend(newRow);
};

bookingForm.addEventListener('submit', (event) => {
  event.preventDefault();

  const guestName = document.getElementById('guestName').value.trim();
  const roomType = document.getElementById('roomType').value;
  const checkIn = document.getElementById('checkIn').value;
  const checkOut = document.getElementById('checkOut').value;

  if (!guestName || !roomType || !checkIn || !checkOut) {
    alert('Please fill in all required booking details.');
    return;
  }

  if (new Date(checkOut) <= new Date(checkIn)) {
    alert('Check-out date must be after check-in date.');
    return;
  }

  addBookingToTable(guestName, roomType, checkIn, checkOut);
  bookingForm.reset();
  alert('Booking confirmed successfully!');
});

const roomSelectButtons = document.querySelectorAll('.room-select');
roomSelectButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const roomType = button.closest('.room-card').querySelector('h3').textContent;
    document.getElementById('roomType').value = roomType;
    document.getElementById('booking').scrollIntoView({ behavior: 'smooth' });
  });
});
