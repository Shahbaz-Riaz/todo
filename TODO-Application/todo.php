<?php
    session_start();
    require_once 'database.php';
    require_once 'todo_functions.php';

    if(!loggedin()) {
        header('location:login.php');
    }

    $username = $_SESSION['username'];

    if(isset($_POST['Delete'])) {
        if(!empty($_POST['check_list'])) {
            $tasks = $_POST['check_list'];
            $length = count($tasks);
            for ($i = 0; $i < $length; $i++) {
                deleteTodoItem($username, $tasks[$i]);
            }
        }
    }
    else if(isset($_POST['Save'])) {
        $conn = connectdatabase();
        $sql = "UPDATE todo.tasks SET done = 0";
        $result = mysqli_query($conn, $sql); 
        mysqli_close($conn);

        if(!empty($_POST['check_list'])) {
            $tasks = $_POST['check_list'];
            $length = count($tasks);
            if($length > 0) {
                for ($i = 0; $i < $length; $i++) {
                    updateDone($tasks[$i]);
                }
            }
        }
    }

    if(isset($_POST['addtask'])) {
        if(!empty($_POST['description'])) {
            addTodoItem($username, $_POST['description']);
            header("Refresh:0");
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
	<title>Todo List</title>
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
		.task-item {
			margin: 10px 0;
			padding: 10px;
			border-bottom: 1px solid #eee;
		}
		.task-item:hover {
			background-color: #f9f9f9;
		}
		.category-tag {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 12px;
			color: white;
			font-size: 0.8em;
			margin-left: 10px;
		}
		.priority-tag {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 12px;
			background-color: #666;
			color: white;
			font-size: 0.8em;
			margin-left: 10px;
		}
		.due-date {
			color: #666;
			font-size: 0.9em;
			margin-left: 10px;
		}
		.overdue {
			color: #ff0000;
		}
		.actions {
			margin-top: 20px;
			text-align: right;
		}
		.actions input[type="submit"] {
			padding: 8px 15px;
			margin-left: 10px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}
		.actions input[type="submit"]:first-child {
			background-color: #ff4444;
			color: white;
		}
		.actions input[type="submit"]:last-child {
			background-color: #4CAF50;
			color: white;
		}
		.manage-link {
			float: right;
			margin-bottom: 20px;
			text-decoration: none;
			color: #4CAF50;
		}
		.nav-links {
			margin-bottom: 20px;
		}
		.nav-links a {
			margin-right: 15px;
			text-decoration: none;
		}
		.nav-links a.logout {
			color: red;
		}
		.nav-links a.settings {
			color: blue;
		}
		.welcome-message {
			text-align: center;
			margin: 20px 0;
			font-size: 1.2em;
		}
		.add-task-form {
			margin: 20px 0;
			padding: 15px;
			background-color: #f9f9f9;
			border-radius: 4px;
		}
		.add-task-form input[type="text"] {
			padding: 8px;
			width: 70%;
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		.add-task-form input[type="submit"] {
			padding: 8px 15px;
			background-color: #4CAF50;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="nav-links">
			<a href="logout.php" class="logout">Logout</a>
			<a href="changepassword.php" class="settings">Change Password</a>
			<a href="deleteaccount.php" class="settings">Delete Account</a>
		</div>

		<?php error(); ?>
		<div class="welcome-message">Welcome <?php echo ucwords($username); ?></div>

		<h1>Todo List</h1>
		<a href="manage_todo.php" class="manage-link">Manage Categories & Tasks</a>

		<div class="add-task-form">
			<form method="POST">
				<input type="text" name="description" placeholder="Enter new task..." required>
				<input type="submit" name="addtask" value="Add Task">
			</form>
		</div>
		
		<form method="POST">
			<?php getTodoItems($username); ?>
		</form>
	</div>
</body>
</html>
