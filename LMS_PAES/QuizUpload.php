<?php
$targetDir = "quiz_uploads/";
$fileName = basename($_FILES["file"]["name"]);
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

// Allow certain file formats
$allowedTypes = array('jpg', 'png', 'jpeg', 'gif', 'mp4', 'mp3', 'wav');
if (in_array($fileType, $allowedTypes)) {
    // Upload file to server
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        // Return the URL of the uploaded file
        echo json_encode(['location' => $targetFilePath]);
    } else {
        echo json_encode(['error' => 'File upload failed.']);
    }
} else {
    echo json_encode(['error' => 'Invalid file type.']);
}
?>