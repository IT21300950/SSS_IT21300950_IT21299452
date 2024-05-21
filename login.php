<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function analyzeLogin($login_time, $login_attempts) {
    $url = 'http://localhost:5001/analyze';
    $data = array('login_time' => $login_time, 'login_attempts' => $login_attempts);

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "Error: Unable to reach the ML service.";
        return false;
    }

    return json_decode($result, true);
}

require 'db.php';
session_start();

$username = $password = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = test_input($_POST['username']);
    $password = test_input($_POST['password']);
    $image = $_FILES['image'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Handle the image upload
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($image["name"]);
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                // Generate the hash using username, password, and image
                $image_content = file_get_contents($target_file);
                $combined_hash = hash('sha256', $username . $row['password'] . $image_content);

                // Verify combined hash
                if ($combined_hash === $row['combined_hash']) {
                    $current_time = time();
                    $login_time = date("Y-m-d H:i:s", $current_time);

                    // Calculate login attempts in the last hour
                    $one_hour_ago = date("Y-m-d H:i:s", $current_time - 3600);
                    $attempt_stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM logins WHERE username = ? AND login_time > ?");
                    $attempt_stmt->bind_param("ss", $username, $one_hour_ago);
                    $attempt_stmt->execute();
                    $attempt_result = $attempt_stmt->get_result();
                    $login_attempts = $attempt_result->fetch_assoc()['attempt_count'];

                    // Perform anomaly detection
                    $response = analyzeLogin($current_time, $login_attempts);

                    if ($response && $response['is_anomaly']) {
                        echo "Unusual login attempt detected!";
                    } else {
                        echo "Login successful!";
                        // Log the current login attempt
                        $log_stmt = $conn->prepare("INSERT INTO logins (username, login_time) VALUES (?, ?)");
                        $log_stmt->bind_param("ss", $username, $login_time);
                        $log_stmt->execute();

                        // Set session and redirect
                        $_SESSION['username'] = $username;
                        header("Location: dashboard.php");
                        exit(); // Make sure to exit after redirection
                    }
                } else {
                    echo "Image authentication failed.";
                }
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this username.";
    }
} else {
    echo "Invalid request method.";
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
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form action="login.php" method="post" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <label for="image">Upload Image:</label>
            <input type="file" name="image" class="form-control" id="image" accept="image/*" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="index.php">Sign up here</a></p>
    </div>
</body>
</html>
