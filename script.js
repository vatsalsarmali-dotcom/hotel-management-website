document.addEventListener('DOMContentLoaded', () => {
  const setCurrentYear = () => {
    const yearEl = document.getElementById('year');
    if (yearEl) {
      yearEl.textContent = new Date().getFullYear();
    }
  };

  const setUserLabel = () => {
    const userLabel = document.getElementById('userDisplayName');
    const dashboardGreeting = document.getElementById('dashboardGreeting');
    const savedUser = JSON.parse(localStorage.getItem('grandstayUser') || 'null');

    if (savedUser && userLabel) {
      userLabel.textContent = `Welcome, ${savedUser.name}`;
    }

    if (savedUser && dashboardGreeting) {
      dashboardGreeting.textContent = `Hello, ${savedUser.name}!`;
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
  };

  const renderBookings = () => {
    const tableBody = document.getElementById('bookingTableBody');
    if (!tableBody) return;

    const storedBookings = JSON.parse(localStorage.getItem('grandstayBookings') || '[]');

    tableBody.innerHTML = storedBookings.length
      ? storedBookings
          .slice()
          .reverse()
          .map(
            (booking) => `
            <tr>
              <td>${booking.guestName}</td>
              <td>${booking.roomType}</td>
              <td>${formatDate(booking.checkIn)}</td>
              <td>${formatDate(booking.checkOut)}</td>
              <td><span class="status confirmed">Confirmed</span></td>
            </tr>
          `
          )
          .join('')
      : `
        <tr>
          <td colspan="5">No bookings yet. Create one from the booking form.</td>
        </tr>
      `;
  };

  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    const addBookingToTable = (booking) => {
      const storedBookings = JSON.parse(localStorage.getItem('grandstayBookings') || '[]');
      storedBookings.push(booking);
      localStorage.setItem('grandstayBookings', JSON.stringify(storedBookings));
      renderBookings();
    };

    bookingForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const guestName = document.getElementById('guestName').value.trim();
      const guestPhone = document.getElementById('guestPhone').value.trim();
      const roomType = document.getElementById('roomType').value;
      const guestCount = document.getElementById('guestCount').value;
      const checkIn = document.getElementById('checkIn').value;
      const checkOut = document.getElementById('checkOut').value;

      if (!guestName || !guestPhone || !roomType || !guestCount || !checkIn || !checkOut) {
        alert('Please fill in all booking details.');
        return;
      }

      if (new Date(checkOut) <= new Date(checkIn)) {
        alert('Check-out date must be after check-in date.');
        return;
      }

      const booking = { guestName, guestPhone, roomType, guestCount, checkIn, checkOut };
      addBookingToTable(booking);
      bookingForm.reset();
      alert('Booking confirmed successfully!');
    });
  }

  const roomSelectButtons = document.querySelectorAll('.room-select');
  roomSelectButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const roomType = button.closest('.room-card').querySelector('h3').textContent;
      const roomTypeField = document.getElementById('roomType');
      if (roomTypeField) {
        roomTypeField.value = roomType;
      }
      document.getElementById('booking')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  const authTabs = document.querySelectorAll('.auth-tab');
  const authForms = document.querySelectorAll('.auth-form');

  authTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.authTab;
      authTabs.forEach((item) => item.classList.toggle('active', item === tab));
      authForms.forEach((form) => {
        form.classList.toggle('active', form.dataset.form === target);
      });
    });
  });

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const email = document.getElementById('loginEmail').value.trim();
      const password = document.getElementById('loginPassword').value.trim();

      if (!email || !password) {
        alert('Please enter email and password.');
        return;
      }

      const savedUser = JSON.parse(localStorage.getItem('grandstayUser') || 'null');
      const userName = savedUser?.name || 'Admin';

      localStorage.setItem(
        'grandstayUser',
        JSON.stringify({
          name: userName,
          email,
          password,
        })
      );

      window.location.href = '../dashboard.html';
    });
  }

  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const name = document.getElementById('registerName').value.trim();
      const email = document.getElementById('registerEmail').value.trim();
      const password = document.getElementById('registerPassword').value.trim();

      if (!name || !email || !password) {
        alert('Please fill all fields to create your account.');
        return;
      }

      localStorage.setItem(
        'grandstayUser',
        JSON.stringify({ name, email, password })
      );

      alert('Registration successful!');
      window.location.href = '../dashboard.html';
    });
  }

  const signOutButton = document.getElementById('signOutButton');
  if (signOutButton) {
    signOutButton.addEventListener('click', () => {
      localStorage.removeItem('grandstayUser');
      window.location.href = 'index.html';
    });
  }

  setCurrentYear();
  setUserLabel();
  renderBookings();
});
