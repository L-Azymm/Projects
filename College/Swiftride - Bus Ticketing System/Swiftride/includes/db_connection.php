<?php
$host = 'localhost';
$dbname = 'swiftride_db';
$username = 'adam_developer';
$password = 'IThinkThisPasswordIsLongEnough';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
