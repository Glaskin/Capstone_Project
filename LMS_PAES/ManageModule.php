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

// Check if module_id is provided in the URL
$module_id = $_GET['module_id'] ?? null;
if (!$module_id || !is_numeric($module_id)) {
    die("A valid Module ID is required.");
}

// Fetch module details based strictly on the `module_id` in the URL
$stmt = $pdo->prepare("SELECT title FROM modules WHERE id = :module_id");
$stmt->execute(['module_id' => $module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    die("Module not found.");
}

// Handle form submission for adding a new lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addLesson'])) {
        $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
        $lessonContent = htmlspecialchars($_POST['lessonContent']);

        // Insert new lesson associated with the current module_id
        $stmt = $pdo->prepare("INSERT INTO lessons (module_id, title, content, type, position) 
                               VALUES (:module_id, :title, :content, 'lesson', 
                               (SELECT COALESCE(MAX(position), 0) + 1 FROM lessons WHERE module_id = :module_id))");
        $stmt->execute(['module_id' => $module_id, 'title' => $title, 'content' => $content]);

        header("Location: ManageModule.php?module_id=$module_id"); // Refresh to show the new lesson
        exit();
    }

    // Handle lesson deletion
    if (isset($_POST['deleteLesson'])) {
        $lesson_id = $_POST['lesson_id'];

        // Delete the lesson associated with the current module_id
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = :lesson_id AND module_id = :module_id");
        $stmt->execute(['lesson_id' => $lesson_id, 'module_id' => $module_id]);

        header("Location: ManageModule.php?module_id=$module_id"); // Refresh to reflect deletion
        exit();
    }
}

// Fetch lessons strictly associated with this specific module
$stmt = $pdo->prepare("SELECT id, title, content, type FROM lessons WHERE module_id = :module_id ORDER BY position ASC");
$stmt->execute(['module_id' => $module_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Module - <?php echo htmlspecialchars($module['title']); ?></title>
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
        <!-- Module Title -->
        <div class="text-center mb-5">
            <h1><?php echo htmlspecialchars($module['title']); ?></h1>
            <p>Manage lessons within this module.</p>
        </div>

        <!-- Lessons List -->
        <h2 class="text-center mb-4">Lessons</h2>
        <div class="list-group mb-5">
            <?php foreach ($lessons as $lesson): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <?php echo htmlspecialchars($lesson['title']); ?>
                    </span>
                    <div>
                        <a href="EditLesson.php?lesson_id=<?php echo $lesson['id']; ?>&module_id=<?php echo $module_id; ?>"
                            class="btn btn-primary">
                            Edit <?php echo htmlspecialchars($lesson['title']); ?>
                        </a>
                        <form method="POST" action="ManageModule.php?module_id=<?php echo $module_id; ?>"
                            style="display: inline;">
                            <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                            <button type="submit" name="deleteLesson" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Lesson Section -->
        <h2 class="text-center mb-4">Add New Lesson</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="ManageModule.php?module_id=<?php echo $module_id; ?>">
                    <div class="mb-3">
                        <label for="lessonTitle" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="lessonTitle" name="lessonTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="lessonContent" class="form-label">Lesson Content</label>
                        <textarea class="form-control" id="lessonContent" name="lessonContent" rows="4"
                            required></textarea>
                    </div>
                    <button type="submit" name="addLesson" class="btn btn-success">Add Lesson</button>
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