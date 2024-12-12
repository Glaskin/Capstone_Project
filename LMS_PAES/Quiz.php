<?php
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

$user_id = $_SESSION['user_id'];

// Fetch the quiz ID and module ID from the URL
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0; // Get module ID

// Initialize variables
$quizTitle = '';
$questions = [];
$score = null;
$submittedAnswers = [];
$correctAnswers = [];
$originalOrder = [];

// Fetch quiz title and number of questions allowed
if ($quiz_id > 0) {
    // Fetch quiz title and number of questions allowed
    $quizQuery = $pdo->prepare("SELECT title, number_of_questions FROM Quizzes WHERE quiz_id = :quiz_id");
    $quizQuery->execute(['quiz_id' => $quiz_id]);
    $quizData = $quizQuery->fetch(PDO::FETCH_ASSOC);

    if ($quizData) {
        $quizTitle = $quizData['title'];
        $number_of_questions = $quizData['number_of_questions'];
        
        // Fetch questions for the quiz with a limit
        $questionsQuery = $pdo->prepare("SELECT question_id, question_text FROM Questions WHERE quiz_id = :quiz_id LIMIT :limit");
        $questionsQuery->bindValue(':quiz_id', $quiz_id, PDO::PARAM_INT);
        $questionsQuery->bindValue(':limit', $number_of_questions, PDO::PARAM_INT);
        $questionsQuery->execute();

        // Store questions in original order
        while ($row = $questionsQuery->fetch(PDO::FETCH_ASSOC)) {
            $questions[$row['question_id']] = $row; // Store question by ID
            $originalOrder[] = $row['question_id']; // Store the original order of question IDs
        }

        // Shuffle the original order of question IDs for display
        shuffle($originalOrder); // Shuffle the order of question IDs
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedAnswers = $_POST['answers'];
    $score = 0;

    // Fetch correct answers for scoring
    $correctAnswersQuery = $pdo->prepare("SELECT question_id, answer_text FROM Answers WHERE is_correct = TRUE AND question_id IN (SELECT question_id FROM Questions WHERE quiz_id = :quiz_id)");
    $correctAnswersQuery->execute(['quiz_id' => $quiz_id]);
    $correctAnswers = $correctAnswersQuery->fetchAll(PDO::FETCH_KEY_PAIR); // Fetch as key-value pairs

    // Calculate the score
    foreach ($originalOrder as $question_id) {
        if (isset($submittedAnswers[$question_id])) {
            $userAnswer = $submittedAnswers[$question_id];
            if (isset($correctAnswers[$question_id]) && $correctAnswers[$question_id] == $userAnswer) {
                $score++;
            }
        }
    }

    // Save the results in the user_quiz_results table
    $stmt = $pdo->prepare("INSERT INTO user_quiz_results (user_id, quiz_id, score, completed_at) VALUES (:user_id, :quiz_id, :score, NOW())
                            ON DUPLICATE KEY UPDATE score = :score, completed_at = NOW()");
    $stmt->execute([
        'user_id' => $user_id,
        'quiz_id' => $quiz_id,
        'score' => $score
    ]);

    // Update the completion status in the database
    $stmt = $pdo->prepare("INSERT INTO user_quizzes_progress (user_id, quiz_id, completed) VALUES (:user_id, :quiz_id, TRUE) ON DUPLICATE KEY UPDATE completed = TRUE");
    $stmt->execute(['user_id' => $user_id, 'quiz_id' => $quiz_id]);

}

// Fetch correct answers for highlighting after submission
if ($quiz_id > 0 && $score !== null) {
    $correctAnswersQuery = $pdo->prepare("SELECT question_id, answer_text FROM Answers WHERE is_correct = TRUE AND question_id IN (SELECT question_id FROM Questions WHERE quiz_id = :quiz_id)");
    $correctAnswersQuery->execute(['quiz_id' => $quiz_id]);
    $correctAnswers = $correctAnswersQuery->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="google-translate-customization" content="9f841e7780177523-3214ceb76f765f38-gc38c6fe6f9d06436-c">
    <title>PAES : Quizzes</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/icon.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

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
                    <div id="google_translate_element"></div>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->


    <!-- Main Content -->
    <div class="quiz-container" style="margin-bottom: 100px;">
        <div class="container mt-5">
            <div class="quiz">
                <div class="d-flex justify-content-between align-items-center">
                    <h1><?php echo htmlspecialchars($quizTitle); ?></h1>
                    <a href="Module.php?module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Back to Module</a>
                </div>
                <?php if ($score !== null): ?>
                    <h3>Your Score: <?php echo $score; ?> / <?php echo count($questions); ?></h3>
                    <a href="Quiz.php?quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Retake Quiz</a>

                    <h4>Results:</h4>
                    <ul style="margin: 10px">
                        <?php foreach ($originalOrder as $question_id): ?>
                            <?php if (isset($questions[$question_id])): ?>
                                <?php
                                $userAnswer = isset($submittedAnswers[$question_id]) ? $submittedAnswers[$question_id] : 'No answer submitted';
                                ?>
                                <li>
                                    <strong><?php echo ($questions[$question_id]['question_text']); ?></strong><br>
                                    <?php
                                    echo "<span>Your Answer: " . htmlspecialchars($userAnswer) . "</span><br>";
                                    if (isset($correctAnswers[$question_id])): ?>
                                        <span class="text-success">Correct Answer: <?php echo htmlspecialchars($correctAnswers[$question_id]); ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">No Correct Answer Found</span>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <form action="Quiz.php?quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>" method="POST">
                        <?php foreach ($originalOrder as $question_id): ?>
                            <?php if (isset($questions[$question_id])): ?>
                                <div class="mb-3">
                                    <label><strong><?php echo ($questions[$question_id]['question_text']); ?></strong></label><br>
                                    <?php
                                    $answersQuery = $pdo->prepare("SELECT answer_text FROM Answers WHERE question_id = :question_id");
                                    $answersQuery->execute(['question_id' => $question_id]);
                                    $answers = $answersQuery->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($answers as $answer): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answers[<?php echo $question_id; ?>]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                                            <label class="form-check-label"><?php echo htmlspecialchars($answer['answer_text']); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Submit Answers</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
    <div class="container-fluid bg-warning text-dark footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; 2024 LMS, All Rights Reserved.
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