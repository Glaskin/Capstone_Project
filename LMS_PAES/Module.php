<?php
// Start session and connect to the database
session_start();

$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pogi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if module_id is provided in the URL
$module_id = $_GET['module_id'] ?? null;
if (!$module_id) {
    die("Module ID is required.");
}

// Fetch module details
$stmt = $pdo->prepare("SELECT title FROM modules WHERE id = :module_id");
$stmt->execute(['module_id' => $module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    die("Module not found.");
}

// Fetch lessons associated with this module (title and type only)
$stmt = $pdo->prepare("SELECT id, title, type FROM lessons WHERE module_id = :module_id ORDER BY position ASC");
$stmt->execute(['module_id' => $module_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
    <title><?php echo htmlspecialchars($module['title']); ?> - Module</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .module-card {
            background-color: #f7f7f9;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 10px;
        }

        .lesson-card {
            border: none;
            border-radius: 5px;
            margin-bottom: 15px;
            transition: box-shadow 0.2s ease;
        }

        .lesson-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .footer {
            background-color: #ffc107;
            color: #333;
            padding: 15px;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="Dashboard.php">ELMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="Dashboard.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="Profile.php" class="nav-link">Profile</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Module Content -->
    <div class="container mt-5" style="margin-top: 100px;">
        <!-- Module Title Section -->
        <div class="module-card text-center mb-5 p-4">
            <h2 class="mb-3"><?php echo htmlspecialchars($module['title']); ?></h2>
            <p class="text-muted">Explore the lessons within this module to deepen your knowledge.</p>
        </div>

        <!-- Lessons List -->
        <div class="row">
            <?php foreach ($lessons as $lesson): ?>
                <div class="col-md-6 mb-4">
                    <div class="card lesson-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                            <p class="card-text text-muted">Type: <?php echo htmlspecialchars($lesson['type']); ?></p>
                            <a href="Lessons.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn btn-primary">Start
                                Lesson</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer text-center mt-5">
        <p>&copy; 2024 ELMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>