<?php
/**
 * Email Notification System for TumainiFuraha
 * Handles sending payment confirmations and receipts
 */

class EmailNotifications {
    
    private $from_email = 'noreply@tumainifuraha.org';
    private $from_name = 'TumainiFuraha';
    private $smtp_host = 'localhost'; // Configure with your SMTP settings
    private $smtp_port = 587;
    private $smtp_username = '';
    private $smtp_password = '';
    
    /**
     * Send donation confirmation email
     */
    public function sendDonationConfirmation($donation_data) {
        $to_email = $donation_data['donor_email'];
        $to_name = $donation_data['donor_name'];
        $subject = 'Thank you for your donation - TumainiFuraha';
        
        $html_content = $this->generateDonationConfirmationHTML($donation_data);
        $text_content = $this->generateDonationConfirmationText($donation_data);
        
        return $this->sendEmail($to_email, $to_name, $subject, $html_content, $text_content);
    }
    
    /**
     * Send donation receipt email
     */
    public function sendDonationReceipt($donation_data) {
        $to_email = $donation_data['donor_email'];
        $to_name = $donation_data['donor_name'];
        $subject = 'Your donation receipt - TumainiFuraha';
        
        $html_content = $this->generateReceiptHTML($donation_data);
        $text_content = $this->generateReceiptText($donation_data);
        
        return $this->sendEmail($to_email, $to_name, $subject, $html_content, $text_content);
    }
    
    /**
     * Generate HTML content for donation confirmation
     */
    private function generateDonationConfirmationHTML($data) {
        $amount = PaymentConfig::formatAmount($data['amount']);
        $date = date('F j, Y \a\t g:i A', strtotime($data['created_at']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Donation Confirmation</title>
            <style>
                body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .highlight { background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .detail-row:last-child { border-bottom: none; }
                .amount { font-size: 24px; font-weight: bold; color: #28a745; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Thank You for Your Donation!</h1>
                    <p>Your generosity brings hope and joy to children in need</p>
                </div>
                
                <div class='content'>
                    <div class='highlight'>
                        <h3>Your donation of <span class='amount'>{$amount}</span> has been successfully processed!</h3>
                        <p>Transaction ID: <strong>{$data['transaction_id']}</strong></p>
                    </div>
                    
                    <div class='details'>
                        <h4>Donation Details</h4>
                        <div class='detail-row'>
                            <span>Date & Time:</span>
                            <span>{$date}</span>
                        </div>
                        <div class='detail-row'>
                            <span>Orphanage:</span>
                            <span>{$data['orphanage_name']}</span>
                        </div>
                        <div class='detail-row'>
                            <span>Location:</span>
                            <span>{$data['location']}</span>
                        </div>
                        " . ($data['campaign_title'] ? "
                        <div class='detail-row'>
                            <span>Campaign:</span>
                            <span>{$data['campaign_title']}</span>
                        </div>
                        " : "") . "
                        <div class='detail-row'>
                            <span>Payment Method:</span>
                            <span>{$data['payment_method_display']}</span>
                        </div>
                    </div>
                    
                    " . ($data['message'] ? "
                    <div class='details'>
                        <h4>Your Message</h4>
                        <p><em>\"{$data['message']}\"</em></p>
                    </div>
                    " : "") . "
                    
                    <div class='highlight'>
                        <h4>What happens next?</h4>
                        <p>Your donation will be transferred to the orphanage within 2-3 business days. The children and staff will be notified of your generous contribution.</p>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://tumainifuraha.org/payment-confirmation.php?transaction_id={$data['transaction_id']}' class='button'>View Full Receipt</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Thank you for being part of the TumainiFuraha community!</p>
                    <p>Together, we bring hope and joy to every child.</p>
                    <p><small>This is an automated message. Please do not reply to this email.</small></p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generate text content for donation confirmation
     */
    private function generateDonationConfirmationText($data) {
        $amount = PaymentConfig::formatAmount($data['amount']);
        $date = date('F j, Y \a\t g:i A', strtotime($data['created_at']));
        
        return "
THANK YOU FOR YOUR DONATION!

Your generous donation of {$amount} has been successfully processed.

DONATION DETAILS:
- Transaction ID: {$data['transaction_id']}
- Date & Time: {$date}
- Orphanage: {$data['orphanage_name']}
- Location: {$data['location']}
" . ($data['campaign_title'] ? "- Campaign: {$data['campaign_title']}\n" : "") . "
- Payment Method: {$data['payment_method_display']}

" . ($data['message'] ? "YOUR MESSAGE:\n\"{$data['message']}\"\n\n" : "") . "
WHAT HAPPENS NEXT:
Your donation will be transferred to the orphanage within 2-3 business days. 
The children and staff will be notified of your generous contribution.

View your full receipt at:
https://tumainifuraha.org/payment-confirmation.php?transaction_id={$data['transaction_id']}

Thank you for being part of the TumainiFuraha community!
Together, we bring hope and joy to every child.

---
TumainiFuraha - Hope and Joy for Every Child
This is an automated message. Please do not reply to this email.
        ";
    }
    
    /**
     * Generate HTML receipt
     */
    private function generateReceiptHTML($data) {
        // Similar to confirmation but formatted as a formal receipt
        return $this->generateDonationConfirmationHTML($data);
    }
    
    /**
     * Generate text receipt
     */
    private function generateReceiptText($data) {
        return $this->generateDonationConfirmationText($data);
    }
    
    /**
     * Send email using PHP mail function (basic implementation)
     * In production, use a proper email service like SendGrid, Mailgun, etc.
     */
    private function sendEmail($to_email, $to_name, $subject, $html_content, $text_content) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headers_string = implode("\r\n", $headers);
        
        // For demo purposes, we'll just log the email instead of sending
        error_log("Email would be sent to: {$to_email}");
        error_log("Subject: {$subject}");
        error_log("Content: " . substr($html_content, 0, 200) . "...");
        
        // Uncomment the line below to actually send emails
        // return mail($to_email, $subject, $html_content, $headers_string);
        
        return true; // Return true for demo purposes
    }
    
    /**
     * Send notification to orphanage about new donation
     */
    public function notifyOrphanageOfDonation($donation_data, $orphanage_email) {
        $subject = 'New Donation Received - TumainiFuraha';
        $amount = PaymentConfig::formatAmount($donation_data['amount']);
        
        $html_content =
            "<h2>New Donation Received!</h2>
            <p>You have received a new donation through TumainiFuraha:</p>
            <ul>
                <li><strong>Amount:</strong> {$amount}</li>
                <li><strong>Transaction ID:</strong> {$donation_data['transaction_id']}</li>
                <li><strong>Date:</strong> " . date('F j, Y', strtotime($donation_data['created_at'])) . "</li>"
                . ($donation_data['campaign_title'] ? "<li><strong>Campaign:</strong> {$donation_data['campaign_title']}</li>" : "")
                . ($donation_data['message'] ? "<li><strong>Donor Message:</strong> {$donation_data['message']}</li>" : "") .
            "</ul>
            <p>The funds will be transferred to your account within 2-3 business days.</p>
            <p>Thank you for your continued work in caring for children!</p>";
        
        return $this->sendEmail($orphanage_email, $donation_data['orphanage_name'], $subject, $html_content, strip_tags($html_content));
    }
}

/**
 * Helper function to send donation confirmation
 */
function sendDonationConfirmationEmail($transaction_id) {
    global $db;
    
    // Get donation details with user and orphanage info
    $stmt = $db->prepare(
        "SELECT d.*, o.name as orphanage_name, o.location, o.contact_email as orphanage_email,
               c.title as campaign_title, u.username as donor_name, u.email as donor_email
        FROM donations d
        LEFT JOIN orphanages o ON d.orphanage_id = o.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        LEFT JOIN users u ON d.user_id = u.id
        WHERE d.transaction_id = ?"
    );
    
    $stmt->bind_param('s', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $donation_data = $result->fetch_assoc();
        
        // Add payment method display name
        $payment_methods = [
            'credit_card' => 'Credit Card',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
            'mpesa' => 'M-Pesa'
        ];
        $donation_data['payment_method_display'] = $payment_methods[$donation_data['payment_method']] ?? $donation_data['payment_method'];
        
        $email_service = new EmailNotifications();
        
        // Send confirmation to donor
        $email_service->sendDonationConfirmation($donation_data);
        
        // Send notification to orphanage
        if ($donation_data['orphanage_email']) {
            $email_service->notifyOrphanageOfDonation($donation_data, $donation_data['orphanage_email']);
        }
        
        return true;
    }
    
    return false;
}
?>
