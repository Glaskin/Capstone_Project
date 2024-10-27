<?php
// Start a session to manage lesson data if needed
session_start();

// Placeholder to manage lessons list in session
if (!isset($_SESSION['lessons'])) {
    $_SESSION['lessons'] = [];
}

// Handle form submission to add, edit, or remove lessons
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addLesson'])) {
        $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
        $lessonContent = htmlspecialchars($_POST['lessonContent']);
        
        if (!empty($lessonTitle) && !empty($lessonContent)) {
            $_SESSION['lessons'][] = [
                'title' => $lessonTitle,
                'content' => $lessonContent
            ];
        }
    } elseif (isset($_POST['editLesson'])) {
        $lessonIndex = $_POST['lessonIndex'];
        $lessonTitle = htmlspecialchars($_POST['lessonTitle']);
        $lessonContent = htmlspecialchars($_POST['lessonContent']);
        
        if (isset($_SESSION['lessons'][$lessonIndex])) {
            $_SESSION['lessons'][$lessonIndex]['title'] = $lessonTitle;
            $_SESSION['lessons'][$lessonIndex]['content'] = $lessonContent;
        }
    } elseif (isset($_POST['removeLesson'])) {
        $lessonIndex = $_POST['lessonIndex'];
        
        if (isset($_SESSION['lessons'][$lessonIndex])) {
            unset($_SESSION['lessons'][$lessonIndex]);
            $_SESSION['lessons'] = array_values($_SESSION['lessons']); // Re-index the array
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Lessons</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/Dashboard.css" />
</head>
<body>
    <nav class="navbar fixed-top navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40" />
                <span>PAES</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="Dashboard.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="Profile.php" class="nav-link">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="LandingPage.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- end of navigation -->

    <div class="container mt-5 pt-5">
        <h2 class="text-center mb-4">Manage Lessons</h2>
        <div class="card p-4">
            <form method="POST" action="LessonsModuleFaculty.php">
                <div class="mb-3">
                    <label for="lesson-title" class="form-label">Lesson Title</label>
                    <input type="text" class="form-control" id="lesson-title" name="lessonTitle" required />
                </div>
                <div class="mb-3">
                    <label for="lesson-content" class="form-label">Lesson Content</label>
                    <textarea class="form-control" id="lesson-content" name="lessonContent" rows="5" required></textarea>
                </div>
                <button type="submit" name="addLesson" class="btn btn-primary">Add Lesson</button>
            </form>
        </div>

        <div id="lessons-list" class="mt-4">
            <?php foreach ($_SESSION['lessons'] as $index => $lesson): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($lesson['content']); ?></p>
                        <form method="POST" action="LessonsModuleFaculty.php" class="d-inline">
                            <input type="hidden" name="lessonIndex" value="<?php echo $index; ?>">
                            <input type="hidden" name="lessonTitle" value="<?php echo htmlspecialchars($lesson['title']); ?>">
                            <input type="hidden" name="lessonContent" value="<?php echo htmlspecialchars($lesson['content']); ?>">
                            <button type="button" class="btn btn-secondary" onclick="editLesson(<?php echo $index; ?>)">Edit</button>
                            <button type="submit" name="removeLesson" class="btn btn-danger">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-warning text-center py-3 mt-5">
        <p>&copy; 2024 LMS. All rights reserved.</p>
    </footer>

    <script>
        function editLesson(index) {
            const title = prompt("Edit lesson title:", document.getElementsByName("lessonTitle")[index].value);
            const content = prompt("Edit lesson content:", document.getElementsByName("lessonContent")[index].value);
            
            if (title && content) {
                document.getElementsByName("lessonTitle")[index].value = title;
                document.getElementsByName("lessonContent")[index].value = content;
                document.getElementsByName("editLesson")[index].click();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
