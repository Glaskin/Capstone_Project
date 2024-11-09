<?php
// Database connection setup
$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pogi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize error message variable
$error_message = '';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate form inputs
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Fetch user data
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and password is correct (plaintext comparison for testing)
        if ($user && $user['password'] === $password) {
            // Store user information in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: AdminDashboard.php");
            } elseif ($user['role'] === 'faculty') {
                header("Location: FacultyDashboard.php");
            } else {
                header("Location: Dashboard.php"); // Redirect students to the student dashboard
            }
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Welcome</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/login/login.css" />
</head>

<body>
    <div class="container-fluid">
        <form class="mx-auto" method="POST" action="LandingPage.php" style="max-width: 400px;">
            <h4 class="text-center">Login</h4>

            <?php
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>

            <div class="mb-3 mt-5">
                <label for="inputEmail" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" id="inputEmail" required>
            </div>

            <div class="mb-3">
                <label for="inputPassword" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="inputPassword" required>
                <div id="passwordHelp" class="form-text"><a href="#">Forget password?</a></div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Login</button>
            <hr class="mt-4 mb-3" />
            <button type="button" onclick="window.location.href='StudentRegistration.php'" class="btn btn-secondary w-100">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
