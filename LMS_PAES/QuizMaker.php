<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pass';

// Ensure only faculty and admin can access this page
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($user_role, ['faculty', 'admin'])) {
    header("Location: LandingPage.php"); // Redirect if not logged in or not a faculty or admin member
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Fetch the specific quiz based on quiz_id
$quiz_id = $_GET['quiz_id'] ?? null;
if ($quiz_id) {
    $stmt = $conn->prepare("SELECT * FROM Quizzes WHERE quiz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch questions and answers for the specific quiz
    $questions = $conn->prepare("SELECT * FROM Questions WHERE quiz_id = :quiz_id");
    $questions->execute(['quiz_id' => $quiz_id]);
    $quiz_questions = $questions->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch answers for each question
    $answers = [];
    foreach ($quiz_questions as $question) {
        $stmt = $conn->prepare("SELECT * FROM Answers WHERE question_id = :question_id");
        $stmt->execute(['question_id' => $question['question_id']]);
        $answers[$question['question_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    echo "No quiz selected.";
    exit();
}

// Handle quiz deletion
if (isset($_GET['delete_quiz'])) {
    $stmt = $conn->prepare("DELETE FROM Quizzes WHERE quiz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    header("Location: QuizMaker.php");
    exit();
}

// Handle question deletion
if (isset($_GET['delete_question'])) {
    $stmt = $conn->prepare("DELETE FROM Questions WHERE question_id = :question_id");
    $stmt->execute(['question_id' => $_GET['delete_question']]);
    header("Location: QuizMaker.php?quiz_id=" . $quiz_id);
    exit();
}

// Handle form submission to save a new or edited question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_text'])) {
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type']; // Get the question type (MC or TF)
    $question_id = $_POST['question_id'] ?? null; // Get the question ID if editing

    try {
        // If editing an existing question
        if ($question_id) {
            // Update existing question
            $stmt = $conn->prepare("UPDATE Questions SET question_text = :question_text WHERE question_id = :question_id");
            $stmt->execute(['question_text' => $question_text, 'question_id' => $question_id]);

            // Delete existing answers
            $stmt = $conn->prepare("DELETE FROM Answers WHERE question_id = :question_id");
            $stmt->execute(['question_id' => $question_id]);
        } else {
            // Insert new question
            $stmt = $conn->prepare("INSERT INTO Questions (quiz_id, question_text) VALUES (:quiz_id, :question_text)");
            $stmt->execute(['quiz_id' => $quiz_id, 'question_text' => $question_text]);
            $question_id = $conn->lastInsertId();
        }

        // Handle answers based on question type
        if ($question_type == 'multiple_choice') {
            $correct_answer_found = false; // Ensure only one correct answer is allowed

            foreach ($_POST['answers'] as $answer) {
                $answer_text = $answer['text'];
                $is_correct = isset($answer['is_correct']) ? 1 : 0;

                // Allow only one correct answer
                if ($is_correct && !$correct_answer_found) {
                    $correct_answer_found = true; // Mark the first correct answer
                } else {
                    $is_correct = 0; // Ensure others are marked as incorrect
                }

                $stmt = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
                $stmt->execute([
                    'question_id' => $question_id,
                    'answer_text' => $answer_text,
                    'is_correct' => $is_correct
                ]);
            }
        } elseif ($question_type == 'true_false') {
            // For true/false, we only need two answers
            $true_answer = $_POST['true_answer'] ?? '';
            $false_answer = $_POST['false_answer'] ?? '';

            // Insert true answer
            $stmt = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 1)");
            $stmt->execute(['question_id' => $question_id, 'answer_text' => $true_answer]);

            // Insert false answer
            $stmt = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 0)");
            $stmt->execute(['question_id' => $question_id, 'answer_text' => $false_answer]);
        }

        header("Location: QuizMaker.php?quiz_id=" . $quiz_id);
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Check if editing a question
$editing_question_id = $_GET['edit_question'] ?? null;
$editing_question = null;
$editing_answers = [];

if ($editing_question_id) {
    $stmt = $conn->prepare("SELECT * FROM Questions WHERE question_id = :question_id");
    $stmt->execute(['question_id' => $editing_question_id]);
    $editing_question = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM Answers WHERE question_id = :question_id");
    $stmt->execute(['question_id' => $editing_question_id]);
    $editing_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleAnswerFields() {
            const questionType = document.getElementById('question_type').value;
            const answersContainer = document.getElementById('answers-container');
            answersContainer.innerHTML = '';

            if (questionType === 'multiple_choice') {
                for (let i = 0; i < 3; i++) {
                    answersContainer.innerHTML += `
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="answers[${i}][text]" placeholder="Answer ${i + 1}" required>
                            <input type="checkbox" class="form-check-input" name="answers[${i}][is_correct]" value="1"> Correct
                        </div>`;
                }
            } else if (questionType === 'true_false') {
                answersContainer.innerHTML += `
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="true_answer" placeholder="Answer 1" required>
                        <input type="checkbox" class="form-check-input" name="true_correct" value="1" checked> Correct
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="false_answer" placeholder="Answer 2" required>
                        <input type="checkbox" class="form-check-input" name="false_correct" value="0"> Correct
                    </div>`;
            }
        }

        window.onload = function() {
            toggleAnswerFields();
        };
    </script>
</head>
<body>
<nav class="navbar navbar-expand-sm navbar-light bg-warning">
    <div class="container-fluid">
        <a class="navbar-brand" href="FacultyDashboard.php">PAES - Faculty</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="DashboardTeacher.php" class="nav-link active">Home</a></li>
                <li class="nav-item"><a href="Profile.php" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>

    <h2 class="mt-5">Questions</h2>
    <ul class ="list-group">
        <?php foreach ($quiz_questions as $question): ?>
            <li class="list-group-item">
                <?php echo htmlspecialchars($question['question_text']); ?>
                <a href="?delete_question=<?php echo $question['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-danger btn-sm float-end" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                <a href="?quiz_id=<?php echo $quiz_id; ?>&edit_question=<?php echo $question['question_id']; ?>" class="btn btn-warning btn-sm float-end me-2">Edit</a>
                <ul>
                    <?php foreach ($answers[$question['question_id']] as $answer): ?>
                        <li><?php echo htmlspecialchars($answer['answer_text']); ?> (<?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2 class="mt-5"><?php echo $editing_question ? 'Edit Question' : 'Add New Question'; ?></h2>
    <form method="POST" action="QuizMaker.php?quiz_id=<?php echo $quiz_id; ?>">
        <input type="hidden" name="question_id" value="<?php echo $editing_question ? $editing_question['question_id'] : ''; ?>">
        <div class="mb-3">
            <label for="question_text" class="form-label">Question Text</label>
            <input type="text" class="form-control" id="question_text" name="question_text" value="<?php echo $editing_question ? htmlspecialchars($editing_question['question_text']) : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label for="question_type" class="form-label">Question Type</label>
            <select class="form-select" id="question_type" name="question_type" onchange="toggleAnswerFields()" required>
                <option value="multiple_choice" <?php echo $editing_question && $editing_answers ? 'selected' : ''; ?>>Multiple Choice</option>
                <option value="true_false" <?php echo $editing_question && !$editing_answers ? 'selected' : ''; ?>>True/False</option>
            </select>
        </div>
        <div id="answers-container" class="mb-3">
            <?php if ($editing_question && $editing_answers): ?>
                <?php if (count($editing_answers) == 2): // True/False ?>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="true_answer" value="<?php echo htmlspecialchars($editing_answers[0]['answer_text']); ?>" placeholder="True Answer" required>
                        <input type="checkbox" class="form-check-input" name="true_correct" value="1" <?php echo $editing_answers[0]['is_correct'] ? 'checked' : ''; ?>> Correct
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="false_answer" value="<?php echo htmlspecialchars($editing_answers[1]['answer_text']); ?>" placeholder="False Answer" required>
                        <input type="checkbox" class="form-check-input" name="false_correct" value="0" <?php echo !$editing_answers[1]['is_correct'] ? 'checked' : ''; ?>> Correct
                    </div>
                <?php else: // Multiple Choice ?>
                    <?php foreach ($editing_answers as $index => $answer): ?>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="answers[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" placeholder="Answer <?php echo $index + 1; ?>" required>
                            <input type="checkbox" class="form-check-input" name="answers[<?php echo $index; ?>][is_correct]" value="1" <?php echo $answer['is_correct'] ? 'checked' : ''; ?>> Correct
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="answers[0][text]" placeholder="Answer 1" required>
                    <input type="checkbox" class="form-check-input" name="answers[0][is_correct]" value="1"> Correct
                </div>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="answers[1][text ```php]" placeholder="Answer 2" required>
                    <input type="checkbox" class="form-check-input" name="answers[1][is_correct]" value="1"> Correct
                </div>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="answers[2][text]" placeholder="Answer 3">
                    <input type="checkbox" class="form-check-input" name="answers[2][is_correct]" value="1"> Correct
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $editing_question ? 'Update Question' : 'Add Question'; ?></button>
    </form>
</div>
</body>
</html>