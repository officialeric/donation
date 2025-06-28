<?php
  // Simple path setup since all admin files are now at the same level
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="../index.php" class="brand-link">
      <!--begin::Brand Icon-->
      <img src="../images/logo-simple.svg" alt="TumainiFuraha Logo" class="brand-image" style="width: 2rem; height: 2rem; margin-right: 0.5rem;">
      <!--end::Brand Icon-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-bold">TumainiFuraha</span>
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
        <li class="nav-item menu-open">
          <a href="index.php" class="nav-link<?php echo ($current_page == 'index.php') ? '' : ' active'; ?>">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>
                Dashboard
            </p>
          </a>
        </li>
        <li class="nav-item menu-open">
          <a href="orphanages.php" class="nav-link<?php echo ($current_page == 'orphanages.php') ? '' : ' active'; ?>">
            <i class="nav-icon bi bi-house-heart"></i>
            <p>
              Orphanages
            </p>
          </a>
        </li>
        <li class="nav-item menu-open">
          <a href="campaigns.php" class="nav-link<?php echo ($current_page == 'campaigns.php') ? '' : ' active'; ?>">
            <i class="nav-icon bi bi-megaphone"></i>
            <p>
              Campaigns
            </p>
          </a>
        </li>
        <li class="nav-item menu-open">
          <a href="donors.php" class="nav-link<?php echo ($current_page == 'donors.php') ? '' : ' active'; ?>">
            <i class="nav-icon bi bi-people"></i>
            <p>
              Donors
            </p>
          </a>
        </li>
        <li class="nav-item menu-open">
          <a href="donations.php" class="nav-link<?php echo ($current_page == 'donations.php') ? '' : ' active'; ?>">
            <i class="nav-icon bi bi-heart-fill"></i>
            <p>
              Donations
            </p>
          </a>
        </li>
        <li class="nav-item menu-open">
          <a href="../../index.php" class="nav-link active" target="_blank">
            <i class="nav-icon bi bi-globe"></i>
            <p>
              View Public Site
            </p>
          </a>
        </li>
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>