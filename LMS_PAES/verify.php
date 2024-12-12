<?php
// Database connection setup
$host = 'localhost';
$dbname = 'paesbluy_lms_database';
$username = 'root';
$password = 'pogi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the temp_users table
    $stmt = $pdo->prepare("SELECT * FROM temp_users WHERE verification_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set the default role to 'student'
        $role = 'student'; 

        // Insert the user into the users table
        $insertStmt = $pdo->prepare("INSERT INTO users (username, password, email, role, firstName, lastName) VALUES (:username, :password, :email, :role, :firstname, :lastname)");
        $insertStmt->bindParam(':username', $user['username']);
        $insertStmt->bindParam(':password', $user['password']); // Ensure this is hashed before inserting
        $insertStmt->bindParam(':email', $user['email']);
        $insertStmt->bindParam(':role', $role); // Bind the role parameter
        $insertStmt->bindParam(':firstname', $firstName);
        $insertStmt->bindParam(':lastname', $lastName);
        if ($insertStmt->execute()) {
            // Delete the user from the temp_users table
            $deleteStmt = $pdo->prepare("DELETE FROM temp_users WHERE verification_token = :token");
            $deleteStmt->bindParam(':token', $token);
            $deleteStmt->execute();

            echo "Email verified successfully! You can now log in.";
        } else {
            echo "Error occurred while verifying your email. Please try again.";
        }
    } else {
        echo "Invalid verification token.";
    }
} else {
    echo "No token provided.";
}
?>