<?php
include("server/connection.php");
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location:login.php");
}


if (isset($_POST['view_ticket'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM booking WHERE booking_id =?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();


    $stmt1 = $conn->prepare("SELECT * FROM users WHERE user_id=?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
} else {
    header("Location:profile.php");
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/ticket.css">
    <title>Flight Ticket</title>

</head>

<body>
    <div class="ticket">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="ticket-header">
                <h1>BOARDING PASS</h1>
                <p>JetSetGo Airlines | Flight <?php echo $row['booking_id']; ?></p>
            </div>

            <div class="ticket-body">
                <div class="flight-info">
                    <div class="airport">
                        <div class="airport-code"><?php echo $row['user_origin']; ?></div>
                        <div class="airport-name">FROM </div>
                    </div>

                    <div class="flight-details ">
                        <div class="flight-number">JSG<?php echo $row['booking_id']; ?> </div>
                        <div class="flight-time"><?php echo $row['departure_date']; ?></div>
                    </div>

                    <div class="airport">
                        <div class="airport-code"><?php echo $row['user_destination']; ?></div>
                        <div class="airport-name">WHERE </div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="passenger-info">
                    <?php while ($row1 = $result1->fetch_assoc()) { ?>
                        <div class="info-group">
                            <div class="info-label">First Name</div>
                            <div class="info-value"><?php echo $row1['user_firstName']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Last Name</div>
                            <div class="info-value"><?php echo $row1['user_lastName']; ?></div>
                        </div>
                    <?php } ?>

                    <div class="info-group">
                        <div class="info-label">Class</div>
                        <div class="info-value"><?php echo $row['class']; ?></div>
                    </div>
                </div>

                <div class="passenger-info">
                    <div class="info-group">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?php echo date("F j, Y", strtotime($row['departure_date'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Departure</div>
                        <div class="info-value"><?php echo date("g:i A", strtotime($row['book_time'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Return</div>
                        <div class="info-value"><?php echo date("F j, Y", strtotime($row['return_date'])); ?></div>
                    </div>
                </div>

                <div class="passenger-info">
                    <div class="info-group">
                        <div class="info-label">Seat</div>
                        <div class="info-value"><?php echo $row['seat_number']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Flight</div>
                        <div class="info-value">JSG<?php echo $row['booking_id']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Passenger</div>
                        <div class="info-value"><?php echo $row['passenger']; ?></div>
                    </div>
                </div>

                <?php if ($row['payment_method'] == "gcash") {
                    echo '
                    <div class="barcode">
                        <img src="assets/images/GCash-Logo.jpg" alt="PaymentMethod">
                    </div>
                    ';
                } else {
                    echo '
                    <div class="barcode">
                        <img src="assets/images/Paypal-Logo-2022 (1).png" alt="PaymentMethod">
                    </div>
                    ';
                }
                ?>

                <div class="terms">
                    This ticket is non-transferable. Please arrive at least 2 hours before departure.
                </div>
            </div>
            <div class="d-flex justify-content-center align-self-center my-2">
                <button class="btn print-btn" onclick="window.print()">Print Ticket</button>
            </div>
            <div class="ticket-footer">
                <img src="assets/images/logo black.png" alt="JetSetGo Logo" class="logo">
                <p>Thank you for flying with JetSetGo Airlines</p>
            </div>
        <?php } ?>

    </div>
</body>

</html>