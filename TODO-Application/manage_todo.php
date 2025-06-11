<?php
    session_start();
    require_once 'database.php';
    require_once 'todo_functions.php';

    if(!loggedin()) {
        header('location:login.php');
    }

    $username = $_SESSION['username'];
    $categories = getCategories($username);
    $priorities = getPriorities();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Todo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="color"] {
            width: 50px;
            height: 30px;
            padding: 0;
            border: none;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .category-item {
            padding: 5px 10px;
            border-radius: 15px;
            color: white;
            display: inline-block;
        }
        .task-form {
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Todo</h1>
        <a href="todo.php">Back to Todo List</a>

        <!-- Category Management Section -->
        <div class="section">
            <h2>Manage Categories</h2>
            <form method="POST" action="manage_todo.php">
                <div class="form-group">
                    <label>Category Name:</label>
                    <input type="text" name="category_name" required>
                </div>
                <div class="form-group">
                    <label>Color:</label>
                    <input type="color" name="category_color" value="#000000">
                </div>
                <button type="submit" name="add_category">Add Category</button>
            </form>

            <div class="category-list">
                <?php foreach($categories as $category): ?>
                    <div class="category-item" style="background-color: <?php echo $category['color']; ?>">
                        <?php echo $category['category_name']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Add New Task Section -->
        <div class="section">
            <h2>Add New Task</h2>
            <form method="POST" action="manage_todo.php" class="task-form">
                <div class="form-group">
                    <label>Task Description:</label>
                    <input type="text" name="task_text" required>
                </div>
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority:</label>
                    <select name="priority_id">
                        <option value="">Select Priority</option>
                        <?php foreach($priorities as $priority): ?>
                            <option value="<?php echo $priority['priority_id']; ?>">
                                <?php echo $priority['priority_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Due Date:</label>
                    <input type="date" name="due_date">
                </div>
                <button type="submit" name="add_task">Add Task</button>
            </form>
        </div>

        <!-- Task History Section -->
        <div class="section">
            <h2>Recent Task History</h2>
            <?php
            $conn = connectdatabase();
            $sql = "SELECT th.*, t.task 
                    FROM task_history th 
                    JOIN tasks t ON th.taskid = t.taskid 
                    WHERE t.username = ? 
                    ORDER BY th.action_date DESC 
                    LIMIT 10";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<div style='margin-bottom: 10px;'>";
                    echo "<strong>Task:</strong> " . $row['task'] . "<br>";
                    echo "<strong>Action:</strong> " . $row['action'] . "<br>";
                    echo "<strong>Date:</strong> " . date('Y-m-d H:i:s', strtotime($row['action_date'])) . "<br>";
                    if($row['old_value']) {
                        echo "<strong>Old Value:</strong> " . $row['old_value'] . "<br>";
                    }
                    if($row['new_value']) {
                        echo "<strong>New Value:</strong> " . $row['new_value'] . "<br>";
                    }
                    echo "<hr>";
                    echo "</div>";
                }
            } else {
                echo "<p>No task history available.</p>";
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>

    <?php
    // Handle form submissions
    if(isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'];
        $category_color = $_POST['category_color'];
        
        if(addCategory($username, $category_name, $category_color)) {
            echo "<div class='success'>Category added successfully!</div>";
            header("Refresh:0");
        } else {
            echo "<div class='error'>Error adding category.</div>";
        }
    }

    if(isset($_POST['add_task'])) {
        $task_text = $_POST['task_text'];
        $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
        $priority_id = !empty($_POST['priority_id']) ? $_POST['priority_id'] : null;
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        if(addTodoItem($username, $task_text, $category_id, $priority_id, $due_date)) {
            echo "<div class='success'>Task added successfully!</div>";
            header("Refresh:0");
        } else {
            echo "<div class='error'>Error adding task.</div>";
        }
    }
    ?>
</body>
</html> 