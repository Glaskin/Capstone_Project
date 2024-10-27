<?php
// Start a session to manage user login status
session_start();

// If the form is submitted, handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Replace these with actual credentials or database check
    $sampleEmail = "user@example.com";
    $samplePass = "password123";

    // Retrieve email and password from form submission
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate credentials
    if ($email === $sampleEmail && $password === $samplePass) {
        // Set session variable to indicate user is logged in
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;

        // Redirect to dashboard
        header("Location: Dashboard.php");
        exit;
    } else {
        $error_message = "Incorrect email or password";
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
        <form class="mx-auto" method="POST" action="LandingPage.php">
            <h4 class="text-center">Login</h4>

            <?php
            if (isset($error_message)) {
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
            <button type="button" onclick="window.location.href='StudentRegistration.html'" class="btn btn-secondary w-100">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
