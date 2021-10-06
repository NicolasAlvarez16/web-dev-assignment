<?php
    session_start();
    if(!isset($_SESSION['success'])){
        header("Location: login.php");
    }
    if($_SESSION['success'] == "false"){
        header("Location: login.php");
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="uft-8">
        <title>Web Dev Assignment</title>

        <!-- Icon Library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        
        <!-- CSS File -->
        <link rel="stylesheet" href="Styles/home.css"/>
    </head> 
    <body>
       <form class="box" action="home.php" method="post">
            <h1>Home</h1>
            <input type="submit" name="modules" value="Modules">
            <input type="submit" name="students" value="Students">
            <input type="submit" name="exams" value="Exams">
       </form>
    </body>
</html> 
<?php 
    if(isset($_POST['modules'])){
        header("Location: modules.php");
    }
    if(isset($_POST['students'])){
        header("Location: students.php");
    }
    if(isset($_POST['exams'])){
        header("Location: exams.php");
    }
?>