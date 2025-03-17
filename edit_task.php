<?php
require_once 'db.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $task_id);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        header('Location: index.php?error=Task not found');
        exit;
    }
} else {
    header('Location: index.php?error=No task specified');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name']);
    
    if (empty($task_name)) {
        $error = "Task name cannot be empty";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE tasks SET task_name = :task_name WHERE id = :id");
            $stmt->bindParam(':task_name', $task_name);
            $stmt->bindParam(':id', $task_id);
            $stmt->execute();
            
            header('Location: index.php?success=Task updated successfully');
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Task</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST" class="task-form edit-form">
            <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
            <button type="submit">Update Task</button>
        </form>
        
        <div class="form-actions">
            <a href="index.php" class="btn-back">Back to List</a>
        </div>
    </div>
</body>
</html>