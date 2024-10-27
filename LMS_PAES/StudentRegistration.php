<?php
// Start a session to manage user data if needed
session_start();

// Placeholder for handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentName = htmlspecialchars($_POST['studentName']);
    $studentEmail = htmlspecialchars($_POST['studentEmail']);
    $studentPassword = htmlspecialchars($_POST['studentPassword']);
    $confirmPassword = htmlspecialchars($_POST['confirmPassword']);

    // Basic validation
    if ($studentPassword !== $confirmPassword) {
        $error_message = 'Passwords do not match!';
    } elseif (empty($studentName) || empty($studentEmail) || empty($studentPassword)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Simulate saving the student data (e.g., to a database)
        // Here, you would typically save the data to a database
        $_SESSION['registered_student'] = [
            'name' => $studentName,
            'email' => $studentEmail,
            'password' => password_hash($studentPassword, PASSWORD_BCRYPT) // Save hashed password for security
        ];
        
        // Redirect to login page or success page
        header("Location: LandingPage.php");
        exit;
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
            if (isset($error_message)) {
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
