<?php

include '../../includes/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Donor Dashboard | My Donations</title>

    <!-- Google Font: Source Sans Pro -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../../css/adminlte.min.css" />
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">
      <!-- Navbar -->
      <?php include 'header.php'; ?>
      <?php

          if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
            header('Location: ../../../index.php');
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
                <h1>My Donations</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item active">My Donations</li>
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
                    <h3 class="card-title">Donation History</h3>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <table id="donations-table" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Orphanage</th>
                          <th>Amount</th>
                          <th>Payment Method</th>
                          <th>Status</th>
                          <th>Transaction ID</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $user_id = $_SESSION['user_id'];
                          $sql = "SELECT d.*, o.name as orphanage_name 
                                  FROM donations d 
                                  JOIN orphanages o ON d.orphanage_id = o.id 
                                  WHERE d.user_id = $user_id 
                                  ORDER BY d.created_at DESC";
                          $result = mysqli_query($db, $sql);
                          
                          while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td>" . $row['orphanage_name'] . "</td>";
                            echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                            echo "<td>" . ucfirst(str_replace('_', ' ', $row['payment_method'])) . "</td>";
                            
                            $status_class = '';
                            if($row['payment_status'] == 'completed') {
                              $status_class = 'badge-success';
                            } else if($row['payment_status'] == 'pending') {
                              $status_class = 'badge-warning';
                            } else {
                              $status_class = 'badge-danger';
                            }
                            
                            echo "<td><span class='badge " . $status_class . "'>" . ucfirst($row['payment_status']) . "</span></td>";
                            echo "<td>" . $row['transaction_id'] . "</td>";
                            echo "</tr>";
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

    <!-- jQuery -->
    <script src="../../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables & Plugins -->
    <script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="../../plugins/jszip/jszip.min.js"></script>
    <script src="../../plugins/pdfmake/pdfmake.min.js"></script>
    <script src="../../plugins/pdfmake/vfs_fonts.js"></script>
    <script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../js/adminlte.min.js"></script>
    <!-- Page specific script -->
    <script>
      $(function () {
        $("#donations-table").DataTable({
          "responsive": true, 
          "lengthChange": false, 
          "autoWidth": false,
          "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#donations-table_wrapper .col-md-6:eq(0)');
      });
    </script>
  </body>
    
