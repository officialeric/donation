<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../index.php');
  exit;
}

if (isset($_POST['process_donation'])) {
  $user_id = $_SESSION['user_id'];
  $orphanage_id = $_POST['orphanage_id'];
  $amount = $_POST['amount'];
  $payment_method = $_POST['payment_method'];
  $message = $_POST['message'];
  
  // Validate inputs
  if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
    header('Location: ../../make-donation.php?orphanage_id=' . $orphanage_id . '&error=Please enter a valid amount');
    exit;
  }
  
  if (empty($payment_method)) {
    header('Location: ../../make-donation.php?orphanage_id=' . $orphanage_id . '&error=Please select a payment method');
    exit;
  }
  
  // In a real application, you would process the payment here
  // For this example, we'll simulate a successful payment
  $transaction_id = 'TXN' . time() . rand(1000, 9999);
  
  // Insert donation record
  $sql = "INSERT INTO donations (user_id, orphanage_id, amount, payment_status, payment_method, transaction_id, message) 
          VALUES ('$user_id', '$orphanage_id', '$amount', 'completed', '$payment_method', '$transaction_id', '$message')";
  
  if (mysqli_query($db, $sql)) {
    header('Location: ../../donation-success.php?transaction_id=' . $transaction_id);
  } else {
    header('Location: ../../make-donation.php?orphanage_id=' . $orphanage_id . '&error=Failed to process donation. Please try again.');
  }
} else {
  header('Location: ../../orphanages.php');
}