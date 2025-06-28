<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | View Orphanage</title>

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
        header('Location: orphanages.php');
        exit;
      }
      
      $orphanage_id = mysqli_real_escape_string($db, $_GET['id']);
      $sql = "SELECT * FROM orphanages WHERE id = '$orphanage_id'";
      $result = mysqli_query($db, $sql);
      
      if (mysqli_num_rows($result) == 0) {
        header('Location: orphanages.php');
        exit;
      }
      
      $orphanage = mysqli_fetch_assoc($result);
      
      // Get orphanage statistics
      $stats_sql = "SELECT 
                      COUNT(d.id) as total_donations,
                      COALESCE(SUM(d.amount), 0) as total_raised,
                      COUNT(DISTINCT d.user_id) as unique_donors
                    FROM donations d 
                    WHERE d.orphanage_id = '$orphanage_id' AND d.payment_status = 'completed'";
      $stats_result = mysqli_query($db, $stats_sql);
      $stats = mysqli_fetch_assoc($stats_result);
      
      // Get active campaigns
      $campaigns_sql = "SELECT * FROM campaigns WHERE orphanage_id = '$orphanage_id' ORDER BY created_at DESC";
      $campaigns_result = mysqli_query($db, $campaigns_sql);
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
                <h1>Orphanage Details</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="orphanages.php">Orphanages</a></li>
                  <li class="breadcrumb-item active">View Orphanage</li>
                </ol>
              </div>
            </div>
          </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- Orphanage Information -->
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($orphanage['name']); ?></h3>
                    <div class="card-tools">
                      <a href="edit-orphanage.php?id=<?php echo $orphanage['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Orphanage
                      </a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <strong>Location:</strong> <?php echo htmlspecialchars($orphanage['location']); ?><br>
                        <strong>Contact Person:</strong> <?php echo htmlspecialchars($orphanage['contact_person']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($orphanage['contact_phone']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($orphanage['contact_email']); ?>
                      </div>
                      <div class="col-md-6">
                        <strong>Status:</strong> 
                        <span class="badge badge-<?php echo $orphanage['status'] == 'active' ? 'success' : 'danger'; ?>">
                          <?php echo ucfirst($orphanage['status']); ?>
                        </span><br>
                        <strong>Created:</strong> <?php echo date('M d, Y', strtotime($orphanage['created_at'])); ?><br>
                        <?php if ($orphanage['updated_at']): ?>
                          <strong>Last Updated:</strong> <?php echo date('M d, Y', strtotime($orphanage['updated_at'])); ?>
                        <?php endif; ?>
                      </div>
                    </div>
                    
                    <hr>
                    
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($orphanage['description'])); ?></p>
                    
                    <hr>
                    
                    <h5>Bank Account Details</h5>
                    <p><?php echo nl2br(htmlspecialchars($orphanage['bank_account'])); ?></p>
                  </div>
                </div>
                
                <!-- Campaigns -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Campaigns</h3>
                    <div class="card-tools">
                      <a href="campaigns.php?orphanage_id=<?php echo $orphanage['id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Campaign
                      </a>
                    </div>
                  </div>
                  <div class="card-body">
                    <?php if (mysqli_num_rows($campaigns_result) > 0): ?>
                      <div class="table-responsive">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Title</th>
                              <th>Target</th>
                              <th>Raised</th>
                              <th>Progress</th>
                              <th>Status</th>
                              <th>Deadline</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while($campaign = mysqli_fetch_assoc($campaigns_result)): 
                              $progress = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                              $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                              $status_class = $campaign['status'] == 'active' ? 'success' : ($campaign['status'] == 'completed' ? 'primary' : ($campaign['status'] == 'paused' ? 'warning' : 'danger'));
                            ?>
                              <tr>
                                <td><?php echo htmlspecialchars($campaign['title']); ?></td>
                                <td>$<?php echo number_format($campaign['target_amount'], 2); ?></td>
                                <td>$<?php echo number_format($campaign['current_amount'], 2); ?></td>
                                <td>
                                  <div class="progress">
                                    <div class="progress-bar bg-<?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%">
                                      <?php echo round($progress, 1); ?>%
                                    </div>
                                  </div>
                                </td>
                                <td><span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($campaign['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($campaign['deadline'])); ?></td>
                                <td>
                                  <a href="view-campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                  </a>
                                  <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                  </a>
                                </td>
                              </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <p class="text-muted">No campaigns created yet for this orphanage.</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <!-- Statistics -->
              <div class="col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="description-block border-bottom">
                          <h5 class="description-header">$<?php echo number_format($stats['total_raised'], 2); ?></h5>
                          <span class="description-text">TOTAL RAISED</span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row mt-3">
                      <div class="col-6">
                        <div class="description-block border-right">
                          <h5 class="description-header"><?php echo $stats['total_donations']; ?></h5>
                          <span class="description-text">DONATIONS</span>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="description-block">
                          <h5 class="description-header"><?php echo $stats['unique_donors']; ?></h5>
                          <span class="description-text">DONORS</span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row mt-3">
                      <div class="col-12">
                        <div class="description-block">
                          <h5 class="description-header"><?php echo mysqli_num_rows($campaigns_result); ?></h5>
                          <span class="description-text">CAMPAIGNS</span>
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
                    <a href="edit-orphanage.php?id=<?php echo $orphanage['id']; ?>" class="btn btn-warning btn-block">
                      <i class="fas fa-edit"></i> Edit Orphanage
                    </a>
                    <a href="campaigns.php?orphanage_id=<?php echo $orphanage['id']; ?>" class="btn btn-success btn-block">
                      <i class="fas fa-megaphone"></i> Manage Campaigns
                    </a>
                    <a href="orphanages.php" class="btn btn-secondary btn-block">
                      <i class="fas fa-arrow-left"></i> Back to Orphanages
                    </a>
                    <a href="../../../orphanage-details.php?id=<?php echo $orphanage['id']; ?>" class="btn btn-info btn-block" target="_blank">
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
