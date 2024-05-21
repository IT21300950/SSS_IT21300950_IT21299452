<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $image_label = $_POST['image_label'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        echo "File is an image - " . $check["mime"] . ".<br>";
        $uploadOk = 1;
    } else {
        echo "File is not an image.<br>";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 5000000) {
        echo "Sorry, your file is too large.<br>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed.<br>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.<br>";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "The file ". basename($_FILES["image"]["name"]). " has been uploaded.<br>";

            // Compute hash of the image
            $image_hash = hash_file('sha256', $target_file);

            // Save the file information to the database
            $username = $_SESSION['username'];
            $sql = "SELECT id FROM users WHERE username='$username'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $user_id = $row['id'];

            $sql = "INSERT INTO images (user_id, image_name, image_label, image_hash) VALUES ('$user_id', '" . basename($_FILES["image"]["name"]) . "', '$image_label', '$image_hash')";
            if ($conn->query($sql) === TRUE) {
                echo "Image information saved to database.<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.<br>";
            echo "Debugging Information:<br>";
            echo "File name: " . basename($_FILES["image"]["name"]) . "<br>";
            echo "Temp file location: " . $_FILES["image"]["tmp_name"] . "<br>";
            echo "Target file location: " . $target_file . "<br>";
            echo "File permissions on target directory: " . substr(sprintf('%o', fileperms($target_dir)), -4) . "<br>";
            echo "Error: " . $_FILES["image"]["error"];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Upload Image</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="image_label">Image Label:</label>
            <input type="text" name="image_label" id="image_label" required>
            <label for="image">Upload Image:</label>
            <input type="file" class="form-control"  name="image" id="image" required>
            <button type="submit">Upload</button>
        </form>
        <br>
        <a href="dashboard.php"   type="button" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
