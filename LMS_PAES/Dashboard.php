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

// Check if the user is logged in and has the student role
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || $user_role !== 'student') {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a student
    exit();
}

// Fetch user details based on the logged-in user
$user = [];
if ($user_id) {
    // Fetch user profile
    $stmt = $pdo->prepare("SELECT username AS name FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch recent activity (last completed lesson)
    $stmt = $pdo->prepare("SELECT lessons.title FROM lessons
                           JOIN student_progress ON lessons.id = student_progress.lesson_id
                           WHERE student_progress.student_id = :user_id AND student_progress.status = 'completed'
                           ORDER BY student_progress.last_accessed DESC LIMIT 1");
    $stmt->execute(['user_id' => $user_id]);
    $user['recentActivity'] = $stmt->fetchColumn() ?: 'No recent activity';

    // Fetch next lesson
    $stmt = $pdo->prepare("SELECT title FROM lessons
                           WHERE course_id IN (SELECT course_id FROM enrollments WHERE student_id = :user_id)
                           AND id NOT IN (SELECT lesson_id FROM student_progress WHERE student_id = :user_id)
                           ORDER BY id ASC LIMIT 1");
    $stmt->execute(['user_id' => $user_id]);
    $user['nextLesson'] = $stmt->fetchColumn() ?: 'All lessons completed';

    // Fetch enrolled courses with progress
    $stmt = $pdo->prepare("SELECT courses.title, courses.description, courses.image, 
                                  IFNULL(SUM(CASE WHEN student_progress.status = 'completed' THEN 1 ELSE 0 END) * 100 / COUNT(lessons.id), 0) AS progress
                           FROM courses
                           LEFT JOIN lessons ON courses.id = lessons.course_id
                           LEFT JOIN student_progress ON lessons.id = student_progress.lesson_id AND student_progress.student_id = :user_id
                           WHERE courses.id IN (SELECT course_id FROM enrollments WHERE student_id = :user_id)
                           GROUP BY courses.id");
    $stmt->execute(['user_id' => $user_id]);
    $user['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch announcements
    $stmt = $pdo->query("SELECT title, date, type FROM announcements WHERE type = 'system' OR course_id IN (SELECT course_id FROM enrollments WHERE student_id = $user_id)");
    $user['announcements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/Style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
                <span>ELMS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="Dashboard.php" class="nav-link active">Home</a></li>
                    <li class="nav-item"><a href="Profile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content" style="margin-top: 100px;">
        <!-- Welcome Section -->
        <div id="Welcome" class="text-center py-5">
            <h1>Welcome, <span id="userName"><?php echo htmlspecialchars($user['name'] ?? 'Student'); ?></span>!</h1>
            <p>Manage your courses and keep track of your progress here.</p>
        </div>

        <!-- Recent Activities and Next Lesson Section -->
        <div class="row mx-4">
            <div class="col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <p class="card-text"><?php echo htmlspecialchars($user['recentActivity'] ?? 'No recent activity'); ?></p>
                        <a href="Course.php" class="btn btn-primary">Continue Learning</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Next Lesson</h5>
                        <p class="card-text"><?php echo htmlspecialchars($user['nextLesson'] ?? 'All lessons completed'); ?></p>
                        <a href="#" class="btn btn-primary">Go to Lesson</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">My Courses</h2>
            <div class="row" id="courseList">
                <?php foreach ($user['courses'] as $course) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($course['image'] ?? 'images/placeholder.jpg'); ?>" class="card-img-top" alt="Course Image" height="236">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $course['progress']; ?>%" aria-valuenow="<?php echo $course['progress']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $course['progress']; ?>% Complete</div>
                                </div>
                                <a href="Course.php" class="btn btn-primary"><?php echo $course['progress'] == 0 ? 'Start' : 'Continue'; ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Announcement Board Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">Announcement Board</h2>
            <ul class="list-group" id="announcementBoard">
                <?php foreach ($user['announcements'] as $announcement) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                        <span class="badge bg-<?php echo htmlspecialchars($announcement['type']); ?> rounded-pill">Date: <?php echo htmlspecialchars($announcement['date']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
