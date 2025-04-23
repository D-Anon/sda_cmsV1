<?php
$host = "localhost";
$dbname = "sda_cms";
$username = "root"; // Change if you have a different username
$password = ""; // Change if your MySQL has a password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
