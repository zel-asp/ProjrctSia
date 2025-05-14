<?php
include("server/connection.php");
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get bookings
$stmt1 = $conn->prepare("SELECT * FROM booking WHERE user_id = ? ORDER BY departure_date DESC");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$bookings = $stmt1->get_result();

// Handle logout
if (isset($_POST['logout_btn'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle booking status updates
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE booking SET status = 'Cancelled' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    echo '<script>alert("Cancelled successfully"); window.location.href = "profile.php";</script>';
    exit();
}

if (isset($_POST['refund'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE booking SET status = 'On hold' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    echo '<script>alert("Refund successfully"); window.location.href = "profile.php";</script>';
    exit();
}

if (isset($_POST['Rebook'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE booking SET status = 'On hold' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    echo '<script>alert("Rebooked successfully"); window.location.href = "profile.php";</script>';
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile | JetSetGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/profile.css">

</head>

<body>
    <!-- Header -->
    <header class="profile-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <img src="assets/images/whitelogo.png" alt="JetSetGo Logo" width="40">
                <h5 class="mb-0">JetSetGo Profile</h5>
            </div>
            <a href="index.php" class="btn btn-sm btn-outline-light rounded-circle" data-bs-toggle="tooltip" title="Back to home">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </header>

    <main class="container my-4">
        <!-- Profile Section -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="card p-4">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                        <!-- Profile Picture -->
                        <div class="position-relative">
                            <i class=" bi bi-person profile-avatar" id="profileUploadArea">
                            </i>
                        </div>

                        <!-- Profile Info -->
                        <div class="text-center text-md-start mt-3 mt-md-0">
                            <h3 class="mb-1"><?php echo htmlspecialchars($user['user_firstName'] . ' ' . $user['user_lastName']); ?></h3>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($user['user_email']); ?></p>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="mt-4">
                        <h5 class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-person-badge-fill text-primary"></i> Personal Information
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-light" id="firstName"
                                        value="<?php echo htmlspecialchars($user['user_firstName']); ?>" readonly>
                                    <label for="firstName">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-light" id="lastName"
                                        value="<?php echo htmlspecialchars($user['user_lastName']); ?>" readonly>
                                    <label for="lastName">Last Name</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="email" class="form-control bg-light" id="email"
                                        value="<?php echo htmlspecialchars($user['user_email']); ?>" readonly>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Travel History -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-4">
                    <h5 class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-airplane-fill text-primary"></i> Travel History
                    </h5>

                    <?php if ($bookings->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($booking = $bookings->fetch_assoc()):

                                $isUpcoming = strtotime($booking['departure_date']) > time();
                                $isRoundTrip = !empty($booking['return_date']);

                                switch ($booking['status']) {
                                    case 'On hold':
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'Unpaid';
                                        break;
                                    case 'Paid':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Confirmed';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Cancelled';
                                        break;
                                    default:
                                }
                            ?>

                                <span class="badge <?php echo $isUpcoming ? 'bg-info' : 'bg-success'; ?>">
                                    <?php echo $isUpcoming ? 'Scheduled' : 'Completed'; ?>
                                </span>
                                <div class="list-group-item border-0 p-3 mb-3 flight-card">

                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex gap-3">
                                            <div class="airport-code bg-light rounded p-2 text-center fw-bold">
                                                <?php echo strtoupper(substr($booking['booking_id'], 0, 3)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($booking['user_origin']); ?>
                                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($booking['user_destination']); ?>
                                                </h6>
                                                <p class="mb-1 text-muted">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <?php echo date("M j, Y", strtotime($booking['departure_date'])); ?>
                                                    <?php if ($isRoundTrip): ?>
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-arrow-return-right me-1"></i>
                                                        <?php echo date("M j, Y", strtotime($booking['return_date'])); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    <?php echo htmlspecialchars($booking['passenger']); ?>
                                                    <span class="mx-2">•</span>
                                                    <i class="bi bi-tag-fill me-1"></i>
                                                    <?php echo htmlspecialchars($booking['class']); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>

                                    <?php if (!$isUpcoming) { ?>
                                        <div class="d-flex justify-content-end gap-2 mt-3 ">
                                            <div class="d-flex justify-content-end gap-2 mt-3 flex-wrap ">
                                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                                    <i class="bi bi-credit-card me-1"></i> Pay
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" name="cancel_booking" disabled>
                                                    <i class="bi bi-trash me-1"></i> Cancel
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" name="refund" disabled>
                                                    <i class="bi bi-credit-card-2-back me-1"></i>Refund
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" name="view_ticket" disabled>
                                                    <i class="bi bi-eye me-1"></i> View Ticket
                                                </button>
                                                <a href="index.php#bookFlight">
                                                    <button class="btn btn-sm btn-outline-primary" name="Rebook">
                                                        <i class="bi bi-plus me-1"></i> Book again
                                                    </button>
                                                </a>
                                            </div>
                                        <?php } else { ?>
                                            <?php if ($booking['status'] === 'On hold') { ?>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                        <a href="payment.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-sm btn-outline-success">
                                                            <i class="bi bi-credit-card me-1"></i> Pay
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" name="cancel_booking">
                                                            <i class="bi bi-trash me-1"></i> Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php } elseif ($booking['status'] === 'Paid') { ?>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                    <input type="hidden" name="users_id" value="<?php echo htmlspecialchars($booking['user_id']); ?>">
                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                                            <i class="bi bi-credit-card me-1"></i> Pay
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" name="cancel_booking" disabled>
                                                            <i class="bi bi-trash me-1"></i>Cancel
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-primary" name="refund">
                                                            <i class="bi bi-credit-card-2-back me-1"></i>Refund
                                                        </button>

                                                </form>
                                                <form action="ticket.php" method="POST">

                                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($booking['user_id']); ?>">
                                                    <button class="btn btn-sm btn-outline-primary" name="view_ticket">
                                                        <i class="bi bi-eye me-1"></i> View Ticket
                                                    </button>

                                                </form>
                                        </div>
                                    <?php } elseif ($booking['status'] === 'Cancelled') { ?>
                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-credit-card me-1"></i> Pay
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" disabled>
                                                <i class="bi bi-trash me-1"></i> Cancel
                                            </button>
                                            <form action="" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($booking['user_id']); ?>">
                                                <button class="btn btn-sm btn-outline-primary" name="Rebook">
                                                    <i class="bi bi-bootstrap-reboot me-1"></i> Rebook
                                                </button>

                                            </form>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-airplane text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No Travel History Yet</h5>
                            <p class="text-muted">Your upcoming and past flights will appear here</p>
                            <a href="index.php#bookFlight" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Book a Flight
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Logout Button -->
    <form action="" method="POST">
        <button type="submit" class="logout-btn" name="logout_btn">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>

</html>