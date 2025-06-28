<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TumainiFuraha | Login</title>
    <!--begin::Favicon-->
    <link rel="icon" type="image/svg+xml" href="dist/images/logo-simple.svg">
    <link rel="alternate icon" href="dist/images/logo-simple.svg">
    <link rel="mask-icon" href="dist/images/logo-simple.svg" color="#667eea">
    <!--end::Favicon-->
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="TumainiFuraha | Login" />
    <meta name="author" content="TumainiFuraha" />
    <meta
      name="description"
      content="Login to TumainiFuraha to make donations to orphanages and bring hope to children."
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
        --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }

      body {
        font-family: 'Poppins', sans-serif;
        background: var(--primary-gradient);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow-x: hidden;
      }

      body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/><circle cx="900" cy="800" r="80" fill="url(%23a)"/></svg>') no-repeat center center;
        background-size: cover;
        pointer-events: none;
      }

      .login-container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
      }

      .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
      }

      .login-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-5px);
      }

      .login-left {
        background: var(--secondary-gradient);
        color: white;
        padding: 60px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
      }

      .login-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        pointer-events: none;
      }

      .login-right {
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
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background: white;
      }

      .form-floating label {
        color: #666;
        font-weight: 500;
      }

      .btn-login {
        background: var(--primary-gradient);
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

      .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
      }

      .btn-login:hover::before {
        left: 100%;
      }

      .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
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
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
      }

      .link-text:hover {
        color: #764ba2;
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

      .remember-me {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .remember-me input[type="checkbox"] {
        margin-right: 0.5rem;
        transform: scale(1.2);
      }

      @media (max-width: 768px) {
        .login-left {
          padding: 40px 20px;
        }

        .login-right {
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

    <div class="login-container">
      <div class="login-card">
        <div class="row g-0">
          <!-- Left Side - Branding -->
          <div class="col-lg-6 d-none d-lg-block">
            <div class="login-left">
              <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
              </div>

              <div class="donation-icon">
                <img src="dist/images/logo.svg" alt="TumainiFuraha Logo" width="80" height="80" style="filter: brightness(0) invert(1);">
              </div>

              <div class="brand-logo">
                TumainiFuraha
              </div>

              <div class="brand-tagline">
                Making a difference in children's lives, one donation at a time. Join our community of caring donors and help orphanages provide better care for children in need.
              </div>

              <div class="mt-4">
                <div class="d-flex justify-content-center align-items-center mb-3">
                  <div class="me-4 text-center">
                    <div class="h4 mb-0">500+</div>
                    <small>Donors</small>
                  </div>
                  <div class="me-4 text-center">
                    <div class="h4 mb-0">50+</div>
                    <small>Orphanages</small>
                  </div>
                  <div class="text-center">
                    <div class="h4 mb-0">$100K+</div>
                    <small>Donated</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Side - Login Form -->
          <div class="col-lg-6">
            <div class="login-right">
              <div class="text-center mb-4 d-lg-none">
                <h2 class="text-primary d-flex align-items-center justify-content-center">
                  <img src="dist/images/logo-simple.svg" alt="TumainiFuraha Logo" width="40" height="40" class="me-2">
                  TumainiFuraha
                </h2>
              </div>

              <h2 class="form-title">Welcome Back!</h2>
              <p class="form-subtitle">Sign in to continue making a difference</p>

              <?php if(isset($_GET['info'])) { ?>
                <div class="alert alert-success alert-custom">
                  <i class="bi bi-check-circle me-2"></i><?= $_GET['info'] ?>
                </div>
              <?php } else if(isset($_GET['error'])) { ?>
                <div class="alert alert-danger alert-custom">
                  <i class="bi bi-exclamation-triangle me-2"></i><?= $_GET['error'] ?>
                </div>
              <?php } ?>

              <form action="dist/includes/auth.php" method="post">
                <?php if(isset($_GET['redirect'])) { ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
                <?php } ?>

                <div class="form-floating">
                  <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required/>
                  <label for="email">
                    <i class="bi bi-envelope me-2"></i>Email Address
                  </label>
                </div>

                <div class="form-floating">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required/>
                  <label for="password">
                    <i class="bi bi-lock-fill me-2"></i>Password
                  </label>
                </div>

                <div class="remember-me">
                  <input type="checkbox" id="remember" name="remember" />
                  <label for="remember">Remember me for 30 days</label>
                </div>

                <button type="submit" name="login" class="btn-login">
                  <i class="bi bi-box-arrow-in-right me-2"></i>
                  Sign In to Continue
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
                  Don't have an account?
                  <a href="register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" class="link-text">
                    Create one now
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
  </body>
  <!--end::Body-->
</html>
