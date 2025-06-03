<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Donation | Orphanages</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Donation | Orphanages" />
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
        <div class="col-12 text-center mb-4">
          <h1>Orphanages</h1>
          <p>Select an orphanage to donate</p>
        </div>
        
        <?php
          include 'dist/includes/connection.php';
          
          $sql = "SELECT * FROM orphanages";
          $result = mysqli_query($db, $sql);
          
          if (mysqli_num_rows($result) > 0) {
            while($orphanage = mysqli_fetch_assoc($result)) {
        ?>
        
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="dist/uploads/orphanages/<?php echo $orphanage['image']; ?>" class="card-img-top" alt="<?php echo $orphanage['name']; ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <h5 class="card-title"><?php echo $orphanage['name']; ?></h5>
              <p class="card-text"><?php echo substr($orphanage['description'], 0, 100); ?>...</p>
              <p><i class="bi bi-geo-alt"></i> <?php echo $orphanage['location']; ?></p>
              <a href="orphanage-details.php?id=<?php echo $orphanage['id']; ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
        
        <?php
            }
          } else {
            echo '<div class="col-12 text-center"><p>No orphanages found</p></div>';
          }
        ?>
      </div>
      
      <div class="text-center mt-3 mb-5">
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
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