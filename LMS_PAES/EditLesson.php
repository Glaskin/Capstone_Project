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

// Ensure only faculty can access this page
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || $user_role !== 'faculty') {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a faculty member
    exit();
}

// Check if lesson_id is provided in the URL
$lesson_id = $_GET['lesson_id'] ?? null;
if (!$lesson_id) {
    die("Lesson ID is required.");
}

// Fetch the lesson data
$stmt = $pdo->prepare("SELECT title, content FROM lessons WHERE id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Lesson not found.");
}

// Fetch uploaded files for the lesson
$stmt = $pdo->prepare("SELECT id, content, media_url FROM lesson_content WHERE lesson_id = :lesson_id");
$stmt->execute(['lesson_id' => $lesson_id]);
$uploadedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file removal
if (isset($_POST['remove_file_id'])) {
    $fileId = $_POST['remove_file_id'];

    // Fetch the file to get the media_url before deleting
    $stmt = $pdo->prepare("SELECT media_url FROM lesson_content WHERE id = :id");
    $stmt->execute(['id' => $fileId]);
    $fileToRemove = $stmt->fetch(PDO::FETCH_ASSOC);

    // Remove the file from the database
    $stmt = $pdo->prepare("DELETE FROM lesson_content WHERE id = :id");
    $stmt->execute(['id' => $fileId]);

    // Remove the file from the server
    if ($fileToRemove && file_exists($fileToRemove['media_url'])) {
        unlink($fileToRemove['media_url']); // Delete the file from the server
    }

    // Refresh the page to see the updated list of files
    header("Location: EditLesson.php?lesson_id=" . $lesson_id . "&module_id=" . $_GET['module_id']);
    exit();
}

// Handle form submission to update lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
    $lessonContent = htmlspecialchars($_POST['lessonContent']);
    
    // Handle file upload
    $mediaUrl = null;
    if (isset($_FILES['lessonFile']) && $_FILES['lessonFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['lessonFile']['tmp_name'];
        $fileName = $_FILES['lessonFile']['name'];
        $fileSize = $_FILES['lessonFile']['size'];
        $fileType = $_FILES['lessonFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Specify the allowed file types
        $allowedfileExtensions = array('pdf', 'docx');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Set the upload file path
            $uploadFileDir = './uploaded_files/';
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Unique file name
            $dest_path = $uploadFileDir . $newFileName;

            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $mediaUrl = $dest_path; // Store the file path
            } else {
                die("There was an error moving the uploaded file.");
            }
        } else {
            die("Upload failed. Allowed file types: " . implode(", ", $allowedfileExtensions));
        }
    }

    // Update lesson in the database
    $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content WHERE id = :lesson_id");
    $stmt->execute(['title' => $lessonTitle, 'content' => $lessonContent, 'lesson_id' => $lesson_id]);

    // Insert or update the lesson content with the media_url
    if ($mediaUrl) {
        $stmt = $pdo->prepare("INSERT INTO lesson_content (lesson_id, content, media_url) VALUES (:lesson_id, :content, :media_url)");
        $stmt->execute(['lesson_id' => $lesson_id, 'content' => $lessonContent, 'media_url' => $mediaUrl]);
    }

    // Redirect back to ManageModule.php after updating
    header("Location: ManageModule.php?module_id=" . $_GET['module_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Lesson - <?php echo htmlspecialchars($lesson['title']); ?></title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/Style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="FacultyDashboard.php">ELMS - Faculty</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="FacultyDashboard.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="FacultyProfile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <<div class="container mt-5" style="margin-top: 100px;">
        <h2 class="text-center mb-4">Edit Lesson</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>&module_id=<?php echo $_GET['module_id']; ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="lessonTitle" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="lessonTitle" name="lessonTitle" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lessonContent" class="form-label">Lesson Content</label>
                        <textarea class="form-control" id="lesson-editor" name="lessonContent" rows="6" required><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="lessonFile" class="form-label">Attach File (PDF/DOCX)</label>
                        <input type="file" class="form-control" id="lessonFile" name="lessonFile" accept=".pdf,.docx">
                    </div>

                    <!-- Display uploaded files -->
                    <h5>Uploaded Files:</h5>
                    <?php if ($uploadedFiles): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($uploadedFiles as $file): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?php echo htmlspecialchars($file['media_url']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($file['content']); ?>
                                    </a>
                                    <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>&module_id=<?php echo $_GET['module_id']; ?>" style="display:inline;">
                                        <input type="hidden" name="remove_file_id" value="<?php echo $file['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No files uploaded for this lesson.</p>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="ManageModule.php?module_id=<?php echo $_GET['module_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/nqnew31549ejg1qkw3pezhkwqbuvz73i9iiouh0jl77ltyvh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#lesson-editor',
        plugins: 'advlist autolink lists link image charmap preview anchor',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent',
        menubar: false
    });
</script>

</body>

</html>