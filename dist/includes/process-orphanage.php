<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../index.php');
  exit;
}

include 'connection.php';

// Add new orphanage
if (isset($_POST['add_orphanage'])) {
  $name = mysqli_real_escape_string($db, $_POST['name']);
  $location = mysqli_real_escape_string($db, $_POST['location']);
  $description = mysqli_real_escape_string($db, $_POST['description']);
  $contact_person = mysqli_real_escape_string($db, $_POST['contact_person']);
  $contact_phone = mysqli_real_escape_string($db, $_POST['contact_phone']);
  $contact_email = mysqli_real_escape_string($db, $_POST['contact_email']);
  $bank_account = mysqli_real_escape_string($db, $_POST['bank_account']);
  $status = isset($_POST['status']) ? mysqli_real_escape_string($db, $_POST['status']) : 'active';

  // Determine redirect URL based on where the request came from
  $redirect_base = strpos($_SERVER['HTTP_REFERER'], 'add-orphanage.php') !== false ?
                   '../pages/add-orphanage.php' : '../pages/orphanages.php';

  // Validate required fields
  if (empty($name) || empty($location) || empty($description) || empty($contact_person) || empty($contact_phone) || empty($contact_email)) {
    header('Location: ' . $redirect_base . '?error=All fields except bank account and image are required');
    exit;
  }

  // Validate email format
  if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirect_base . '?error=Please enter a valid email address');
    exit;
  }

  // Check if orphanage name already exists
  $check_sql = "SELECT id FROM orphanages WHERE name = '$name'";
  $check_result = mysqli_query($db, $check_sql);
  if (mysqli_num_rows($check_result) > 0) {
    header('Location: ' . $redirect_base . '?error=An orphanage with this name already exists');
    exit;
  }

  // Handle image upload
  $image_filename = null;
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = '../uploads/orphanages/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['image']['type'], $allowed_types)) {
      header('Location: ' . $redirect_base . '?error=Invalid image format. Please use JPG, PNG, or GIF');
      exit;
    }

    if ($_FILES['image']['size'] > $max_size) {
      header('Location: ' . $redirect_base . '?error=Image size too large. Maximum size is 5MB');
      exit;
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_filename = 'orphanage_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
    $upload_path = $upload_dir . $image_filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
      header('Location: ' . $redirect_base . '?error=Failed to upload image');
      exit;
    }
  }

  $sql = "INSERT INTO orphanages (name, location, description, contact_person, contact_phone, contact_email, bank_account, image, status)
          VALUES ('$name', '$location', '$description', '$contact_person', '$contact_phone', '$contact_email', '$bank_account', " .
          ($image_filename ? "'$image_filename'" : "NULL") . ", '$status')";

  if (mysqli_query($db, $sql)) {
    $new_orphanage_id = mysqli_insert_id($db);
    header('Location: ../pages/orphanages.php?success=Orphanage "' . $name . '" added successfully with ID: ' . $new_orphanage_id);
  } else {
    // If database insert fails and we uploaded an image, delete it
    if ($image_filename && file_exists($upload_dir . $image_filename)) {
      unlink($upload_dir . $image_filename);
    }
    header('Location: ' . $redirect_base . '?error=Failed to add orphanage: ' . mysqli_error($db));
  }
}

// Update orphanage
if (isset($_POST['update_orphanage'])) {
  $id = mysqli_real_escape_string($db, $_POST['orphanage_id']);
  $name = mysqli_real_escape_string($db, $_POST['name']);
  $location = mysqli_real_escape_string($db, $_POST['location']);
  $description = mysqli_real_escape_string($db, $_POST['description']);
  $contact_person = mysqli_real_escape_string($db, $_POST['contact_person']);
  $contact_phone = mysqli_real_escape_string($db, $_POST['contact_phone']);
  $contact_email = mysqli_real_escape_string($db, $_POST['contact_email']);
  $bank_account = mysqli_real_escape_string($db, $_POST['bank_account']);
  $status = isset($_POST['status']) ? mysqli_real_escape_string($db, $_POST['status']) : 'active';

  // Validate required fields
  if (empty($id) || empty($name) || empty($location) || empty($description) || empty($contact_person) || empty($contact_phone) || empty($contact_email)) {
    header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=All fields except bank account and image are required');
    exit;
  }

  // Validate email format
  if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=Please enter a valid email address');
    exit;
  }

  // Check if orphanage name already exists (excluding current orphanage)
  $check_sql = "SELECT id FROM orphanages WHERE name = '$name' AND id != '$id'";
  $check_result = mysqli_query($db, $check_sql);
  if (mysqli_num_rows($check_result) > 0) {
    header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=An orphanage with this name already exists');
    exit;
  }

  // Get current orphanage data for image handling
  $current_sql = "SELECT image FROM orphanages WHERE id = '$id'";
  $current_result = mysqli_query($db, $current_sql);
  $current_orphanage = mysqli_fetch_assoc($current_result);
  $current_image = $current_orphanage['image'];

  // Handle image upload
  $image_update = "";
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = '../uploads/orphanages/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['image']['type'], $allowed_types)) {
      header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=Invalid image format. Please use JPG, PNG, or GIF');
      exit;
    }

    if ($_FILES['image']['size'] > $max_size) {
      header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=Image size too large. Maximum size is 5MB');
      exit;
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_filename = 'orphanage_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
    $upload_path = $upload_dir . $image_filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
      // Delete old image if it exists
      if ($current_image && file_exists($upload_dir . $current_image)) {
        unlink($upload_dir . $current_image);
      }
      $image_update = ", image = '$image_filename'";
    } else {
      header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=Failed to upload image');
      exit;
    }
  }

  $sql = "UPDATE orphanages SET
          name = '$name',
          location = '$location',
          description = '$description',
          contact_person = '$contact_person',
          contact_phone = '$contact_phone',
          contact_email = '$contact_email',
          bank_account = '$bank_account',
          status = '$status',
          updated_at = CURRENT_TIMESTAMP
          $image_update
          WHERE id = '$id'";

  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/orphanages.php?success=Orphanage "' . $name . '" updated successfully');
  } else {
    header('Location: ../pages/edit-orphanage.php?id=' . $id . '&error=Failed to update orphanage: ' . mysqli_error($db));
  }
}

// Delete orphanage
if (isset($_POST['delete_orphanage'])) {
  $id = mysqli_real_escape_string($db, $_POST['orphanage_id']);

  if (empty($id)) {
    header('Location: ../pages/orphanages.php?error=Invalid orphanage ID');
    exit;
  }

  // Check if there are donations associated with this orphanage
  $check_donations_sql = "SELECT COUNT(*) as count FROM donations WHERE orphanage_id = '$id'";
  $donations_result = mysqli_query($db, $check_donations_sql);
  $donations_row = mysqli_fetch_assoc($donations_result);

  if ($donations_row['count'] > 0) {
    header('Location: ../pages/orphanages.php?error=Cannot delete orphanage because it has ' . $donations_row['count'] . ' associated donations. Consider marking it as inactive instead.');
    exit;
  }

  // Check if there are campaigns associated with this orphanage
  $check_campaigns_sql = "SELECT COUNT(*) as count FROM campaigns WHERE orphanage_id = '$id'";
  $campaigns_result = mysqli_query($db, $check_campaigns_sql);
  $campaigns_row = mysqli_fetch_assoc($campaigns_result);

  if ($campaigns_row['count'] > 0) {
    header('Location: ../pages/orphanages.php?error=Cannot delete orphanage because it has ' . $campaigns_row['count'] . ' associated campaigns. Please delete campaigns first.');
    exit;
  }

  $sql = "DELETE FROM orphanages WHERE id = '$id'";

  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/orphanages.php?success=Orphanage deleted successfully');
  } else {
    header('Location: ../pages/orphanages.php?error=Failed to delete orphanage: ' . mysqli_error($db));
  }
}

// If no action was performed, redirect back to orphanages page
header('Location: ../pages/orphanages.php');