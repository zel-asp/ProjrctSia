<?php
include("server/connection.php");
session_start();

$error = '';

if (isset($_POST['login_btn'])) {
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];


    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {

        $stmt = $conn->prepare("SELECT user_id, user_firstName, user_lastName, user_email, user_password FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();


            if (password_verify($user_password, $user['user_password'])) {

                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_fname'] = $user['user_firstName'];
                $_SESSION['user_lname'] = $user['user_lastName'];
                $_SESSION['user_email'] = $user['user_email'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
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
                <i class="bi bi-x text-primary"></i>
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
            <form action="" method="POST">
                <div class="tab-content" id="authTabContent">
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <div class="auth-body">
                            <div class="form-floating mb-3">

                                <input type="text" class="form-control" id="loginUsername" placeholder="User email" name="user_email" required data-bs-toggle="tooltip" data-bs-placement="right" title="Enter your registered email address">
                                <label for="loginUsername"> <span class="bi bi-person input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your registered email"></span> Email</label>
                            </div>

                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" id="loginPassword" placeholder="Password" name="user_password" required data-bs-toggle="tooltip" data-bs-placement="right" title="Enter your account password">
                                <label for="loginPassword" class="ml-5"><span class="bi bi-lock input-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Your account password"></span> Password</label>
                                <i class="bi bi-eye password-toggle" id="toggleLoginPassword" data-bs-toggle="tooltip" data-bs-placement="top" title="Show/hide password"></i>
                            </div>

                            <button type="submit" class="btn btn-auth w-100 mb-3 text-white" name="login_btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Sign in to your account">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login
                            </button>

                            <div class="auth-footer">
                                Don't have an account? <a href="signup.php" class="switch-tab" data-bs-toggle="tooltip" data-bs-placement="top" title="Create a new account">Sign up now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login_sigup.js"></script>
</body>

</html>