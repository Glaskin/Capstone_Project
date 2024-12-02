<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pass';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Connection failed: " . $e->getMessage());
}

// Ensure only faculty and admin can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    header("Location: LandingPage.php");
    exit();
}

// Fetch module_id from the URL
$module_id = $_GET['module_id'] ?? null;
if (!$module_id) {
    exit("No module selected.");
}

// Fetch quiz based on quiz_id
$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) {
    exit("No quiz selected.");
}
$quiz = $conn->prepare("SELECT * FROM Quizzes WHERE quiz_id = :quiz_id");
$quiz->execute(['quiz_id' => $quiz_id]);
$quiz = $quiz->fetch(PDO::FETCH_ASSOC);

// Debugging: Check if quiz was fetched
if (!$quiz) {
    exit("Quiz not found.");
}

// Fetch questions for the specific quiz
$quiz_questions = $conn->prepare("SELECT * FROM Questions WHERE quiz_id = :quiz_id");
$quiz_questions->execute(['quiz_id' => $quiz_id]);
$quiz_questions = $quiz_questions->fetchAll(PDO::FETCH_ASSOC);

// Initialize answers array
$answers = [];

// Fetch answers for each question
if (!empty($quiz_questions)) {
    foreach ($quiz_questions as $question) {
        $stmt = $conn->prepare("SELECT * FROM Answers WHERE question_id = :question_id");
        $stmt->execute(['question_id' => $question['question_id']]);
        $answers[$question['question_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    echo "No questions found for this quiz.";
}

// Handle deletions
if (isset($_GET['delete_quiz'])) {
    $stmt = $conn->prepare("DELETE FROM Quizzes WHERE quiz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    header("Location: QuizMaker.php?quiz_id=$quiz_id&module_id=$module_id");
    exit();
}

if (isset($_GET['delete_question'])) {
    $stmt = $conn->prepare("DELETE FROM Questions WHERE question_id = :question_id");
    $stmt->execute(['question_id' => $_GET['delete_question']]);
    header("Location: QuizMaker.php?quiz_id=$quiz_id&module_id=$module_id");
    exit();
}

// Handle form submission to save a new or edited question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'] ?? '';
    $question_type = $_POST['question_type'] ?? null;
    $action = $_POST['action'] ?? null;
    $question_id = $_POST['question_id'] ?? null;

    // Debugging: Print posted data
    echo "<pre>";
    echo "POST Data: ";
    print_r($_POST);
    echo "</pre>";

    try {
        if ($action === 'add') {
            // Check if question text is empty
            if (empty($question_text)) {
                echo "Question text cannot be empty.";
                return; // Stop execution
            }

            // Insert new question
            $stmt = $conn->prepare("INSERT INTO Questions (quiz_id, question_text) VALUES (:quiz_id, :question_text)");
            $stmt->execute(['quiz_id' => $quiz_id, 'question_text' => $question_text]);
            $question_id = $conn->lastInsertId();

            // Debugging: Confirm question insertion
            echo "New question added with ID: $question_id";

            // Insert answers based on question type
            if ($question_type === 'multiple_choice') {
                foreach ($_POST['answers'] as $answer) {
                    if (!empty($answer['text'])) { // Ensure answer text is not empty
                        $stmt = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
                        $stmt->execute(['question_id' => $question_id, 'answer_text' => $answer['text'], 'is_correct' => isset($answer['is_correct']) ? 1 : 0]);
                    } else {
                        echo "Answer text cannot be empty.";
                    }
                }
            } elseif ($question_type === 'true_false') {
                $true_answer = $_POST['true_answer'] ?? '';
                $false_answer = $_POST['false_answer'] ?? '';

                if (!empty($true_answer)) {
                    $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 1)")
                        ->execute(['question_id' => $question_id, 'answer_text' => $true_answer]);
                } else {
                    echo "True answer cannot be empty.";
                }

                if (!empty($false_answer)) {
                    $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 0)")
                        ->execute(['question_id' => $question_id, 'answer_text' => $false_answer]);
                } else {
                    echo "False answer cannot be empty.";
                }
            }

            header("Location: QuizMaker.php?quiz_id=$quiz_id&module_id=$module_id");
            exit();
        } elseif ($action === 'update' && $question_id) {
            // Update existing question
            $stmt = $conn->prepare("UPDATE Questions SET question_text = :question_text WHERE question_id = :question_id");
            $stmt->execute(['question_text' => $question_text, 'question_id' => $question_id]);

            // Clear existing answers if updating
            $conn->prepare("DELETE FROM Answers WHERE question_id = :question_id")->execute(['question_id' => $question_id]);

            // Insert new answers
            if ($question_type === 'multiple_choice') {
                foreach ($_POST['answers'] as $answer) {
                    if (!empty($answer['text'])) {
                        $stmt = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
                        $stmt->execute(['question_id' => $question_id, 'answer_text' => $answer['text'], 'is_correct' => isset($answer['is_correct']) ? 1 : 0]);
                    }
                }
            } elseif ($question_type === 'true_false') {
                $true_answer = $_POST['true_answer'] ?? '';
                $false_answer = $_POST['false_answer'] ?? '';

                if (!empty($true_answer)) {
                    $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 1)")
                        ->execute(['question_id' => $question_id, 'answer_text' => $true_answer]);
                }

                if (!empty($false_answer)) {
                    $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, 0)")
                        ->execute(['question_id' => $question_id, 'answer_text' => $false_answer]);
                }
            }

            header("Location: QuizMaker.php?quiz_id=$quiz_id&module_id=$module_id");
            exit();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Check if editing a question
$editing_question_id = $_GET['edit_question'] ?? null;
$editing_question = null;
$editing_answers = [];

if ($editing_question_id) {
    $editing_question = $conn->prepare("SELECT * FROM Questions WHERE question_id = :question_id");
    $editing_question->execute(['question_id' => $editing_question_id]);
    $editing_question = $editing_question->fetch(PDO::FETCH_ASSOC);
    $editing_answers = $conn->prepare("SELECT * FROM Answers WHERE question_id = :question_id");
    $editing_answers->execute(['question_id' => $editing_question_id]);
    $editing_answers = $editing_answers->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/nqnew31549ejg1qkw3pezhkwqbuvz73i9iiouh0jl77ltyvh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#question_text',
            plugins: 'image media link',
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | image media link | code',
            images_upload_url: 'QuizUpload.php',
            media_url_resolver: function (data, resolve) {
                resolve({html: '<iframe width="560" height="315" src="' + data.url + '" frameborder="0" allowfullscreen></iframe>'});
            }
        });
    </script>
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
                    <input type="text" class="form-control" name="true_answer" placeholder="True Answer" required>
                    <input type="checkbox" class="form-check-input" name="true_correct" value="1" checked> Correct
                </div>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="false_answer" placeholder="False Answer" required>
                    <input type="checkbox" class="form-check-input" name="false_correct" value="0"> Correct
                </div>`;
        }
    }

    // Call the function on window load
    window.onload = function() {
        toggleAnswerFields();
    };

    // Add an event listener for the question type change
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('question_type').addEventListener('change', toggleAnswerFields);
    });
</script>
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-warning">
        <div class="container">
            <a class="navbar-brand" href="FacultyDashboard.php">PAES - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="AdminDashboard.php" class="nav-link">Back to Admin</a></li>
                    <li class="nav-item"><a href="LandingPage.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
<div class="content" style="margin-top: 100px; margin-bottom: 100px">
<div class="container mt-4">
    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>

    <h2 class="mt-5">Questions</h2>
    <ul class ="list-group">
        <?php foreach ($quiz_questions as $question): ?>
            <li class="list-group-item">
                <?php echo($question['question_text']); ?>
                <a href="?delete_question=<?php echo $question['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>" class="btn btn-danger btn-sm float-end" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                <a href="?quiz_id=<?php echo $quiz_id; ?>&edit_question=<?php echo $question['question_id']; ?>&module_id=<?php echo $module_id; ?>" class="btn btn-warning btn-sm float-end me-2">Edit</a>
                <ul>
                    <?php if (isset($answers[$question['question_id']])): ?>
                        <?php foreach ($answers[$question['question_id']] as $answer): ?>
                            <li><?php echo htmlspecialchars($answer['answer_text']); ?> (<?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No answers available for this question.</li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2 class="mt-5"><?php echo $editing_question ? 'Edit Question' : 'Add New Question'; ?></h2>
    <form method="POST" action="QuizMaker.php?quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>">
    <input type="hidden" name="question_id" value="<?php echo $editing_question ? $editing_question['question_id'] : ''; ?>">
    
    <div class="mb-3">
        <label for="question_text" class="form-label">Question Text</label>
        <textarea id="question_text" name="question_text" required><?php echo htmlspecialchars($editing_question['question_text'] ?? ' '); ?></textarea>
    </div>
    
    <div class="mb-3">
        <label for="question_type" class="form-label">Question Type</label>
        <select class="form-select" id="question_type" name="question_type" required>
            <option value="multiple_choice" <?php echo ($editing_question && $editing_answers) ? 'selected' : ''; ?>>Multiple Choice</option>
            <option value="true_false" <?php echo ($editing_question && !$editing_answers) ? 'selected' : ''; ?>>True/False</option>
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
                <input type="text" class="form-control" name="answers[1][text]" placeholder="Answer 2" required>
                <input type="checkbox" class="form-check-input" name="answers[1][is_correct]" value="1"> Correct
            </div>
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="answers[2][text]" placeholder="Answer 3">
                <input type="checkbox" class="form-check-input" name="answers[2][is_correct]" value="1"> Correct
            </div>
        <?php endif; ?>
    </div>
    
    <!-- New Submit Button -->
    <?php if ($editing_question): ?>
        <button type="submit" name="action" value="update" class="btn btn-primary">Update Question</button>
    <?php else: ?>
        <button type="submit" name="action" value="add" class="btn btn-primary">Add Question</button>
    <?php endif; ?>
    </form>
    <a href="ManageModule.php?module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Back to Module</a>

</div>
</div>

</body>
</html>