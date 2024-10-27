<?php
// Start a session to manage modules data
session_start();

// Placeholder for managing modules in the session
if (!isset($_SESSION['modules'])) {
    $_SESSION['modules'] = [
        [
            'title' => 'Module 1: Introduction',
            'lessons' => ['Lesson 1: Course Overview', 'Lesson 2: Basic Concepts', 'Quiz: Introduction']
        ],
        [
            'title' => 'Module 2: Core Topics',
            'lessons' => ['Lesson 1: Topic A', 'Lesson 2: Topic B', 'Lesson 3: Topic C', 'Assignment: Core Topics']
        ],
        [
            'title' => 'Module 3: Advanced Concepts',
            'lessons' => ['Lesson 1: Advanced Topic X', 'Lesson 2: Advanced Topic Y', 'Lesson 3: Advanced Topic Z', 'Quiz: Advanced Concepts']
        ]
    ];
}

// Handle form submission to add, edit, or remove modules
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addModule'])) {
        $moduleTitle = htmlspecialchars($_POST['moduleTitle']);
        $lessons = array_map('htmlspecialchars', explode("\n", $_POST['moduleLessons']));
        
        if (!empty($moduleTitle) && !empty($lessons)) {
            $_SESSION['modules'][] = [
                'title' => $moduleTitle,
                'lessons' => $lessons
            ];
        }
    } elseif (isset($_POST['removeModule'])) {
        $moduleIndex = $_POST['moduleIndex'];
        if (isset($_SESSION['modules'][$moduleIndex])) {
            unset($_SESSION['modules'][$moduleIndex]);
            $_SESSION['modules'] = array_values($_SESSION['modules']); // Re-index the array
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Course Modules</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/course.css">
    <link rel="stylesheet" href="css/Module.css" />
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">Home</a>
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

    <div class="content">
        <div class="container my-5">
            <h2 class="text-center mb-4">Course Modules</h2>
            
            <div class="accordion" id="moduleAccordion">
                <?php foreach ($_SESSION['modules'] as $index => $module): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($module['title']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#moduleAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <?php foreach ($module['lessons'] as $lesson): ?>
                                        <li><?php echo htmlspecialchars($lesson); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <form method="POST" action="Module.php">
                                    <input type="hidden" name="moduleIndex" value="<?php echo $index; ?>">
                                    <button type="submit" name="removeModule" class="btn btn-danger mt-2">Remove Module</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Module Section -->
            <div class="card mt-5 p-4">
                <h4 class="text-center mb-4">Add New Module</h4>
                <form method="POST" action="Module.php">
                    <div class="mb-3">
                        <label for="moduleTitle" class="form-label">Module Title</label>
                        <input type="text" class="form-control" id="moduleTitle" name="moduleTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="moduleLessons" class="form-label">Lessons (One per line)</label>
                        <textarea class="form-control" id="moduleLessons" name="moduleLessons" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="addModule" class="btn btn-primary">Add Module</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
