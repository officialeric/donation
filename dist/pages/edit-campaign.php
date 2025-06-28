<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Edit Campaign</title>

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
      
      if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: campaigns.php');
        exit;
      }
      
      $campaign_id = mysqli_real_escape_string($db, $_GET['id']);
      $sql = "SELECT c.*, o.name as orphanage_name FROM campaigns c 
              LEFT JOIN orphanages o ON c.orphanage_id = o.id 
              WHERE c.id = '$campaign_id'";
      $result = mysqli_query($db, $sql);
      
      if (mysqli_num_rows($result) == 0) {
        header('Location: campaigns.php');
        exit;
      }
      
      $campaign = mysqli_fetch_assoc($result);
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
                <h1>Edit Campaign</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="campaigns.php">Campaigns</a></li>
                  <li class="breadcrumb-item active">Edit Campaign</li>
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
                    <h3 class="card-title">Edit Campaign Details</h3>
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
                      <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="orphanage_id">Orphanage</label>
                            <select class="form-control" id="orphanage_id" name="orphanage_id" required>
                              <option value="">Select Orphanage</option>
                              <?php
                                $orphanage_sql = "SELECT id, name FROM orphanages WHERE status = 'active' ORDER BY name";
                                $orphanage_result = mysqli_query($db, $orphanage_sql);
                                while($orphanage = mysqli_fetch_assoc($orphanage_result)) {
                                  $selected = ($orphanage['id'] == $campaign['orphanage_id']) ? 'selected' : '';
                                  echo "<option value='" . $orphanage['id'] . "' $selected>" . htmlspecialchars($orphanage['name']) . "</option>";
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" id="priority" name="priority" required>
                              <option value="low" <?php echo ($campaign['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                              <option value="medium" <?php echo ($campaign['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                              <option value="high" <?php echo ($campaign['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                              <option value="urgent" <?php echo ($campaign['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="title">Campaign Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($campaign['title']); ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($campaign['description']); ?></textarea>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label for="target_amount">Target Amount ($)</label>
                            <input type="number" class="form-control" id="target_amount" name="target_amount" step="0.01" min="1" value="<?php echo $campaign['target_amount']; ?>" required>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label for="deadline">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo $campaign['deadline']; ?>" required>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                              <option value="active" <?php echo ($campaign['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                              <option value="paused" <?php echo ($campaign['status'] == 'paused') ? 'selected' : ''; ?>>Paused</option>
                              <option value="completed" <?php echo ($campaign['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                              <option value="cancelled" <?php echo ($campaign['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Current Amount</label>
                            <div class="form-control-plaintext">$<?php echo number_format($campaign['current_amount'], 2); ?></div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Progress</label>
                            <div class="progress">
                              <?php 
                                $progress = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                                $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                              ?>
                              <div class="progress-bar bg-<?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%">
                                <?php echo round($progress, 2); ?>%
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <a href="campaigns.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="update_campaign" class="btn btn-primary">Update Campaign</button>
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
        // Set minimum date to today for deadline (only if current deadline is in the future)
        var currentDeadline = document.getElementById('deadline').value;
        var today = new Date().toISOString().split('T')[0];
        
        if (currentDeadline >= today) {
          document.getElementById('deadline').min = today;
        }
      });
    </script>
  </body>
</html>
