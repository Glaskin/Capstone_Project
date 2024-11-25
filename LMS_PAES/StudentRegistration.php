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
    $name = trim($_POST['studentName']);
    $email = trim($_POST['studentEmail']);
    $password = $_POST['studentPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Define the minimum password length
$minPasswordLength = 8;

// Validate form inputs
if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
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
            $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'catapangcezarjr@gmail.com '; // SMTP username
            $mail->Password   = 'REMOVED-SECRET'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('catapangcezarjr@gmail.com', 'Your Name');
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body    = "Please click the following link to verify your email: <a href='$verificationLink'>Verify Email</a>";
            $mail->AltBody = "Please click the following link to verify your email: $verificationLink";

            // Send the email
            $mail->send();

            // Store the user temporarily in a separate table or session
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $stmt = $pdo->prepare("INSERT INTO temp_users (username, password, email, verification_token) VALUES (:name, :password, :email, :token)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password', $hashedPassword); // Bind the hashed password
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token); // Bind the token

            if ($stmt->execute()) {
                echo "Registration successful! Please check your email to verify your account.";
                header("Location: LandingPage.php");
                 exit();
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
    <title>Student Registration</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/login/registration.css" />
</head>

<body>
    <div class="container-fluid">
        <form class="mx-auto" id="registrationForm" method="POST" action="StudentRegistration.php" style="max-width: 400px;">
            <h4 class="text-center mb-4">Registration</h4>
            
            <?php
            // Display error message if there is one
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
            }
            ?>
            
            <div class="mb-3 mt-4">
                <label for="studentName" class="form-label">Name</label>
                <input type="text" class="form-control" id="studentName" name="studentName" required />
            </div>
            <div class="mb-3">
                <label for="studentEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="studentEmail" name="studentEmail" required />
            </div>
            <div class="mb-3">
                <label for="studentPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="studentPassword" name="studentPassword" required />
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required />
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>