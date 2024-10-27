<?php
// Start session to keep track of the current step in the training module
session_start();

// Initialize current step if not set
if (!isset($_SESSION['currentStep'])) {
    $_SESSION['currentStep'] = 0;
}

// Handle form submissions to navigate between steps
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['next'])) {
        $_SESSION['currentStep']++;
    } elseif (isset($_POST['prev'])) {
        $_SESSION['currentStep']--;
    } elseif (isset($_POST['finish'])) {
        $_SESSION['currentStep'] = 0;
        echo "<script>alert('Congratulations! You have completed the training module.'); window.location.href = 'Course.php';</script>";
    }
}

// Ensure the current step stays within bounds
$totalSteps = 4;
$_SESSION['currentStep'] = max(0, min($_SESSION['currentStep'], $totalSteps - 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step-by-Step Training Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/Module.css" />
    <link rel="stylesheet" href="css/Dashboard.css" />
</head>
<body>
    <nav class="navbar fixed-top navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
                <img src="Images/abeLogo.png" alt="PAES Logo" height="40" />
                <span>PAES</span>
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

    <div class="container mt-5 pt-5">
        <?php if ($_SESSION['currentStep'] == 0): ?>
            <div id="step1" class="step-content">
                <h2>Step 1: Introduction</h2>
                <p>Welcome to the first step of your training module. In this module, we will cover the basics of our topic. You'll learn the fundamental concepts and prepare yourself for the more advanced sections to follow.</p>
                <ul>
                    <li>Overview of the training module</li>
                    <li>Goals and objectives</li>
                    <li>How to navigate through the steps</li>
                </ul>
                <form method="POST" action="Lessons.php">
                    <button type="submit" name="next" class="btn btn-primary">Next</button>
                </form>
            </div>
        <?php elseif ($_SESSION['currentStep'] == 1): ?>
            <div id="step2" class="step-content">
                <h2>Step 2: Learning Objectives</h2>
                <p>In this step, you will learn the objectives of the training. Understanding the objectives will help you focus on the key points and ensure you gain the necessary skills by the end of the module.</p>
                <h4>Objectives:</h4>
                <ul>
                    <li>Understand the core concepts</li>
                    <li>Apply knowledge in practical scenarios</li>
                    <li>Evaluate your understanding through assessments</li>
                </ul>
                <form method="POST" action="Lessons.php">
                    <button type="submit" name="prev" class="btn btn-secondary">Previous</button>
                    <button type="submit" name="next" class="btn btn-primary">Next</button>
                </form>
            </div>
        <?php elseif ($_SESSION['currentStep'] == 2): ?>
            <div id="step3" class="step-content">
                <h2>Step 3: Course Content</h2>
                <p>Here is the main content of the training module. This section provides detailed information on the subject matter, including examples and case studies to help you understand the concepts better.</p>
                <h4>Topics Covered:</h4>
                <ul>
                    <li>Topic 1: Introduction to the subject</li>
                    <li>Topic 2: Detailed exploration of key areas</li>
                    <li>Topic 3: Case studies and real-world applications</li>
                </ul>
                <form method="POST" action="Lessons.php">
                    <button type="submit" name="prev" class="btn btn-secondary">Previous</button>
                    <button type="submit" name="next" class="btn btn-primary">Next</button>
                </form>
            </div>
        <?php elseif ($_SESSION['currentStep'] == 3): ?>
            <div id="step4" class="step-content">
                <h2>Step 4: Assessment</h2>
                <p>This step will test your knowledge of the content covered in the previous steps. Answer the following questions to assess your understanding. Your performance will help you identify areas where you may need further review.</p>
                <h4>Questions:</h4>
                <ol>
                    <li>What are the main objectives of this training module?</li>
                    <li>Describe one key concept you learned in this module.</li>
                    <li>How can you apply the knowledge gained in real-world scenarios?</li>
                </ol>
                <form method="POST" action="Lessons.php">
                    <button type="submit" name="prev" class="btn btn-secondary">Previous</button>
                    <button type="submit" name="finish" class="btn btn-success">Finish</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-warning text-center py-3 mt-5">
        <p>&copy; 2024 LMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="bootstrap-offline/js/bootstrap.min.js"></script>
</body>
</html>
