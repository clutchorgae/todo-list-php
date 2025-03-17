<?php
require 'db.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
}

header("Location: index.php");
exit();
?>
