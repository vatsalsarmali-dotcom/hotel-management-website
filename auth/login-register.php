<?php
  $submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | GrandStay Hotel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../style.css" />
  </head>
  <body>
    <div class="auth-shell">
      <div class="auth-panel">
        <div class="auth-grid">
          <aside class="auth-side">
            <span class="eyebrow" style="color:#fff;">GrandStay</span>
            <h1>Welcome back</h1>
            <p>Manage bookings, room availability, restaurant orders, and guest experiences in one place.</p>
            <ul>
              <li>✓ Staff management</li>
              <li>✓ Real-time reservation updates</li>
              <li>✓ Hotel operations dashboard</li>
            </ul>
          </aside>

          <div class="auth-form-box">
            <div class="auth-tabs">
              <button class="auth-tab active" type="button" data-auth-tab="login">Login</button>
              <button class="auth-tab" type="button" data-auth-tab="register">Register</button>
            </div>

            <div class="auth-forms">
              <form id="loginForm" class="auth-form active" data-form="login">
                <div class="form-row">
                  <label>
                    Email
                    <input id="loginEmail" type="email" placeholder="admin@grandstay.com" required />
                  </label>
                </div>

                <div class="form-row">
                  <label>
                    Password
                    <input id="loginPassword" type="password" placeholder="Enter password" required />
                  </label>
                </div>

                <button class="btn btn-primary btn-block" type="submit">Login to Dashboard</button>
                <p class="secure-note">Demo credentials: admin / admin123</p>
              </form>

              <form id="registerForm" class="auth-form" data-form="register">
                <div class="form-row">
                  <label>
                    Full Name
                    <input id="registerName" type="text" placeholder="Your name" required />
                  </label>
                </div>

                <div class="form-row">
                  <label>
                    Email
                    <input id="registerEmail" type="email" placeholder="you@example.com" required />
                  </label>
                </div>

                <div class="form-row">
                  <label>
                    Password
                    <input id="registerPassword" type="password" placeholder="Create password" required />
                  </label>
                </div>

                <button class="btn btn-primary btn-block" type="submit">Create Account</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="../script.js"></script>
  </body>
</html>
