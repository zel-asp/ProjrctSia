<?php

include("server/connection.php");
session_start();

$error = '';
$success = '';

if (isset($_POST['create_btn'])) {
    $user_fname = $_POST['user_fname'];
    $user_lname = $_POST['user_lname'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];
    $confirm_password = $_POST['confirm_password'];
    $hash_password = password_hash($user_password, PASSWORD_DEFAULT);

    // Validate email format
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($user_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if user already exists
        $check = $conn->prepare("SELECT user_email FROM users WHERE user_email = ?");
        $check->bind_param("s", $user_email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo '<script>alert("this user is alraedy registered");</script>';
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (user_firstName, user_lastName, user_email, user_password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $user_fname, $user_lname, $user_email, $hash_password);

            if ($stmt->execute()) {

                $user_id  = $stmt->insert_id;

                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_fname'] = $user_fname;
                $_SESSION['user_lname'] = $user_lname;
                $_SESSION['user_email'] = $user_email;
                $_SESSION['user_password'] = $hash_password;

                echo "<script>
                alert('Registered successful');
                window.location.href='index.php';
            </script>";
                exit();

                header("Location:index.php");
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login & Register | JetSetGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/login_register.css" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <button class="btn-close-top" onclick="window.location.href='index.php'" data-bs-toggle="tooltip" data-bs-placement="left" title="Return to homepage">
                <i class="bi bi-x"></i>
            </button>

            <div class="auth-header">
                <img class="auth-logo" src="assets/images/logo black.png" alt="JetSetGo Logo" />
                <h3 class="auth-title">Welcome to JetSetGo</h3>
                <p class="auth-subtitle">Sign in or create an account to continue</p>
            </div>


            <?php if (!empty($error)): ?>
                <div class="d-flex justify-content-center align-items-center text-center p-3 mb-3 mx-5 rounded shadow-sm bg-danger text-white animate__animated animate__fadeIn">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="tab-content d-block" id="authTabContent">

                <!-- Registration Form -->
                <div class="tab-pane fade show active" id="register" role="tabpanel">

                    <div class="auth-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">

                                        <input type="text" class="form-control" id="firstName" placeholder="Full name" name="user_fname" required data-bs-toggle="tooltip" data-bs-placement="right" title="Enter your first name">
                                        <label for="firstName"><span class="bi bi-person input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your first name"></span>First Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">

                                        <input type="text" class="form-control" id="lastName" placeholder="Last Name" name="user_lname" required data-bs-toggle="tooltip" data-bs-placement="right" title="Enter your last name">
                                        <label for="lastName"><span class="bi bi-person input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your last name"></span> Last Name</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-3">

                                <input type="email" class="form-control" id="registerEmail" placeholder="Email Address" name="user_email" required data-bs-toggle="tooltip" data-bs-placement="right" title="Enter a valid email address">
                                <label for="registerEmail"><span class="bi bi-envelope input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your email address"></span> Email Address</label>
                            </div>

                            <div class="form-floating mb-3 position-relative">

                                <input type="password" class="form-control" id="registerPassword" placeholder="Password" name="user_password" required data-bs-toggle="tooltip" data-bs-placement="right" title="Create a strong password">
                                <label for="registerPassword"><span class="bi bi-lock input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your password"></span>Password</label>
                                <i class="bi bi-eye password-toggle" id="toggleRegisterPassword" data-bs-toggle="tooltip" data-bs-placement="top" title="Show/hide password"></i>
                            </div>

                            <div class="form-floating mb-3 position-relative">

                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" name="confirm_password" required data-bs-toggle="tooltip" data-bs-placement="right" title="Re-enter your password">
                                <label for="confirmPassword"> <span class="bi bi-lock-fill input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Confirm your password"></span> Confirm Password</label>
                                <i class="bi bi-eye password-toggle" id="toggleConfirmPassword" data-bs-toggle="tooltip" data-bs-placement="top" title="Show/hide password"></i>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="termsAgree" required data-bs-toggle="tooltip" data-bs-placement="right" title="You must agree to the terms">
                                <label class="form-check-label" for="termsAgree">
                                    I agree to the <a href="#" class="text-white text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Read our terms and conditions">Terms and Conditions</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-auth w-100 mb-3 text-white" name="create_btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Create your account">
                                <i class="bi bi-person-plus me-2"></i> Create Account
                            </button>

                            <div class="auth-footer">
                                Already have an account? <a href="login.php" class="switch-tab" data-tab="login" data-bs-toggle="tooltip" data-bs-placement="top" title="Switch to login form">Sign in here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login_sigup.js"></script>
</body>

</html>