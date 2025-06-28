<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | View Campaign</title>

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
      $sql = "SELECT c.*, o.name as orphanage_name, o.location as orphanage_location,
              ROUND((c.current_amount / c.target_amount) * 100, 2) as progress_percentage,
              DATEDIFF(c.deadline, CURDATE()) as days_remaining
              FROM campaigns c 
              LEFT JOIN orphanages o ON c.orphanage_id = o.id 
              WHERE c.id = '$campaign_id'";
      $result = mysqli_query($db, $sql);
      
      if (mysqli_num_rows($result) == 0) {
        header('Location: campaigns.php');
        exit;
      }
      
      $campaign = mysqli_fetch_assoc($result);
      
      // Get campaign donations
      $donations_sql = "SELECT d.*, u.username, u.first_name, u.last_name 
                        FROM donations d 
                        LEFT JOIN users u ON d.user_id = u.id 
                        WHERE d.campaign_id = '$campaign_id' AND d.payment_status = 'completed'
                        ORDER BY d.created_at DESC";
      $donations_result = mysqli_query($db, $donations_sql);
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
                <h1>Campaign Details</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="campaigns.php">Campaigns</a></li>
                  <li class="breadcrumb-item active">View Campaign</li>
                </ol>
              </div>
            </div>
          </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- Campaign Information -->
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($campaign['title']); ?></h3>
                    <div class="card-tools">
                      <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Campaign
                      </a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <strong>Orphanage:</strong> <?php echo htmlspecialchars($campaign['orphanage_name']); ?><br>
                        <strong>Location:</strong> <?php echo htmlspecialchars($campaign['orphanage_location']); ?><br>
                        <strong>Priority:</strong> 
                        <?php 
                          $priority_class = $campaign['priority'] == 'urgent' ? 'danger' : ($campaign['priority'] == 'high' ? 'warning' : ($campaign['priority'] == 'medium' ? 'info' : 'secondary'));
                        ?>
                        <span class="badge badge-<?php echo $priority_class; ?>"><?php echo ucfirst($campaign['priority']); ?></span><br>
                        <strong>Status:</strong> 
                        <?php 
                          $status_class = $campaign['status'] == 'active' ? 'success' : ($campaign['status'] == 'completed' ? 'primary' : ($campaign['status'] == 'paused' ? 'warning' : 'danger'));
                        ?>
                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($campaign['status']); ?></span>
                      </div>
                      <div class="col-md-6">
                        <strong>Created:</strong> <?php echo date('M d, Y', strtotime($campaign['created_at'])); ?><br>
                        <strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($campaign['deadline'])); ?><br>
                        <strong>Days Remaining:</strong> 
                        <?php 
                          if ($campaign['days_remaining'] > 0) {
                            echo "<span class='text-success'>" . $campaign['days_remaining'] . " days</span>";
                          } elseif ($campaign['days_remaining'] == 0) {
                            echo "<span class='text-warning'>Today</span>";
                          } else {
                            echo "<span class='text-danger'>Expired</span>";
                          }
                        ?>
                      </div>
                    </div>
                    
                    <hr>
                    
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($campaign['description'])); ?></p>
                  </div>
                </div>
                
                <!-- Campaign Donations -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Recent Donations</h3>
                  </div>
                  <div class="card-body">
                    <?php if (mysqli_num_rows($donations_result) > 0): ?>
                      <div class="table-responsive">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Donor</th>
                              <th>Amount</th>
                              <th>Date</th>
                              <th>Message</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while($donation = mysqli_fetch_assoc($donations_result)): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($donation['username']); ?></td>
                                <td>$<?php echo number_format($donation['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($donation['message'] ?: 'No message'); ?></td>
                              </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <p class="text-muted">No donations received yet for this campaign.</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <!-- Campaign Statistics -->
              <div class="col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Campaign Progress</h3>
                  </div>
                  <div class="card-body">
                    <div class="progress mb-3">
                      <?php 
                        $progress = $campaign['progress_percentage'];
                        $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                      ?>
                      <div class="progress-bar bg-<?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%">
                        <?php echo $progress; ?>%
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-6">
                        <div class="description-block border-right">
                          <h5 class="description-header">$<?php echo number_format($campaign['current_amount'], 2); ?></h5>
                          <span class="description-text">RAISED</span>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="description-block">
                          <h5 class="description-header">$<?php echo number_format($campaign['target_amount'], 2); ?></h5>
                          <span class="description-text">TARGET</span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row mt-3">
                      <div class="col-6">
                        <div class="description-block border-right">
                          <?php 
                            $total_donations_sql = "SELECT COUNT(*) as total_donations FROM donations WHERE campaign_id = '$campaign_id' AND payment_status = 'completed'";
                            $total_donations_result = mysqli_query($db, $total_donations_sql);
                            $total_donations = mysqli_fetch_assoc($total_donations_result)['total_donations'];
                          ?>
                          <h5 class="description-header"><?php echo $total_donations; ?></h5>
                          <span class="description-text">DONATIONS</span>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="description-block">
                          <?php 
                            $unique_donors_sql = "SELECT COUNT(DISTINCT user_id) as unique_donors FROM donations WHERE campaign_id = '$campaign_id' AND payment_status = 'completed'";
                            $unique_donors_result = mysqli_query($db, $unique_donors_sql);
                            $unique_donors = mysqli_fetch_assoc($unique_donors_result)['unique_donors'];
                          ?>
                          <h5 class="description-header"><?php echo $unique_donors; ?></h5>
                          <span class="description-text">DONORS</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                  </div>
                  <div class="card-body">
                    <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-warning btn-block">
                      <i class="fas fa-edit"></i> Edit Campaign
                    </a>
                    <a href="campaigns.php" class="btn btn-secondary btn-block">
                      <i class="fas fa-arrow-left"></i> Back to Campaigns
                    </a>
                    <a href="../../../orphanage-details.php?id=<?php echo $campaign['orphanage_id']; ?>" class="btn btn-info btn-block" target="_blank">
                      <i class="fas fa-external-link-alt"></i> View Public Page
                    </a>
                  </div>
                </div>
              </div>
            </div>
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
  </body>
</html>
