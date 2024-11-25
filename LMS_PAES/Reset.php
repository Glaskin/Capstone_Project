<?php
session_start();

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
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];

    // Validate the token
    $stmt = $pdo->prepare("SELECT id, reset_token_created_at FROM users WHERE reset_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the token is still valid (30 minutes)
        $token_created_at = new DateTime($user['reset_token_created_at']);
        $current_time = new DateTime();
        $interval = $current_time->diff($token_created_at);

        if ($interval->i >= 30) {
            $error_message = "This reset token has expired. Please request a new password reset.";
        } else {
            // Check if passwords match
            if ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match.";
            } else {
                // Update the password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_created_at = NULL WHERE id = :id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                $success_message = "Your password has been reset successfully.";
            }
        }
    } else {
        $error_message = "Invalid token.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/login/login.css" />
</head>

<body>
    <div class="container-fluid">
        <form class="mx-auto" method="POST" action="Reset.php" style="max-width: 400px;">
            <h4 class="text-center">Reset Password</h4>

            <?php
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger" role=" alert">' . htmlspecialchars($error_message) . '</div>';
            }
            if (!empty($success_message)) {
                echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
            }
            ?>

            <div class="mb-3 mt-5">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" id="new_password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            </div>

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <button type="submit" class="btn btn-primary mt-4">Reset Password</button>
            <hr class="mt-4 mb-3" />
            <button type="button" onclick="window.location.href='LandingPage.php'" class="btn btn-secondary w-100">Back to Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>