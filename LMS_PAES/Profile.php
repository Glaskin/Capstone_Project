<?php
// Start a session to manage user data if needed
session_start();

// Placeholder user data (you can replace this with data from a database)
$user = [
    'name' => 'User Name',
    'email' => 'user@example.com',
    'phone' => '(123) 456-7890',
    'address' => '123 Main St, Bacoor, Cavite',
    'bio' => 'This is a brief bio about the user. It provides background information, interests, and any other relevant details.',
    'avatar' => 'img/user-avatar.jpg'
];

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user['name'] = htmlspecialchars($_POST['userName']);
    $user['email'] = htmlspecialchars($_POST['userEmail']);
    $user['phone'] = htmlspecialchars($_POST['userPhone']);
    $user['address'] = htmlspecialchars($_POST['userAddress']);
    $user['bio'] = htmlspecialchars($_POST['userBio']);
    // Note: Profile picture handling should include proper file upload handling (not shown here)
    $_SESSION['user'] = $user;
} elseif (isset($_SESSION['user'])) {
    // Restore user data from session if available
    $user = $_SESSION['user'];
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
            <a class="navbar-brand" href="Dashboard.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
                PAES
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="Dashboard.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link active">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="LandingPage.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" class="card-img-top rounded-circle mx-auto mt-4" alt="User Avatar" id="avatar" style="width: 150px; height: 150px;">
                    <div class="card-body text-center">
                        <h5 class="card-title" id="userName"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="card-text text-muted" id="userEmail"><?php echo htmlspecialchars($user['email']); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h3>Profile Details</h3>
                <p class="lead" id="userBio"><?php echo htmlspecialchars($user['bio']); ?></p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Email:</strong> <span id="emailDetail"><?php echo htmlspecialchars($user['email']); ?></span></li>
                    <li class="list-group-item"><strong>Phone:</strong> <span id="phoneDetail"><?php echo htmlspecialchars($user['phone']); ?></span></li>
                    <li class="list-group-item"><strong>Address:</strong> <span id="addressDetail"><?php echo htmlspecialchars($user['address']); ?></span></li>
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
    <script src="js/Script.js"></script>
</body>
</html>
