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

$user_id = $_SESSION['user_id'];

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

// Check completion status for each module
$moduleCompletionStatus = [];
foreach ($modules as $module) {
    // Check if the module is completed
    $moduleQuery = $pdo->prepare("SELECT completed FROM user_progress WHERE user_id = :user_id AND module_id = :module_id");
    $moduleQuery->execute(['user_id' => $user_id, 'module_id' => $module['id']]);
    $moduleCompleted = $moduleQuery->fetchColumn();

    // Fetch lessons for the module
    $lessonsQuery = $pdo->prepare("SELECT id FROM lessons WHERE module_id = :module_id");
    $lessonsQuery->execute(['module_id' => $module['id']]);
    $lessons = $lessonsQuery->fetchAll(PDO::FETCH_COLUMN);

    // Check if all lessons are completed
    $allLessonsCompleted = true;
    foreach ($lessons as $lesson_id) {
        $lessonQuery = $pdo->prepare("SELECT completed FROM user_lessons_progress WHERE user_id = :user_id AND lesson_id = :lesson_id");
        $lessonQuery->execute(['user_id' => $user_id, 'lesson_id' => $lesson_id]);
        if (!$lessonQuery->fetchColumn()) {
            $allLessonsCompleted = false;
            break;
        }
    }

    // Fetch quizzes for the module
    $quizzesQuery = $pdo->prepare("SELECT quiz_id FROM Quizzes WHERE module_id = :module_id");
    $quizzesQuery->execute(['module_id' => $module['id']]);
    $quizzes = $quizzesQuery->fetchAll(PDO::FETCH_COLUMN);

    // Check if all quizzes are completed
    $allQuizzesCompleted = true;
    foreach ($quizzes as $quiz_id) {
        $quizQuery = $pdo->prepare("SELECT completed FROM user_quizzes_progress WHERE user_id = :user_id AND quiz_id = :quiz_id");
        $quizQuery->execute(['user_id' => $user_id, 'quiz_id' => $quiz_id]);
        if (!$quizQuery->fetchColumn()) {
            $allQuizzesCompleted = false;
            break;
        }
    }

    // Determine if the module is fully completed
    $moduleCompletionStatus[$module['id']] = $moduleCompleted && $allLessonsCompleted && $allQuizzesCompleted;

    // Update user_progress table if all lessons and quizzes are completed
    if ($allLessonsCompleted && $allQuizzesCompleted) {
        // Check if the record already exists
        $checkProgressQuery = $pdo->prepare("SELECT completed FROM user_progress WHERE user_id = :user_id AND module_id = :module_id");
        $checkProgressQuery->execute(['user_id' => $user_id, 'module_id' => $module['id']]);
        $existingProgress = $checkProgressQuery->fetchColumn();

        if ($existingProgress !== false) {
            // Update existing record
            $updateQuery = $pdo->prepare("UPDATE user_progress SET completed = TRUE WHERE user_id = :user_id AND module_id = :module_id");
            $updateQuery->execute(['user_id' => $user_id, 'module_id' => $module['id']]);
        } else {
            // Insert new record
            $insertQuery = $pdo->prepare("INSERT INTO user_progress (user_id, module_id, completed) VALUES (:user_id, :module_id, TRUE)");
            $insertQuery->execute(['user_id' => $user_id, 'module_id' => $module['id']]);
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>PAES : Courses</title>
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
    <style>
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            /* Allow the main content to grow and push the footer down */
        }

        .footer {
            margin-top: auto;
            /* Automatically stick footer at the bottom */
        }
    </style>


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
                    <div id="google_translate_element">
                    </div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <main class="container mt-5"> <!-- Wrap content in a main tag -->
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
                            <?php if ($moduleCompletionStatus[$module['id']]): ?>
                                <span class="checkmark">✔️</span>
                            <?php endif; ?>
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
    </main> <!-- End of main content -->

    </div>
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