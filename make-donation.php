<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php?redirect=make-donation.php' . (isset($_GET['orphanage_id']) ? '?orphanage_id=' . $_GET['orphanage_id'] : ''));
  exit;
}

include 'dist/includes/connection.php';
include 'dist/includes/payment_config.php';

if (!isset($_GET['orphanage_id']) || empty($_GET['orphanage_id'])) {
  header('Location: index.php');
  exit;
}

$orphanage_id = mysqli_real_escape_string($db, $_GET['orphanage_id']);
$sql = "SELECT * FROM orphanages WHERE id = '$orphanage_id' AND status = 'active'";
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) == 0) {
  header('Location: index.php');
  exit;
}

$orphanage = mysqli_fetch_assoc($result);

// Check if this is a campaign-specific donation
$campaign_id = isset($_GET['campaign_id']) ? mysqli_real_escape_string($db, $_GET['campaign_id']) : '';
$campaign = null;

if ($campaign_id) {
  $campaign_sql = "SELECT * FROM campaigns WHERE id = '$campaign_id' AND orphanage_id = '$orphanage_id' AND status = 'active'";
  $campaign_result = mysqli_query($db, $campaign_sql);

  if (mysqli_num_rows($campaign_result) > 0) {
    $campaign = mysqli_fetch_assoc($campaign_result);
  } else {
    // Invalid campaign, redirect to general donation
    header('Location: make-donation.php?orphanage_id=' . $orphanage_id);
    exit;
  }
}
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TumainiFuraha | Make a Donation</title>
    <!--begin::Favicon-->
    <link rel="icon" type="image/svg+xml" href="dist/images/logo-simple.svg">
    <link rel="alternate icon" href="dist/images/logo-simple.svg">
    <link rel="mask-icon" href="dist/images/logo-simple.svg" color="#667eea">
    <!--end::Favicon-->
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="TumainiFuraha | Make a Donation" />
    <meta name="author" content="TumainiFuraha" />
    <meta
      name="description"
      content="Make a secure donation to <?= htmlspecialchars($orphanage['name']) ?> and help bring hope and joy to children in need."
    />
    <meta
      name="keywords"
      content="donation, orphanage, charity, children, hope, joy, TumainiFuraha"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <style>
      :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        --input-focus: #667eea;
      }

      body {
        background: var(--primary-gradient);
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        min-height: 100vh;
      }

      .donation-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
      }

      .donation-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
      }

      .donation-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2rem;
        text-align: center;
      }

      .orphanage-info {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1rem;
      }

      .campaign-info {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
        border-left: 4px solid #2196f3;
      }

      .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
      }

      .payment-method {
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
      }

      .payment-method:hover {
        border-color: var(--input-focus);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
      }

      .payment-method.active {
        border-color: var(--input-focus);
        background: rgba(102, 126, 234, 0.1);
      }

      .payment-method input[type="radio"] {
        display: none;
      }

      .payment-method-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
      }

      .payment-form {
        display: none;
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        margin: 1rem 0;
        border: 1px solid #e1e5e9;
      }

      .payment-form.active {
        display: block;
        animation: fadeIn 0.3s ease;
      }

      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .form-floating {
        margin-bottom: 1.5rem;
      }

      .form-floating .form-control {
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        padding: 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
      }

      .form-floating .form-control:focus {
        border-color: var(--input-focus);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
      }

      .card-input {
        position: relative;
      }

      .card-type-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        z-index: 10;
      }

      .card-number-input {
        padding-right: 50px !important;
      }

      .amount-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 0.5rem;
        margin: 1rem 0;
      }

      .amount-btn {
        border: 2px solid #e1e5e9;
        background: white;
        border-radius: 8px;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
      }

      .amount-btn:hover, .amount-btn.active {
        border-color: var(--input-focus);
        background: rgba(102, 126, 234, 0.1);
        color: var(--input-focus);
      }

      .donate-btn {
        background: var(--success-gradient);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }

      .donate-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
      }

      .donate-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
      }

      .loading-spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .security-info {
        background: #e8f5e8;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        border-left: 4px solid #4caf50;
      }

      .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
      }

      .success-message {
        color: #28a745;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
      }

      .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #e1e5e9;
        overflow: hidden;
        margin: 0.5rem 0;
      }

      .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
      }

      @media (max-width: 768px) {
        .donation-container {
          margin: 1rem auto;
          padding: 0 0.5rem;
        }

        .donation-header {
          padding: 1.5rem;
        }

        .payment-methods {
          grid-template-columns: repeat(2, 1fr);
        }

        .payment-form {
          padding: 1rem;
        }
      }
    </style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
          <img src="dist/images/logo-simple.svg" alt="TumainiFuraha Logo" width="40" height="40" class="me-2">
          <span>TumainiFuraha</span>
        </a>
        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="index.php">
            <i class="bi bi-arrow-left me-1"></i>Back to Orphanages
          </a>
        </div>
      </div>
    </nav>

    <div class="donation-container">
      <div class="donation-card">
        <!-- Header Section -->
        <div class="donation-header">
          <h2><i class="bi bi-heart-fill me-2"></i>Make a Donation</h2>
          <p class="mb-0">Your generosity brings hope and joy to children in need</p>

          <div class="orphanage-info">
            <h4><?php echo htmlspecialchars($orphanage['name']); ?></h4>
            <p class="mb-2">
              <i class="bi bi-geo-alt me-1"></i>
              <?php echo htmlspecialchars($orphanage['location']); ?>
            </p>
            <p class="mb-0">
              <i class="bi bi-info-circle me-1"></i>
              <?php echo htmlspecialchars($orphanage['description']); ?>
            </p>
          </div>
        </div>

        <!-- Campaign Info (if applicable) -->
        <?php if ($campaign): ?>
          <div class="p-4">
            <div class="campaign-info">
              <h5><i class="bi bi-megaphone me-2"></i><?php echo htmlspecialchars($campaign['title']); ?></h5>
              <p class="mb-3"><?php echo htmlspecialchars($campaign['description']); ?></p>

              <?php
                $progress = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                $progress_class = $progress >= 75 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
              ?>

              <div class="row">
                <div class="col-md-8">
                  <div class="progress-bar-custom">
                    <div class="progress-fill bg-<?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%"></div>
                  </div>
                  <small class="text-muted"><?php echo round($progress, 1); ?>% of goal reached</small>
                </div>
                <div class="col-md-4 text-end">
                  <div><strong><?php echo PaymentConfig::formatAmount($campaign['current_amount']); ?></strong> raised</div>
                  <small class="text-muted">of <?php echo PaymentConfig::formatAmount($campaign['target_amount']); ?> goal</small>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Donation Form -->
        <div class="p-4">
          <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i><?php echo $_GET['error']; ?>
            </div>
          <?php endif; ?>

          <form id="donationForm" action="dist/includes/process-donation.php" method="post">
            <input type="hidden" name="orphanage_id" value="<?php echo $orphanage['id']; ?>">
            <?php if ($campaign): ?>
              <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
            <?php endif; ?>

            <!-- Amount Selection -->
            <div class="mb-4">
              <label class="form-label h5">Donation Amount</label>
              <div class="amount-buttons">
                <button type="button" class="amount-btn" data-amount="25">$25</button>
                <button type="button" class="amount-btn" data-amount="50">$50</button>
                <button type="button" class="amount-btn" data-amount="100">$100</button>
                <button type="button" class="amount-btn" data-amount="250">$250</button>
                <button type="button" class="amount-btn" data-amount="500">$500</button>
                <button type="button" class="amount-btn" data-amount="custom">Custom</button>
              </div>

              <div class="form-floating mt-3">
                <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" placeholder="Enter amount" required>
                <label for="amount">
                  <i class="bi bi-currency-dollar me-2"></i>Amount (USD)
                </label>
                <div class="error-message" id="amount-error"></div>
              </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="mb-4">
              <label class="form-label h5">Payment Method</label>
              <div class="payment-methods">
                <label class="payment-method" for="credit_card">
                  <input type="radio" name="payment_method" id="credit_card" value="credit_card" required>
                  <i class="bi bi-credit-card payment-method-icon"></i>
                  <div>Credit Card</div>
                  <small class="text-muted">Visa, MasterCard, Amex</small>
                </label>

                <label class="payment-method" for="paypal">
                  <input type="radio" name="payment_method" id="paypal" value="paypal" required>
                  <i class="bi bi-paypal payment-method-icon" style="color: #0070ba;"></i>
                  <div>PayPal</div>
                  <small class="text-muted">Secure online payment</small>
                </label>

                <label class="payment-method" for="bank_transfer">
                  <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                  <i class="bi bi-bank payment-method-icon"></i>
                  <div>Bank Transfer</div>
                  <small class="text-muted">Direct bank transfer</small>
                </label>

                <label class="payment-method" for="mpesa">
                  <input type="radio" name="payment_method" id="mpesa" value="mpesa" required>
                  <i class="bi bi-phone payment-method-icon" style="color: #00a651;"></i>
                  <div>M-Pesa</div>
                  <small class="text-muted">Mobile money</small>
                </label>
              </div>
            </div>

            <!-- Credit Card Payment Form -->
            <div id="credit_card_form" class="payment-form">
              <h6><i class="bi bi-credit-card me-2"></i>Credit Card Information</h6>

              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" placeholder="Cardholder Name">
                <label for="card_holder_name">
                  <i class="bi bi-person me-2"></i>Cardholder Name
                </label>
                <div class="error-message" id="card_holder_name-error"></div>
              </div>

              <div class="form-floating mb-3 card-input">
                <input type="text" class="form-control card-number-input" id="card_number" name="card_number" placeholder="Card Number" maxlength="19">
                <label for="card_number">
                  <i class="bi bi-credit-card me-2"></i>Card Number
                </label>
                <div class="card-type-icon" id="card-type-icon"></div>
                <div class="error-message" id="card_number-error"></div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                    <label for="card_expiry">
                      <i class="bi bi-calendar me-2"></i>Expiry Date (MM/YY)
                    </label>
                    <div class="error-message" id="card_expiry-error"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="CVV" maxlength="4">
                    <label for="card_cvv">
                      <i class="bi bi-shield-lock me-2"></i>CVV/CVC
                    </label>
                    <div class="error-message" id="card_cvv-error"></div>
                  </div>
                </div>
              </div>

              <div class="security-info">
                <i class="bi bi-shield-check me-2"></i>
                <small>Your payment information is encrypted and secure. We use industry-standard SSL encryption.</small>
              </div>
            </div>

            <!-- PayPal Payment Form -->
            <div id="paypal_form" class="payment-form">
              <h6><i class="bi bi-paypal me-2" style="color: #0070ba;"></i>PayPal Payment</h6>

              <div class="form-floating mb-3">
                <input type="email" class="form-control" id="paypal_email" name="paypal_email" placeholder="PayPal Email">
                <label for="paypal_email">
                  <i class="bi bi-envelope me-2"></i>PayPal Email Address
                </label>
                <div class="error-message" id="paypal_email-error"></div>
              </div>

              <div class="security-info">
                <i class="bi bi-paypal me-2" style="color: #0070ba;"></i>
                <small>You will be redirected to PayPal to complete your payment securely.</small>
              </div>
            </div>

            <!-- Bank Transfer Form -->
            <div id="bank_transfer_form" class="payment-form">
              <h6><i class="bi bi-bank me-2"></i>Bank Transfer Information</h6>

              <div class="form-floating mb-3">
                <select class="form-control" id="bank_name" name="bank_name">
                  <option value="">Select Your Bank</option>
                  <optgroup label="Kenya">
                    <option value="KCB">Kenya Commercial Bank (KCB)</option>
                    <option value="EQUITY">Equity Bank Kenya</option>
                    <option value="COOP">Cooperative Bank of Kenya</option>
                    <option value="SCB">Standard Chartered Kenya</option>
                  </optgroup>
                  <optgroup label="Tanzania">
                    <option value="CRDB">CRDB Bank Tanzania</option>
                    <option value="NMB">National Microfinance Bank (NMB)</option>
                    <option value="EXIM">Exim Bank Tanzania</option>
                  </optgroup>
                  <optgroup label="Uganda">
                    <option value="STANBIC">Stanbic Bank Uganda</option>
                    <option value="CENTENARY">Centenary Bank Uganda</option>
                    <option value="BOA">Bank of Africa Uganda</option>
                  </optgroup>
                  <option value="OTHER">Other Bank</option>
                </select>
                <label for="bank_name">
                  <i class="bi bi-bank me-2"></i>Select Bank
                </label>
              </div>

              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" placeholder="Account Holder Name">
                <label for="account_holder_name">
                  <i class="bi bi-person me-2"></i>Account Holder Name
                </label>
              </div>

              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account Number">
                <label for="account_number">
                  <i class="bi bi-hash me-2"></i>Account Number
                </label>
              </div>

              <div class="security-info">
                <i class="bi bi-info-circle me-2"></i>
                <small>Bank transfer instructions will be provided after you submit this form.</small>
              </div>
            </div>

            <!-- M-Pesa Payment Form -->
            <div id="mpesa_form" class="payment-form">
              <h6><i class="bi bi-phone me-2" style="color: #00a651;"></i>M-Pesa Payment</h6>

              <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="mpesa_phone" name="mpesa_phone" placeholder="Phone Number" pattern="[0-9]{10}">
                <label for="mpesa_phone">
                  <i class="bi bi-phone me-2"></i>M-Pesa Phone Number
                </label>
                <div class="error-message" id="mpesa_phone-error"></div>
                <small class="text-muted">Format: 0762345678</small>
              </div>

              <div class="security-info">
                <i class="bi bi-phone me-2" style="color: #00a651;"></i>
                <small>You will receive an M-Pesa prompt on your phone to complete the payment.</small>
              </div>
            </div>

            <!-- Donor Message -->
            <div class="mb-4">
              <div class="form-floating">
                <textarea class="form-control" id="donor_message" name="donor_message" placeholder="Leave a message" style="height: 100px"></textarea>
                <label for="donor_message">
                  <i class="bi bi-chat-heart me-2"></i>Message of Support (Optional)
                </label>
              </div>
            </div>

            <!-- Anonymous Donation -->
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="anonymous" name="anonymous" value="1">
                <label class="form-check-label" for="anonymous">
                  <i class="bi bi-incognito me-2"></i>Make this donation anonymous
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
              <button type="submit" name="process_donation" class="donate-btn" id="donate-btn">
                <span class="loading-spinner" id="loading-spinner"></span>
                <i class="bi bi-heart-fill me-2"></i>
                <span id="btn-text">Complete Donation</span>
              </button>
              <a href="orphanage-details.php?id=<?php echo $orphanage['id']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orphanage
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Amount button functionality
        const amountButtons = document.querySelectorAll('.amount-btn');
        const amountInput = document.getElementById('amount');

        amountButtons.forEach(button => {
          button.addEventListener('click', function() {
            amountButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const amount = this.dataset.amount;
            if (amount !== 'custom') {
              amountInput.value = amount;
            } else {
              amountInput.focus();
            }
          });
        });

        // Payment method functionality
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const paymentForms = document.querySelectorAll('.payment-form');

        paymentMethods.forEach(method => {
          method.addEventListener('change', function() {
            // Remove active class from all payment methods
            document.querySelectorAll('.payment-method').forEach(pm => pm.classList.remove('active'));

            // Add active class to selected method
            this.closest('.payment-method').classList.add('active');

            // Hide all payment forms
            paymentForms.forEach(form => form.classList.remove('active'));

            // Show selected payment form
            const selectedForm = document.getElementById(this.value + '_form');
            if (selectedForm) {
              selectedForm.classList.add('active');
            }
          });
        });

        // Credit card number formatting and validation
        const cardNumberInput = document.getElementById('card_number');
        const cardTypeIcon = document.getElementById('card-type-icon');

        if (cardNumberInput) {
          cardNumberInput.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '');
            let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();

            if (formattedValue.length > 19) {
              formattedValue = formattedValue.substring(0, 19);
            }

            this.value = formattedValue;

            // Detect card type
            const cardType = detectCardType(value);
            updateCardTypeIcon(cardType);
          });
        }

        // Card expiry formatting
        const cardExpiryInput = document.getElementById('card_expiry');
        if (cardExpiryInput) {
          cardExpiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
              value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
          });
        }

        // CVV input restriction
        const cardCvvInput = document.getElementById('card_cvv');
        if (cardCvvInput) {
          cardCvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
          });
        }

        // M-Pesa phone number formatting
        const mpesaPhoneInput = document.getElementById('mpesa_phone');
        if (mpesaPhoneInput) {
          mpesaPhoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 10) {
              value = value.substring(0, 10);
            }
            this.value = value;
          });
        }

        // Form submission
        const donationForm = document.getElementById('donationForm');
        const donateBtn = document.getElementById('donate-btn');
        const loadingSpinner = document.getElementById('loading-spinner');
        const btnText = document.getElementById('btn-text');

        donationForm.addEventListener('submit', function(e) {
          e.preventDefault();

          if (validateForm()) {
            // Show loading state
            donateBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Processing...';

            // Submit form after a short delay (for UX)
            setTimeout(() => {
              this.submit();
            }, 1000);
          }
        });

        // Form validation
        function validateForm() {
          let isValid = true;

          // Validate amount
          const amount = parseFloat(amountInput.value);
          if (!amount || amount < 1) {
            showError('amount-error', 'Please enter a valid donation amount');
            isValid = false;
          } else if (amount > 5000) {
            showError('amount-error', 'Maximum donation amount is $5,000');
            isValid = false;
          } else {
            hideError('amount-error');
          }

          // Validate payment method
          const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
          if (!selectedPaymentMethod) {
            alert('Please select a payment method');
            isValid = false;
          } else {
            // Validate specific payment method fields
            const paymentMethod = selectedPaymentMethod.value;

            if (paymentMethod === 'credit_card') {
              isValid = validateCreditCard() && isValid;
            } else if (paymentMethod === 'paypal') {
              isValid = validatePayPal() && isValid;
            } else if (paymentMethod === 'mpesa') {
              isValid = validateMPesa() && isValid;
            }
          }

          return isValid;
        }

        function validateCreditCard() {
          let isValid = true;

          const cardHolderName = document.getElementById('card_holder_name').value.trim();
          const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
          const cardExpiry = document.getElementById('card_expiry').value;
          const cardCvv = document.getElementById('card_cvv').value;

          if (!cardHolderName) {
            showError('card_holder_name-error', 'Cardholder name is required');
            isValid = false;
          } else {
            hideError('card_holder_name-error');
          }

          if (!cardNumber || cardNumber.length < 13) {
            showError('card_number-error', 'Please enter a valid card number');
            isValid = false;
          } else if (!validateCardNumber(cardNumber)) {
            showError('card_number-error', 'Invalid card number');
            isValid = false;
          } else {
            hideError('card_number-error');
          }

          if (!cardExpiry || !validateExpiry(cardExpiry)) {
            showError('card_expiry-error', 'Please enter a valid expiry date');
            isValid = false;
          } else {
            hideError('card_expiry-error');
          }

          if (!cardCvv || cardCvv.length < 3) {
            showError('card_cvv-error', 'Please enter a valid CVV');
            isValid = false;
          } else {
            hideError('card_cvv-error');
          }

          return isValid;
        }

        function validatePayPal() {
          const paypalEmail = document.getElementById('paypal_email').value.trim();
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

          if (!paypalEmail || !emailRegex.test(paypalEmail)) {
            showError('paypal_email-error', 'Please enter a valid PayPal email address');
            return false;
          } else {
            hideError('paypal_email-error');
            return true;
          }
        }

        function validateMPesa() {
          const mpesaPhone = document.getElementById('mpesa_phone').value.trim();

          if (!mpesaPhone || mpesaPhone.length !== 10 || !mpesaPhone.startsWith('07')) {
            showError('mpesa_phone-error', 'Please enter a valid M-Pesa number (07XXXXXXXX)');
            return false;
          } else {
            hideError('mpesa_phone-error');
            return true;
          }
        }

        function detectCardType(cardNumber) {
          if (/^4/.test(cardNumber)) return 'visa';
          if (/^5[1-5]/.test(cardNumber)) return 'mastercard';
          if (/^3[47]/.test(cardNumber)) return 'amex';
          if (/^6/.test(cardNumber)) return 'discover';
          return 'unknown';
        }

        function updateCardTypeIcon(cardType) {
          const icons = {
            'visa': '<i class="bi bi-credit-card" style="color: #1a1f71;"></i>',
            'mastercard': '<i class="bi bi-credit-card" style="color: #eb001b;"></i>',
            'amex': '<i class="bi bi-credit-card" style="color: #006fcf;"></i>',
            'discover': '<i class="bi bi-credit-card" style="color: #ff6000;"></i>',
            'unknown': '<i class="bi bi-credit-card" style="color: #666;"></i>'
          };

          cardTypeIcon.innerHTML = icons[cardType] || icons['unknown'];
        }

        function validateCardNumber(cardNumber) {
          // Luhn algorithm
          let sum = 0;
          let alternate = false;

          for (let i = cardNumber.length - 1; i >= 0; i--) {
            let n = parseInt(cardNumber.charAt(i), 10);

            if (alternate) {
              n *= 2;
              if (n > 9) {
                n = (n % 10) + 1;
              }
            }

            sum += n;
            alternate = !alternate;
          }

          return (sum % 10) === 0;
        }

        function validateExpiry(expiry) {
          const [month, year] = expiry.split('/');
          const currentDate = new Date();
          const currentYear = currentDate.getFullYear() % 100;
          const currentMonth = currentDate.getMonth() + 1;

          const expiryMonth = parseInt(month, 10);
          const expiryYear = parseInt(year, 10);

          if (expiryMonth < 1 || expiryMonth > 12) return false;
          if (expiryYear < currentYear) return false;
          if (expiryYear === currentYear && expiryMonth < currentMonth) return false;

          return true;
        }

        function showError(elementId, message) {
          const errorElement = document.getElementById(elementId);
          if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
          }
        }

        function hideError(elementId) {
          const errorElement = document.getElementById(elementId);
          if (errorElement) {
            errorElement.style.display = 'none';
          }
        }
      });
    </script>
  </body>
  <!--end::Body-->
</html>