<!DOCTYPE html>
<html>
<head>
    <style type="text/css" media="screen">
        input.largerCheckbox { 
            width: 20px; 
            height: 20px; 
        } 
    </style>
</head>
</html>

<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    function connectdatabase() {
        return mysqli_connect("localhost", "root", "", "todo");
    }

    function loggedin() {
        return isset($_SESSION['username']);
    }

    function logout() {
        $_SESSION['error'] = "&nbsp; Successfully logout !!";
        unset($_SESSION['username']);
    }

    function spaces($n) {
        for($i=0; $i<$n; $i++)
            echo "&nbsp;";
    }

    function userexist($username) 
    {
        $conn = connectdatabase();
        $sql = "SELECT * FROM todo.users WHERE username = '".$username."'"; 
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);

        if(!$result || mysqli_num_rows($result) == 0) { 
           return false;
        }
        return true;
    }

    function validuser($username, $password) 
    {
        $conn = connectdatabase();
        $sql = "SELECT * FROM todo.users WHERE username = '".$username."'AND password = '".$password."'"; 
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);

        if(!$result || mysqli_num_rows($result) == 0) { 
           return false;
        }
        return true;
    }

    function error() 
    {
        if(isset($_SESSION['error'])) {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        }
    }

    function updatepassword($username, $password) {
        $conn = connectdatabase();
        $sql = "UPDATE todo.users SET password = '".$password."' WHERE username = '".$username."';";
        $result = mysqli_query($conn, $sql);

        $_SESSION['error'] = "<br> &nbsp; Password Updated !! ";
        header('location:todo.php');
    }

    function deleteaccount($username) {
        $conn = connectdatabase();
        $sql = "DELETE FROM todo.tasks WHERE username = '".$username."';";
        $result = mysqli_query($conn, $sql);

        $sql = "DELETE FROM todo.users WHERE username = '".$username."';";
        $result = mysqli_query($conn, $sql);

        $_SESSION['error'] = "&nbsp; Account Deleted !! ";
        unset($_SESSION['username']);
        header('location:login.php');
    }

    function createUser($username, $password)
    {
        if(!userexist($username))
        {
            $conn = connectdatabase();
            $sql = "INSERT INTO todo.users (username, password) VALUES ('".$username."','".$password."')";
            $result = mysqli_query($conn, $sql);

            $_SESSION["username"] = $username;
            header('location:todo.php');
        }
        else
        {
            $_SESSION['error'] = "&nbsp; Username already exists !! ";
            header('location:newuser.php');
        }
    }
    
    function isValid($username, $password, $usercaptcha)
    {
        $capcode = $_SESSION['captcha'];

        if(!strcmp($usercaptcha,$capcode))
        {
            if(validuser($username, $password))
            {
                $_SESSION["username"] = $username;
                header('location:todo.php');
            }
            else
            {
                $_SESSION['error'] = "&nbsp; Invalid Username or Password !! ";
                header('location:login.php');
            }
            mysqli_close($conn);
        }
        else
        {
            $_SESSION['error'] = "&nbsp; Invalid captcha code !! ";
            header('location:login.php');
        }
    }
?>