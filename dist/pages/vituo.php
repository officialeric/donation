      <!--Header-->
      <?php include 'header.php' ?>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <?php include 'sidebar.php' ?>
      <!--end::Sidebar-->

      <?php
        include "../includes/connection.php";

        $sql = "SELECT o.*,
                       COUNT(d.id) as total_donations,
                       COALESCE(SUM(CASE WHEN d.payment_status = 'completed' THEN d.amount ELSE 0 END), 0) as total_raised
                FROM orphanages o
                LEFT JOIN donations d ON o.id = d.orphanage_id
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        $orphanages = mysqli_query($db, $sql);
      ?>

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Orphanages</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">All Orphanages</li>
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
                  <div class="card-header">
                    <h3 class="card-title">All Orphanages</h3>
                    <div class="card-tools">
                      <a href="admin/orphanages.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Add New Orphanage
                      </a>
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Contact Person</th>
                            <th>Contact Info</th>
                            <th>Total Donations</th>
                            <th>Total Raised</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          if (mysqli_num_rows($orphanages) > 0) {
                            $i = 0;
                            while ($orphanage = mysqli_fetch_assoc($orphanages)) :
                          ?>
                          <tr class="align-middle">
                            <td><?= ++$i ?></td>
                            <td>
                              <strong><?= htmlspecialchars($orphanage['name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($orphanage['location']) ?></td>
                            <td><?= htmlspecialchars($orphanage['contact_person']) ?></td>
                            <td>
                              <small>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($orphanage['contact_phone']) ?><br>
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($orphanage['contact_email']) ?>
                              </small>
                            </td>
                            <td>
                              <span class="badge bg-info"><?= $orphanage['total_donations'] ?></span>
                            </td>
                            <td>
                              <strong>$<?= number_format($orphanage['total_raised'], 2) ?></strong>
                            </td>
                            <td>
                              <?php
                                $status_class = $orphanage['status'] == 'active' ? 'bg-success' : 'bg-secondary';
                              ?>
                              <span class="badge <?= $status_class ?>"><?= ucfirst($orphanage['status']) ?></span>
                            </td>
                            <td><?= date('M d, Y', strtotime($orphanage['created_at'])) ?></td>
                            <td>
                              <div class="btn-group" role="group">
                                <a href="../../orphanage-details.php?id=<?= $orphanage['id'] ?>" class="btn btn-info btn-sm" target="_blank">
                                  <i class="bi bi-eye"></i> View
                                </a>
                                <a href="admin/edit-orphanage.php?id=<?= $orphanage['id'] ?>" class="btn btn-warning btn-sm">
                                  <i class="bi bi-pencil"></i> Edit
                                </a>
                              </div>
                            </td>
                          </tr>
                          <?php
                            endwhile;
                          } else {
                          ?>
                          <tr>
                            <td colspan="10" class="text-center">
                              <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No orphanages found.
                              </div>
                            </td>
                          </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
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
    