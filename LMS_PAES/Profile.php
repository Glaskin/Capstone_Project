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
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    </meta>

    <title>PAES : Profile</title>
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

<body>
    <!-- Spinner Start
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-warning navbar-light shadow sticky-top p-0">
        <a href="Dashboard.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <p class="m-0 fw-bold" style="font-size: 25px;"><img src="img/icon.png" alt="" height="50px">PAES<span
                    style="color: #000000;"> REVIEW</span></p>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="Dashboard.php" class="nav-item nav-link">Home</a>
                <a href="Profile.php" class="nav-item nav-link">Profile</a>
                <a href="LandingPage.php" class="nav-item nav-link">Logout</a>
                <a href="#" class="nav-item nav-link">
                    <div id="google_translate_element">
                    </div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Main Profile Section -->
    <main class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                        class="card-img-top rounded-circle mx-auto mt-4" alt="User  Avatar"
                        style="width: 150px; height: 150px;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit
                            Profile</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h3>Profile Details</h3>
                <p class="lead"><?php echo htmlspecialchars($user['bio']); ?></p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </li>
                    <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?>
                    </li>
                    <li class="list-group-item"><strong>Address:</strong>
                        <?php echo htmlspecialchars($user['address']); ?></li>
                </ul>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
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
                            <input type="text" name="userName" class="form-control" id="userNameInput"
                                value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userEmailInput" class="form-label">Email</label>
                            <input type="email" name="userEmail" class="form-control" id="userEmailInput"
                                value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userPhoneInput" class="form-label">Phone</label>
                            <input type="text" name="userPhone" class="form-control" id="userPhoneInput"
                                value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userAddressInput" class="form-label">Address</label>
                            <input type="text" name="userAddress" class="form-control" id="userAddressInput"
                                value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="userBioInput" class="form-label">Bio</label>
                            <textarea name="userBio" class="form-control"
                                id="userBioInput"><?php echo htmlspecialchars($user['bio']); ?></textarea>
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

    <!-- Footer Start -->
    <div class="container-fluid bg-warning text-dark footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy 2024 LMS</a>, All Right Reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

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