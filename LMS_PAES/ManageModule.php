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

// Check if module_id is provided in the URL
$module_id = $_GET['module_id'] ?? null;
if (!$module_id || !is_numeric($module_id)) {
    die("A valid Module ID is required.");
}

$course_id = $_GET['course_id'] ?? null;

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
        VALUES (:module_id, :lessonTitle, :lessonContent, 'lesson', 
        (SELECT COALESCE(MAX(position), 0) + 1 FROM (SELECT position FROM lessons WHERE module_id = :module_id) AS temp))");
        $stmt->execute(['module_id' => $module_id, 'lessonTitle' => $lessonTitle, 'lessonContent' => $lessonContent]);
        $_SESSION['message'] = "Lesson '$lessonTitle' added successfully!";
        header("Location: ManageModule.php?module_id=$module_id"); // Refresh to show the new lesson
        exit();
    }

    // Handle lesson deletion
    if (isset($_POST['deleteLesson'])) {
        $lesson_id = $_POST['lesson_id'];
        
        // Delete the lesson associated with the current module_id
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = :lesson_id AND module_id = :module_id");
        $stmt->execute(['lesson_id' => $lesson_id, 'module_id' => $module_id]);
        $_SESSION['message'] = "Lesson deleted successfully!";
        header("Location: ManageModule.php?module_id=$module_id"); // Refresh to reflect deletion
        exit();
    }
}

// Initialize variables
$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;
$quizzes = [];

// Fetch quizzes from the database
if ($module_id) {
    $stmt = $pdo->prepare("SELECT * FROM Quizzes WHERE module_id = ?");
    $stmt->execute([$module_id]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle adding a new quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addQuiz'])) {
    $quizTitle = $_POST['quizTitle'];
    $number_of_questions = $_POST['number_of_questions']; // Get the number of questions
    $stmt = $pdo->prepare("INSERT INTO Quizzes (title, module_id, number_of_questions) VALUES (?, ?, ?)");
    $stmt->execute([$quizTitle, $module_id, $number_of_questions]);
    $_SESSION['message'] = "Quiz '$quizTitle' added successfully!";
    header("Location: ManageModule.php?module_id=$module_id");
    exit();
}

// Handle deleting a quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteQuiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $stmt = $pdo->prepare("DELETE FROM Quizzes WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $_SESSION['message'] = "Quiz deleted successfully!";
    header("Location: ManageModule.php?module_id=$module_id");
    exit();
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
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>Manage Module</title>
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
                    style="color: #000000;"> ADMIN</span></p>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="AdminDashboard.php" class="nav-item nav-link">Back to Admin</a>
                <a href="LandingPage.php" class="nav-item nav-link">Logout</a>
                <a href="#" class="nav-item nav-link">
                    <div id="google_translate_element">
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <div class="content" style="margin-top: 100px; margin-bottom: 100px;">
        <div class="container mt-5">
            <div class="text-start mb-4">
                <a href="ManageCourse.php?course_id=<?php echo htmlspecialchars($course_id); ?>"
                    class="btn btn-secondary">
                    Back to Course
                </a>
            </div>
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
                        <button type="submit" name="addLesson" class="btn btn-success">Add Lesson</button>
                    </form>
                </div>
            </div>

            <div class="container">
                <!-- Quizzes List -->
                <h2 class="text-center mb-4">Quizzes and Exercise</h2>
                <div class="list-group mb-5">
                    <?php if (!empty($quizzes)): ?>
                        <?php foreach ($quizzes as $quiz): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <?php echo htmlspecialchars($quiz['title']); ?>
                                    | Questions: <?php echo htmlspecialchars($quiz['number_of_questions']); ?>
                                </span>
                                <div>
                                    <a href="QuizMaker.php?quiz_id=<?php echo htmlspecialchars($quiz['quiz_id']); ?>&module_id=<?php echo htmlspecialchars($module_id); ?>"
                                        class="btn btn-primary btn-sm">
                                        Manage <?php echo htmlspecialchars($quiz['title']); ?>
                                    </a>
                                    <form method="POST"
                                        action="ManageModule.php?module_id=<?php echo htmlspecialchars($module_id); ?>"
                                        style="display: inline;">
                                        <input type="hidden" name="quiz_id"
                                            value="<?php echo htmlspecialchars($quiz['quiz_id']); ?>">
                                        <button type="submit" name="deleteQuiz" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No Quizzes/Exercise available for this module.</p>
                    <?php endif; ?>
                </div>

                <?php
                // Display success message if set
                if (isset($_SESSION['message'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                    unset($_SESSION['message']); // Clear the message after displaying
                }
                ?>

                <!-- Add Quiz Section -->
<h2 class="text-center mb-4">Add New Quiz/Exercise</h2>
<div class="card mb-5">
    <div class="card-body">
        <form method="POST" action="ManageModule.php?module_id=<?php echo htmlspecialchars($module_id); ?>">
            <div class="mb-3">
                <label for="quizTitle" class="form-label">Quiz Title</label>
                <input type="text" class="form-control" id="quizTitle" name="quizTitle" required>
            </div>
            <div class="mb-3">
                <label for="number_of_questions" class="form-label">Number of Questions</label>
                <input type="number" class="form-control" id="number_of_questions" name="number_of_questions" min="1" required>
            </div>
            <button type="submit" name="addQuiz" class="btn btn-success">Add Quiz</button>
        </form>
    </div>
</div>
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