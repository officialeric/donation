<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Campaigns</title>

    <!-- Google Font: Source Sans Pro -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css" />
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
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
                <?php 
                  $orphanage_filter = isset($_GET['orphanage_id']) ? $_GET['orphanage_id'] : '';
                  if ($orphanage_filter) {
                    include '../includes/connection.php';
                    $orphanage_sql = "SELECT name FROM orphanages WHERE id = '$orphanage_filter'";
                    $orphanage_result = mysqli_query($db, $orphanage_sql);
                    $orphanage_data = mysqli_fetch_assoc($orphanage_result);
                    echo "<h1>Campaigns for " . htmlspecialchars($orphanage_data['name']) . "</h1>";
                  } else {
                    echo "<h1>Manage Campaigns</h1>";
                  }
                ?>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <?php if ($orphanage_filter): ?>
                    <li class="breadcrumb-item"><a href="orphanages.php">Orphanages</a></li>
                    <li class="breadcrumb-item"><a href="campaigns.php">Campaigns</a></li>
                    <li class="breadcrumb-item active">Orphanage Campaigns</li>
                  <?php else: ?>
                    <li class="breadcrumb-item active">Campaigns</li>
                  <?php endif; ?>
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
                    <h3 class="card-title">Campaigns List</h3>
                    <div class="card-tools">
                      <?php if ($orphanage_filter): ?>
                        <a href="orphanages.php" class="btn btn-secondary">
                          <i class="fas fa-arrow-left"></i> Back to Orphanages
                        </a>
                      <?php endif; ?>
                      <a href="add-campaign.php<?php echo $orphanage_filter ? '?orphanage_id=' . $orphanage_filter : ''; ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Campaign (Page)
                      </a>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-campaign-modal">
                        <i class="fas fa-plus"></i> Add Campaign (Modal)
                      </button>
                    </div>
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
                    
                    <table id="campaigns-table" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Title</th>
                          <th>Orphanage</th>
                          <th>Target Amount</th>
                          <th>Current Amount</th>
                          <th>Progress</th>
                          <th>Deadline</th>
                          <th>Priority</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          if (!isset($db)) {
                            include '../includes/connection.php';
                          }
                          
                          $orphanage_filter = isset($_GET['orphanage_id']) ? mysqli_real_escape_string($db, $_GET['orphanage_id']) : '';
                          
                          $sql = "SELECT c.*, o.name as orphanage_name,
                                  ROUND((c.current_amount / c.target_amount) * 100, 2) as progress_percentage
                                  FROM campaigns c 
                                  LEFT JOIN orphanages o ON c.orphanage_id = o.id";
                          
                          if ($orphanage_filter) {
                            $sql .= " WHERE c.orphanage_id = '$orphanage_filter'";
                          }
                          
                          $sql .= " ORDER BY c.created_at DESC";
                          $result = mysqli_query($db, $sql);
                          
                          if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                              $progress = $row['progress_percentage'];
                              $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                              $priority_class = $row['priority'] == 'urgent' ? 'danger' : ($row['priority'] == 'high' ? 'warning' : ($row['priority'] == 'medium' ? 'info' : 'secondary'));
                              $status_class = $row['status'] == 'active' ? 'success' : ($row['status'] == 'completed' ? 'primary' : ($row['status'] == 'paused' ? 'warning' : 'danger'));
                              
                              echo "<tr>";
                              echo "<td>" . $row['id'] . "</td>";
                              echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['orphanage_name']) . "</td>";
                              echo "<td>$" . number_format($row['target_amount'], 2) . "</td>";
                              echo "<td>$" . number_format($row['current_amount'], 2) . "</td>";
                              echo "<td>
                                      <div class='progress'>
                                        <div class='progress-bar bg-$progress_class' style='width: $progress%'>
                                          $progress%
                                        </div>
                                      </div>
                                    </td>";
                              echo "<td>" . date('M d, Y', strtotime($row['deadline'])) . "</td>";
                              echo "<td><span class='badge badge-$priority_class'>" . ucfirst($row['priority']) . "</span></td>";
                              echo "<td><span class='badge badge-$status_class'>" . ucfirst($row['status']) . "</span></td>";
                              echo "<td>
                                      <a href='view-campaign.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>
                                        <i class='fas fa-eye'></i> View
                                      </a>
                                      <a href='edit-campaign.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>
                                        <i class='fas fa-edit'></i> Edit
                                      </a>
                                      <button type='button' class='btn btn-danger btn-sm' data-toggle='modal' data-target='#delete-campaign-modal' data-id='" . $row['id'] . "'>
                                        <i class='fas fa-trash'></i> Delete
                                      </button>
                                    </td>";
                              echo "</tr>";
                            }
                          } else {
                            echo "<tr><td colspan='10' class='text-center'>No campaigns found</td></tr>";
                          }
                        ?>
                      </tbody>
                    </table>
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

    <!-- Add Campaign Modal -->
    <div class="modal fade" id="add-campaign-modal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Add New Campaign</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="../includes/process-campaign.php" method="post">
            <div class="modal-body">
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
                          $selected = ($orphanage_filter && $orphanage['id'] == $orphanage_filter) ? 'selected' : '';
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
                      <option value="low">Low</option>
                      <option value="medium" selected>Medium</option>
                      <option value="high">High</option>
                      <option value="urgent">Urgent</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="title">Campaign Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
              </div>

              <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="target_amount">Target Amount ($)</label>
                    <input type="number" class="form-control" id="target_amount" name="target_amount" step="0.01" min="1" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input type="date" class="form-control" id="deadline" name="deadline" required>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" name="add_campaign" class="btn btn-primary">Save Campaign</button>
            </div>
          </form>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <!-- Delete Campaign Modal -->
    <div class="modal fade" id="delete-campaign-modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Delete Campaign</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="../includes/process-campaign.php" method="post">
            <div class="modal-body">
              <p>Are you sure you want to delete this campaign?</p>
              <p class="text-warning">This action cannot be undone.</p>
              <input type="hidden" name="campaign_id" id="delete-campaign-id">
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              <button type="submit" name="delete_campaign" class="btn btn-danger">Delete</button>
            </div>
          </form>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables  & Plugins -->
    <script src="../plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="../plugins/jszip/jszip.min.js"></script>
    <script src="../plugins/pdfmake/pdfmake.min.js"></script>
    <script src="../plugins/pdfmake/vfs_fonts.js"></script>
    <script src="../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="../plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../js/adminlte.min.js"></script>
    <!-- Page specific script -->
    <script>
      $(function () {
        $("#campaigns-table").DataTable({
          "responsive": true,
          "lengthChange": false,
          "autoWidth": false,
          "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#campaigns-table_wrapper .col-md-6:eq(0)');

        // Handle delete modal
        $('#delete-campaign-modal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget);
          var campaignId = button.data('id');
          var modal = $(this);
          modal.find('#delete-campaign-id').val(campaignId);
        });

        // Set minimum date to today for deadline
        document.getElementById('deadline').min = new Date().toISOString().split('T')[0];
      });
    </script>
  </body>
</html>
