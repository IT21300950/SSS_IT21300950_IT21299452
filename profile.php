<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $new_image = $_FILES['new_image'];

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "New password and confirm password do not match.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Handle the new image upload
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($new_image["name"]);
            if (move_uploaded_file($new_image["tmp_name"], $target_file)) {
                // Hash the new password if provided
                $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

                // Generate the new combined hash
                $image_content = file_get_contents($target_file);
                $combined_hash = hash('sha256', $username . $new_password_hash . $image_content);

                // Update the password and combined hash in the database
                $sql = "UPDATE users SET password='$new_password_hash', combined_hash='$combined_hash' WHERE username='$username'";
                if ($conn->query($sql) === TRUE) {
                    echo "Password and image updated successfully.";
                } else {
                    echo "Error updating password and image: " . $conn->error;
                }
            } else {
                echo "Error uploading new image.";
            }
        } else {
            echo "Current password is incorrect.";
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
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Profile</h1>
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo $user['username']; ?>" readonly>
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" id="current_password" required>
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <label for="new_image">Upload New Image:</label>
            <input type="file" name="new_image" id="new_image" required>
            <button type="submit">Update Password and Image</button>
        </form>
        <br>
        <a href="dashboard.php" type="button" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
