<?php
$targetDir = "uploaded_files/media_files/"; // Directory to save uploaded files
$uploadOk = 1;

// Check if the file was uploaded without errors
if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
    $fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
    $targetFile = $targetDir . basename($_FILES["file"]["name"]);

    // Allow certain file formats
    $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'pdf', 'docx', 'pptx'];
    if (!in_array($fileType, $allowedFileTypes)) {
        echo json_encode(['error' => 'Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI, MOV, PDF, DOCX, & PPTX files are allowed.']);
        $uploadOk = 0;
    }

    // Check file size (30 MB limit)
    if ($_FILES["file"]["size"] > 30 * 1024 * 1024) {
        echo json_encode(['error' => 'File size must be less than 30 MB.']);
        $uploadOk = 0;
    }

    // Check if the file is an image, video, or document
    $mimeType = mime_content_type($_FILES["file"]["tmp_name"]);
    if (strpos($mimeType, 'image') === 0) {
        // Handle image upload
        $check = getimagesize($_FILES["file"]["tmp_name"]);
        if ($check === false) {
            echo json_encode(['error' => 'File is not a valid image.']);
            $uploadOk = 0;
        }
    } elseif (strpos($mimeType, 'video') === 0) {
        // Handle video upload
        // Assume video is valid; you can add additional checks if needed
    } elseif (!in_array($fileType, ['pdf', 'docx', 'pptx'])) {
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