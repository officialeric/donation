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
  
  $sql = "INSERT INTO orphanages (name, location, description, contact_person, contact_phone, contact_email, bank_account) 
          VALUES ('$name', '$location', '$description', '$contact_person', '$contact_phone', '$contact_email', '$bank_account')";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/admin/orphanages.php?success=Orphanage added successfully');
  } else {
    header('Location: ../pages/admin/orphanages.php?error=Failed to add orphanage: ' . mysqli_error($db));
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
  
  $sql = "UPDATE orphanages SET 
          name = '$name', 
          location = '$location', 
          description = '$description', 
          contact_person = '$contact_person', 
          contact_phone = '$contact_phone', 
          contact_email = '$contact_email', 
          bank_account = '$bank_account' 
          WHERE id = $id";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/admin/orphanages.php?success=Orphanage updated successfully');
  } else {
    header('Location: ../pages/admin/orphanages.php?error=Failed to update orphanage: ' . mysqli_error($db));
  }
}

// Delete orphanage
if (isset($_POST['delete_orphanage'])) {
  $id = mysqli_real_escape_string($db, $_POST['orphanage_id']);
  
  // Check if there are donations associated with this orphanage
  $check_sql = "SELECT COUNT(*) as count FROM donations WHERE orphanage_id = $id";
  $result = mysqli_query($db, $check_sql);
  $row = mysqli_fetch_assoc($result);
  
  if ($row['count'] > 0) {
    header('Location: ../pages/admin/orphanages.php?error=Cannot delete orphanage because it has associated donations');
    exit;
  }
  
  $sql = "DELETE FROM orphanages WHERE id = $id";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/admin/orphanages.php?success=Orphanage deleted successfully');
  } else {
    header('Location: ../pages/admin/orphanages.php?error=Failed to delete orphanage: ' . mysqli_error($db));
  }
}

// If no action was performed, redirect back to orphanages page
header('Location: ../pages/admin/orphanages.php');