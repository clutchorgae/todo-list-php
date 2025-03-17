<?php
require_once 'db.php';

$active_tasks_per_page = 5;
$active_current_page = isset($_GET['active_page']) ? (int)$_GET['active_page'] : 1;
$active_offset = ($active_current_page - 1) * $active_tasks_per_page;

$completed_tasks_per_page = 5;
$completed_current_page = isset($_GET['completed_page']) ? (int)$_GET['completed_page'] : 1;
$completed_offset = ($completed_current_page - 1) * $completed_tasks_per_page;

$stmt = $conn->query("SELECT COUNT(*) FROM tasks WHERE is_completed = 0");
$total_active_tasks = $stmt->fetchColumn();
$total_active_pages = ceil($total_active_tasks / $active_tasks_per_page);

$stmt = $conn->query("SELECT COUNT(*) FROM tasks WHERE is_completed = 1");
$total_completed_tasks = $stmt->fetchColumn();
$total_completed_pages = ceil($total_completed_tasks / $completed_tasks_per_page);

if ($active_current_page < 1) {
    $active_current_page = 1;
} elseif ($active_current_page > $total_active_pages && $total_active_pages > 0) {
    $active_current_page = $total_active_pages;
}

if ($completed_current_page < 1) {
    $completed_current_page = 1;
} elseif ($completed_current_page > $total_completed_pages && $total_completed_pages > 0) {
    $completed_current_page = $total_completed_pages;
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 0 ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $active_offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $active_tasks_per_page, PDO::PARAM_INT);
$stmt->execute();
$active_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 1 ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $completed_offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $completed_tasks_per_page, PDO::PARAM_INT);
$stmt->execute();
$completed_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Todo List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>My Todo List</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <form action="add_task.php" method="POST" class="task-form">
            <input type="text" name="task_name" placeholder="Enter a new task..." required>
            <button type="submit">Add Task</button>
        </form>
        
        <div class="tasks-container">
            <h2>Active Tasks <span class="task-count">(<?php echo $total_active_tasks; ?>)</span></h2>
            <?php if (count($active_tasks) > 0): ?>
                <ul class="task-list">
                    <?php foreach ($active_tasks as $task): ?>
                        <li class="task-item">
                            <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                            <div class="task-actions">
                                <a href="complete_task.php?id=<?php echo $task['id']; ?>" class="btn-complete">Complete</a>
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn-edit">Edit</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn-delete">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if ($total_active_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($active_current_page > 1): ?>
                            <a href="index.php?active_page=<?php echo $active_current_page - 1; ?>&completed_page=<?php echo $completed_current_page; ?>" class="page-link">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_active_pages; $i++): ?>
                            <a href="index.php?active_page=<?php echo $i; ?>&completed_page=<?php echo $completed_current_page; ?>" class="page-link <?php echo $i == $active_current_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($active_current_page < $total_active_pages): ?>
                            <a href="index.php?active_page=<?php echo $active_current_page + 1; ?>&completed_page=<?php echo $completed_current_page; ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="no-tasks">No active tasks. Add a new task above!</p>
            <?php endif; ?>
        </div>
        
        <div class="tasks-container completed-container">
            <h2>Completed Tasks <span class="task-count">(<?php echo $total_completed_tasks; ?>)</span></h2>
            <?php if (count($completed_tasks) > 0): ?>
                <ul class="task-list">
                    <?php foreach ($completed_tasks as $task): ?>
                        <li class="task-item completed">
                            <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                            <div class="task-actions">
                                <a href="uncomplete_task.php?id=<?php echo $task['id']; ?>" class="btn-uncomplete">Reactivate</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn-delete">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if ($total_completed_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($completed_current_page > 1): ?>
                            <a href="index.php?active_page=<?php echo $active_current_page; ?>&completed_page=<?php echo $completed_current_page - 1; ?>" class="page-link">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_completed_pages; $i++): ?>
                            <a href="index.php?active_page=<?php echo $active_current_page; ?>&completed_page=<?php echo $i; ?>" class="page-link <?php echo $i == $completed_current_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($completed_current_page < $total_completed_pages): ?>
                            <a href="index.php?active_page=<?php echo $active_current_page; ?>&completed_page=<?php echo $completed_current_page + 1; ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="no-tasks">No completed tasks yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>