<?php

include '../../includes/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard | Manage Orphanages</title>

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
      <?php include '../header.php'; 
      if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../../index.php');
  exit;
}
      ?>
      <!-- /.navbar -->

      <!-- Main Sidebar Container -->
      <?php include '../sidebar.php'; ?>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1>Manage Orphanages</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item active">Orphanages</li>
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
                    <h3 class="card-title">Orphanages List</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-orphanage-modal">
                        <i class="fas fa-plus"></i> Add New Orphanage
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
                    
                    <table id="orphanages-table" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Name</th>
                          <th>Location</th>
                          <th>Contact Phone</th>
                          <th>Contact Email</th>
                          <th>Created At</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $sql = "SELECT * FROM orphanages ORDER BY id DESC";
                          $result = mysqli_query($db, $sql);
                          
                          while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['location'] . "</td>";
                            echo "<td>" . $row['contact_phone'] . "</td>";
                            echo "<td>" . $row['contact_email'] . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td>
                                    <a href='view-orphanage.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>
                                      <i class='fas fa-eye'></i> View
                                    </a>
                                    <a href='edit-orphanage.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>
                                      <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <button type='button' class='btn btn-danger btn-sm' data-toggle='modal' data-target='#delete-orphanage-modal' data-id='" . $row['id'] . "'>
                                      <i class='fas fa-trash'></i> Delete
                                    </button>
                                  </td>";
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
      
      <?php include '../footer.php'; ?>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- Add Orphanage Modal -->
    <div class="modal fade" id="add-orphanage-modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Add New Orphanage</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="../../includes/process-orphanage.php" method="post">
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Orphanage Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="form-group">
                <label for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location" required>
              </div>
              <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
              </div>
              <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" required>
              </div>
              <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" required>
              </div>
              <div class="form-group">
                <label for="bank_account">Bank Account Details</label>
                <textarea class="form-control" id="bank_account" name="bank_account" rows="2" required></textarea>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" name="add_orphanage" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <!-- Delete Orphanage Modal -->
    <div class="modal fade" id="delete-orphanage-modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Delete Orphanage</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="../../includes/process-orphanage.php" method="post">
            <div class="modal-body">
              <p>Are you sure you want to delete this orphanage?</p>
              <p class="text-warning">This action cannot be undone.</p>
              <input type="hidden" name="orphanage_id" id="delete-orphanage-id">
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              <button type="submit" name="delete_orphanage" class="btn btn-danger">Delete</button>
            </div>
          </form>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

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
        $("#orphanages-table").DataTable({
          "responsive": true, 
          "lengthChange": false, 
          "autoWidth": false,
          "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#orphanages-table_wrapper .col-md-6:eq(0)');
        
        // Set orphanage ID for delete modal
        $('#delete-orphanage-modal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget);
          var id = button.data('id');
          var modal = $(this);
          modal.find('#delete-orphanage-id').val(id);
        });
      });
    </script>
  </body>
</html>
