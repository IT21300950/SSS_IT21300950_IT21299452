<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE username='$username'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$user_id = $row['id'];

$sql = "SELECT * FROM images WHERE user_id='$user_id'";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
        <h1>Dashboard</h1>
        <nav class="navbar bg-body-tertiary">
  <form class="container-fluid justify-content-start">
    
        <a href="upload.php" button class="btn btn-outline-success me-2" type="button" >Upload New Image</a> 
        <a href="authenticate.php"  button class="btn btn-outline-success me-2" type="button" >Authenticate Image</a>
        <a href="profile.php"  button class="btn btn-outline-success me-2" type="button" >User Profile</a>
  </form>
</nav>
        <h2>Your Uploaded Images</h2>
        <table class="table table-hover">
            <tr>
                <th>Image</th>
                <th>Label</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='uploads/" . $row['image_name'] . "' alt='" . $row['image_label'] . "' width='100'></td>";
                    echo "<td>" . $row['image_label'] . "</td>";
                    echo "<td>
                            <form action='delete.php' method='post' style='display:inline;'>
                                <input type='hidden' name='image_id' value='" . $row['id'] . "'>
                                <button type='submit'>Delete</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No images uploaded yet.</td></tr>";
            }
            ?>
        </table>
        <a href="logout.php"  type="button" class="btn btn-danger" >Logout</a>
    </div>
</body>
</html>
