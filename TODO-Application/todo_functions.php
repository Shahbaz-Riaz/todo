<?php
    // Category Functions
    function addCategory($username, $category_name, $color = '#000000') {
        $conn = connectdatabase();
        $sql = "INSERT INTO categories (username, category_name, color) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $category_name, $color);
        $result = mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        return $result;
    }

    function getCategories($username) {
        $conn = connectdatabase();
        $sql = "SELECT * FROM categories WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $categories = array();
        while($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        mysqli_close($conn);
        return $categories;
    }

    // Priority Functions
    function getPriorities() {
        $conn = connectdatabase();
        $sql = "SELECT * FROM priorities ORDER BY priority_level";
        $result = mysqli_query($conn, $sql);
        $priorities = array();
        while($row = mysqli_fetch_assoc($result)) {
            $priorities[] = $row;
        }
        mysqli_close($conn);
        return $priorities;
    }

    // Task History Functions
    function addTaskHistory($taskid, $action, $old_value = null, $new_value = null) {
        $conn = connectdatabase();
        $sql = "INSERT INTO task_history (taskid, action, action_date, old_value, new_value) 
                VALUES (?, ?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isss", $taskid, $action, $old_value, $new_value);
        $result = mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        return $result;
    }

    function getTaskHistory($taskid) {
        $conn = connectdatabase();
        $sql = "SELECT * FROM task_history WHERE taskid = ? ORDER BY action_date DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $taskid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $history = array();
        while($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        mysqli_close($conn);
        return $history;
    }

    // Enhanced Task Functions
    function addTodoItem($username, $todo_text, $category_id = null, $priority_id = null, $due_date = null) {
        $conn = connectdatabase();
        $sql = "INSERT INTO tasks (username, task, done, category_id, priority_id, due_date) 
                VALUES (?, ?, 0, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssiis", $username, $todo_text, $category_id, $priority_id, $due_date);
        $result = mysqli_stmt_execute($stmt);
        
        if($result) {
            $taskid = mysqli_insert_id($conn);
            addTaskHistory($taskid, 'CREATE', null, $todo_text);
        }
        
        mysqli_close($conn);
        return $result;
    }

    function updateTodoItem($taskid, $todo_text, $category_id = null, $priority_id = null, $due_date = null) {
        $conn = connectdatabase();
        
        // Get old values
        $sql = "SELECT task, category_id, priority_id, due_date FROM tasks WHERE taskid = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $taskid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_values = mysqli_fetch_assoc($result);
        
        // Update task
        $sql = "UPDATE tasks SET task = ?, category_id = ?, priority_id = ?, due_date = ? WHERE taskid = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siisi", $todo_text, $category_id, $priority_id, $due_date, $taskid);
        $result = mysqli_stmt_execute($stmt);
        
        if($result) {
            // Log changes in history
            if($old_values['task'] != $todo_text) {
                addTaskHistory($taskid, 'UPDATE_TASK', $old_values['task'], $todo_text);
            }
            if($old_values['category_id'] != $category_id) {
                addTaskHistory($taskid, 'UPDATE_CATEGORY', $old_values['category_id'], $category_id);
            }
            if($old_values['priority_id'] != $priority_id) {
                addTaskHistory($taskid, 'UPDATE_PRIORITY', $old_values['priority_id'], $priority_id);
            }
            if($old_values['due_date'] != $due_date) {
                addTaskHistory($taskid, 'UPDATE_DUE_DATE', $old_values['due_date'], $due_date);
            }
        }
        
        mysqli_close($conn);
        return $result;
    }

    function getTodoItems($username) {
        $conn = connectdatabase();
        $sql = "SELECT t.*, c.category_name, c.color, p.priority_name 
                FROM tasks t 
                LEFT JOIN categories c ON t.category_id = c.category_id 
                LEFT JOIN priorities p ON t.priority_id = p.priority_id 
                WHERE t.username = ? 
                ORDER BY t.due_date ASC, p.priority_level DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        echo "<form method='POST'>";
        echo "<pre>";
        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                spaces(15);
                if($row['done']) {
                    echo "<input type='checkbox' checked class='largerCheckbox' name='check_list[]' value='".$row["taskid"] ."'>";
                } else {
                    echo "<input type='checkbox' class='largerCheckbox' name='check_list[]' value='".$row["taskid"] ."'>";
                }
                
                // Display task with category color and priority
                echo "<span style='color: " . $row['color'] . "'>";
                echo $row["task"];
                if($row['category_name']) {
                    echo " [".$row['category_name']."]";
                }
                if($row['priority_name']) {
                    echo " (".$row['priority_name'].")";
                }
                if($row['due_date']) {
                    echo " Due: ".date('Y-m-d', strtotime($row['due_date']));
                }
                echo "</span>";
                echo "<br>";
            }
        }
        echo "</pre> <hr>";
        spaces(35);
        echo "<input type='submit' name='Delete' value='Delete'/>";
        spaces(10);
        echo "<input type='submit' name='Save' value='Save'/>";
        echo "</form>";
        echo "<br><br>";
        mysqli_close($conn);
    }
?> 