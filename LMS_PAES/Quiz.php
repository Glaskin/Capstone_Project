<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/QuizMaker.css" />
</head>
<body>
<!-- Navbar -->
<nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
    <div class="container-fluid">
        <a class="navbar-brand navbar-brand-logo" href="FacultyDashboard.php">
            <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
            <span>PAES - Faculty</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="DashboardTeacher.php" class="nav-link active">Home</a>
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


<!-- Main Content -->
<div class="container mt-5 pt-5">
    <h1>Take a Quiz</h1>

    <?php if (isset($noQuizMessage)): ?>
        <p class="text-muted"><?php echo $noQuizMessage; ?></p>
    <?php else: ?>
        <form action="SubmitQuiz.php" method="POST">
            <?php foreach ($quizzes as $quizTitle => $questions): ?>
                <h2><?php echo htmlspecialchars($quizTitle); ?></h2>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="mb-3">
                        <label><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question); ?></label>
                        <input type="text" class="form-control" name="answers[<?php echo htmlspecialchars($quizTitle); ?>][<?php echo $index; ?>]" required>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Submit Answers</button>
        </form>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-warning text-center py-3 mt-5">
    <p>&copy; 2024 LMS. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
