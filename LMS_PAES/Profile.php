<?php
// Start a session and include the database connection
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

// Verify the user is logged in and is a student
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || $user_role !== 'student') {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a student
    exit();
}

// Fetch user details from the users and student_details tables
$stmt = $pdo->prepare("SELECT u.username AS name, u.email, sd.phone, sd.address, sd.bio, sd.avatar
                       FROM users u
                       LEFT JOIN student_details sd ON u.id = sd.student_id
                       WHERE u.id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default avatar if none is present
$user['avatar'] = $user['avatar'] ?? 'img/user-avatar.jpg';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and assign user data
    $user['name'] = htmlspecialchars($_POST['userName']);
    $user['email'] = htmlspecialchars($_POST['userEmail']);
    $user['phone'] = htmlspecialchars($_POST['userPhone']);
    $user['address'] = htmlspecialchars($_POST['userAddress']);
    $user['bio'] = htmlspecialchars($_POST['userBio']);

    // Handle file upload for avatar
    if (isset($_FILES['userAvatar']) && $_FILES['userAvatar']['error'] === UPLOAD_ERR_OK) {
        $avatarDir = 'img/avatars/';
        $avatarPath = $avatarDir . basename($_FILES['userAvatar']['name']);
        
        if (move_uploaded_file($_FILES['userAvatar']['tmp_name'], $avatarPath)) {
            $user['avatar'] = $avatarPath;
        }
    }

    // Update the users table for name and email
    $stmt = $pdo->prepare("UPDATE users SET username = :name, email = :email WHERE id = :user_id");
    $stmt->execute([
        'name' => $user['name'],
        'email' => $user['email'],
        'user_id' => $user_id
    ]);

    // Check if student details already exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_details WHERE student_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Update existing record in student_details
        $stmt = $pdo->prepare("UPDATE student_details SET phone = :phone, address = :address, bio = :bio, avatar = :avatar WHERE student_id = :user_id");
    } else {
        // Insert new record into student_details
        $stmt = $pdo->prepare("INSERT INTO student_details (student_id, phone, address, bio, avatar) VALUES (:user_id, :phone, :address, :bio, :avatar)");
    }
    
    // Execute the insert or update
    $stmt->execute([
        'user_id' => $user_id,
        'phone' => $user['phone'],
        'address' => $user['address'],
        'bio' => $user['bio'],
        'avatar' => $user['avatar']
    ]);

    // Store updated data in session
    $_SESSION['user'] = $user;
    header("Location: Profile.php"); // Refresh to show updated info
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Profile Page</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/Style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand" href="Dashboard.php">PAES REVIEWER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="Dashboard.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="#" class="nav-link active">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" class="card-img-top rounded-circle mx-auto mt-4" alt="User Avatar" style="width: 150px; height: 150px;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h3>Profile Details</h3>
                <p class="lead"><?php echo htmlspecialchars($user['bio']); ?></p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                    <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></li>
                    <li class="list-group-item"><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="Profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="userNameInput" class="form-label">Name</label>
                            <input type="text" name="userName" class="form-control" id="userNameInput" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userEmailInput" class="form-label">Email</label>
                            <input type="email" name="userEmail" class="form-control" id="userEmailInput" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userPhoneInput" class="form-label">Phone</label>
                            <input type="text" name="userPhone" class="form-control" id="userPhoneInput" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userAddressInput" class="form-label">Address</label>
                            <input type="text" name="userAddress" class="form-control" id="userAddressInput" value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userBioInput" class="form-label">Bio</label>
                            <textarea name="userBio" class="form-control" id="userBioInput"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="userAvatarInput" class="form-label">Profile Picture</label>
                            <input type="file" name="userAvatar" class="form-control" id="userAvatarInput">
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-warning text-center py-3 mt-5">
        <p>&copy; 2024 LMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
