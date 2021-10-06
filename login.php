<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="uft-8">
        <title>Web Dev Assignment</title>
        
        <!-- CSS File -->
        <link rel="stylesheet" href="Styles/login.css">

        <!-- Icon Library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        
    </head> 
    <body>
       <form class="box" action="validate-account.php" method="post">
           <h1>Login</h1>
            <input type="text" name="username" placeholder="Username"><br>
            <input type="password" name="password" placeholder="Password">
            <input type="submit" name="login" value="Login">
            <?php
                if(isset($_SESSION["error"])){
                    $error = $_SESSION["error"];
                    echo "<h5 style='color: #eb4d4b'>$error<h5>";
                }
            ?>
       </form>
    </body>
</html> 
<?php
    unset($_SESSION["error"]);
?>