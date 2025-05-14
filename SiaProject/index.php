<?php
include("server/connection.php");
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location:login.php");
}

// Discover PH
$stmt = $conn->prepare("SELECT * FROM destinations LIMIT 6");
$stmt->execute();
$result = $stmt->get_result();

// Popular destination
$stmt1 = $conn->prepare("SELECT * FROM destinations");
$stmt1->execute();
$result1 = $stmt1->get_result();

// Retrieve destinations for dropdown
$stmt3 = $conn->prepare("SELECT * FROM destinations");
$stmt3->execute();
$result3 = $stmt3->get_result();

// Values in booking
$stmt2 = $conn->prepare("SELECT * FROM destinations");
$stmt2->execute();
$result2 = $stmt2->get_result();

// Booking Section

if (isset($_POST['book_btn'])) {
    $user_id = $_SESSION['user_id'];
    $destination_id = $_POST['destination_id'];
    $user_origin = $_POST['user_origin'];
    $user_destination = $_POST['user_destination'];
    $departure_date = $_POST['departure_date'];
    $return_date = !empty($_POST['return_date']) ? $_POST['return_date'] : null;
    $passenger = $_POST['passenger'];
    $class = $_POST['passenger_class'];
    $seat_number = $_POST['seat_number'];
    $price = $_POST['calculated_price'];

    // Check if seat is already taken
    $seat_check = $conn->prepare("SELECT booking_id FROM booking 
        WHERE user_origin = ? AND user_destination = ? AND departure_date = ? AND seat_number = ?");
    $seat_check->bind_param("ssss", $user_origin, $user_destination, $departure_date, $seat_number);
    $seat_check->execute();
    $seat_check->store_result();

    if ($seat_check->num_rows > 0) {
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_error'] = "Seat $seat_number is already booked for this route and date. Please choose a different seat.";
        header("Location: index.php#bookFlight");
        exit();
    }

    // Check if user already booked this trip
    $check = $conn->prepare("SELECT booking_id FROM booking 
        WHERE user_id = ? AND destination_id = ? AND departure_date = ?");
    $check->bind_param("iis", $user_id, $destination_id, $departure_date);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_error'] = "You already booked this trip.";
        header("Location: index.php#bookFlight");
        exit();
    } else {
        // Proceed to insert booking
        $stmt = $conn->prepare("INSERT INTO booking (
            user_id, destination_id, user_origin, user_destination, departure_date,
            return_date, passenger, class, price, seat_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "iissssssss",
            $user_id,
            $destination_id,
            $user_origin,
            $user_destination,
            $departure_date,
            $return_date,
            $passenger,
            $class,
            $price,
            $seat_number
        );

        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;

            $_SESSION['booking_id'] = $booking_id;
            $_SESSION['destination_id'] = $destination_id;
            $_SESSION['departure_date'] = $departure_date;
            $_SESSION['return_date'] = $return_date;

            echo "<script>
                alert('Booking successful!');
                window.location.href = 'index.php#invoice';
            </script>";
        } else {
            echo "<script>
                alert('Booking failed. Please try again.');
            </script>";
        }
    }
}


// Fetch booking information for display
if (isset($_SESSION['booking_id'])) {
    $booking_id = $_SESSION['booking_id'];
    $stmt4 = $conn->prepare("SELECT * FROM booking WHERE booking_id = ?");
    $stmt4->bind_param("i", $booking_id);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
} else {
    // If no booking ID in session, get the user's most recent booking
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt4 = $conn->prepare("SELECT * FROM booking WHERE user_id = ? ORDER BY booking_id DESC LIMIT 1");
        $stmt4->bind_param("i", $user_id);
        $stmt4->execute();
        $result4 = $stmt4->get_result();
    }
}


$stmt5 = $conn->prepare("SELECT * FROM booking ORDER BY booking_id DESC LIMIT 1");
$stmt5->execute();
$result3 = $stmt5->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airlines Booking | JetSetGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>
    <!-- Header/Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="container">
                <!-- menu list icon -->
                <button class="navbar-toggler border-0" type="button" id="menuToggle" data-bs-toggle="tooltip" data-bs-placement="right" title="Open menu">
                    <i class="bi bi-list text-white fs-3"></i>
                </button>

                <!-- Logo -->
                <a class="navbar-brand" href="#" data-bs-toggle="tooltip" data-bs-placement="bottom" title="JetSetGo Home">
                    <img src="assets/images/whitelogo.png" class="me-2" style="width: 80px;">
                    <h3 class="text-white mb-0">JetSetGo</h3>
                </a>

                <!-- Login Button -->
                <?php if (!isset($_SESSION['logged_in'])) {
                    echo '<a href="login.php" class="btn btn-link text-decoration-none ms-auto d-block d-sm-none" data-bs-toggle="tooltip" data-bs-placement="left" title="Sign in">
                    <i class="bi bi-person text-white fs-3"></i>
                </a>';
                } else {
                    echo '<a href="profile.php" class="btn btn-link text-decoration-none ms-auto d-lg-none" data-bs-toggle="tooltip" data-bs-placement="left" title="Account">
                    <i class="bi bi-person text-white fs-3"></i>
                </a>';
                } ?>

                <!-- Desktop Navigation -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link text-white" href="#whyPh" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Explore destinations">Destinations</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="#wheretofly" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Special offers">Deals</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="#bookFlight" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Reserve your flight">Book</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="#flightStatus" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Check flight updates">Status</a></li>
                    </ul>
                    <?php if (!isset($_SESSION['logged_in'])) {
                        echo '<a href="login.php" class="btn btn-outline-light ms-3 d-none d-lg-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Sign in to your account">
                        <i class="bi bi-person me-2"></i> Sign In
                    </a>';
                    } else {
                        echo '<a href="profile.php" class="btn btn-outline-light ms-3 d-none d-lg-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Manage your account">
                        <i class="bi bi-person me-2"></i> Account
                    </a>';
                    } ?>
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation -->
        <div class="Navigations position-fixed top-0 start-0 h-100 bg-white shadow d-block d-lg-none" id="navMenu" style="width: 280px; z-index: 1100;">
            <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                <h3 class="text-white mb-0">JetSetGo</h3>
                <button class="btn p-0" id="Exbtn" data-bs-toggle="tooltip" data-bs-placement="left" title="Close menu"><i class="bi bi-x text-white fs-3"></i></button>
            </div>
            <ul class="list-unstyled p-3">
                <li class="mb-2">
                    <a href="#whyPh" class="text-decoration-none text-dark d-block py-2 px-3 rounded" data-bs-toggle="tooltip" data-bs-placement="right" title="Discover places">Destinations</a>
                </li>
                <li class="mb-2">
                    <a href="#wheretofly" class="text-decoration-none text-dark d-block py-2 px-3 rounded" data-bs-toggle="tooltip" data-bs-placement="right" title="Special offers">Deals</a>
                </li>
                <li class="mb-2">
                    <a href="#bookFlight" class="text-decoration-none text-dark d-block py-2 px-3 rounded" data-bs-toggle="tooltip" data-bs-placement="right" title="Reserve flights">Book Flight</a>
                </li>
                <li class="mb-2">
                    <a href="#flightStatus" class="text-decoration-none text-dark d-block py-2 px-3 rounded" data-bs-toggle="tooltip" data-bs-placement="right" title="Flight updates">Flight Status</a>
                </li>
                <?php if (!isset($_SESSION['logged_in'])) {
                    echo '<li class="mt-4 pt-3 border-top">
                    <a href="login.php" class="btn btn-primary w-100 py-2" data-bs-toggle="tooltip" data-bs-placement="right" title="Access your account">
                        <i class="bi bi-person me-2"></i> Sign In
                    </a>
                </li>';
                } else {
                    echo '<li class="mt-4 pt-3 border-top">
                    <a href="profile.php" class="btn btn-primary w-100 py-2" data-bs-toggle="tooltip" data-bs-placement="right" title="View your account">
                        <i class="bi bi-person me-2"></i> Account
                    </a>
                </li>';
                } ?>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-center text-lg-start">
                        <h1 class="hero-title">Explore the Beauty of the Philippines</h1>
                        <p class="hero-subtitle">Discover amazing destinations with our exclusive flight deals and promotions</p>
                        <a href="#bookFlight" class="btn btn-hero" data-bs-toggle="tooltip" data-bs-placement="top" title="Start your journey">Book Your Flight</a>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <a href="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0d/e7/b0/ea/photo0jpg.jpg?w=2200&h=800&s=1" data-bs-toggle="tooltip" data-bs-placement="left" title="View Palawan destinations"><img src="assets/images/palawan.jpg" class="img-fluid rounded-3 shadow-lg" alt="Travel Philippines"></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Philippines Section/discover the ph -->
        <section id="whyPh" class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-5 mb-3">Discover the Philippines</h2>
                <p class="lead text-muted">Explore our country's stunning islands, vibrant culture, and breathtaking landscapes</p>
            </div>

            <div class="row g-4">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="col-md-4">
                        <div class="gallery-item">
                            <a href="<?php echo $row['image_link']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View <?php echo $row['destination_name']; ?>"><img src="assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['destination_name']; ?>"></a>
                            <div class="image-overlay">
                                <h5 class="mb-0"><?php echo $row['destination_name']; ?> - <?php echo $row['destination_place']; ?></h5>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!-- Popular Destinations Section -->
        <section id="wheretofly" class="py-5" style="background-color: #f8f9fa;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold display-5 mb-3">Popular Destinations</h2>
                    <p class="lead text-muted">Find your next adventure with our most booked flights</p>
                </div>
                <form action="" method="GET">
                    <div class="row g-4">
                        <?php while ($row = $result1->fetch_assoc()) { ?>
                            <div class="col-md-4">
                                <div class="destination-card card h-100">
                                    <div class="position-relative">
                                        <img src="assets/images/<?php echo $row['image']; ?>"
                                            class="card-img-top" alt="<?php echo $row['destination_place']; ?>">
                                        <span class="price-badge">From ₱<?php echo $row['destination_price']; ?></span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row['destination_place']; ?></h5>
                                        <p class="card-text text-muted"><?php echo $row['destination_description']; ?></p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <a href="#bookFlight"
                                            class="btn btn-sm btn-primary w-100 book-now-btn"
                                            data-destination-id="<?php echo trim($row['destination_id']); ?>" style="background-color: #715BF4; border-radius:30px;">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </form>

            </div>
        </section>

        <!-- Book Flight Section -->



        <section id="bookFlight" class="container py-5">

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php
                    $form_error = $_SESSION['form_error'] ?? '';
                    $form_data = $_SESSION['form_data'] ?? [];
                    unset($_SESSION['form_error'], $_SESSION['form_data']);
                    ?>
                    <?php if (!empty($form_error)): ?>
                        <div class="container alert alert-danger text-center"><?php echo $form_error; ?></div>
                    <?php endif; ?>
                    <div class="booking-card card">
                        <div class="card-header text-center">
                            <h3 class="mb-0">Find Your Perfect Flight</h3>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="POST" id="bookingForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="origin" class="form-label">From:</label>
                                        <select class="form-select" id="origin" name="user_origin" required>
                                            <option selected disabled value="">Select Origin</option>
                                            <option value="Manila (MNL)">Manila (MNL)</option>
                                            <option value="Visayas (VYS)">Visayas (VYS)</option>
                                            <option value="Mindanao (MND)">Mindanao (DVO)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="destination" class="form-label">To:</label>
                                        <select class="form-select" id="destination" name="destination_id" required>
                                            <option selected disabled value="">Select Destination</option>
                                            <?php while ($row = $result2->fetch_assoc()) { ?>
                                                <option value="<?php echo trim($row['destination_id']); ?>"
                                                    data-price="<?php echo $row['destination_price']; ?>">
                                                    <?php echo $row['value']; ?> (₱<?php echo number_format($row['destination_price'], 2); ?>)
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <!-- Hidden fields to store destination name and price -->
                                        <input type="hidden" name="user_destination" id="user_destination">
                                        <input type="hidden" name="base_price" id="base_price">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="departure" class="form-label">Departure Date</label>
                                        <input type="date" class="form-control" id="departure" name="departure_date"
                                            value="<?php echo $form_data['departure_date'] ?? ''; ?>" required>

                                    </div>
                                    <div class="col-md-6">
                                        <label for="return" class="form-label">Return Date (Optional)</label>
                                        <input type="date" class="form-control" id="return" name="return_date"
                                            value="<?php echo $form_data['return_date'] ?? ''; ?>">

                                    </div>
                                    <div class="col-md-4">
                                        <label for="passengers" class="form-label">Passengers</label>
                                        <select class="form-select" id="passengers" name="passenger" required>
                                            <option value="1 Adult" data-multiplier="1">1 Adult</option>
                                            <option value="2 Adults" data-multiplier="2">2 Adults</option>
                                            <option value="1 Adult, 1 Child" data-multiplier="1.5">1 Adult, 1 Child</option>
                                            <option value="2 Adults, 1 Child" data-multiplier="2.5">2 Adults, 1 Child</option>
                                            <option value="Family (2+2)" data-multiplier="3">Family (2+2)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="class" class="form-label">Class</label>
                                        <select class="form-select" id="class" name="passenger_class" required>
                                            <option value="Economy" data-multiplier="1">Economy</option>
                                            <option value="Premium Economy" selected data-multiplier="1.2">Premium Economy</option>
                                            <option value="Business" data-multiplier="1.5">Business</option>
                                            <option value="First Class" data-multiplier="2">First Class</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="seat_number" class="form-label">Seat Selection</label>
                                        <select class="form-select" id="seat_number" name="seat_number" required>
                                            <option value="" selected disabled>Select Seat</option>
                                            <optgroup label="Window Seats">
                                                <option value="1A (Window)">1A (Window)</option>
                                                <option value="2A (Window)">2A (Window)</option>
                                                <option value="3A (Window)">3A (Window)</option>
                                                <option value="4A (Window)">4A (Window)</option>
                                            </optgroup>
                                            <optgroup label="Aisle Seats">
                                                <option value="1C (Aisle)">1C (Aisle)</option>
                                                <option value="2C (Aisle)">2C (Aisle)</option>
                                                <option value="3C (Aisle)">3C (Aisle)</option>
                                                <option value="4C (Aisle)">4C (Aisle)</option>
                                            </optgroup>
                                            <optgroup label="Middle Seats">
                                                <option value="1B (Middle)">1B (Middle)</option>
                                                <option value="2B (Middle)">2B (Middle)</option>
                                                <option value="3B (Middle)">3B (Middle)</option>
                                                <option value="4B (Middle)">4B (Middle)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <!-- Price preview section -->
                                    <div class="col-12 mt-3">
                                        <div class="card bg-light p-3">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="mb-0">Estimated Price:</h5>
                                                <h5 class="mb-0" id="estimatedPrice">₱0.00</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-3">
                                        <button type="submit" class="btn btn-primary px-5 py-2" name="book_btn">
                                            Book Flights <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Hidden field for final calculated price -->
                                <input type="hidden" name="calculated_price" id="calculated_price">
                            </form>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const bookButtons = document.querySelectorAll('.book-now-btn');
                                    const destinationSelect = document.getElementById('destination');
                                    const hiddenDestinationInput = document.getElementById('user_destination');
                                    const basePriceInput = document.getElementById('base_price');
                                    const estimatedPriceElement = document.getElementById('estimatedPrice');
                                    const calculatedPriceInput = document.getElementById('calculated_price');
                                    const bookingForm = document.getElementById('bookingForm');

                                    // Price calculation elements
                                    const passengerSelect = document.getElementById('passengers');
                                    const classSelect = document.getElementById('class');
                                    const returnDateInput = document.getElementById('return');

                                    // Function to calculate price
                                    function calculatePrice() {
                                        const destinationOption = destinationSelect.options[destinationSelect.selectedIndex];
                                        const basePrice = parseFloat(destinationOption.dataset.price) || 0;
                                        const passengerMultiplier = parseFloat(passengerSelect.options[passengerSelect.selectedIndex].dataset.multiplier) || 1;
                                        const classMultiplier = parseFloat(classSelect.options[classSelect.selectedIndex].dataset.multiplier) || 1;
                                        const isRoundTrip = returnDateInput.value !== '';

                                        let subtotal = basePrice * passengerMultiplier * classMultiplier;
                                        if (isRoundTrip) {
                                            subtotal *= 1.8; // Round trip is 1.8x of one-way (you can adjust this)
                                        }

                                        // Add taxes and fees (10%)
                                        const taxesAndFees = subtotal * 0.1;
                                        const total = subtotal + taxesAndFees;

                                        // Update display
                                        estimatedPriceElement.textContent = '₱' + total.toFixed(2);
                                        calculatedPriceInput.value = total.toFixed(2);
                                        basePriceInput.value = basePrice;
                                    }

                                    // Set up event listeners for price calculation
                                    destinationSelect.addEventListener('change', function() {
                                        const selectedText = this.options[this.selectedIndex].text.split(' (₱')[0];
                                        hiddenDestinationInput.value = selectedText;
                                        calculatePrice();
                                    });

                                    passengerSelect.addEventListener('change', calculatePrice);
                                    classSelect.addEventListener('change', calculatePrice);
                                    returnDateInput.addEventListener('change', calculatePrice);

                                    // Book now buttons functionality
                                    bookButtons.forEach(button => {
                                        button.addEventListener('click', function() {
                                            const destinationId = this.getAttribute('data-destination-id').trim();

                                            for (let i = 0; i < destinationSelect.options.length; i++) {
                                                if (destinationSelect.options[i].value.trim() === destinationId) {
                                                    destinationSelect.selectedIndex = i;
                                                    const selectedText = destinationSelect.options[i].text.split(' (₱')[0];
                                                    hiddenDestinationInput.value = selectedText;
                                                    basePriceInput.value = destinationSelect.options[i].dataset.price;
                                                    calculatePrice();
                                                    break;
                                                }
                                            }

                                            document.getElementById('bookFlight').scrollIntoView({
                                                behavior: 'smooth'
                                            });
                                        });
                                    });

                                    // Initial calculation
                                    calculatePrice();
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Invoice Section -->
        <section id="invoice" class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold display-5 mb-3">Booking Summary</h2>
                    <p class="lead text-muted">Review your flight details before payment</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Your Itinerary</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table invoice-table">
                                        <thead>
                                            <tr>
                                                <th>Flight</th>
                                                <th>Date & Time</th>
                                                <th>Passenger</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                            <?php
                                            if ($result4 && $result4->num_rows > 0) {
                                                $rows = $result4->fetch_assoc();
                                                $isRoundTrip = !empty($rows['return_date']);
                                                $pricePerFlight = $isRoundTrip ? $rows['price'] / 2 : $rows['price'];
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo $rows['user_origin'] ?> → <?php echo $rows['user_destination'] ?></strong><br>
                                                        <small class="text-muted">JetSetGo Flight #<?php echo $rows['booking_id'] ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo date("F j, Y", strtotime($rows['departure_date'])); ?><br>
                                                        <small class="text-muted"><?php echo date("h:i:s A", strtotime($rows['book_time'])); ?></small>
                                                    </td>
                                                    <td><?php echo $rows['passenger'] ?></td>
                                                    <td>₱<?php echo number_format($pricePerFlight, 2) ?></td>
                                                </tr>
                                                <?php if ($isRoundTrip) { ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo $rows['user_destination'] ?> → <?php echo $rows['user_origin'] ?></strong><br>
                                                            <small class="text-muted">JetSetGo Flight #<?php echo $rows['booking_id'] ?></small>
                                                        </td>
                                                        <td>
                                                            <?php echo date("F j, Y", strtotime($rows['return_date'])); ?><br>
                                                            <small class="text-muted"><?php echo date("h:i:s A", strtotime($rows['book_time'])); ?></small>
                                                        </td>
                                                        <td><?php echo $rows['passenger'] ?></td>
                                                        <td>₱<?php echo number_format($pricePerFlight, 2) ?></td>
                                                    </tr>
                                                <?php } ?>

                                                <tr class="table-light">
                                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                                    <td class="fw-bold">₱<?php echo number_format($rows['price'] * 0.9, 2) ?></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="3" class="text-end fw-bold">Taxes & Fees:</td>
                                                    <td class="fw-bold">₱<?php echo number_format($rows['price'] * 0.1, 2) ?></td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                                    <td class="fw-bold">₱<?php echo number_format($rows['price'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end">
                                                        <?php
                                                        $isUpcoming = strtotime($rows['departure_date']) > time();
                                                        $paymentUrl = "payment.php?booking_id=" . htmlspecialchars($rows['booking_id']);

                                                        if (!$isUpcoming) { ?>
                                                            <button class="btn btn-success px-4 mt-2" disabled>
                                                                <i class="bi bi-credit-card me-2"></i> This is overdue
                                                            </button>
                                                        <?php } elseif ($rows['status'] === "Paid" || $rows['status'] === "Cancelled") { ?>
                                                            <span data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="This booking is no longer available for payment">
                                                                <button class="btn btn-success px-4 mt-2" disabled>
                                                                    <i class="bi bi-credit-card me-2"></i> This is already paid or cancelled
                                                                </button>
                                                            </span>
                                                        <?php } else {  ?>
                                                            <a href="<?php echo $paymentUrl; ?>">
                                                                <button class="btn btn-success px-4 mt-2"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Complete your booking">
                                                                    <i class="bi bi-credit-card me-2"></i> Proceed to Payment
                                                                </button>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <p class="text-muted">No bookings found</p>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flight Status Section -->
        <section id="flightStatus" class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-5 mb-3">Check Flight Status</h2>
                <p class="lead text-muted">Get real-time updates on your flight</p>
            </div>
            <?php while ($row3 = $result3->fetch_assoc()) {
                $isUpcoming = strtotime($row3['departure_date']) > time(); ?>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="status-card card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="flight-status-result mt-4">
                                    <hr>
                                    <div class="alert alert-success border-0">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Flight JSG<?php echo $row3['booking_id']; ?> - On Time</h5>
                                            <span class="badge <?php echo $isUpcoming ? 'bg-success' : 'bg-success'; ?>">
                                                <?php echo $isUpcoming ? 'Scheduled' : 'Completed'; ?>
                                            </span>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="p-3 bg-light rounded">
                                                    <p class="mb-1"><strong><?php echo $row3['user_origin']; ?></strong></p>
                                                    <p class="mb-1"><small class="text-muted">Terminal 3</small></p>
                                                    <p class="mb-0">Scheduled: <strong><?php echo date("h:i:s A", strtotime($rows['book_time'])); ?></strong></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 bg-light rounded">
                                                    <p class="mb-1"><strong><?php echo $row3['user_destination']; ?></strong></p>
                                                    <p class="mb-1"><small class="text-muted">Terminal 1</small></p>
                                                    <p class="mb-0">Scheduled: <strong><?php echo date("h:i:s A", strtotime($row3['book_time'])); ?></strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($isUpcoming == 'Scheduled') {
                                            echo '<div class="progress mt-4 status-progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>';
                                        } else {
                                            echo '<div class="progress mt-4 status-progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>';
                                        } ?>

                                        <p class="mt-2 mb-0 text-center text-muted"><small>Flight is scheduled and on time</small></p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="assets/images/whitelogo.png"
                            style="width: 60px;" class="me-2">
                        <h4 class="mb-0">JetSetGo</h4>
                    </div>
                    <p class="text-white">Your trusted partner for affordable flights across the Philippines and beyond.
                    </p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#whyPh">Destinations</a></li>
                        <li><a href="#wheretofly">Deals</a></li>
                        <li><a href="#bookFlight">Book Flight</a></li>
                        <li><a href="#flightStatus">Flight Status</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-3"> Group 1</h5>
                    <ul class="list-unstyled footer-links ">
                        <li class="text-white">Janzel Dolo</li>
                        <li class="text-white">Janna Mendoza </li>
                        <li class="text-white">Iliw-Iliw, Maricar</li>
                        <li class="text-white">Jonathan Talib</li>
                        <li class="text-white">De Mesa, Chloe Mae</li>
                        <li class="text-white">Raphael Gomez</li>
                        <li class="text-white">Santoalla joshua</li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> 123 Aviation Road, BCP, Quezon City, Philippines
                        </li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> +63 123 456 7890</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> contact@jetsetgo.com</li>
                        <li class="mb-2"><i class="bi bi-clock me-2"></i> Mon-Fri: 8AM - 6PM</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="text-center  text-white">
                    <p class="mb-0 text-white">&copy; 2025 JetSetGo. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sia.js"></script>
</body>

</html>