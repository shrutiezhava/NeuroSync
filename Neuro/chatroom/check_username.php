<?php
include_once('config.php');

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $query = "SELECT * FROM users WHERE user_name='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<font color='red'>Username already taken</font>";
    } else {
        echo "<font color='green'>Username available</font>";
    }
}
?>
