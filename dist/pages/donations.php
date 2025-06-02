      <!--Header-->
      <?php include 'header.php' ?>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <?php include 'sidebar.php' ?>
      <!--end::Sidebar-->

      <?php 
        include "../includes/connection.php";

        $sql = "SELECT * FROM users WHERE role = 'donor'";
        $donors = mysqli_query($db, $sql);
      ?>

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Donations</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">All Donations</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-12">
                <div class="card mb-4">
                  <div class="card-header"><h3 class="card-title">
                    All Donations
                  </h3></div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
                          <th>Email</th>
                          <th>Phone</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($donors as $i => $donor) : ?>
                        <tr class="align-middle">
                          <td><?=++$i?></td>
                          <td><?=$donor['username']?></td>
                          <td><?=$donor['email']?></td>
                          <td><?=$donor['phone']?></td>
                          <td>
                            <a href="donor-details.php" class="btn btn-primary btn-sm">View</a>
                            <a href="edit-donor.php" class="btn btn-warning btn-sm">Edit</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                          </td>
                        </tr>

                      <?php endforeach; ?>
                        
                      </tbody>
                    </table>
                  </div>
                  
                </div>
                
              </div>
              <!-- /.col -->
              
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer-->
       <?php include 'footer.php' ?>
      <!--end::Footer-->
    