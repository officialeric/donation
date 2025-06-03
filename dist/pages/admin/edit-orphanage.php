<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../../index.php');
  exit;
}

include '../../includes/connection.php';

if (!isset($_GET['id'])) {
  header('Location: orphanages.php');
  exit;
}

$id = mysqli_real_escape_string($db, $_GET['id']);
$sql = "SELECT * FROM orphanages WHERE id = $id";
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) == 0) {
  header('Location: orphanages.php?error=Orphanage not found');
  exit;
}

$orphanage = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Edit Orphanage</title>

    <!-- Google Font: Source Sans Pro -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../../css/adminlte.min.css" />
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">
      <!-- Navbar -->
      <?php include 'navbar.php'; ?>
      <!-- /.navbar -->

      <!-- Main Sidebar Container -->
      <?php include 'sidebar.php'; ?>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1>Edit Orphanage</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="orphanages.php">Orphanages</a></li>
                  <li class="breadcrumb-item active">Edit Orphanage</li>
                </ol>
              </div>
            </div>
          </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Edit Orphanage Details</h3>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <form action="../../includes/process-orphanage.php" method="post">
                      <input type="hidden" name="orphanage_id" value="<?php echo $orphanage['id']; ?>">
                      
                      <div class="form-group">
                        <label for="name">Orphanage Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $orphanage['name']; ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo $orphanage['location']; ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $orphanage['description']; ?></textarea>
                      </div>
                      
                      <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo $orphanage['contact_person']; ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo $orphanage['contact_phone']; ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $orphanage['contact_email']; ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="bank_account">Bank Account Details</label>
                        <textarea class="form-control" id="bank_account" name="bank_account" rows="2" required><?php echo $orphanage['bank_account']; ?></textarea>
                      </div>
                      
                      <div class="form-group">
                        <a href="orphanages.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="update_orphanage" class="btn btn-primary">Update Orphanage</button>
                      </div>
                    </form>
                  </div>
                  <!-- /.card-body -->
                </div>
                <!-- /.card -->
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
          </div>
          <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
      
      <?php include 'footer.php'; ?>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="../../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../js/adminlte.min.js"></script>
  </body>
</html>