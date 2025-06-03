      <!--Header-->
      <?php include 'header.php' ?>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <?php include 'sidebar.php' ?>
      <!--end::Sidebar-->

      <?php
        include "../includes/connection.php";

        $sql = "SELECT d.*, u.username, u.email, o.name as orphanage_name
                FROM donations d
                JOIN users u ON d.user_id = u.id
                JOIN orphanages o ON d.orphanage_id = o.id
                ORDER BY d.created_at DESC";
        $donations = mysqli_query($db, $sql);
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
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Donor</th>
                            <th>Orphanage</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          if (mysqli_num_rows($donations) > 0) {
                            $i = 0;
                            while ($donation = mysqli_fetch_assoc($donations)) :
                          ?>
                          <tr class="align-middle">
                            <td><?= ++$i ?></td>
                            <td>
                              <strong><?= htmlspecialchars($donation['username']) ?></strong><br>
                              <small class="text-muted"><?= htmlspecialchars($donation['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($donation['orphanage_name']) ?></td>
                            <td><strong>$<?= number_format($donation['amount'], 2) ?></strong></td>
                            <td>
                              <span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></span>
                            </td>
                            <td>
                              <?php
                                $status_class = '';
                                switch($donation['payment_status']) {
                                  case 'completed': $status_class = 'bg-success'; break;
                                  case 'pending': $status_class = 'bg-warning'; break;
                                  case 'failed': $status_class = 'bg-danger'; break;
                                  case 'refunded': $status_class = 'bg-secondary'; break;
                                  default: $status_class = 'bg-light';
                                }
                              ?>
                              <span class="badge <?= $status_class ?>"><?= ucfirst($donation['payment_status']) ?></span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($donation['created_at'])) ?></td>
                            <td>
                              <small class="text-muted"><?= htmlspecialchars($donation['transaction_id']) ?></small>
                            </td>
                            <td>
                              <button class="btn btn-primary btn-sm" onclick="viewDonation(<?= $donation['id'] ?>)">
                                <i class="bi bi-eye"></i> View
                              </button>
                            </td>
                          </tr>
                          <?php
                            endwhile;
                          } else {
                          ?>
                          <tr>
                            <td colspan="9" class="text-center">
                              <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No donations found.
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

      <!-- Donation Details Modal -->
      <div class="modal fade" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="donationModalLabel">Donation Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="donationModalBody">
              <!-- Content will be loaded here -->
            </div>
          </div>
        </div>
      </div>

      <script>
        function viewDonation(donationId) {
          // Simple alert for now - you can implement a proper modal with AJAX
          alert('Viewing donation ID: ' + donationId + '\n\nThis would show detailed donation information in a modal.');
        }
      </script>
    