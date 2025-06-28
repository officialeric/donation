<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Add Orphanage</title>

    <!-- Google Font: Source Sans Pro -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../css/adminlte.min.css" />
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">
      <!-- Navbar -->
      <?php include 'header.php'; 
      if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../../index.php');
        exit;
      }
      
      include '../includes/connection.php';
      ?>
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
                <h1>Add New Orphanage</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="orphanages.php">Orphanages</a></li>
                  <li class="breadcrumb-item active">Add Orphanage</li>
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
                    <h3 class="card-title">Orphanage Information</h3>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <?php if(isset($_GET['success'])): ?>
                      <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $_GET['success']; ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_GET['error'])): ?>
                      <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $_GET['error']; ?>
                      </div>
                    <?php endif; ?>
                    
                    <form action="../includes/process-orphanage.php" method="post" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="name">Orphanage Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter orphanage name" required>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="location">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="City, Region/State" required>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the orphanage, its mission, and the children it serves..." required></textarea>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="contact_person">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Full name of primary contact" required>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="contact_phone">Contact Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" placeholder="+1234567890" required>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="contact_email">Contact Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="contact@orphanage.org" required>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                              <option value="active" selected>Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="bank_account">Bank Account Details</label>
                        <textarea class="form-control" id="bank_account" name="bank_account" rows="3" placeholder="Bank name, account number, routing number..."></textarea>
                      </div>
                      
                      <div class="form-group">
                        <label for="image">Orphanage Image</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Optional. Max size: 5MB.</small>
                      </div>
                      
                      <div class="form-group">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="terms" required>
                          <label class="custom-control-label" for="terms">
                            I confirm that all information provided is accurate and this orphanage is legitimate and authorized to receive donations.
                          </label>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <a href="orphanages.php" class="btn btn-secondary">
                          <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" name="add_orphanage" class="btn btn-primary">
                          <i class="fas fa-save"></i> Create Orphanage
                        </button>
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../js/adminlte.min.js"></script>
    <!-- Page specific script -->
    <script>
      $(function () {
        // Phone number formatting
        $('#contact_phone').on('input', function() {
          var value = $(this).val().replace(/\D/g, '');
          if (value.length > 0 && !value.startsWith('+')) {
            $(this).val('+' + value);
          }
        });
        
        // Email validation feedback
        $('#contact_email').on('blur', function() {
          var email = $(this).val();
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          
          if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
              $(this).after('<div class="invalid-feedback">Please enter a valid email address.</div>');
            }
          } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
          }
        });
        
        // Image preview
        $('#image').on('change', function() {
          var file = this.files[0];
          if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
              if (!$('#image-preview').length) {
                $('#image').after('<div id="image-preview" class="mt-2"><img src="" style="max-width: 200px; max-height: 200px;" class="img-thumbnail"></div>');
              }
              $('#image-preview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
          }
        });
      });
    </script>
  </body>
</html>
