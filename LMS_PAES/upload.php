<?php
$targetDir = "uploaded_files/media_files/"; // Directory to save uploaded files
$fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
$targetFile = $targetDir . basename($_FILES["file"]["name"]);
$uploadOk = 1;

// Check if the file was uploaded without errors
if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {

    // Allow certain file formats
    $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'pdf', 'docx', 'pptx'];
    if (!in_array($fileType, $allowedFileTypes)) {
        echo json_encode(['error' => 'Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI, MOV, PDF, DOCX, & PPTX files are allowed.']);
        $uploadOk = 0;
    }

    // Check if the file is an image, video, or document
    if (strpos(mime_content_type($_FILES["file"]["tmp_name"]), 'image') === 0) {
        // Handle image upload
        $check = getimagesize($_FILES["file"]["tmp_name"]);
        if ($check === false) {
            echo json_encode(['error' => 'File is not a valid image.']);
            $uploadOk = 0;
        }
    } elseif (strpos(mime_content_type($_FILES["file"]["tmp_name"]), 'video') === 0) {
        // Handle video upload
        // Assume video is valid
    } elseif (in_array($fileType, ['pdf', 'docx', 'pptx'])) {
        // Handle document upload
        // Assume document is valid
    } else {
        echo json_encode(['error' => 'File is not an image, video, or allowed document.']);
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo json_encode(['error' => 'Sorry, your file was not uploaded.']);
    } else {
        // Create the upload directory if it does not exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Attempt to move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            echo json_encode(['location' => $targetFile]); // Return the file URL
        } else {
            echo json_encode(['error' => 'Sorry, there was an error uploading your file.']);
        }
    }
} else {
    echo json_encode(['error' => 'No file uploaded or upload error.']);
}
?>