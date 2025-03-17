<?php
require_once 'db.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    
    try {
        $stmt = $conn->prepare("UPDATE tasks SET is_completed = 0 WHERE id = :id");
        $stmt->bindParam(':id', $task_id);
        $stmt->execute();
        
        header('Location: index.php?success=Task reactivated successfully');
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php?error=No task specified');
    exit;
}
?>