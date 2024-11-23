<?php
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

    // Validate form inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
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
            // Insert new student into users table
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (:name, :password, :email, 'student')");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password', $password);  // Store plaintext password for testing
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                // Get the last inserted user ID
                $userId = $pdo->lastInsertId();

                // Fetch all available courses
                $coursesStmt = $pdo->prepare("SELECT id FROM courses");
                $coursesStmt->execute();
                $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

                // Prepare to insert enrollments
$enrollmentStmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)");

// Enroll the user in each course
foreach ($courses as $course) {
    $courseId = $course['id'];
    $enrollmentStmt->bindParam(':student_id', $userId); // Correctly bind the user ID
    $enrollmentStmt->bindParam(':course_id', $courseId);
    
    // Execute the enrollment for the current course
    $enrollmentStmt->execute();
}
                // Redirect to login or dashboard
                header("Location: LandingPage.php");
                exit();
            } else {
                $error_message = "Registration failed. Please try again.";
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