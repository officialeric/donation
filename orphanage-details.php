<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Donation | Orphanage Details</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Donation | Orphanage Details" />
    <meta name="author" content="Donation Platform" />
    <meta
      name="description"
      content="Donation platform for orphanages"
    />
    <meta
      name="keywords"
      content="donation, orphanage, charity"
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
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
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
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="bg-light">
    <?php
      session_start();
      include 'dist/includes/connection.php';

      if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: index.php');
        exit;
      }

      $id = mysqli_real_escape_string($db, $_GET['id']);
      $sql = "SELECT * FROM orphanages WHERE id = '$id' AND status = 'active'";
      $result = mysqli_query($db, $sql);

      if (mysqli_num_rows($result) == 0) {
        header('Location: index.php');
        exit;
      }

      $orphanage = mysqli_fetch_assoc($result);
    ?>

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

    <div class="container mt-5">
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <div class="card shadow">
            <?php if(!empty($orphanage['image'])): ?>
            <img src="dist/uploads/orphanages/<?php echo htmlspecialchars($orphanage['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($orphanage['name']); ?>" style="height: 300px; object-fit: cover;">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
              <i class="bi bi-house-heart text-muted" style="font-size: 4rem;"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
              <h2 class="card-title text-primary"><?php echo htmlspecialchars($orphanage['name']); ?></h2>
              <p class="card-text"><?php echo htmlspecialchars($orphanage['description']); ?></p>

              <div class="mt-4">
                <h5>Contact Information</h5>
                <div class="row">
                  <div class="col-md-6">
                    <p><i class="bi bi-geo-alt text-primary"></i> <strong>Location:</strong><br><?php echo htmlspecialchars($orphanage['location']); ?></p>
                    <p><i class="bi bi-person text-primary"></i> <strong>Contact Person:</strong><br><?php echo htmlspecialchars($orphanage['contact_person']); ?></p>
                  </div>
                  <div class="col-md-6">
                    <p><i class="bi bi-telephone text-primary"></i> <strong>Phone:</strong><br><?php echo htmlspecialchars($orphanage['contact_phone']); ?></p>
                    <p><i class="bi bi-envelope text-primary"></i> <strong>Email:</strong><br><?php echo htmlspecialchars($orphanage['contact_email']); ?></p>
                  </div>
                </div>
              </div>

              <!-- Current Campaigns Section -->
              <?php
                $campaigns_sql = "SELECT * FROM campaigns WHERE orphanage_id = '" . $orphanage['id'] . "' AND status = 'active' ORDER BY priority DESC, deadline ASC";
                $campaigns_result = mysqli_query($db, $campaigns_sql);

                if (mysqli_num_rows($campaigns_result) > 0):
              ?>
              <div class="mt-4">
                <h4 class="text-primary mb-3">
                  <i class="bi bi-megaphone me-2"></i>Current Campaigns
                </h4>
                <div class="row">
                  <?php while($campaign = mysqli_fetch_assoc($campaigns_result)):
                    $progress = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                    $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                    $priority_class = $campaign['priority'] == 'urgent' ? 'danger' : ($campaign['priority'] == 'high' ? 'warning' : ($campaign['priority'] == 'medium' ? 'info' : 'secondary'));
                    $days_remaining = (strtotime($campaign['deadline']) - time()) / (60 * 60 * 24);
                  ?>
                  <div class="col-md-6 mb-3">
                    <div class="card h-100 border-<?php echo $priority_class; ?>">
                      <div class="card-header bg-<?php echo $priority_class; ?> text-white">
                        <h6 class="card-title mb-0">
                          <?php echo htmlspecialchars($campaign['title']); ?>
                          <span class="badge badge-light float-end"><?php echo ucfirst($campaign['priority']); ?></span>
                        </h6>
                      </div>
                      <div class="card-body">
                        <p class="card-text"><?php echo htmlspecialchars(substr($campaign['description'], 0, 150)) . (strlen($campaign['description']) > 150 ? '...' : ''); ?></p>

                        <div class="mb-3">
                          <div class="d-flex justify-content-between mb-1">
                            <small>Progress</small>
                            <small><?php echo round($progress, 1); ?>%</small>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-<?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%"></div>
                          </div>
                        </div>

                        <div class="row text-center">
                          <div class="col-6">
                            <strong class="text-<?php echo $progress_class; ?>">$<?php echo number_format($campaign['current_amount'], 0); ?></strong>
                            <br><small class="text-muted">Raised</small>
                          </div>
                          <div class="col-6">
                            <strong>$<?php echo number_format($campaign['target_amount'], 0); ?></strong>
                            <br><small class="text-muted">Goal</small>
                          </div>
                        </div>

                        <div class="mt-2 text-center">
                          <small class="text-muted">
                            <i class="bi bi-calendar"></i>
                            <?php
                              if ($days_remaining > 0) {
                                echo ceil($days_remaining) . " days left";
                              } elseif ($days_remaining == 0) {
                                echo "Ends today";
                              } else {
                                echo "Campaign ended";
                              }
                            ?>
                          </small>
                        </div>
                      </div>
                      <div class="card-footer">
                        <?php if(isset($_SESSION['user_id'])): ?>
                          <a href="make-donation.php?orphanage_id=<?php echo $orphanage['id']; ?>&campaign_id=<?php echo $campaign['id']; ?>" class="btn btn-<?php echo $priority_class; ?> btn-sm w-100">
                            <i class="bi bi-heart-fill me-1"></i>Donate to This Campaign
                          </a>
                        <?php else: ?>
                          <a href="register.php?redirect=make-donation.php?orphanage_id=<?php echo $orphanage['id']; ?>&campaign_id=<?php echo $campaign['id']; ?>" class="btn btn-<?php echo $priority_class; ?> btn-sm w-100">
                            <i class="bi bi-heart-fill me-1"></i>Register to Donate
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?php endwhile; ?>
                </div>
              </div>
              <?php endif; ?>

              <div class="mt-4 text-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                  <a href="make-donation.php?orphanage_id=<?php echo $orphanage['id']; ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-heart-fill me-2"></i>Donate Now
                  </a>
                <?php else: ?>
                  <a href="register.php?redirect=make-donation.php?orphanage_id=<?php echo $orphanage['id']; ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-heart-fill me-2"></i>Register to Donate
                  </a>
                  <div class="mt-2">
                    <small class="text-muted">Already have an account? <a href="login.php?redirect=make-donation.php?orphanage_id=<?php echo $orphanage['id']; ?>">Login here</a></small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="text-center mt-3 mb-5">
            <a href="index.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i>Back to All Orphanages
            </a>
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
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
  </body>
  <!--end::Body-->
</html>