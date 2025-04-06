<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'ajaxdb') or die('Database error');
mysqli_set_charset($conn, 'utf8mb4');

// Security Headers
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");