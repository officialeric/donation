      <!--Header-->
      <?php include 'header.php' ?>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <?php include 'sidebar.php' ?>
      <!--end::Sidebar-->


      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Profile</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">My Profile</li>
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
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="../includes/profile.inc.php">
                                <?php if (isset($_GET['update'])): ?>
                                    <div class="alert 
                                        <?php
                                            if ($_GET['update'] == 'success') echo 'alert-success';
                                            elseif ($_GET['update'] == 'empty') echo 'alert-warning';
                                            elseif ($_GET['update'] == 'error') echo 'alert-danger';
                                        ?>">
                                        <?php
                                            if ($_GET['update'] == 'success') echo 'Profile updated successfully.';
                                            elseif ($_GET['update'] == 'empty') echo 'Please fill in all fields.';
                                            elseif ($_GET['update'] == 'error') echo 'An error occurred. Please try again.';
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3 row">
                                    <label for="name" class="col-sm-2 col-form-label">Full Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="name" name="username" value="<?=$_SESSION['username']?>">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="email" name="email" value="<?=$_SESSION['email']?>">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="phone" class="col-sm-2 col-form-label">Phone</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?=$_SESSION['phone']?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->      </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer-->
       <?php include 'footer.php' ?>
      <!--end::Footer-->
    