<?php
// Start a session and connect to the database
session_start();

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

// Ensure only faculty can access this page
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || $user_role !== 'faculty') {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a faculty member
    exit();
}

// Fetch faculty's username for the welcome message
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id AND role = 'faculty'");
$stmt->execute(['user_id' => $user_id]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faculty) {
    die("Faculty member not found.");
}

// Fetch courses managed by this faculty member
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.description 
    FROM courses c
    JOIN faculty_courses fc ON c.id = fc.course_id
    WHERE fc.faculty_id = :faculty_id
");
$stmt->execute(['faculty_id' => $user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/Style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="FacultyDashboard.php">ELMS - Faculty</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="FacultyDashboard.php" class="nav-link active">Home</a></li>
                    <li class="nav-item"><a href="FacultyProfile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="margin-top: 100px;">
        <!-- Welcome Section -->
        <div class="text-center mb-5">
            <h1>Welcome, <?php echo htmlspecialchars($faculty['username']); ?>!</h1>
            <p>Manage your courses and announcements here.</p>
        </div>

        <!-- Courses Management Section -->
        <h2 class="text-center mb-4">Your Courses</h2>
        <div class="list-group mb-5">
            <?php foreach ($courses as $course): ?>
                <a href="ManageCourse.php?course_id=<?php echo $course['id']; ?>"
                    class="list-group-item list-group-item-action">
                    <h5 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                    <p class="mb-1"><?php echo htmlspecialchars($course['description']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
