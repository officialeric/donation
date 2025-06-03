<?php
session_start();
include 'dist/includes/connection.php';
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Donation Platform | Support Orphanages</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Donation Platform | Support Orphanages" />
    <meta name="author" content="Donation Platform" />
    <meta
      name="description"
      content="Support orphanages by making donations. Browse through our list of verified orphanages and make a difference in children's lives."
    />
    <meta
      name="keywords"
      content="donation, orphanage, charity, children, support, help, donate"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
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
      .orphanage-card {
        transition: transform 0.2s;
      }
      .orphanage-card:hover {
        transform: translateY(-5px);
      }
      .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 0;
      }
    </style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
          <i class="bi bi-heart-fill me-2"></i>Donation Platform
        </a>

        <div class="navbar-nav ms-auto">
          <?php if(isset($_SESSION['user_id'])): ?>
            <span class="navbar-text me-3">Welcome, <?= $_SESSION['username'] ?>!</span>
            <?php if($_SESSION['role'] == 'admin'): ?>
              <a href="dist/pages/index.php" class="btn btn-outline-light me-2">Admin Dashboard</a>
            <?php else: ?>
              <a href="dist/pages/donor/index.php" class="btn btn-outline-light me-2">My Dashboard</a>
            <?php endif; ?>
            <form action="dist/includes/auth.php" method="post" class="d-inline">
              <button type="submit" name="logout" class="btn btn-outline-light">Logout</button>
            </form>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-light me-2">Login</a>
            <a href="register.php" class="btn btn-light">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
      <div class="container">
        <h1 class="display-4 fw-bold mb-4">Make a Difference Today</h1>
        <p class="lead mb-4">Support orphanages and help children build a brighter future. Every donation counts.</p>

        <?php if(isset($_GET['info'])) { ?>
        <div class="alert alert-success d-inline-block"><?= $_GET['info'] ?></div>
        <?php } else if(isset($_GET['error'])) { ?>
        <div class="alert alert-danger d-inline-block"><?= $_GET['error'] ?></div>
        <?php } ?>
      </div>
    </section>

    <!-- Orphanages Section -->
    <section class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h2 class="fw-bold">Our Partner Orphanages</h2>
            <p class="text-muted">Choose an orphanage to support and make a donation</p>
          </div>
        </div>

        <div class="row">
          <?php
            $sql = "SELECT * FROM orphanages WHERE status = 'active' ORDER BY name";
            $result = mysqli_query($db, $sql);

            if (mysqli_num_rows($result) > 0) {
              while($orphanage = mysqli_fetch_assoc($result)) {
          ?>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card orphanage-card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-primary"><?= htmlspecialchars($orphanage['name']) ?></h5>
                <p class="card-text">
                  <i class="bi bi-geo-alt text-muted me-1"></i>
                  <?= htmlspecialchars($orphanage['location']) ?>
                </p>
                <p class="card-text"><?= htmlspecialchars(substr($orphanage['description'], 0, 120)) ?>...</p>

                <div class="mt-3">
                  <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($orphanage['contact_person']) ?></p>
                  <p class="mb-1"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($orphanage['contact_phone']) ?></p>
                </div>
              </div>
              <div class="card-footer bg-transparent">
                <div class="d-grid gap-2">
                  <a href="orphanage-details.php?id=<?= $orphanage['id'] ?>" class="btn btn-outline-primary">View Details</a>
                  <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="make-donation.php?orphanage_id=<?= $orphanage['id'] ?>" class="btn btn-primary">
                      <i class="bi bi-heart-fill me-1"></i>Donate Now
                    </a>
                  <?php else: ?>
                    <a href="register.php?redirect=make-donation.php?orphanage_id=<?= $orphanage['id'] ?>" class="btn btn-primary">
                      <i class="bi bi-heart-fill me-1"></i>Register to Donate
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php
              }
            } else {
          ?>
          <div class="col-12 text-center">
            <div class="alert alert-info">
              <h4>No orphanages available at the moment</h4>
              <p>Please check back later or contact the administrator.</p>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h5>Donation Platform</h5>
            <p class="mb-0">Making a difference in children's lives, one donation at a time.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <p class="mb-0">&copy; 2025 Donation Platform. All rights reserved.</p>
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
              <small><a href="dist/pages/index.php" class="text-light">Admin Panel</a></small>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </footer>

    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
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
