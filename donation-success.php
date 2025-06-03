<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['transaction_id'])) {
  header('Location: index.php');
  exit;
}

include 'dist/includes/connection.php';

$transaction_id = $_GET['transaction_id'];
$sql = "SELECT d.*, o.name as orphanage_name 
        FROM donations d 
        JOIN orphanages o ON d.orphanage_id = o.id 
        WHERE d.transaction_id = '$transaction_id' AND d.user_id = " . $_SESSION['user_id'];
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) == 0) {
  header('Location: index.php');
  exit;
}

$donation = mysqli_fetch_assoc($result);
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Donation | Success</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Donation | Success" />
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
            <div class="card-body text-center">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
              <h2 class="mt-3">Thank You!</h2>
              <p class="lead">Your donation has been processed successfully.</p>
              
              <div class="alert alert-success mt-4">
                <h5>Donation Details:</h5>
                <p><strong>Transaction ID:</strong> <?php echo $donation['transaction_id']; ?></p>
                <p><strong>Amount:</strong> $<?php echo number_format($donation['amount'], 2); ?></p>
                <p><strong>Orphanage:</strong> <?php echo $donation['orphanage_name']; ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($donation['created_at'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $donation['payment_method'])); ?></p>
              </div>
              
              <p>A confirmation email has been sent to your registered email address.</p>
              
              <div class="mt-4">
                <a href="dist/pages/donor/index.php" class="btn btn-primary">Go to Dashboard</a>
                <a href="orphanages.php" class="btn btn-secondary">Donate Again</a>
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
