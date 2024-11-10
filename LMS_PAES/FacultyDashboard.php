<?php
// Start a session to manage user data if needed
session_start();

// Placeholder faculty data
$faculty = [
    'name' => 'Dr. Jane Smith',
    'studentsProgress' => [
        [
            'name' => 'John Doe',
            'course' => 'Web Development',
            'progress' => 70
        ],
        [
            'name' => 'Alice Johnson',
            'course' => 'Data Structures',
            'progress' => 80
        ],
        [
            'name' => 'Mark Brown',
            'course' => 'Database Management',
            'progress' => 60
        ]
    ],
    'courses' => []
];

// Handle form submission for announcements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcementTitle'])) {
    $announcementTitle = htmlspecialchars($_POST['announcementTitle']);
    $announcementDate = htmlspecialchars($_POST['announcementDate']);
    $announcementType = htmlspecialchars($_POST['announcementType']);
    $announcement = [
        'title' => $announcementTitle,
        'date' => $announcementDate,
        'type' => $announcementType
    ];
    $_SESSION['announcements'][] = $announcement;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Dashboard</title>

    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/Style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="DashboardFaculty.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
                <span>PAES - Faculty</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="DashboardFaculty.php" class="nav-link active">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="FacultyProfile.php" class="nav-link">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="LandingPage.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- end of navigation -->

    <div class="content" style="margin-top: 100px;">
        <!-- Welcome Section -->
        <div id="Welcome" class="text-center py-5">
            <h1>Welcome, <span id="userName"><?php echo htmlspecialchars($faculty['name']); ?></span>!</h1>
            <p>Manage your courses and track your students' progress here.</p>
        </div>

        <!-- Create Announcement Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">Create Announcement</h2>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="DashboardFaculty.php">
                        <div class="mb-3">
                            <label for="announcementTitle" class="form-label">Title</label>
                            <input type="text" name="announcementTitle" class="form-control" id="announcementTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="announcementDate" class="form-label">Date</label>
                            <input type="date" name="announcementDate" class="form-control" id="announcementDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="announcementType" class="form-label">Type</label>
                            <select name="announcementType" class="form-select" id="announcementType" required>
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Announcement</button>
                    </form>
                </div>
            </div>

            <!-- Announcements List -->
            <ul class="list-group my-4" id="announcementBoard">
                <?php
                if (isset($_SESSION['announcements'])) {
                    foreach ($_SESSION['announcements'] as $announcement) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo htmlspecialchars($announcement['title']);
                        echo '<span class="badge bg-' . htmlspecialchars($announcement['type']) . ' rounded-pill">Date: ' . htmlspecialchars($announcement['date']) . '</span>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>

        <!-- Courses Management Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">Courses Management</h2>
            <button id="add-course-btn" class="btn btn-success mb-3">Add Course</button>
            <div id="coursesList" class="row">
                <!-- Courses will be added dynamically using JavaScript -->
            </div>
        </div>

        <!-- Students Progress Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">Students Progress</h2>
            <div id="studentsProgress" class="row">
                <?php foreach ($faculty['studentsProgress'] as $student): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($student['name']); ?></h5>
                                <p class="card-text">Course: <?php echo htmlspecialchars($student['course']); ?></p>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $student['progress']; ?>%" aria-valuenow="<?php echo $student['progress']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $student['progress']; ?>% Complete</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/dashboardFaculty.js"></script>
</body>
</html>
