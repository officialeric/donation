<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Add Campaign</title>

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
      
      // Check if orphanage_id is provided for pre-selection
      $orphanage_filter = isset($_GET['orphanage_id']) ? mysqli_real_escape_string($db, $_GET['orphanage_id']) : '';
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
                <h1>Add New Campaign</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="campaigns.php">Campaigns</a></li>
                  <li class="breadcrumb-item active">Add Campaign</li>
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
                    <h3 class="card-title">Campaign Information</h3>
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
                    
                    <form action="../includes/process-campaign.php" method="post">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="orphanage_id">Orphanage <span class="text-danger">*</span></label>
                            <select class="form-control" id="orphanage_id" name="orphanage_id" required>
                              <option value="">Select Orphanage</option>
                              <?php
                                $orphanage_sql = "SELECT id, name FROM orphanages WHERE status = 'active' ORDER BY name";
                                $orphanage_result = mysqli_query($db, $orphanage_sql);
                                while($orphanage = mysqli_fetch_assoc($orphanage_result)) {
                                  $selected = ($orphanage_filter && $orphanage['id'] == $orphanage_filter) ? 'selected' : '';
                                  echo "<option value='" . $orphanage['id'] . "' $selected>" . htmlspecialchars($orphanage['name']) . "</option>";
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="priority">Priority <span class="text-danger">*</span></label>
                            <select class="form-control" id="priority" name="priority" required>
                              <option value="low">Low</option>
                              <option value="medium" selected>Medium</option>
                              <option value="high">High</option>
                              <option value="urgent">Urgent</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="title">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter campaign title" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe what this campaign is for and how it will help..." required></textarea>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="target_amount">Target Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="target_amount" name="target_amount" step="0.01" min="1" placeholder="0.00" required>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="deadline">Deadline <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deadline" name="deadline" required>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="terms" required>
                          <label class="custom-control-label" for="terms">
                            I confirm that all information provided is accurate and this campaign is legitimate.
                          </label>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <a href="campaigns.php<?php echo $orphanage_filter ? '?orphanage_id=' . $orphanage_filter : ''; ?>" class="btn btn-secondary">
                          <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" name="add_campaign" class="btn btn-primary">
                          <i class="fas fa-save"></i> Create Campaign
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
        // Set minimum date to today for deadline
        document.getElementById('deadline').min = new Date().toISOString().split('T')[0];
      });
    </script>
  </body>
</html>
