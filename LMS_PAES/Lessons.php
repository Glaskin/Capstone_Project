<?php
// Start session and connect to the database
session_start();

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

// Get lesson_id from URL
$lesson_id = $_GET['lesson_id'] ?? null;
if (!$lesson_id) {
    die("Lesson ID is required.");
}

// Fetch lesson title and type from lessons table
$stmt = $pdo->prepare("SELECT title, type, module_id FROM lessons WHERE id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Lesson not found.");
}

// Fetch detailed content from lesson_content table
$stmt = $pdo->prepare("SELECT content, media_url FROM lesson_content WHERE lesson_id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch module and course information for breadcrumb navigation
$stmt = $pdo->prepare("
    SELECT modules.title AS module_title, modules.course_id, courses.title AS course_title 
    FROM modules 
    JOIN courses ON modules.course_id = courses.id 
    WHERE modules.id = :module_id
");
$stmt->execute(['module_id' => $lesson['module_id']]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Lesson</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="Dashboard.php">ELMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="Dashboard.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="Profile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="course.php?course_id=<?php echo $module['course_id']; ?>">
                    <?php echo htmlspecialchars($module['course_title']); ?>
                </a></li>
                <li class="breadcrumb-item"><a href="module.php?module_id=<?php echo $lesson['module_id']; ?>">
                    <?php echo htmlspecialchars($module['module_title']); ?>
                </a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($lesson['title']); ?></li>
            </ol>
        </nav>

        <!-- Lesson Content -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h2>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($content['content'])); ?></p>
                <?php if (!empty($content['media_url'])): ?>
                    <div class="mt-4">
                        
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-4">
            <a href="module.php?module_id=<?php echo $lesson['module_id']; ?>" class="btn btn-secondary">
                Back to Module
            </a>
            <a href="course.php?course_id=<?php echo $module['course_id']; ?>" class="btn btn-secondary">
                Back to Course
            </a>
        </div>
    </div>

    <footer class="bg-warning text-center py-3 mt-5">
        <p>&copy; 2024 ELMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
