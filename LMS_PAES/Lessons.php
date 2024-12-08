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
// Fetch lesson title, content, and file path from lessons table
$stmt = $pdo->prepare("SELECT title, content, module_id, file_path FROM lessons WHERE id = :lesson_id");
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
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>PAES : Lessons</title>
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
    <!-- Spinner Start
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    Spinner End -->

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
                    <div id="google_translate_element"></div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Main Content Start -->
    <main class="container mt-5"> <!-- Add 'container' class for consistent styling -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h2>
                <div class="card-text">
                    <?php echo $lesson['content']; // Display the content directly as HTML ?>
                </div>

                <!-- Display uploaded files if they exist -->
                <?php if (!empty($lesson['file_path'])): ?>
                    <div class="mt-3">
                        <strong>Available Files:</strong>
                        <?php
                        $filePaths = explode(',', $lesson['file_path']);
                        foreach ($filePaths as $filePath): ?>
                            <div>
                                <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">Download File</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation links -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <?php if ($previous_lesson): ?>
                <a href="Lessons.php?lesson_id=<?php echo $previous_lesson['id']; ?>" class="btn btn-secondary">Previous
                    Lesson</a>
            <?php endif; ?>

            <a href="Module.php?module_id=<?php echo $lesson['module_id']; ?>" class="btn btn-primary">Return to
                Module</a>

            <?php if ($next_lesson): ?>
                <a href="Lessons.php?lesson_id=<?php echo $next_lesson['id']; ?>" class="btn btn-secondary">Next Lesson</a>
            <?php endif; ?>
        </div>
    </main>
    <!-- Main Content End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-warning text-dark footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; 2024 LMS, All Rights Reserved.
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