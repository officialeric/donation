<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../index.php');
  exit;
}

include 'connection.php';

// Add new campaign
if (isset($_POST['add_campaign'])) {
  $orphanage_id = mysqli_real_escape_string($db, $_POST['orphanage_id']);
  $title = mysqli_real_escape_string($db, $_POST['title']);
  $description = mysqli_real_escape_string($db, $_POST['description']);
  $target_amount = mysqli_real_escape_string($db, $_POST['target_amount']);
  $deadline = mysqli_real_escape_string($db, $_POST['deadline']);
  $priority = mysqli_real_escape_string($db, $_POST['priority']);
  
  // Validate inputs
  if (empty($orphanage_id) || empty($title) || empty($description) || empty($target_amount) || empty($deadline) || empty($priority)) {
    header('Location: ../pages/campaigns.php?error=All fields are required');
    exit;
  }

  if (!is_numeric($target_amount) || $target_amount <= 0) {
    header('Location: ../pages/campaigns.php?error=Target amount must be a positive number');
    exit;
  }

  if (strtotime($deadline) <= time()) {
    header('Location: ../pages/campaigns.php?error=Deadline must be in the future');
    exit;
  }

  // Check if orphanage exists
  $check_orphanage = "SELECT id FROM orphanages WHERE id = '$orphanage_id' AND status = 'active'";
  $check_result = mysqli_query($db, $check_orphanage);

  if (mysqli_num_rows($check_result) == 0) {
    header('Location: ../pages/campaigns.php?error=Invalid orphanage selected');
    exit;
  }
  
  $sql = "INSERT INTO campaigns (orphanage_id, title, description, target_amount, deadline, priority) 
          VALUES ('$orphanage_id', '$title', '$description', '$target_amount', '$deadline', '$priority')";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/campaigns.php?success=Campaign added successfully');
  } else {
    header('Location: ../pages/campaigns.php?error=Failed to add campaign: ' . mysqli_error($db));
  }
}

// Update campaign
if (isset($_POST['update_campaign'])) {
  $campaign_id = mysqli_real_escape_string($db, $_POST['campaign_id']);
  $orphanage_id = mysqli_real_escape_string($db, $_POST['orphanage_id']);
  $title = mysqli_real_escape_string($db, $_POST['title']);
  $description = mysqli_real_escape_string($db, $_POST['description']);
  $target_amount = mysqli_real_escape_string($db, $_POST['target_amount']);
  $deadline = mysqli_real_escape_string($db, $_POST['deadline']);
  $priority = mysqli_real_escape_string($db, $_POST['priority']);
  $status = mysqli_real_escape_string($db, $_POST['status']);
  
  // Validate inputs
  if (empty($campaign_id) || empty($orphanage_id) || empty($title) || empty($description) || empty($target_amount) || empty($deadline) || empty($priority) || empty($status)) {
    header('Location: ../pages/edit-campaign.php?id=' . $campaign_id . '&error=All fields are required');
    exit;
  }

  if (!is_numeric($target_amount) || $target_amount <= 0) {
    header('Location: ../pages/edit-campaign.php?id=' . $campaign_id . '&error=Target amount must be a positive number');
    exit;
  }

  // Check if orphanage exists
  $check_orphanage = "SELECT id FROM orphanages WHERE id = '$orphanage_id' AND status = 'active'";
  $check_result = mysqli_query($db, $check_orphanage);

  if (mysqli_num_rows($check_result) == 0) {
    header('Location: ../pages/edit-campaign.php?id=' . $campaign_id . '&error=Invalid orphanage selected');
    exit;
  }
  
  $sql = "UPDATE campaigns SET 
          orphanage_id = '$orphanage_id',
          title = '$title', 
          description = '$description', 
          target_amount = '$target_amount', 
          deadline = '$deadline', 
          priority = '$priority',
          status = '$status',
          updated_at = CURRENT_TIMESTAMP
          WHERE id = '$campaign_id'";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/campaigns.php?success=Campaign updated successfully');
  } else {
    header('Location: ../pages/edit-campaign.php?id=' . $campaign_id . '&error=Failed to update campaign: ' . mysqli_error($db));
  }
}

// Delete campaign
if (isset($_POST['delete_campaign'])) {
  $campaign_id = mysqli_real_escape_string($db, $_POST['campaign_id']);
  
  if (empty($campaign_id)) {
    header('Location: ../pages/campaigns.php?error=Invalid campaign ID');
    exit;
  }

  // Check if campaign has donations
  $check_donations = "SELECT COUNT(*) as donation_count FROM donations WHERE campaign_id = '$campaign_id'";
  $donation_result = mysqli_query($db, $check_donations);
  $donation_data = mysqli_fetch_assoc($donation_result);

  if ($donation_data['donation_count'] > 0) {
    header('Location: ../pages/campaigns.php?error=Cannot delete campaign with existing donations. Consider marking it as cancelled instead.');
    exit;
  }
  
  $sql = "DELETE FROM campaigns WHERE id = '$campaign_id'";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../pages/campaigns.php?success=Campaign deleted successfully');
  } else {
    header('Location: ../pages/campaigns.php?error=Failed to delete campaign: ' . mysqli_error($db));
  }
}

// Update campaign current amount (called when donations are made)
function updateCampaignAmount($campaign_id, $donation_amount, $db) {
  $sql = "UPDATE campaigns SET current_amount = current_amount + $donation_amount WHERE id = $campaign_id";
  return mysqli_query($db, $sql);
}

// Mark campaign as completed if target is reached
function checkCampaignCompletion($campaign_id, $db) {
  $sql = "SELECT target_amount, current_amount FROM campaigns WHERE id = $campaign_id";
  $result = mysqli_query($db, $sql);
  
  if ($result && mysqli_num_rows($result) > 0) {
    $campaign = mysqli_fetch_assoc($result);
    
    if ($campaign['current_amount'] >= $campaign['target_amount']) {
      $update_sql = "UPDATE campaigns SET status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE id = $campaign_id";
      mysqli_query($db, $update_sql);
    }
  }
}

// If no action is specified, redirect to campaigns page
if (!isset($_POST['add_campaign']) && !isset($_POST['update_campaign']) && !isset($_POST['delete_campaign'])) {
  header('Location: ../pages/campaigns.php');
  exit;
}
?>
