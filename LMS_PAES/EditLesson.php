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

// Check if lesson_id is provided in the URL
$lesson_id = $_GET['lesson_id'] ?? null;
if (!$lesson_id) {
    die("Lesson ID is required.");
}

// Fetch the lesson data
$stmt = $pdo->prepare("SELECT title, content FROM lessons WHERE id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Lesson not found.");
}

// Handle form submission to update lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
    $lessonContent = htmlspecialchars($_POST['lessonContent']);
    
    $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content WHERE id = :lesson_id");
    $stmt->execute(['title' => $lessonTitle, 'content' => $lessonContent, 'lesson_id' => $lesson_id]);
    
    // Redirect back to ManageModule.php after updating
    header("Location: ManageModule.php?module_id=" . $_GET['module_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Lesson - <?php echo htmlspecialchars($lesson['title']); ?></title>
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
        <!-- Edit Lesson Form -->
        <h2 class="text-center mb-4">Edit Lesson</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>&module_id=<?php echo $_GET['module_id']; ?>">
                    <div class="mb-3">
                        <label for="lessonTitle" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="lessonTitle" name="lessonTitle" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lessonContent" class="form-label">Lesson Content</label>
                        <textarea class="form-control" id="lessonContent" name="lessonContent" rows="6" required><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="ManageModule.php?module_id=<?php echo $_GET['module_id']; ?>" class="btn btn-secondary">Cancel</a>
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
