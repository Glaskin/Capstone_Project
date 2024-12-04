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
    error_log("Form submitted"); // Debugging line
    $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
    $lessonContent = $_POST['lessonContent']; // Get the raw content from Tiny MCE

    // Initialize filePaths with the existing file path or an empty array
    $filePaths = !empty($lesson['file_path']) ? explode(',', $lesson['file_path']) : [];

    // Handle file upload from TinyMCE
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];

        // Specify the upload directory
        $uploadFileDir = './uploaded_files/media_files/';
        $dest_path = $uploadFileDir . basename($fileName);

        // Check file size (30 MB)
        if ($fileSize > 30 * 1024 * 1024) {
            error_log("File size must be less than 30 MB for file: " . $fileName);
            die("File size must be less than 30 MB.");
        }

        // Move the file to the upload directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $filePaths[] = $dest_path; // Add the new file path to the array
        } else {
            error_log("Error moving the uploaded file from TinyMCE: " . $fileName);
            die("There was an error moving the uploaded file from TinyMCE.");
        }
    }

    // Handle file upload from the regular file input
    if (isset($_FILES['lessonFile'])) {
        foreach ($_FILES['lessonFile']['tmp_name'] as $key => $fileTmpPath) {
            $fileName = $_FILES['lessonFile']['name'][$key];
            $fileSize = $_FILES['lessonFile']['size'][$key];
            $fileError = $_FILES['lessonFile']['error'][$key];

            // Specify the upload directory
            $uploadFileDir = './uploaded_files/media_files/';
            $dest_path = $uploadFileDir . basename($fileName);

            // Check for upload errors
            if ($fileError !== UPLOAD_ERR_OK) {
                error_log("File upload error: " . $fileError); // Log the error
                continue; // Skip this file and continue with the next
            }

            // Check file size (30 MB)
            if ($fileSize > 30 * 1024 * 1024) {
                error_log("File size must be less than 30 MB for file: " . $fileName);
                continue; // Skip this file and continue with the next
            }

            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $filePaths[] = $dest_path; // Add the new file path to the array
            } else {
                error_log("Error moving the uploaded file: " . $fileName);
            }
        }
    }

    // Update lesson in the database
    $filePathString = implode(',', $filePaths); // Convert array back to string for database
    $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content, file_path = :file_path WHERE id = :lesson_id");
    $stmt->execute(['title' => $lessonTitle, 'content' => $lessonContent, 'file_path' => $filePathString, 'lesson_id' => $lesson_id]);

    error_log("Lesson updated successfully"); // Debugging line

    // Redirect back to the same page to refresh
    header("Location: EditLesson.php?lesson_id=" . $lesson_id);
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
    <script src="https://cdn.tiny.cloud/1/nqnew31549ejg1qkw3pezhkwqbuvz73i9iiouh0jl77ltyvh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
tinymce.init({
    selector: '#lesson-editor',
    plugins: 'advlist autolink lists link image media charmap preview anchor',
    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | image media document',
    menubar: false,
    images_upload_url: 'upload.php', // URL to your image upload handler
    automatic_uploads: true,
    file_picker_types: 'image media document', // Add document type
    file_picker_callback: function (callback, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');

        // Set the accept attribute based on file type
        if (meta.filetype === 'image') {
            input.setAttribute('accept', 'image/*');
        } else if (meta.filetype === 'media') {
            input.setAttribute('accept', 'video/*, audio/*'); // Accept video and audio files
        } else if (meta.filetype === 'document') {
            input.setAttribute('accept', '.pdf,.docx,.pptx'); // Accept document files
        }

        input.onchange = function () {
            var file = this.files[0];

            var formData = new FormData();
            formData.append('file', file); // This should remain as 'file'

            // Use fetch API to upload the file
            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.location) {
                    // Call the callback with the URL of the uploaded file
                    callback(data.location, { alt: file.name });
                } else {
                    alert('File upload failed: ' + (data.error || 'Unknown error.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('File upload failed, please try again.');
            });
        };
        input.click();
    }
});
</script>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="AdminDashboard.php">PAES - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="AdminDashboard.php" class="nav-link">Back to Admin</a>
                    </li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="margin-top: 100px;">
        <h2 class="text-center mb-4">Edit Lesson</h2>
        <div class="card mb-5">
            <div class="card-body">
                <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="lessonTitle" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="lessonTitle" name="lessonTitle" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lessonContent" class="form-label">Lesson Content</label>
                        <textarea class="form-control" id="lesson-editor" name="lessonContent" rows="6" required><?php echo ($lesson['content']); ?></textarea>
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
                                        <form method="POST" action="EditLesson.php?lesson_id=<?php echo $lesson_id; ?>" style="display:inline;">
                                            <input type="hidden" name="removeFile" value="<?php echo htmlspecialchars($filePath); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                                <br>
                                <small>You can upload new files to add to the current ones.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="ManageModule.php?module_id=<?php echo $lesson['module_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 PAES Review.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>