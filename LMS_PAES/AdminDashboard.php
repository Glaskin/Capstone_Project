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
    if (isset($_POST['addUser'])) {
        // Add User logic
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password, 'role' => $role]);
        $activeTab = 'users';
    } elseif (isset($_POST['editUser'])) {
        // Edit User logic
        $userId = $_POST['userId'];
        $newUsername = $_POST['editUsername'];
        $newEmail = $_POST['editEmail'];
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->execute(['username' => $newUsername, 'email' => $newEmail, 'id' => $userId]);
        $activeTab = 'users';
    } elseif (isset($_POST['deleteUser'])) {
        $userId = $_POST['userId'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $activeTab = 'users';
    } elseif (isset($_POST['addCourse'])) {
        // Add Course logic
        $courseName = $_POST['courseName'];
        $courseDescription = $_POST['courseDescription'];
        $stmt = $pdo->prepare("INSERT INTO courses (title, description) VALUES (:title, :description)");
        $stmt->execute(['title' => $courseName, 'description' => $courseDescription]);
        $activeTab = 'courses';
    } elseif (isset($_POST['editCourse'])) {
        // Edit Course logic
        $courseId = $_POST['courseId'];
        $courseName = $_POST['editCourseName'];
        $courseDescription = $_POST['editCourseDescription'];
        $stmt = $pdo->prepare("UPDATE courses SET title = :title, description = :description WHERE id = :id");
        $stmt->execute(['title' => $courseName, 'description' => $courseDescription, 'id' => $courseId]);
        $activeTab = 'courses';
    } elseif (isset($_POST['deleteCourse'])) {
        $courseId = $_POST['courseId'];
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute(['id' => $courseId]);
        $activeTab = 'courses';
    } elseif (isset($_POST['addAnnouncement'])) {
        // Add Announcement logic
        $announcementTitle = $_POST['announcementTitle'];
        $announcementContent = $_POST['announcementContent'];
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (:title, :content)");
        $stmt->execute(['title' => $announcementTitle, 'content' => $announcementContent]);
        $activeTab = 'announcement';
    } elseif (isset($_POST['deleteAnnouncement'])) {
        $announcementId = $_POST['announcementId'];
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
        $stmt->execute(['id' => $announcementId]);
        $activeTab = 'announcement';
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
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>PAES : Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/icon.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style2.css">


</head>

<body class="bg-warning text-light">

    <!-- Spinner Start 
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
     Spinner End -->

    <div class="container mt-5">
        <h1 class="text-center mb-4">Admin Dashboard</h1>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="dashboardTabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'users' ? 'active text-light bg-secondary' : 'text-light bg-secondary'; ?>"
                    id="users-tab" data-bs-toggle="tab" href="#users-section">Manage Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'courses' ? 'active text-light bg-secondary' : 'text-light bg-secondary'; ?>"
                    id="courses-tab" data-bs-toggle="tab" href="#courses-section">Manage Courses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'announcements' ? 'active text-light bg-secondary' : 'text-light bg-secondary'; ?>"
                    id="announcements-tab" data-bs-toggle="tab" href="#announcements-section">Announcements</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-4">
            <!-- Users Section -->
            <div class="tab-pane fade <?php echo $activeTab === 'users' ? 'show active' : ''; ?>" id="users-section">
                <h2>Manage Users</h2>
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
                    <input type="email" name="email" placeholder="Email" class="form-control mb ```php
                    -2" required>
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
            <div class="tab-pane fade <?php echo $activeTab === 'courses' ? 'show active' : ''; ?>"
                id="courses-section">
                <h2>Manage Courses</h2>

                <!-- Add Course Form -->
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="courseName" placeholder="Course Name" class="form-control mb-2" required>
                    <textarea name="courseDescription" placeholder="Course Description" class="form-control mb-2"
                        rows="2" required></textarea>
                    <button type="submit" name="addCourse" class="btn btn-success">Add Course</button>
                </form>

                <!-- Courses Table -->
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
                                        <input type="text" name="editCourseName" placeholder="New Course Name"
                                            class="form-control mb-2"
                                            value="<?php echo htmlspecialchars($course['title']); ?>" required>
                                        <textarea name="editCourseDescription" placeholder="New Course Description"
                                            class="form-control mb-2" rows="2"
                                            required><?php echo htmlspecialchars($course['description']); ?></textarea>
                                        <button type="submit" name="editCourse" class="btn btn-warning btn-sm">Save</button>
                                        <button type="submit" name="deleteCourse"
                                            class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    <a href="ManageCourse.php?course_id=<?php echo $course['id']; ?>"
                                        class="btn btn-info btn-sm mt-2">Edit Content</a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Announcements Section -->
            <div class="tab-pane fade <?php echo $activeTab === 'announcements' ? 'show active' : ''; ?>"
                id="announcements-section">
                <h2>Manage Announcements</h2>
                <form method="POST" action="AdminDashboard.php" class="mb-3">
                    <input type="text" name="announcementTitle" placeholder="Announcement Title"
                        class="form-control mb-2" required>
                    <textarea name="announcementContent" placeholder="Announcement Content" class="form-control mb-2"
                        rows="3" required></textarea>
                    <button type="submit" name="addAnnouncement" class="btn btn-success">Post Announcement</button>
                </form>
                <table class="table table-bordered ```php
                table-dark table-striped">
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

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>