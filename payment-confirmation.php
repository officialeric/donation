<?php
session_start();
include 'dist/includes/connection.php';
include 'dist/includes/payment_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get transaction ID from URL
$transaction_id = $_GET['transaction_id'] ?? '';

if (empty($transaction_id)) {
    header('Location: index.php');
    exit;
}

// Get transaction details
$stmt = $db->prepare("
    SELECT d.*, o.name as orphanage_name, o.location, c.title as campaign_title, u.username, u.email
    FROM donations d
    LEFT JOIN orphanages o ON d.orphanage_id = o.id
    LEFT JOIN campaigns c ON d.campaign_id = c.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE d.transaction_id = ? AND d.user_id = ?
");

$stmt->bind_param('si', $transaction_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php?error=Transaction not found');
    exit;
}

$donation = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TumainiFuraha | Payment Confirmation</title>
    
    <!--begin::Favicon-->
    <link rel="icon" type="image/svg+xml" href="dist/images/logo-simple.svg">
    <link rel="alternate icon" href="dist/images/logo-simple.svg">
    <link rel="mask-icon" href="dist/images/logo-simple.svg" color="#667eea">
    <!--end::Favicon-->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .confirmation-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .success-header {
            background: var(--success-gradient);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .transaction-details {
            padding: 2rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
        }
        
        .detail-value {
            font-weight: 600;
            color: #333;
        }
        
        .amount-highlight {
            font-size: 1.5rem;
            color: #28a745;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-buttons {
            padding: 2rem;
            background: #f8f9fa;
            text-align: center;
        }
        
        .btn-custom {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            margin: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .receipt-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .print-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .confirmation-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .action-buttons,
            .print-button {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .confirmation-container {
                padding: 0 0.5rem;
            }
            
            .success-header {
                padding: 2rem 1rem;
            }
            
            .transaction-details {
                padding: 1rem;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="confirmation-container">
        <div class="confirmation-card position-relative">
            <button class="btn btn-outline-secondary btn-sm print-button" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print Receipt
            </button>
            
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2>Payment Successful!</h2>
                <p class="mb-0">Thank you for your generous donation to help children in need</p>
            </div>
            
            <!-- Transaction Details -->
            <div class="transaction-details">
                <h4 class="mb-4">
                    <i class="bi bi-receipt me-2"></i>Transaction Details
                </h4>
                
                <div class="receipt-section">
                    <div class="detail-row">
                        <span class="detail-label">Transaction ID</span>
                        <span class="detail-value"><?php echo htmlspecialchars($donation['transaction_id']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Date & Time</span>
                        <span class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($donation['created_at'])); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Donation Amount</span>
                        <span class="detail-value amount-highlight"><?php echo PaymentConfig::formatAmount($donation['amount']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value">
                            <?php
                            $payment_methods = [
                                'credit_card' => 'Credit Card',
                                'paypal' => 'PayPal',
                                'bank_transfer' => 'Bank Transfer',
                                'mpesa' => 'M-Pesa'
                            ];
                            echo $payment_methods[$donation['payment_method']] ?? $donation['payment_method'];
                            ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">
                            <span class="status-badge status-<?php echo $donation['payment_status']; ?>">
                                <?php echo ucfirst($donation['payment_status']); ?>
                            </span>
                        </span>
                    </div>
                </div>
                
                <h5 class="mt-4 mb-3">
                    <i class="bi bi-building me-2"></i>Donation Recipient
                </h5>
                
                <div class="receipt-section">
                    <div class="detail-row">
                        <span class="detail-label">Orphanage</span>
                        <span class="detail-value"><?php echo htmlspecialchars($donation['orphanage_name']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Location</span>
                        <span class="detail-value"><?php echo htmlspecialchars($donation['location']); ?></span>
                    </div>
                    
                    <?php if ($donation['campaign_title']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Campaign</span>
                        <span class="detail-value"><?php echo htmlspecialchars($donation['campaign_title']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($donation['message']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Your Message</span>
                        <span class="detail-value"><?php echo htmlspecialchars($donation['message']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>What happens next?</strong><br>
                    Your donation will be processed and transferred to the orphanage within 2-3 business days. 
                    You will receive an email confirmation shortly with your receipt.
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary-custom btn-custom">
                    <i class="bi bi-house me-2"></i>Back to Home
                </a>
                <a href="make-donation.php?orphanage_id=<?php echo $donation['orphanage_id']; ?>" class="btn btn-outline-primary btn-custom">
                    <i class="bi bi-heart me-2"></i>Donate Again
                </a>
                <a href="dist/pages/donor/index.php" class="btn btn-outline-secondary btn-custom">
                    <i class="bi bi-person-circle me-2"></i>My Donations
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-scroll to top on page load
        window.scrollTo(0, 0);
        
        // Add some celebration effects
        document.addEventListener('DOMContentLoaded', function() {
            // Create confetti effect (simple version)
            createConfetti();
        });
        
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#4facfe', '#00f2fe', '#f093fb', '#f5576c'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'fixed';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.top = '-10px';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.borderRadius = '50%';
                    confetti.style.pointerEvents = 'none';
                    confetti.style.zIndex = '9999';
                    confetti.style.animation = 'fall 3s linear forwards';
                    
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 3000);
                }, i * 100);
            }
        }
        
        // Add CSS animation for confetti
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
