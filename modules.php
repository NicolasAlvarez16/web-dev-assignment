<?php
    session_start(); // Start session
    if(!isset($_SESSION['success'])){
        header("Location: login.php");
    }
    if($_SESSION['success'] == "false"){
        header("Location: login.php");
    }

    if(isset($_POST['home'])){
        header("Location: home.php");
    }
    
    // Create variables for all the fields in the table
    $idModule = "";
    $moduleName = "";
    $moduleDesc = "";
    $moduleOutcomes = "";
    $difficultyLevel = "";

    if($_SERVER['QUERY_STRING'] != ""){
        if($_GET['mode'] == "edit"){
            // Get values from the row the user wants to update -> So I can display them
            list($idModule, $moduleName, $moduleDesc, $moduleOutcomes, $difficultyLevel) = getValues($_GET['id']);
        }
        else{
            // Delete field
            deleteRow($_GET['id']);
        }
    }
    
    if(isset($_POST['edit-row']) && strlen($_POST['id-module-h']) > 0){
        editRow(); // Update table
    }

    if(isset($_POST['add-row']) && strlen($_POST['id-module-h']) == 0){
        addRow(); // Insert into table
    }
?>

<html>
    <head>
        <meta charset="uft-8">
        <title>Web Dev Assignment</title>

        <!-- Icon Library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <!-- CSS File -->
        <link rel="stylesheet" href="Styles/tables-style.css">
    </head>
    <body>
        <h1>Modules Manager</h1>
        <div id="search">
            <form class="box" action="modules.php" method="post">
                <input type="text" name="search" placeholder="Search">
                <button type="submit" name="submit-search" value="Search"><i class="fa fa-search" style="font-size: 18px; color: white"></i></button>
                <button type="submit" name="home" value="Home"><i class="fa fa-home" style="font-size: 18px; color: white"></i></button>
            </form>
        <div>
        <div id="edit-table-container">
            <form class='edit-table' action='modules.php' method='post'>
                <input type="text" name="id-module" placeholder="ID Module" disabled value = <?php echo $idModule;?>><i class="fa fa-lock" style="margin-left: -10px"></i>
                <input type="text" name="id-module-h" hidden value = <?php echo $idModule;?>>
                <input type="text" name="module-name" placeholder="Module Name" value="<?php echo $moduleName;?>">
                <input type="text" name="module-desc" placeholder="Module Description" value="<?php echo $moduleDesc;?>">
                <input type="text" name="module-outcomes" placeholder="Module Outcomes" value="<?php echo $moduleOutcomes;?>">
                <input type="text" name="difficulty-level" placeholder="Difficulty Level" value="<?php echo $difficultyLevel;?>">
                <?php
                    if($_SERVER['QUERY_STRING'] != ""){
                        if($_GET['mode'] == "edit"){
                            echo "<button type='submit' name='edit-row' value='Add'><i class='fa fa-clone'></i></button>";
                        }
                        else{
                            echo "<button type='submit' name='add-row' value='Add'><i class='fa fa-plus'></i></button>";
                        }
                    }
                    else{
                        echo "<button type='submit' name='add-row' value='Add'><i class='fa fa-plus'></i></button>";
                    }
                ?>
            </form>
        </div>
        <div id="table-div">
            <table class="content-table">
                <thead>
                    <th>ID Module</th>
                    <th>Module&nbsp;Name</th>
                    <th>Module Desc</th>
                    <th>Module Outcomes</th>
                    <th>Difficulty Level</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php
                        mainPanel();
                    ?>
                </tbody>
            </table>
        <div>
    </body>
</html>
<?php
    function mainPanel(){
        $conn = new mysqli("localhost", "root", "", "MYDB"); //Connect to db
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        if(isset($_POST['search'])){
            // Search for anything the user types
            $search = $_POST["search"];
            $sql = "SELECT ID_MODULE, MODULE_NAME, MODULE_DESC, MODULE_OUTCOMES, DIFFICULTY_LEVEL FROM MODULES
                    WHERE CONVERT(ID_MODULE, CHAR) = '$search' OR MODULE_NAME LIKE '%$search%' OR MODULE_DESC LIKE '%$search%' OR MODULE_OUTCOMES LIKE '%$search%'";
        }else{
            // If nothing is search select all the contents on the table
            $sql = "SELECT ID_MODULE, MODULE_NAME, MODULE_DESC, MODULE_OUTCOMES, DIFFICULTY_LEVEL FROM MODULES";
        }
        
        if($result = $conn->query($sql)){
            // Display contents of the select query
            $row_count = $result->num_rows;
            for($i = 0; $i < $row_count; $i++){
                if($row = $result->fetch_assoc()){
                    echo "<tr>";
                        echo "<td>" . $row['ID_MODULE'] . "</td>";
                        echo "<td>" . $row['MODULE_NAME'] . "</td>";
                        echo "<td>" . $row['MODULE_DESC'] . "</td>";
                        echo "<td>" . $row['MODULE_OUTCOMES'] . "</td>";
                        echo "<td>" . $row['DIFFICULTY_LEVEL'] . "</td>";
                        echo "<td id='action'>" . "<a href='modules.php?mode=edit&id=" . $row['ID_MODULE'] . "'>" . "<i class='fa fa-edit'" . "style='font-size: 23px; color: black; text-align: center'></i>" . "</a>&nbsp;&nbsp;";
                        echo "<a href='modules.php?mode=delete&id=" . $row['ID_MODULE'] . " '>". "<i class='fa fa-trash'" . "style='font-size: 23px; color: #ff7675;'></i>" . "</a></td>";
                    echo "</tr>";
                }
            }
        }
        $conn->close();
    }

    function getValues($id){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to db
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT ID_MODULE, MODULE_NAME, MODULE_DESC, MODULE_OUTCOMES, DIFFICULTY_LEVEL FROM MODULES
                    WHERE ID_MODULE = $id"; // Select the conents of the row that is going to be edited -> So this contents can be displayed in the form
        if($result = $conn->query($sql)){
            if($row = $result->fetch_assoc()){
                // Save the values in the variabled below
                $idModule = $id;
                $moduleName = $row['MODULE_NAME'];
                $moduleDesc = $row['MODULE_DESC'];
                $moduleOutcomes = $row['MODULE_OUTCOMES'];
                $difficultyLevel =  $row['DIFFICULTY_LEVEL'];
            }
        }
        // echo $sql;
        $conn->close();
        return array($idModule, $moduleName, $moduleDesc, $moduleOutcomes, $difficultyLevel); // Return all the values
    }

    function deleteRow($id){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to the db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "DELETE FROM MODULES WHERE ID_MODULE = $id"; // Delete selected row
        if($conn->query($sql) === TRUE){ 
            // Inform user -> Row deleted
            echo "<script language='JavaScript'>alert('Row deleted successfully');</script>";
        }
        else{
            // Inform user -> Error deliting row
            echo "<script language='JavaScript'>alert('Error deleting record!);</script>";
        }
        // header("Location: modules.php");
        $conn->close();
    }

    function addRow(){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Save the values from the form into variables
        $moduleName = $_POST['module-name'];
        $moduleDesc = $_POST['module-desc'];
        $moduleOutcomes = $_POST['module-outcomes'];
        $difficultyLevel =  $_POST['difficulty-level'];
        
        if(isset($moduleName) && isset($moduleDesc) && isset($moduleOutcomes) && isset($difficultyLevel)){
            $last_id = "SELECT MAX(ID_MODULE)+1 AS ID_MAX FROM MODULES"; // Get a new id
            if($result = $conn->query($last_id)){
                if($row = $result->fetch_assoc()){
                    $last_id = $row['ID_MAX'];
                }
            }
            if(is_null($last_id)){
                // If there are no rows, new id = 1;
                $last_id = 1; 
            }

            $sql = "INSERT INTO MODULES(ID_MODULE, MODULE_NAME, MODULE_DESC, MODULE_OUTCOMES, DIFFICULTY_LEVEL)
                        VALUES($last_id, '$moduleName', '$moduleDesc', '$moduleOutcomes', $difficultyLevel)"; // Insert the values from the form
            // echo $sql;
            if($conn->query($sql) === True){
                // Inform the user that the insert was completed
                echo "<script language='JavaScript'>alert('Row added successfully');</script>";
            }
        }
        else{
            echo "Something is missing";
        }
        if(strlen($conn->error) > 0){
            // Inform the user that an error occurred when inserting
            echo "<script language='JavaScript'>alert('Error when inserting data');</script>";
        }
        $conn->close();
    }

    function editRow(){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to the db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Save the values from the form into variables
        $id = $_POST['id-module-h'];
        $moduleName = $_POST['module-name'];
        $moduleDesc = $_POST['module-desc'];
        $moduleOutcomes = $_POST['module-outcomes'];
        $difficultyLevel =  $_POST['difficulty-level'];

        $sql = "UPDATE MODULES
                    SET MODULE_NAME = '$moduleName', MODULE_DESC = '$moduleDesc', MODULE_OUTCOMES = '$moduleOutcomes', DIFFICULTY_LEVEL = $difficultyLevel
                    WHERE ID_MODULE = $id"; // Update the values from the form
        // echo $sql;
        if($conn->query($sql) === True){
            // Inform the user that the update was completed
            echo "<script language='JavaScript'>alert('Row updated successfully');</script>";
        }
        else{
            // Inform the user that an error occured when updating the table
            echo "<script language='JavaScript'>alert('Error updated record!);</script>";
        }
        $conn->close();
    }
?>
