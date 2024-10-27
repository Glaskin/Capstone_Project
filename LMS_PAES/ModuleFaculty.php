<?php
// Start session to manage module and lesson data
session_start();

// Initialize modules data if not already set
if (!isset($_SESSION['modules'])) {
    $_SESSION['modules'] = [
        [
            'title' => 'Module 1: Introduction',
            'lessons' => ['Lesson 1: Course Overview', 'Lesson 2: Basic Concepts', 'Quiz: Introduction']
        ]
    ];
}

// Handle form submissions to add modules or lessons
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addModule'])) {
        $moduleName = htmlspecialchars($_POST['moduleName']);
        if (!empty($moduleName)) {
            $_SESSION['modules'][] = [
                'title' => $moduleName,
                'lessons' => []
            ];
        }
    } elseif (isset($_POST['addLesson'])) {
        $moduleIndex = $_POST['moduleIndex'];
        $lessonName = htmlspecialchars($_POST['lessonName']);
        if (isset($_SESSION['modules'][$moduleIndex]) && !empty($lessonName)) {
            $_SESSION['modules'][$moduleIndex]['lessons'][] = $lessonName;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Course Modules - Faculty View</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/course.css">
    <link rel="stylesheet" href="css/Module.css"/>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
                <span>PAES</span>
            </a>
        </div>
    </nav>

    <div class="content mt-5 pt-3">
        <div class="container my-5">
            <h2 class="text-center mb-4">Course Modules - Faculty Management</h2>
            
            <!-- Add New Module Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Add New Module</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="ModuleFaculty.php">
                        <div class="mb-3">
                            <label for="moduleName" class="form-label">Module Name</label>
                            <input type="text" class="form-control" id="moduleName" name="moduleName" required>
                        </div>
                        <button type="submit" name="addModule" class="btn btn-primary">Add Module</button>
                    </form>
                </div>
            </div>

            <!-- Existing Modules -->
            <div class="accordion" id="moduleAccordion">
                <?php foreach ($_SESSION['modules'] as $index => $module): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($module['title']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#moduleAccordion">
                            <div class="accordion-body">
                                <ul id="lessonList<?php echo $index; ?>">
                                    <?php foreach ($module['lessons'] as $lesson): ?>
                                        <li><?php echo htmlspecialchars($lesson); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <!-- Add Lesson Form -->
                                <form method="POST" action="ModuleFaculty.php" class="mt-3">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="lessonName" placeholder="New Lesson Name" required>
                                        <input type="hidden" name="moduleIndex" value="<?php echo $index; ?>">
                                        <button type="submit" name="addLesson" class="btn btn-outline-secondary">Add Lesson</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
