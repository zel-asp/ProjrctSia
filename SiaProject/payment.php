<?php
include("server/connection.php");
session_start();

if (!isset($_GET['booking_id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

$stmt = $conn->prepare("SELECT * FROM booking WHERE booking_id=?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$booking = $result->fetch_assoc();
$isRoundTrip = !empty($booking['return_date']);
$basePrice = $isRoundTrip ? $booking['price'] / 1.8 : $booking['price']; // Reverse calculate base price
$taxesAndFees = $booking['price'] * 0.1;
$subtotal = $booking['price'] - $taxesAndFees;


if (isset($_POST['pay_btn'])) {
    $status = "Paid";
    $payment_method = $_POST['payment_method'];
    $booking_id = $_POST['booking_id'];

    $stmt2 = $conn->prepare("UPDATE booking SET status = ?, payment_method = ? WHERE booking_id = ?");
    $stmt2->bind_param("ssi", $status, $payment_method, $booking_id);

    if ($stmt2->execute()) {
        echo "<script>
                alert('Payment successful. Be on time!');
                window.location.href='ticket.php';
              </script>";
    } else {
        echo "Error: " . $stmt2->error;
    }
}

if (isset($_POST['view_ticket'])) {
    header("Location:ticket.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | JetSetGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/payment.css">
</head>

<body>
    <div class="payment-container py-5">
        <div class="container">
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-step completed">
                    <span class="progress-step-label">Flight Selection</span>
                </div>
                <div class="progress-step completed">
                    <span class="progress-step-label">Passenger Details</span>
                </div>
                <div class="progress-step active">
                    <span class="progress-step-label">Payment</span>
                </div>
                <div class="progress-step">
                    <span class="progress-step-label">Confirmation</span>
                </div>
            </div>

            <div class="text-center mb-5">
                <h1 class="fw-bold mb-3">Complete Your Booking</h1>
                <p class="text-muted">Review your flight details and choose your preferred payment method</p>
            </div>

            <div class="row g-4">
                <!-- Booking Summary -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3><i class="bi bi-ticket-detailed me-2"></i> Booking Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="flight-route mb-4">
                                <div class="d-flex justify-content-between position-relative mb-4">
                                    <div class="flight-dot"></div>
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($booking['user_origin']); ?></h5>
                                        <small class="text-muted">Departure</small>
                                    </div>
                                    <div class="text-end">
                                        <strong><?php echo date("M j, Y", strtotime($booking['departure_date'])); ?></strong>
                                        <div class="badge badge-light mt-1">
                                            <i class="bi bi-airplane me-1"></i> Flight <?php echo $booking['booking_id']; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between position-relative">
                                    <div class="flight-dot arrival"></div>
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($booking['user_destination']); ?></h5>
                                        <small class="text-muted">Arrival</small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($isRoundTrip): ?>
                                            <strong><?php echo date("M j, Y", strtotime($booking['return_date'])); ?></strong>
                                            <div class="badge badge-light mt-1">
                                                <i class="bi bi-airplane me-1"></i> Return Flight
                                            </div>
                                            <small class="text-muted">Round trip</small>
                                        <?php else: ?>
                                            <strong>N/A</strong>
                                            <small class="text-muted">One way</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Passengers</small>
                                    <strong><?php echo htmlspecialchars($booking['passenger']); ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Class</small>
                                    <strong><?php echo htmlspecialchars($booking['class']); ?></strong>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Base Fare</span>
                                    <span>₱<?php echo number_format($basePrice, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Taxes & Fees</span>
                                    <span>₱<?php echo number_format($taxesAndFees, 2); ?></span>
                                </div>

                                <?php if ($isRoundTrip): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Return Flight</span>
                                        <span>₱<?php echo number_format($basePrice * 0.8, 2); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total Amount</h5>
                                    <h3 class="mb-0 price-tag">₱<?php echo number_format($booking['price'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="bi bi-credit-card me-2"></i> Payment Method</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">eror</div>


                            <form action="" method="POST">
                                <h5 class="fw-bold mb-4">Select Payment Option</h5>
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">


                                <div class="mb-4">
                                    <div class="payment-method" onclick="selectPayment('paypal')">
                                        <div class="payment-icon">
                                            <i class="bi bi-paypal"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">PayPal</h6>
                                            <p class="small text-muted mb-0">Pay securely with your PayPal account</p>
                                        </div>
                                        <input class="form-check-input ms-auto" type="radio" name="payment_method" id="paypal" value="paypal">
                                    </div>

                                    <div class="payment-method" onclick="selectPayment('gcash')">
                                        <div class="payment-icon">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">GCash</h6>
                                            <p class="small text-muted mb-0">Pay using your GCash wallet</p>
                                        </div>
                                        <input class="form-check-input ms-auto" type="radio" name="payment_method" id="gcash" value="gcash">
                                    </div>
                                </div>

                                <!-- PayPal Details -->
                                <div id="paypal_details" class="payment-details">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <h6 class="mb-0">About PayPal</h6>
                                    </div>
                                    <p class="small text-muted">You'll be redirected to PayPal to complete your payment securely. Make sure you have sufficient balance in your PayPal wallet.</p>
                                    <img src="assets/images/Paypal-Logo-2022 (1).png" alt="PayPal" class="img-fluid mt-2" style="height: 30px;">
                                </div>

                                <!-- GCash Details -->
                                <div id="gcash_details" class="payment-details">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <h6 class="mb-0">About GCash</h6>
                                    </div>
                                    <p class="small text-muted">You'll be redirected to GCash to complete your payment. Make sure you have sufficient balance in your GCash wallet.</p>
                                    <img src="assets/images/GCash-Logo.jpg" alt="GCash" class="img-fluid mt-2" style="height: 30px;">
                                </div>

                                <button type="submit" name="pay_btn" class="btn btn-pay mb-3">
                                    <i class="bi bi-lock-fill me-2"></i> Pay Securely ₱<?php echo number_format($booking['price'], 2); ?>
                                </button>

                                <div class="d-flex justify-content-center gap-4">
                                    <div class="security-badge">
                                        <i class="bi bi-shield-lock"></i>
                                        <span>Secured</span>
                                    </div>
                                    <div class="security-badge">
                                        <i class="bi bi-credit-card"></i>
                                        <span>Payment</span>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function selectPayment(method) {
            // Update active state for payment methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            // Check the corresponding radio button
            document.getElementById(method).checked = true;

            // Show the correct payment details
            document.querySelectorAll('.payment-details').forEach(el => {
                el.style.display = 'none';
            });
            document.getElementById(method + '_details').style.display = 'block';
        }

        // Initialize with first payment method selected
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.payment-method').click();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>