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
    $idExam = "";
    $examDate = "";
    $examDuration = "";
    $examTime = "";
    $idModule = "";

    if($_SERVER['QUERY_STRING'] != ""){
        if($_GET['mode'] == "edit"){
            // Get values from the row the user wants to update -> So I can display them
            list($idExam, $examDate, $examDuration, $examTime, $idModule) = getValues($_GET['id']);
        }
        else{
            // Delete field
            deleteRow($_GET['id']);
        }
    }
    
    if(isset($_POST['edit-row']) && strlen($_POST['id-exam-h']) > 0){
        editRow(); // Update table
    }

    if(isset($_POST['add-row']) && strlen($_POST['id-exam-h']) == 0){
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
        <h1>Exam Manager</h1>
        <div id="search">
            <form class="box" action="exams.php" method="post">
                <input type="text" name="search" placeholder="Search">
                <button type="submit" name="submit-search" value="Search"><i class="fa fa-search" style="font-size: 18px; color: white"></i></button>
                <button type="submit" name="home" value="Home"><i class="fa fa-home" style="font-size: 18px; color: white"></i></button>
            </form>
        <div>
        <div id="edit-table-container">
            <form class='edit-table' action='exams.php' method='post'>
                <input type="text" name="id-exam" placeholder="ID Exam" disabled value = <?php echo $idExam;?>><i class="fa fa-lock" style="margin-left: -10px"></i>
                <input type="text" name="id-exam-h" hidden value = <?php echo $idExam;?>>
                <input type="text" name="exam-date" placeholder="Exam Date" value="<?php echo $examDate;?>">
                <input type="text" name="exam-duration" placeholder="Exam Duration" value="<?php echo $examDuration;?>">
                <input type="text" name="exam-time" placeholder="Exam Time" value="<?php echo $examTime;?>">
                <!-- <input type="text" name="id-module" placeholder="ID Module" value="<?php //echo $idModule;?>"> -->
                <select name="id-module" id="dropdown">
                    <?php
                        $itemSelected = ""; 
                        $conn = new mysqli("localhost", "root", "", "MYDB"); //Connect to db
                        $sql = "SELECT ID_MODULE, MODULE_NAME FROM MODULES";
                        if($result = $conn->query($sql)){
                            $row_count = $result->num_rows;
                            for($i = 0; $i < $row_count; $i++){
                                if($row = $result->fetch_assoc()){
                                    if($row['ID_MODULE'] == $idModule){
                                        $itemSelected = "selected";
                                    }
                                    else{
                                        $itemSelected = ""; 
                                    }
                                    echo "<option value=" . $row['ID_MODULE'] . " " . $itemSelected . ">" . $row['MODULE_NAME'] . "</option>";
                                }
                            }
                        }
                        $conn->close();
                    ?>
                </select>
                <?php
                    if($_SERVER['QUERY_STRING'] != ""){
                        if($_GET['mode'] == "edit"){
                            echo "<button type='submit' name='edit-row' value='Add'><i class='fa fa-clone'></i></button>";
                            echo "<button type='submit'" . "formaction='exam-questions.php?id-exam=" . $idExam . "&id=" . "'name='questions' value='Questions'><i class='fa fa-file'></i></button>";
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
                    <th>ID Exam</th>
                    <th>Exam&nbsp;Date</th>
                    <th>Exam&nbsp;Duration</th>
                    <th>Exam Time</th>
                    <th>ID Module</th>
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
            $sql = "SELECT ID_EXAM, EXAM_DATE, EXAM_DURATION, EXAM_TIME, MODULES.MODULE_NAME AS MODULE_NAME FROM EXAMS
                    INNER JOIN MODULES ON EXAMS.ID_MODULE = MODULES.ID_MODULE 
                    WHERE CONVERT(ID_EXAM, CHAR) = '$search' OR EXAM_DATE LIKE '%$search%' OR EXAM_DURATION LIKE '%$search%' OR EXAM_TIME LIKE '%$search%' OR MODULE_NAME LIKE '%$search%'";
        }
        else{
            // If nothing is search select all the contents on the table
            $sql = "SELECT ID_EXAM, EXAM_DATE, EXAM_DURATION, EXAM_TIME, MODULES.MODULE_NAME AS MODULE_NAME FROM EXAMS
                    INNER JOIN MODULES ON EXAMS.ID_MODULE = MODULES.ID_MODULE";
        }
        
        if($result = $conn->query($sql)){
            // Display contents of the select query
            $row_count = $result->num_rows;
            for($i = 0; $i < $row_count; $i++){
                if($row = $result->fetch_assoc()){
                    echo "<tr>";
                        echo "<td>" . $row['ID_EXAM'] . "</td>";
                        echo "<td>" . $row['EXAM_DATE'] . "</td>";
                        echo "<td>" . $row['EXAM_DURATION'] . "</td>";
                        echo "<td>" . $row['EXAM_TIME'] . "</td>";
                        echo "<td>" . $row['MODULE_NAME'] . "</td>";
                        echo "<td id='action'>" . "<a href='exams.php?mode=edit&id=" . $row['ID_EXAM'] . "'>" . "<i class='fa fa-edit'" . "style='font-size: 23px; color: black; text-align: center'></i>" . "</a>&nbsp;&nbsp;";
                        echo "<a href='exams.php?mode=delete&id=" . $row['ID_EXAM'] . " '>". "<i class='fa fa-trash'" . "style='font-size: 23px; color: #ff7675;'></i>" . "</a></td>";
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
        $sql = "SELECT ID_EXAM, EXAM_DATE, EXAM_DURATION, EXAM_TIME, ID_MODULE FROM EXAMS
                    WHERE ID_EXAM = $id"; // Select the conents of the row that is going to be edited -> So this contents can be displayed in the form
        if($result = $conn->query($sql)){
            if($row = $result->fetch_assoc()){
                // Save the values in the variabled below
                $idExam = $id;
                $examDate = $row['EXAM_DATE'];
                $examDuration = $row['EXAM_DURATION'];
                $examTime = $row['EXAM_TIME'];
                $idModule =  $row['ID_MODULE'];
            }
        }
        // echo $sql;
        $conn->close();
        return array($idExam, $examDate, $examDuration, $examTime, $idModule); // Return all the values
    }

    function deleteRow($id){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to the db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "DELETE FROM EXAMS WHERE ID_EXAM = $id"; // Delete selected row
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
        $examDate = $_POST['exam-date'];
        $examDuration = $_POST['exam-duration'];
        $examTime = $_POST['exam-time'];
        $idModule =  $_POST['id-module'];
        
        if(isset($examDate) && isset($examDuration) && isset($examTime) && isset($idModule)){
            $last_id = "SELECT MAX(ID_EXAM)+1 AS ID_MAX FROM EXAMS"; // Get a new id
            if($result = $conn->query($last_id)){
                if($row = $result->fetch_assoc()){
                    $last_id = $row['ID_MAX'];
                }
            }
            if(is_null($last_id)){
                // If there are no rows, new id = 1;
                $last_id = 1; 
            }

            $sql = "INSERT INTO EXAMS(ID_EXAM, EXAM_DATE, EXAM_DURATION, EXAM_TIME, ID_MODULE)
                        VALUES($last_id, DATE('$examDate'), '$examDuration', '$examTime', $idModule)"; // Insert the values from the form
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
        $id = $_POST['id-exam-h'];
        $examDate = $_POST['exam-date'];
        $examDuration = $_POST['exam-duration'];
        $examTime = $_POST['exam-time'];
        $idModule =  $_POST['id-module'];

        $sql = "UPDATE EXAMS
                    SET EXAM_DATE = '$examDate', EXAM_DURATION = '$examDuration', EXAM_TIME = '$examTime', ID_MODULE = $idModule
                    WHERE ID_EXAM = $id"; // Update the values from the form
        // echo $sql;
        if($conn->query($sql) === True){
            // Inform the user that the update was completed
            echo "<script language='JavaScript'>alert('Row updated successfully');</script>";
        }
        else{
            // Inform the user that an error occured when updating the table
            echo "<script language='JavaScript'>alert('Error updated not record!);</script>";
        }
        $conn->close();
    }
?>
