<?php
    session_start();
    $error = "username/password incorrect";
    $success = "Login successful";
    $conn = new mysqli("localhost", "root", "", "MYDB");
    if(isset($_POST['username']) && isset($_POST['password'])){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $sql = "SELECT * FROM USERS WHERE USER_NAME = '$username' AND USER_PASS = '$password'";
        $result = $conn->query($sql);
        if(!empty($result) && $result->num_rows > 0){
            $_SESSION['success'] = $success;
            header("Location: home.php");
        }
        else{
            $_SESSION["error"] = $error;
            header("Location: login.php");
        }
    }   
?>