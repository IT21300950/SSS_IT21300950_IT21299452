<?php

require 'db.php';

$username = $email = $password = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = test_input($_POST['username']);
    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);
    $image = $_FILES['image'];

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Handle the image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image["name"]);
    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        // Generate the hash using username, password, and image
        $image_content = file_get_contents($target_file);
        $combined_hash = hash('sha256', $username . $password_hash . $image_content);

        // Save user to the database
        $sql = "INSERT INTO users (username, email, password, combined_hash) VALUES ('$username', '$email', '$password_hash', '$combined_hash')";

        if ($conn->query($sql) === TRUE) {
            echo "Registration successful.";
            header("Location: login.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error uploading image.";
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Signup</h1>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <label for="image">Upload Image:</label>
            <input type="file" name="image" class="form-control" id="image" required>
            <button type="submit">Signup</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
