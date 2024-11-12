<?php
// Start session and connect to the database
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

// Get course ID from URL
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    die("Course ID is required.");
}

// Fetch course details
$stmt = $pdo->prepare("SELECT title, description FROM courses WHERE id = :course_id");
$stmt->execute(['course_id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found.");
}

// Fetch modules for this course
$stmt = $pdo->prepare("SELECT id, title, position FROM modules WHERE course_id = :course_id ORDER BY position ASC");
$stmt->execute(['course_id' => $course_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions for adding or deleting modules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addModule'])) {
        $moduleTitle = htmlspecialchars($_POST['moduleTitle']);
        $modulePosition = htmlspecialchars($_POST['modulePosition']);
        
        $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description) VALUES (:course_id, :title, :description)");
        $stmt->execute(['course_id' => $course_id, 'title' => $title, 'position' => $description]);
        
        header("Location: ManageCourse.php?course_id=" . $course_id); // Refresh to show the new module
        exit();
    } elseif (isset($_POST['deleteModule'])) {
        $module_id = $_POST['module_id'];
        
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = :module_id");
        $stmt->execute(['module_id' => $module_id]);
        
        header("Location: ManageCourse.php?course_id=" . $course_id); // Refresh to show the updated module list
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Course - <?php echo htmlspecialchars($course['title']); ?></title>
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
                    <li class="nav-item"><a href="FacultyDashboard.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="FacultyProfile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="margin-top: 100px;">
        <!-- Course Details -->
        <div class="text-center mb-5">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>

        <!-- Modules List -->
        <h2 class="text-center mb-4">Modules</h2>
        <div class="list-group mb-5">
            <?php foreach ($modules as $module): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <?php echo htmlspecialchars($module['title']); ?> (Position: <?php echo $module['position']; ?>)
                    </span>
                    <div>
                        <a href="ManageModule.php?module_id=<?php echo $module['id']; ?>" class="btn btn-primary btn-sm">Manage</a>
                        <form method="POST" action="ManageCourse.php?course_id=<?php echo $course_id; ?>" style="display: inline;">
                            <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                            <button type="submit" name="deleteModule" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Module Section -->
        <h2 class="text-center mb-4">Add New Module</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="ManageCourse.php?course_id=<?php echo $course_id; ?>">
                    <div class="mb-3">
                        <label for="moduleTitle" class="form-label">Module Title</label>
                        <input type="text" class="form-control" id="moduleTitle" name="moduleTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="modulePosition" class="form-label">Position</label>
                        <input type="number" class="form-control" id="modulePosition" name="modulePosition" required>
                    </div>
                    <button type="submit" name="addModule" class="btn btn-success">Add Module</button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
