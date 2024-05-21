<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $image_id = $_POST['image_id'];

    // Get image information from database
    $sql = "SELECT image_name FROM images WHERE id='$image_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_name = $row['image_name'];
        $image_path = "uploads/" . $image_name;

        // Delete the image file from the server
        if (file_exists($image_path)) {
            if (unlink($image_path)) {
                // Delete image record from database
                $sql = "DELETE FROM images WHERE id='$image_id'";
                if ($conn->query($sql) === TRUE) {
                    echo "Image deleted successfully.<br>";
                } else {
                    echo "Error deleting record: " . $conn->error;
                }
            } else {
                echo "Error deleting the image file.<br>";
            }
        } else {
            echo "File does not exist.<br>";
        }
    } else {
        echo "Image not found.<br>";
    }
}

header("Location: dashboard.php");
exit();
?>
