<?php
/**
 * Stripe Webhook Handler for TumainiFuraha
 * Handles real-time payment status updates from Stripe
 */

require_once 'vendor/autoload.php'; // Composer autoloader for Stripe
require_once 'dist/includes/connection.php';
require_once 'dist/includes/stripe_integration.php';
require_once 'dist/includes/email_notifications.php';

// Set content type
header('Content-Type: application/json');

try {
    // Get the payload and signature
    $payload = file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    // Log webhook for debugging (remove in production)
    error_log('Stripe Webhook Received: ' . substr($payload, 0, 200) . '...');
    
    // Initialize Stripe integration
    $stripe = new StripeIntegration();
    
    // Handle the webhook
    $result = $stripe->handleWebhook($payload, $sig_header);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $result['error']]);
    }
    
} catch (Exception $e) {
    error_log('Stripe Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
?>
