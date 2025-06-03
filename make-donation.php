<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php?redirect=make-donation.php' . (isset($_GET['orphanage_id']) ? '?orphanage_id=' . $_GET['orphanage_id'] : ''));
  exit;
}

include 'dist/includes/connection.php';

if (!isset($_GET['orphanage_id']) || empty($_GET['orphanage_id'])) {
  header('Location: index.php');
  exit;
}

$orphanage_id = mysqli_real_escape_string($db, $_GET['orphanage_id']);
$sql = "SELECT * FROM orphanages WHERE id = '$orphanage_id' AND status = 'active'";
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) == 0) {
  header('Location: index.php');
  exit;
}

$orphanage = mysqli_fetch_assoc($result);
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Donation | Make Donation</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Donation | Make Donation" />
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
  <body class="bg-body-secondary">
    <div class="container mt-5">
      <div class="row">
        <div class="col-md-6 offset-md-3">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Make a Donation</h3>
            </div>
            <div class="card-body">
              <h5>Donating to: <?php echo $orphanage['name']; ?></h5>
              <p><i class="bi bi-geo-alt"></i> <?php echo $orphanage['location']; ?></p>
              
              <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
              <?php endif; ?>
              
              <form action="dist/includes/process-donation.php" method="post">
                <input type="hidden" name="orphanage_id" value="<?php echo $orphanage_id; ?>">
                
                <div class="mb-3">
                  <label for="amount" class="form-label">Donation Amount ($)</label>
                  <input type="number" class="form-control" id="amount" name="amount" min="1" required>
                </div>
                
                <div class="mb-3">
                  <label for="payment_method" class="form-label">Payment Method</label>
                  <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="">Select payment method</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                  </select>
                </div>
                
                <div class="mb-3">
                  <label for="message" class="form-label">Message (Optional)</label>
                  <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                </div>
                
                <div class="d-grid gap-2">
                  <button type="submit" name="process_donation" class="btn btn-primary">Proceed to Payment</button>
                  <a href="orphanage-details.php?id=<?php echo $orphanage_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
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