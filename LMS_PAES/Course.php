<?php
// Start a session to manage course data
session_start();

// Placeholder for managing course information in the session
if (!isset($_SESSION['course'])) {
    $_SESSION['course'] = [
        'title' => 'Course Title',
        'description' => 'Short description of the course. This paragraph provides an overview of the key topics covered in the course and its learning objectives.',
        'instructor' => [
            'name' => 'Instructor Name',
            'bio' => 'Brief bio or description of the instructor. This paragraph can include the instructor\'s expertise and qualifications.',
            'avatar' => 'img/user-avatar.jpg'
        ],
        'syllabus' => [
            'Part 1: Introduction',
            'Part 2: Learning Objectives',
            'Part 3: Course Content',
            'Part 4: Assessment'
        ]
    ];
}

// Handle form submission to update course information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['updateCourse'])) {
        $_SESSION['course']['title'] = htmlspecialchars($_POST['courseTitle']);
        $_SESSION['course']['description'] = htmlspecialchars($_POST['courseDescription']);
        $_SESSION['course']['instructor']['name'] = htmlspecialchars($_POST['instructorName']);
        $_SESSION['course']['instructor']['bio'] = htmlspecialchars($_POST['instructorBio']);
    } elseif (isset($_POST['addSyllabusItem'])) {
        $newSyllabusItem = htmlspecialchars($_POST['syllabusItem']);
        if (!empty($newSyllabusItem)) {
            $_SESSION['course']['syllabus'][] = $newSyllabusItem;
        }
    } elseif (isset($_POST['removeSyllabusItem'])) {
        $syllabusIndex = $_POST['syllabusIndex'];
        if (isset($_SESSION['course']['syllabus'][$syllabusIndex])) {
            unset($_SESSION['course']['syllabus'][$syllabusIndex]);
            $_SESSION['course']['syllabus'] = array_values($_SESSION['course']['syllabus']); // Re-index the array
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($_SESSION['course']['title']); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/course.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
                <span>PAES</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
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

    <div class="content">
        <div class="container my-5">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <h2 class="text-center mb-4"><?php echo htmlspecialchars($_SESSION['course']['title']); ?></h2>
                    <p class="lead"><?php echo htmlspecialchars($_SESSION['course']['description']); ?></p>

                    <h4 class="mb-3">Instructor</h4>
                    <div class="media mb-4">
                        <img src="<?php echo htmlspecialchars($_SESSION['course']['instructor']['avatar']); ?>" class="mr-3 rounded-circle" alt="Instructor Avatar" style="width: 100px;">
                        <div class="media-body">
                            <h5 class="mt-0"><?php echo htmlspecialchars($_SESSION['course']['instructor']['name']); ?></h5>
                            <p><?php echo htmlspecialchars($_SESSION['course']['instructor']['bio']); ?></p>
                        </div>
                    </div>

                    <h4 class="mb-3">Syllabus</h4>
                    <ul>
                        <?php foreach ($_SESSION['course']['syllabus'] as $index => $syllabusItem): ?>
                            <li>
                                <?php echo htmlspecialchars($syllabusItem); ?>
                                <form method="POST" action="Course.php" style="display:inline;">
                                    <input type="hidden" name="syllabusIndex" value="<?php echo $index; ?>">
                                    <button type="submit" name="removeSyllabusItem" class="btn btn-sm btn-danger ml-2">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="ModuleTest.php" class="btn btn-primary mt-4">Start</a>
                </div>
            </div>

            <!-- Update Course Information Section -->
            <div class="card mt-5 p-4">
                <h4 class="text-center mb-4">Update Course Information</h4>
                <form method="POST" action="Course.php">
                    <div class="mb-3">
                        <label for="courseTitle" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="courseTitle" name="courseTitle" value="<?php echo htmlspecialchars($_SESSION['course']['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="courseDescription" class="form-label">Course Description</label>
                        <textarea class="form-control" id="courseDescription" name="courseDescription" rows="3" required><?php echo htmlspecialchars($_SESSION['course']['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="instructorName" class="form-label">Instructor Name</label>
                        <input type="text" class="form-control" id="instructorName" name="instructorName" value="<?php echo htmlspecialchars($_SESSION['course']['instructor']['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="instructorBio" class="form-label">Instructor Bio</label>
                        <textarea class="form-control" id="instructorBio" name="instructorBio" rows="3" required><?php echo htmlspecialchars($_SESSION['course']['instructor']['bio']); ?></textarea>
                    </div>
                    <button type="submit" name="updateCourse" class="btn btn-success">Update Course</button>
                </form>
            </div>

            <!-- Add Syllabus Item Section -->
            <div class="card mt-5 p-4">
                <h4 class="text-center mb-4">Add Syllabus Item</h4>
                <form method="POST" action="Course.php">
                    <div class="mb-3">
                        <label for="syllabusItem" class="form-label">Syllabus Item</label>
                        <input type="text" class="form-control" id="syllabusItem" name="syllabusItem" required>
                    </div>
                    <button type="submit" name="addSyllabusItem" class="btn btn-primary">Add Item</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-warning text-center py-3">
        <p>&copy; 2024 LMS. All rights reserved.</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
