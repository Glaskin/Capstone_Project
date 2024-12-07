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

// Ensure only faculty and admin can access this page
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($user_role, ['faculty', 'admin'])) {
    var_dump($user_id, $user_role); // Debugging line
    header("Location: LandingPage.php");
    exit();
}
// Retrieve the course ID from the URL
if (!isset($_GET['course_id'])) { // Ensure this matches the URL parameter
    die("Course ID is required.");
}

$course_id = $_GET['course_id']; // Correctly assigning the course ID from the URL

// Fetch course details using the correct variable name
$stmt = $pdo->prepare("SELECT title, description FROM courses WHERE id = :course_id");
$stmt->execute(['course_id' => $course_id]); // Use $course_id here
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found.");
}

// Fetch modules for this course
$stmt = $pdo->prepare("SELECT id, title, position FROM modules WHERE course_id = :course_id ORDER BY position ASC");
$stmt->execute(['course_id' => $course_id]); // Use $courseId here
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions for adding or deleting modules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addModule'])) {
        $moduleTitle = htmlspecialchars($_POST['moduleTitle']);
        $modulePosition = htmlspecialchars($_POST['modulePosition']);
        
        $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, position) VALUES (:course_id, :title, :position)");
        $stmt->execute(['course_id' => $course_id, 'title' => $moduleTitle, 'position' => $modulePosition]);
        
        header("Location: ManageCourse.php?course_id=" . $course_id); // Refresh to show the new module
        exit();
    } elseif (isset($_POST['deleteModule'])) {
        $module_id = $_POST['module_id'];
        
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = :module_id");
        $stmt->execute(['module_id' => $module_id]);
        
        header("Location: ManageCourse.php?course_id=" . $course_id); // Refresh to show the updated module list
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>Manage Course</title>
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
            <!-- Course Details -->
            <div class="text-center mb-5">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p><?php echo htmlspecialchars($course['description']); ?></p>
            </div>

            <!-- Modules List -->
            <h2 class="text-center mb-4">Modules</h2>
            <div class="list-group mb-5">
                <?php foreach ($modules as $module): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <?php echo htmlspecialchars($module['title']); ?> (Position: <?php echo $module['position']; ?>)
                        </span>
                        <div>
                            <a href="ManageModule.php?module_id=<?php echo $module['id']; ?>&course_id=<?php echo $course_id; ?>"
                                class="btn btn-primary btn-sm">Manage</a>
                            <form method="POST" action="ManageCourse.php?course_id=<?php echo $course_id; ?>"
                                style="display: inline;">
                                <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                <button type="submit" name="deleteModule" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Module Section -->
            <h2 class="text-center mb-4">Add New Module</h2>
            <div class="card mb-5">
                <div class="card-body">
                    <form method="POST" action="ManageCourse.php?course_id=<?php echo $course_id; ?>">
                        <div class="mb-3">
                            <label for="moduleTitle" class="form-label">Module Title</label>
                            <input type="text" class="form-control" id="moduleTitle" name="moduleTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="modulePosition" class="form-label">Position</label>
                            <input type="number" class="form-control" id="modulePosition" name="modulePosition"
                                required>
                        </div>
                        <button type="submit" name="addModule" class="btn btn-success">Add Module</button>
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