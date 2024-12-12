<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Database connection setup
$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize error message variable
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $name = trim($_POST['username']);
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['studentEmail']);
    $password = $_POST['studentPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Define the minimum password length
    $minPasswordLength = 8;

    // Validate form inputs
    if (empty($name) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < $minPasswordLength) {
        $error_message = "Password must be at least $minPasswordLength characters long.";
    } elseif ($password !== $confirmPassword) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email or username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $name);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error_message = "Email or username is already registered.";
        } else {
            // Generate a verification token
            $token = bin2hex(random_bytes(16)); // Create a random token

            // Send verification email
            $verificationLink = "http://localhost/Capstone_Project/LMS_PAES/verify.php?token=" . $token; // Adjust to your actual path

            // Send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Set the SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'catapangcezarjr@gmail.com '; // SMTP username
                $mail->Password = 'REMOVED-SECRET'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('catapangcezarjr@gmail.com', 'Your Name');
                $mail->addAddress($email); // Add a recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Email Verification';
                $mail->Body = "Please click the following link to verify your email: <a href='$verificationLink'>Verify Email</a>";
                $mail->AltBody = "Please click the following link to verify your email: $verificationLink";

                // Send the email
                $mail->send();

                // Store the user temporarily in a separate table or session
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
                $stmt = $pdo->prepare("INSERT INTO temp_users (username, password, email, verification_token, firstName, LastName) VALUES (:name, :password, :email, :token, :firstname, :lastname)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':password', $hashedPassword); // Bind the hashed password
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':token', $token); // Bind the token
                $stmt->bindParam(':firstname', $firstName);
                $stmt->bindParam(':lastname', $lastName);

                if ($stmt->execute()) {
                    // Set success message
                    $success_message = "Registration successful! A verification email has been sent to your email address.";
                    header("refresh:5;url=LandingPage.php"); // Redirect after 5 seconds
                } else {
                    $error_message = "Failed to store temporary user data. Please try again.";
                }
            } catch (Exception $e) {
                $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">

    <title>PAES REVIEW : Register</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/icon.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style2.css" />
    <link rel="stylesheet" href="css/login/registration.css" />
</head>

<body>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-warning navbar-light shadow sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <p class="m-0 fw-bold" style="font-size: 25px;"><img src="img/icon.png" alt="" height="50px">PAES<span
                    style="color: #000000;"> REVIEW</span></p>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="#" class="nav-item nav-link">
                    <div id="google_translate_element">
                    </div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <div class="container-fluid">
        <form class="mx-auto" id="registrationForm" method="POST" action="StudentRegistration.php"
            style="max-width: 400px;">
            <h4 class="text-center mb-4">Registration</h4>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php elseif (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required />
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName"
                        value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>" required />
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName"
                        value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>" required />
                </div>
            </div>
            <div class="mb-3">
                <label for="studentEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="studentEmail" name="studentEmail"
                    value="<?= htmlspecialchars($_POST['studentEmail'] ?? '') ?>" required />
            </div>
            <div class="mb-3">
                <label for="studentPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="studentPassword" class="form-control" id="studentPassword" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleStudentPassword">
                        <i class="bi bi-eye" id="toggleStudentIcon"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                        <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
            <p class="text-center mt-3">Already have an account? <a href="LandingPage.php">Login</a></p>
        </form>
    </div>
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="js/login/registration.js"></script>
</body>

</html>