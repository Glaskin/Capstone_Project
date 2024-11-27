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

// Fetch lesson title and content from lessons table
$stmt = $pdo->prepare("SELECT title, content, module_id FROM lessons WHERE id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Lesson not found.");
}

// Fetch module and course information for breadcrumb navigation
$stmt = $pdo->prepare("
    SELECT modules.title AS module_title, modules.course_id, courses.title AS course_title 
    FROM modules 
    JOIN courses ON modules.course_id = courses.id 
    WHERE modules.id = :module_id
");
$stmt->execute(['module_id' => $lesson['module_id']]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the previous and next lessons based on the current lesson's position
$stmt = $pdo->prepare("SELECT id, title, position FROM lessons WHERE module_id = :module_id AND position < (SELECT position FROM lessons WHERE id = :lesson_id) ORDER BY position DESC LIMIT 1");
$stmt->execute(['module_id' => $lesson['module_id'], 'lesson_id' => $lesson_id]);
$previous_lesson = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, title, position FROM lessons WHERE module_id = :module_id AND position > (SELECT position FROM lessons WHERE id = :lesson_id) LIMIT 1");
$stmt->execute(['module_id' => $lesson['module_id'], 'lesson_id' => $lesson_id]);
$next_lesson = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Lesson</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="Dashboard.php">PAES REVIEWER</a>
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
            <div class="card -body">
                <h2 class="card-title"> <?php echo htmlspecialchars($lesson['title']); ?></h2>
                <div class="card-text">
                    <?php echo $lesson['content']; // Display the content directly as HTML ?>
                </div>
            </div>
        </div>

        <!-- Navigation links -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <?php if ($previous_lesson): ?>
                <a href="Lessons.php?lesson_id=<?php echo $previous_lesson['id']; ?>" class="btn btn-secondary">Previous Lesson</a>
            <?php endif; ?>

            <a href="Module.php?module_id=<?php echo $lesson['module_id']; ?>" class="btn btn-primary">Return to Module</a>

            <?php if ($next_lesson): ?>
                <a href="Lessons.php?lesson_id=<?php echo $next_lesson['id']; ?>" class="btn btn-secondary">Next Lesson</a>
            <?php endif; ?>
        </div>

        
    </div>
    <footer class="footer text-center mt-5">
        <p>&copy; 2024 ELMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>