<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TumainiFuraha | Register</title>
    <!--begin::Favicon-->
    <link rel="icon" type="image/svg+xml" href="dist/images/logo-simple.svg">
    <link rel="alternate icon" href="dist/images/logo-simple.svg">
    <link rel="mask-icon" href="dist/images/logo-simple.svg" color="#667eea">
    <!--end::Favicon-->
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="TumainiFuraha | Register" />
    <meta name="author" content="TumainiFuraha" />
    <meta
      name="description"
      content="Join TumainiFuraha to make donations to orphanages and help bring hope and joy to children in need."
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <style>
      :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --register-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }

      body {
        font-family: 'Poppins', sans-serif;
        background: var(--register-gradient);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow-x: hidden;
        padding: 20px 0;
      }

      body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="150" cy="150" r="80" fill="url(%23a)"/><circle cx="850" cy="200" r="120" fill="url(%23a)"/><circle cx="300" cy="800" r="100" fill="url(%23a)"/><circle cx="950" cy="900" r="60" fill="url(%23a)"/></svg>') no-repeat center center;
        background-size: cover;
        pointer-events: none;
      }

      .register-container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
      }

      .register-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
      }

      .register-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-5px);
      }

      .register-left {
        background: var(--primary-gradient);
        color: white;
        padding: 60px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
      }

      .register-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="45" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="45" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        pointer-events: none;
      }

      .register-right {
        padding: 60px 40px;
      }

      .brand-logo {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(45deg, #fff, #f0f0f0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }

      .brand-tagline {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        line-height: 1.6;
      }

      .donation-icon {
        font-size: 4rem;
        margin-bottom: 2rem;
        opacity: 0.8;
      }

      .form-title {
        font-size: 2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
      }

      .form-subtitle {
        color: #666;
        margin-bottom: 2rem;
        font-size: 1rem;
      }

      .form-floating {
        margin-bottom: 1.5rem;
      }

      .form-floating .form-control {
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        padding: 1rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.8);
      }

      .form-floating .form-control:focus {
        border-color: #fa709a;
        box-shadow: 0 0 0 0.2rem rgba(250, 112, 154, 0.25);
        background: white;
      }

      .form-floating label {
        color: #666;
        font-weight: 500;
      }

      .btn-register {
        background: var(--register-gradient);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }

      .btn-register::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
      }

      .btn-register:hover::before {
        left: 100%;
      }

      .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(250, 112, 154, 0.3);
      }

      .divider {
        text-align: center;
        margin: 2rem 0;
        position: relative;
        color: #666;
      }

      .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e1e5e9;
      }

      .divider span {
        background: rgba(255, 255, 255, 0.95);
        padding: 0 1rem;
        font-size: 0.9rem;
      }

      .link-text {
        color: #fa709a;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
      }

      .link-text:hover {
        color: #fee140;
        text-decoration: underline;
      }

      .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
      }

      .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
      }

      .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
      }

      .terms-checkbox {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        color: #666;
      }

      .terms-checkbox input[type="checkbox"] {
        margin-right: 0.5rem;
        margin-top: 0.2rem;
        transform: scale(1.2);
      }

      .password-strength {
        margin-top: 0.5rem;
        font-size: 0.8rem;
      }

      .strength-bar {
        height: 4px;
        border-radius: 2px;
        background: #e1e5e9;
        margin-top: 0.5rem;
        overflow: hidden;
      }

      .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
      }

      .strength-weak { background: #dc3545; width: 25%; }
      .strength-fair { background: #ffc107; width: 50%; }
      .strength-good { background: #28a745; width: 75%; }
      .strength-strong { background: #007bff; width: 100%; }

      @media (max-width: 768px) {
        .register-left {
          padding: 40px 20px;
        }

        .register-right {
          padding: 40px 20px;
        }

        .brand-logo {
          font-size: 2rem;
        }

        .form-title {
          font-size: 1.5rem;
        }
      }

      .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
      }

      .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
      }

      .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 20%;
        left: 10%;
        animation-delay: 0s;
      }

      .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 10%;
        animation-delay: 2s;
      }

      .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 20%;
        animation-delay: 4s;
      }

      @keyframes float {
        0%, 100% {
          transform: translateY(0px) rotate(0deg);
        }
        50% {
          transform: translateY(-20px) rotate(180deg);
        }
      }

      .benefits-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
      }

      .benefits-list li {
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
      }

      .benefits-list li i {
        margin-right: 0.5rem;
        color: rgba(255, 255, 255, 0.8);
      }
    </style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body>
    <div class="floating-shapes">
      <div class="shape"></div>
      <div class="shape"></div>
      <div class="shape"></div>
    </div>

    <div class="register-container">
      <div class="register-card">
        <div class="row g-0">
          <!-- Left Side - Branding -->
          <div class="col-lg-5 d-none d-lg-block">
            <div class="register-left">
              <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
              </div>

              <div class="donation-icon">
                <img src="dist/images/logo.svg" alt="TumainiFuraha Logo" width="80" height="80" style="filter: brightness(0) invert(1);">
              </div>

              <div class="brand-logo">
                Join TumainiFuraha
              </div>

              <div class="brand-tagline">
                Become part of a caring community dedicated to improving children's lives. Your registration opens the door to making meaningful contributions to orphanages worldwide.
              </div>

              <ul class="benefits-list">
                <li>
                  <i class="bi bi-check-circle-fill"></i>
                  Make secure donations to verified orphanages
                </li>
                <li>
                  <i class="bi bi-check-circle-fill"></i>
                  Track your donation impact and history
                </li>
                <li>
                  <i class="bi bi-check-circle-fill"></i>
                  Receive updates on how your donations help
                </li>
                <li>
                  <i class="bi bi-check-circle-fill"></i>
                  Connect with a community of caring donors
                </li>
              </ul>

              <div class="mt-4">
                <div class="d-flex justify-content-center align-items-center">
                  <div class="me-4 text-center">
                    <div class="h4 mb-0">500+</div>
                    <small>Happy Donors</small>
                  </div>
                  <div class="me-4 text-center">
                    <div class="h4 mb-0">50+</div>
                    <small>Orphanages Helped</small>
                  </div>
                  <div class="text-center">
                    <div class="h4 mb-0">$100K+</div>
                    <small>Lives Changed</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Side - Register Form -->
          <div class="col-lg-7">
            <div class="register-right">
              <div class="text-center mb-4 d-lg-none">
                <h2 class="text-primary d-flex align-items-center justify-content-center">
                  <img src="dist/images/logo-simple.svg" alt="TumainiFuraha Logo" width="40" height="40" class="me-2">
                  TumainiFuraha
                </h2>
              </div>

              <h2 class="form-title">Create Your Account</h2>
              <p class="form-subtitle">Join us in making a difference in children's lives</p>

              <?php if(isset($_GET['error'])) : ?>
                <div class="alert alert-danger alert-custom">
                  <i class="bi bi-exclamation-triangle me-2"></i><?=$_GET['error']  ?>
                </div>
              <?php endif ?>

              <form action="dist/includes/auth.php" method="post" id="registerForm">
                <?php if(isset($_GET['redirect'])) { ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
                <?php } ?>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required/>
                      <label for="username">
                        <i class="bi bi-person me-2"></i>Username
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required/>
                      <label for="email">
                        <i class="bi bi-envelope me-2"></i>Email Address
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-floating">
                  <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required/>
                  <label for="phone">
                    <i class="bi bi-phone me-2"></i>Phone Number
                  </label>
                </div>

                <div class="form-floating">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required/>
                  <label for="password">
                    <i class="bi bi-lock-fill me-2"></i>Password
                  </label>
                  <div class="password-strength">
                    <div class="strength-bar">
                      <div class="strength-fill" id="strengthBar"></div>
                    </div>
                    <small id="strengthText" class="text-muted">Password strength: <span id="strengthLevel">Enter a password</span></small>
                  </div>
                </div>

                <div class="terms-checkbox">
                  <input type="checkbox" id="terms" name="terms" required />
                  <label for="terms">
                    I agree to the <a href="#" class="link-text">Terms of Service</a> and <a href="#" class="link-text">Privacy Policy</a>. I understand that my donations will help support orphanages and children in need.
                  </label>
                </div>

                <button type="submit" name="register" class="btn-register" id="registerBtn">
                  <i class="bi bi-person-plus me-2"></i>
                  Create My Account
                </button>
              </form>

              <div class="divider">
                <span>or</span>
              </div>

              <div class="text-center">
                <p class="mb-2">
                  <a href="index.php" class="link-text">
                    <i class="bi bi-arrow-left me-1"></i>Back to Orphanages
                  </a>
                </p>
                <p class="mb-0">
                  Already have an account?
                  <a href="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" class="link-text">
                    Sign in here
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <script>
      // Add interactive effects and validation
      document.addEventListener('DOMContentLoaded', function() {
        // Floating label effect
        const inputs = document.querySelectorAll('.form-floating input');
        inputs.forEach(input => {
          input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
          });

          input.addEventListener('blur', function() {
            if (!this.value) {
              this.parentElement.classList.remove('focused');
            }
          });
        });

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthLevel = document.getElementById('strengthLevel');

        if (passwordInput && strengthBar && strengthLevel) {
          passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);

            strengthBar.className = 'strength-fill strength-' + strength.level;
            strengthLevel.textContent = strength.text;
          });
        }

        function checkPasswordStrength(password) {
          let score = 0;

          if (password.length >= 8) score++;
          if (/[a-z]/.test(password)) score++;
          if (/[A-Z]/.test(password)) score++;
          if (/[0-9]/.test(password)) score++;
          if (/[^A-Za-z0-9]/.test(password)) score++;

          if (score < 2) return { level: 'weak', text: 'Weak' };
          if (score < 3) return { level: 'fair', text: 'Fair' };
          if (score < 4) return { level: 'good', text: 'Good' };
          return { level: 'strong', text: 'Strong' };
        }

        // Add loading state to register button
        const registerForm = document.getElementById('registerForm');
        const registerBtn = document.getElementById('registerBtn');

        if (registerForm && registerBtn) {
          registerForm.addEventListener('submit', function() {
            registerBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Creating Account...';
            registerBtn.disabled = true;
          });
        }

        // Animate elements on load
        const card = document.querySelector('.register-card');
        if (card) {
          card.style.opacity = '0';
          card.style.transform = 'translateY(30px)';

          setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, 100);
        }

        // Email validation
        const emailInput = document.getElementById('email');
        if (emailInput) {
          emailInput.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
              this.classList.add('is-invalid');
              if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Please enter a valid email address.';
                this.parentNode.appendChild(feedback);
              }
            } else {
              this.classList.remove('is-invalid');
              const feedback = this.parentNode.querySelector('.invalid-feedback');
              if (feedback) feedback.remove();
            }
          });
        }

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
          phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('+')) {
              this.value = '+' + value;
            }
          });
        }
      });
    </script>
  </body>
  <!--end::Body-->
</html>
