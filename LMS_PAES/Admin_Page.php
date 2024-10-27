<?php
// Start a session to manage data
session_start();

// Initialize mock data for users, courses, and announcements if not set
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        ['id' => 1, 'username' => 'john_doe', 'email' => 'john@example.com'],
        ['id' => 2, 'username' => 'jane_doe', 'email' => 'jane@example.com'],
    ];
}

if (!isset($_SESSION['courses'])) {
    $_SESSION['courses'] = [
        ['id' => 1, 'name' => 'Cloud Basics', 'description' => 'Introduction to Cloud Concepts'],
        ['id' => 2, 'name' => 'JavaScript 101', 'description' => 'Learn the basics of JavaScript'],
    ];
}

if (!isset($_SESSION['announcements'])) {
    $_SESSION['announcements'] = [];
}

if (!isset($_SESSION['auditLog'])) {
    $_SESSION['auditLog'] = [
        ['timestamp' => '2023-02-20 14:30:00', 'user' => 'admin', 'action' => 'Added new user', 'details' => 'User ID: 12345'],
        ['timestamp' => '2023-02-19 10:45:00', 'user' => 'moderator', 'action' => 'Updated course', 'details' => 'Course ID: 67890'],
    ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addUser'])) {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $_SESSION['users'][] = ['id' => count($_SESSION['users']) + 1, 'username' => $username, 'email' => $email];
    } elseif (isset($_POST['deleteUser'])) {
        $userId = $_POST['userId'];
        $_SESSION['users'] = array_filter($_SESSION['users'], function($user) use ($userId) {
            return $user['id'] != $userId;
        });
        $_SESSION['users'] = array_values($_SESSION['users']);
    } elseif (isset($_POST['addCourse'])) {
        $courseName = htmlspecialchars($_POST['courseName']);
        $description = htmlspecialchars($_POST['courseDescription']);
        $_SESSION['courses'][] = ['id' => count($_SESSION['courses']) + 1, 'name' => $courseName, 'description' => $description];
    } elseif (isset($_POST['deleteCourse'])) {
        $courseId = $_POST['courseId'];
        $_SESSION['courses'] = array_filter($_SESSION['courses'], function($course) use ($courseId) {
            return $course['id'] != $courseId;
        });
        $_SESSION['courses'] = array_values($_SESSION['courses']);
    } elseif (isset($_POST['addAnnouncement'])) {
        $title = htmlspecialchars($_POST['announcementTitle']);
        $content = htmlspecialchars($_POST['announcementContent']);
        $_SESSION['announcements'][] = ['id' => count($_SESSION['announcements']) + 1, 'title' => $title, 'content' => $content];
    } elseif (isset($_POST['deleteAnnouncement'])) {
        $announcementId = $_POST['announcementId'];
        $_SESSION['announcements'] = array_filter($_SESSION['announcements'], function($announcement) use ($announcementId) {
            return $announcement['id'] != $announcementId;
        });
        $_SESSION['announcements'] = array_values($_SESSION['announcements']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dark Scheme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-page.css">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1>Admin Dashboard</h1>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="dashboardTabs">
            <li class="nav-item">
                <a class="nav-link active text-light bg-secondary" id="users-tab" data-bs-toggle="tab" href="#users-section">Manage Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light bg-secondary" id="courses-tab" data-bs-toggle="tab" href="#courses-section">Manage Courses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light bg-secondary" id="announcement-tab" data-bs-toggle="tab" href="#announcements-section">Announcement</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light bg-secondary" id="auditlog-tab" data-bs-toggle="tab" href="#auditlog-section">Audit Log</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-4">
            <!-- Users Section -->
            <div class="tab-pane fade show active" id="users-section">
                <h2>Manage Users</h2>
                <form method="POST" action="AdminPage.php" class="mb-3">
                    <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
                    <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
                    <button type="submit" name="addUser" class="btn btn-success">Add User</button>
                </form>
                <table class="table table-bordered table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['users'] as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form method="POST" action="AdminPage.php" style="display:inline;">
                                        <input type="hidden" name="userId" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="deleteUser" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Courses Section -->
            <div class="tab-pane fade" id="courses-section">
                <h2>Manage Courses</h2>
                <form method="POST" action="AdminPage.php" class="mb-3">
                    <input type="text" name="courseName" placeholder="Course Name" class="form-control mb-2" required>
                    <textarea name="courseDescription" placeholder="Course Description" class="form-control mb-2" rows="2" required></textarea>
                    <button type="submit" name="addCourse" class="btn btn-success">Add Course</button>
                </form>
                <table class="table table-bordered table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['courses'] as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                <td><?php echo htmlspecialchars($course['description']); ?></td>
                                <td>
                                    <form method="POST" action="AdminPage.php" style="display:inline;">
                                        <input type="hidden" name="courseId" value="<?php echo $course['id']; ?>">
                                        <button type="submit" name="deleteCourse" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Announcements Section -->
            <div class="tab-pane fade" id="announcements-section">
                <h2>Manage Announcements</h2>
                <form method="POST" action="AdminPage.php" class="mb-3">
                    <input type="text" name="announcementTitle" placeholder="Announcement Title" class="form-control mb-2" required>
                    <textarea name="announcementContent" placeholder="Announcement Content" class="form-control mb-2" rows="3" required></textarea>
                    <button type="submit" name="addAnnouncement" class="btn btn-success">Post Announcement</button>
                </form>
                <table class="table table-bordered table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['announcements'] as $announcement): ?>
                            <tr>
                                <td><?php echo $announcement['id']; ?></td>
                                <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                                <td><?php echo htmlspecialchars($announcement['content']); ?></td>
                                <td>
                                    <form method="POST" action="AdminPage.php" style="display:inline;">
                                        <input type="hidden" name="announcementId" value="<?php echo $announcement['id']; ?>">
                                        <button type="submit" name="deleteAnnouncement" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Audit Log Section -->
            <div class="tab-pane fade" id="auditlog-section">
                <h2>Audit Log</h2>
                <table class="table table-bordered table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['auditLog'] as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($log['user']); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['details']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
