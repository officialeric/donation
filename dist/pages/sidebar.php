<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="index.php" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="../assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Donation</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false"
            >
              <?php
                $current_page = basename($_SERVER['PHP_SELF']);
              ?>
              <li class="nav-item menu-open">
                <a href="index.php" class="nav-link<?php echo ($current_page == 'index.php') ? '' : ' active'; ?>">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Dashboard
                  </p>
                </a>
              </li>
              <li class="nav-item menu-open">
                <a href="vituo.php" class="nav-link<?php echo ($current_page == 'vituo.php') ? '' : ' active'; ?>">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Vituo
                  </p>
                </a>
              </li>
              <li class="nav-item menu-open">
                <a href="donors.php" class="nav-link<?php echo ($current_page == 'donors.php') ? '' : ' active'; ?>">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Donors
                  </p>
                </a>
              </li>
              <li class="nav-item menu-open">
                <a href="donations.php" class="nav-link<?php echo ($current_page == 'donations.php') ? '' : ' active'; ?>">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Donations
                  </p>
                </a>
              </li>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>