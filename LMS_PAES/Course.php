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

// Get course_id from URL
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
$stmt = $pdo->prepare("SELECT id, title FROM modules WHERE course_id = :course_id ORDER BY position ASC");
$stmt->execute(['course_id' => $course_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($course['title']); ?> - Syllabus</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Style.css">
    <link rel="stylesheet" href="css/Dashboard.css">
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
        <!-- Course Header -->
        <div class="text-center mb-5">
            <h2><?php echo htmlspecialchars($course['title']); ?></h2>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>

        <!-- Modules and Syllabus -->
        <div class="accordion" id="syllabusAccordion">
            <?php foreach ($modules as $index => $module): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false"
                                aria-controls="collapse<?php echo $index; ?>">
                            <?php echo htmlspecialchars($module['title']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse"
                         aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#syllabusAccordion">
                        <div class="accordion-body">
                            <p>This module covers the following topics:</p>
                            <ul class="list-group">
                                <li class="list-group-item">Overview of Module Content</li>
                                <li class="list-group-item">Key Concepts</li>
                                <li class="list-group-item">Practical Applications</li>
                                <li class="list-group-item">
                                    <a href="module.php?module_id=<?php echo $module['id']; ?>" class="btn btn-primary">
                                        View Lessons
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-warning text-center py-3 mt-5">
        <p>&copy; 2024 ELMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
