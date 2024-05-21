<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $sql = "SELECT id FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $user_id = $row['id'];

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Compute hash of the new image
            $image_hash = hash_file('sha256', $target_file);

            // Compare with existing hashes in the database
            $sql = "SELECT image_label, image_hash FROM images WHERE user_id='$user_id'";
            $result = $conn->query($sql);

            $authenticated = false;
            while ($row = $result->fetch_assoc()) {
                if ($image_hash === $row['image_hash']) {
                    echo "Image authenticated successfully with label: " . $row['image_label'] . "<br>";
                    $authenticated = true;
                    break;
                }
            }

            if (!$authenticated) {
                echo "Authentication failed. No matching image found.<br>";
            }
        } else {
            echo "Sorry, there was an error uploading your file.<br>";
        }
    } else {
        echo "File is not an image.<br>";
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
    <title>Authenticate Image</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Authenticate Image</h1>
        <form action="authenticate.php" method="post" enctype="multipart/form-data">
            <label for="image">Upload Image to Authenticate:</label>
            <input type="file" class="form-control"  name="image" id="image" required>
            <button type="submit">Authenticate</button>
        </form>
        <br>
        <a href="dashboard.php"  type="button" class="btn btn-secondary" >Back to Dashboard</a>
    </div>
</body>
</html>
