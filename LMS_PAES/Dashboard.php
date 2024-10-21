<?php
// Assuming you're fetching user data from a database


$user = [
    'name' => 'John Doe',
    'recentActivity' => 'You recently completed Module 3: Introduction to JavaScript.',
    'nextLesson' => 'Start Module 4: Advanced JavaScript Techniques.',
    'courses' => [
        [
            'title' => 'Course Title 1',
            'description' => 'Short description of the course.',
            'progress' => 41,
            'image' => 'img/course-image-1.jpg',
        ],
        [
            'title' => 'Course Title 2',
            'description' => 'Short description of the course.',
            'progress' => 15,
            'image' => 'img/course-image-2.jpg',
        ],
        [
            'title' => 'Course Title 3',
            'description' => 'Short description of the course.',
            'progress' => 70,
            'image' => 'img/course-image-3.jpg',
        ],
    ],
    'announcements' => [
        [
            'title' => 'System Maintenance',
            'date' => 'Oct 16',
            'type' => 'info',
        ],
        [
            'title' => 'New Course Available: Advanced CSS',
            'date' => 'Oct 18',
            'type' => 'success',
        ],
        [
            'title' => 'Holiday Schedule Update',
            'date' => 'Oct 23',
            'type' => 'warning',
        ],
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/Style.css">
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
                        <a href="Dashboard.php" class="nav-link active">Home</a>
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

    <div class="content" style="margin-top: 100px;">
        <!-- Welcome Section -->
        <div id="Welcome" class="text-center py-5">
            <h1>Welcome, <span id="userName"><?php echo htmlspecialchars($user['name']); ?></span>!</h1>
            <p>Manage your courses and keep track of your progress here.</p>
        </div>

        <!-- Recent Activities and Next Lesson Section -->
        <div class="row mx-4">
            <div class="col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <p class="card-text"><?php echo htmlspecialchars($user['recentActivity']); ?></p>
                        <a href="Course.php" class="btn btn-primary">Continue Learning</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Next Lesson</h5>
                        <p class="card-text"><?php echo htmlspecialchars($user['nextLesson']); ?></p>
                        <a href="#" class="btn btn-primary">Go to Lesson</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">My Courses</h2>
            <div class="row" id="courseList">
                <!-- Course Cards -->
                <?php foreach ($user['courses'] as $course) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($course['image']); ?>" class="card-img-top" alt="Course Image" height="236">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $course['progress']; ?>%" aria-valuenow="<?php echo $course['progress']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $course['progress']; ?>% Complete</div>
                                </div>
                                <a href="Course.php" class="btn btn-primary"><?php echo $course['progress'] === 0 ? 'Start' : 'Continue'; ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Announcement Board Section -->
        <div class="container my-5">
            <h2 class="text-center mb-4">Announcement Board</h2>
            <ul class="list-group" id="announcementBoard">
                <!-- Announcements -->
                <?php foreach ($user['announcements'] as $announcement) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                        <span class="badge bg-<?php echo htmlspecialchars($announcement['type']); ?> rounded-pill">Date: <?php echo htmlspecialchars($announcement['date']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Footer -->
        <footer class="bg-warning text-center py-3 mt-5">
            <p>&copy; 2024 LMS. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
