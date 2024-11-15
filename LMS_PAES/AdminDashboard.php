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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // User Management: Add, Edit, Delete
    if (isset($_POST['addUser'])) {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password, 'role' => $role]);
    } elseif (isset($_POST['editUser'])) {
        $userId = $_POST['userId'];
        $username = htmlspecialchars($_POST['editUsername']);
        $email = htmlspecialchars($_POST['editEmail']);

        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $userId]);
    } elseif (isset($_POST['deleteUser'])) {
        $userId = $_POST['userId'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    // Course Management: Add, Delete
    if (isset($_POST['addCourse'])) {
        $courseName = htmlspecialchars($_POST['courseName']);
        $description = htmlspecialchars($_POST['courseDescription']);
        $stmt = $pdo->prepare("INSERT INTO courses (name, description) VALUES (:name, :description)");
        $stmt->execute(['name' => $courseName, 'description' => $description]);
    } elseif (isset($_POST['deleteCourse'])) {
        $courseId = $_POST['courseId'];
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute(['id' => $courseId]);
    }

    // Announcement Management: Add, Delete
    if (isset($_POST['addAnnouncement'])) {
        $title = htmlspecialchars($_POST['announcementTitle']);
        $content = htmlspecialchars($_POST['announcementContent']);
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (:title, :content)");
        $stmt->execute(['title' => $title, 'content' => $content]);
    } elseif (isset($_POST['deleteAnnouncement'])) {
        $announcementId = $_POST['announcementId'];
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
        $stmt->execute(['id' => $announcementId]);
    }
}

// Fetch all users, courses, and announcements
$users = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT id, title, description FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$announcements = $pdo->query("SELECT id, title, content FROM announcements")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Admin Dashboard</h1>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="dashboardTabs">
            <li class="nav-item">
                <a class="nav-link active text-light bg-secondary" id="users-tab" data-bs-toggle="tab"
                    href="#users-section">Manage Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light bg-secondary" id="courses-tab" data-bs-toggle="tab"
                    href="#courses-section">Manage Courses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light bg-secondary" id="announcements-tab" data-bs-toggle="tab"
                    href="#announcements-section">Announcements</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-4">
            <!-- Users Section -->
            <div class="tab-pane fade show active" id="users-section">
                <h2>Manage Users</h2>
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
                    <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
                    <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
                    <select name="role" class="form-select mb-2" required>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                    <button type="submit" name="addUser" class="btn btn-success">Add User</button>
                </form>
                <table class="table table-bordered table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                                <td>
                                    <form method="POST" action="AdminDashboard.php" style="display: inline;">
                                        <input type="hidden" name="userId" value="<?php echo $user['id']; ?>">
                                        <input type="text" name="editUsername" placeholder="New Username"
                                            class="form-control mb-2"
                                            value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        <input type="email" name="editEmail" placeholder="New Email"
                                            class="form-control mb-2"
                                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <button type="submit" name="editUser" class="btn btn-warning btn-sm">Save</button>
                                        <button type="submit" name="deleteUser"
                                            class="btn btn-danger btn-sm">Delete</button>
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
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="courseName" placeholder="Course Name" class="form-control mb-2" required>
                    <textarea name="courseDescription" placeholder="Course Description" class="form-control mb-2"
                        rows="2" required></textarea>
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
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['description']); ?></td>
                                <td>
                                    <form method="POST" action="AdminDashboard.php" style="display: inline;">
                                        <input type="hidden" name="courseId" value="<?php echo $course['id']; ?>">
                                        <button type="submit" name="deleteCourse"
                                            class="btn btn-danger btn-sm">Delete</button>
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
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="announcementTitle" placeholder="Announcement Title"
                        class="form-control mb-2" required>
                    <textarea name="announcementContent" placeholder="Announcement Content" class="form-control mb-2"
                        rows="3" required></textarea>
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
                        <?php foreach ($announcements as $announcement): ?>
                            <tr>
                                <td><?php echo $announcement['id']; ?></td>
                                <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                                <td><?php echo htmlspecialchars($announcement['content']); ?></td>
                                <td>
                                    <form method="POST" action="AdminDashboard.php" style="display: inline;">
                                        <input type="hidden" name="announcementId"
                                            value="<?php echo $announcement['id']; ?>">
                                        <button type="submit" name="deleteAnnouncement"
                                            class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
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