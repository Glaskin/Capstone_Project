<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    header("Location: LandingPage.php");
    exit();
}

// Check if lesson_id is provided in the URL
$lesson_id = $_GET['lesson_id'] ?? null;
if (!$lesson_id) {
    die("Lesson ID is required.");
}

// Fetch the lesson data
$stmt = $pdo->prepare("SELECT title, content, module_id, file_path FROM lessons WHERE id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Lesson not found.");
}

// Initialize filePaths with the existing file path or an empty array
$filePaths = !empty($lesson['file_path']) ? explode(',', $lesson['file_path']) : [];

// Handle form submission to update lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
    $lessonContent = $_POST['lessonContent']; // Get the raw content from Tiny MCE

    // Handle file upload
    if (isset($_FILES['lessonFile'])) {
        foreach ($_FILES['lessonFile']['tmp_name'] as $key => $fileTmpPath) {
            $fileName = $_FILES['lessonFile']['name'][$key];
            $fileSize = $_FILES['lessonFile']['size'][$key];
            $fileError = $_FILES['lessonFile']['error'][$key];

            // Specify the upload directory
            $uploadFileDir = './uploaded_files/media_files/';
            $dest_path = $uploadFileDir . basename($fileName);

            // Check for upload errors
            if ($fileError === UPLOAD_ERR_OK) {
                // Check file size (30 MB)
                if ($fileSize <= 30 * 1024 * 1024) {
                    // Move the file to the upload directory
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $filePaths[] = $dest_path; // Add the new file path to the array
                    } else {
                        echo "Error moving the uploaded file: " . $fileName;
                    }
                } else {
                    echo "File size must be less than 30 MB for file: " . $fileName;
                }
            } else {
                echo "File upload error: " . $fileError;
            }
        }
    }

// Handle file removal
if (isset($_POST['removeFile'])) {
    $fileToRemove = $_POST['removeFile'];
    if (($key = array_search($fileToRemove, $filePaths)) !== false) {
        // Remove the file from the server
        if (file_exists($fileToRemove)) {
            unlink($fileToRemove); // Delete the file from the server
        }
        unset($filePaths[$key]); // Remove the file path from the array

        // Update lesson in the database
        $filePathString = implode(',', $filePaths); // Convert array back to string for database
        $stmt = $pdo->prepare("UPDATE lessons SET file_path = :file_path WHERE id = :lesson_id");
        $stmt->execute(['file_path' => $filePathString, 'lesson_id' => $lesson_id]);
    }
}

    // Update lesson in the database
    $filePathString = implode(',', $filePaths); // Convert array back to string for database
    $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content, file_path = :file_path WHERE id = :lesson_id");
    $stmt->execute(['title' => $lessonTitle, 'content' => $lessonContent, 'file_path' => $filePathString, 'lesson_id' => $lesson_id]);

    // Redirect back to the same page to refresh
    header("Location: EditLesson.php?lesson_id=" . $lesson_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>
    <title>Edit Lesson - <?php echo htmlspecialchars($lesson['title']); ?></title>
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

    <script src="https://cdn.tiny.cloud/1/nqnew31549ejg1qkw3pezhkwqbuvz73i9iiouh0jl77ltyvh/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#lesson-editor',
            plugins: 'advlist autolink lists link image media charmap preview anchor',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | image media document',
            menubar: false,
            automatic_uploads: true,
            file_picker_types: 'image media document',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save(); // Save the content to the textarea on change
                });
            }
        });

        // Add event listener to the form to trigger save on submit
        document.querySelector('form').addEventListener('submit', function () {
            tinymce.triggerSave(); // Save the content of the TinyMCE editor back to the textarea
        });
    </script>


</head>

<body>
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

    <div class="container mt-5" style="margin-top: 100px;">
        <h2 class="text-center mb-4">Edit Lesson</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>"
                    enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="lessonTitle" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="lessonTitle" name="lessonTitle"
                            value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lessonContent" class="form-label">Lesson Content</label>
                        <textarea class="form-control" id="lesson-editor" name="lessonContent" rows="6"
                            required><?php echo ($lesson['content']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="lessonFile" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="lessonFile" name="lessonFile[]" multiple>

                        <?php if (!empty($lesson['file_path'])): ?>
                            <div class="mt-2">
                                <strong>Current Files:</strong>
                                <?php foreach ($filePaths as $filePath): ?>
                                    <div>
                                        <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">Download File</a>
                                        <button class="btn btn-danger btn-sm" type="button"
                                            onclick="removeFile('<?php echo htmlspecialchars($filePath); ?>')">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                                <br>
                                <small>You can upload new files to add to the current ones.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="ManageModule.php?module_id=<?php echo $lesson['module_id']; ?>"
                        class="btn btn-secondary">Cancel</a>
                </form>
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

    <script>
        function removeFile(filePath) {
            var form = document.querySelector('form');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removeFile';
            input.value = filePath;
            form.appendChild(input);
            form.submit();
        }
    </script>

</body>

</html>