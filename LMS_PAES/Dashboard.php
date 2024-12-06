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

// Check if the user is logged in and has the student role
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || $user_role !== 'student') {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a student
    exit();
}

// Fetch user profile details
$stmt = $pdo->prepare("SELECT username AS name FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?? ['name' => 'Student'];

// Since student progress is not implemented, we'll set default values for recent activity and next lesson
$user['recentActivity'] = 'No recent activity';
$user['nextLesson'] = 'No upcoming lessons';

// Fetch all available courses
$stmt = $pdo->prepare("
    SELECT id, title, description, image 
    FROM courses
");
$stmt->execute();
$user['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch system and course-specific announcements
$stmt = $pdo->prepare("
    SELECT title, date, type 
    FROM announcements 
    WHERE type = 'system' 
    OR course_id IN (SELECT course_id FROM enrollments WHERE student_id = :user_id)
");
$stmt->execute(['user_id' => $user_id]);
$user['announcements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>PAES : Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/icon.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style2.css">


</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-warning navbar-light shadow sticky-top p-0">
        <a href="Dashboard.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <p class="m-0 fw-bold" style="font-size: 25px;"><img src="img/icon.png" alt="" height="50px">PAES<span
                    style="color: #000000;"> REVIEW</span></p>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="Dashboard.php" class="nav-item nav-link">Home</a>
                <a href="Profile.php" class="nav-item nav-link">Profile</a>
                <a href="LandingPage.php" class="nav-item nav-link">Logout</a>
                <a href="#" class="nav-item nav-link">
                    <div id="google_translate_element">
                    </div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

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
                        <p class="card-text">
                            <?php echo htmlspecialchars($user['recentActivity'] ?? 'No recent activity'); ?>
                        </p>
                        <a href="Course.php" class="btn btn-primary">Continue Learning</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Next Lesson</h5>
                        <p class="card-text">
                            <?php echo htmlspecialchars($user['nextLesson'] ?? 'All lessons completed'); ?>
                        </p>
                        <a href="#" class="btn btn-primary">Go to Lesson</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">My Courses</h2>
            <div class="row" id="courseList">
                <?php foreach ($user['courses'] as $course): ?>
                    <div class="col-md-4 mb-4 d-flex align-items-stretch">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($course['image']); ?>" class="card-img-top"
                                alt="Course Image" height="235">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <!-- Link to Course.php with course_id parameter -->
                                <a href="Course.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                    Start Course
                                </a>
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
                <?php foreach ($user['announcements'] as $announcement): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                        <span class="badge bg-<?php echo htmlspecialchars($announcement['type']); ?> rounded-pill">Date:
                            <?php echo htmlspecialchars($announcement['date']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>


    </div>
    <!-- Footer Start -->
    <div class="container-fluid bg-warning text-dark footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy 2024 LMS</a>, All Right Reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>