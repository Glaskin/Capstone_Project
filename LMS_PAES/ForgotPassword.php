<?php
// Include PHPMailer
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


$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error_message = "Email is required.";
    } else {
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(50));
            $created_at = date('Y-m-d H:i:s'); // Get current date and time
    
            // Update users table with the token and created_at timestamp
            $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_created_at = :created_at WHERE email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Send email with reset link
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
                $mail->Subject = 'Password Reset Request';
                $mail->Body = 'Click on this link to reset your password: <a href="http://localhost/Capstone_Project/LMS_PAES/Reset.php?token=' . $token . '">Reset Password</a>';

                $mail->send();
                $success_message = "Password reset link has been sent to your email.";
            } catch (Exception $e) {
                $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Email not found.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/login/login.css" />
</head>

<body>
    <div class="container-fluid">
        <form class="mx-auto" method="POST" action="ForgotPassword.php" style="max-width: 400px;">
            <h4 class="text-center">Forgot Password</h4>

            <?php
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
            }
            if (!empty($success_message)) {
                echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
            }
            ?>

            <div class="mb-3 mt-5">
                <label for="inputEmail" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" id="inputEmail" required>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Send Reset Link</button>
            <hr class="mt-4 mb-3" />
            <button type="button" onclick="window.location.href='LandingPage.php'" class="btn btn-secondary w-100">Back to Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>