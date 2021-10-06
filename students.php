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
    $idStudent = "";
    $studentName = "";
    $studentSurname = "";
    $studentDOB = "";

    if($_SERVER['QUERY_STRING'] != ""){
        if($_GET['mode'] == "edit"){
            // Get values from the row the user wants to update -> So I can display them
            list($idStudent, $studentName, $studentSurname, $studentDOB) = getValues($_GET['id']);
        }
        else{
            // Delete field
            deleteRow($_GET['id']);
        }
    }
    
    if(isset($_POST['edit-row']) && strlen($_POST['id-student-h']) > 0){
        editRow(); // Update table
    }

    if(isset($_POST['add-row']) && strlen($_POST['id-student-h']) == 0){
        addRow(); // Insert into table
    }

    function connection(){
        return new mysqli("localhost", "root", "", "MYDB"); //Connect to db
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
        <h1>Student Manager</h1>
        <div id="search">
            <form class="box" action="students.php" method="post">
                <input type="text" name="search" placeholder="Search">
                <button type="submit" name="submit-search" value="Search"><i class="fa fa-search" style="font-size: 18px; color: white"></i></button>
                <button type="submit" name="home" value="Home"><i class="fa fa-home" style="font-size: 18px; color: white"></i></button>
            </form>
        <div>
        <div id="edit-table-container">
            <form class='edit-table' action='students.php' method='post'>
                <input type="text" name="id-student" placeholder="ID Student" disabled value = <?php echo $idStudent;?>><i class="fa fa-lock" style="margin-left: -10px"></i>
                <input type="text" name="id-student-h" hidden value = <?php echo $idStudent;?>>
                <input type="text" name="student-name" placeholder="Student Name" value="<?php echo $studentName;?>">
                <input type="text" name="student-surname" placeholder="Student Surname" value="<?php echo $studentSurname;?>">
                <input type="text" name="student-dob" placeholder="Student DOB" value="<?php echo $studentDOB;?>">
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
                    <th>ID Student</th>
                    <th>Student&nbsp;Name</th>
                    <th>Student Surname</th>
                    <th>Student DOB</th>
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
        $conn = connection(); //Connect to db
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        if(isset($_POST['search'])){
            // Search for anything the user types
            $search = $_POST["search"];
            $sql = "SELECT ID_STUDENT, STUDENT_NAME, STUDENT_SURNAME, STUDENT_DOB FROM STUDENTS
                    WHERE CONVERT(ID_STUDENT, CHAR) = '$search' OR STUDENT_NAME LIKE '%$search%' OR STUDENT_SURNAME LIKE '%$search%' OR STUDENT_DOB LIKE '%$search%'";
        }else{
            // If nothing is search select all the contents on the table
            $sql = "SELECT ID_STUDENT, STUDENT_NAME, STUDENT_SURNAME, STUDENT_DOB FROM STUDENTS";
        }
        // echo $sql;
        if($result = $conn->query($sql)){
            // Display contents of the select query
            $row_count = $result->num_rows;
            for($i = 0; $i < $row_count; $i++){
                if($row = $result->fetch_assoc()){
                    echo "<tr>";
                        echo "<td>" . $row['ID_STUDENT'] . "</td>";
                        echo "<td>" . $row['STUDENT_NAME'] . "</td>";
                        echo "<td>" . $row['STUDENT_SURNAME'] . "</td>";
                        echo "<td>" . $row['STUDENT_DOB'] . "</td>";
                        echo "<td id='action'>" . "<a href='students.php?mode=edit&id=" . $row['ID_STUDENT'] . "'>" . "<i class='fa fa-edit'" . "style='font-size: 23px; color: black; text-align: center'></i>" . "</a>&nbsp;&nbsp;";
                        echo "<a href='students.php?mode=delete&id=" . $row['ID_STUDENT'] . " '>". "<i class='fa fa-trash'" . "style='font-size: 23px; color: #ff7675;'></i>" . "</a></td>";
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
        $sql = "SELECT ID_STUDENT, STUDENT_NAME, STUDENT_SURNAME, STUDENT_DOB FROM STUDENTS
                    WHERE ID_STUDENT = $id"; // Select the conents of the row that is going to be edited -> So this contents can be displayed in the form
        if($result = $conn->query($sql)){
            if($row = $result->fetch_assoc()){
                // Save the values in the variabled below
                $idStudent = $id;
                $studentName = $row['STUDENT_NAME'];
                $studentSurname = $row['STUDENT_SURNAME'];
                $studentDOB = $row['STUDENT_DOB'];
            }
        }
        // echo $sql;
        $conn->close();
        return array($idStudent, $studentName, $studentSurname, $studentDOB); // Return all the values
    }

    function deleteRow($id){
        $conn = new mysqli("localhost", "root", "", "MYDB"); // Connect to the db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "DELETE FROM STUDENTS WHERE ID_STUDENT = $id"; // Delete selected row

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
        $conn = connection(); // Connect to db
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Save the values from the form into variables
        $studentName = $_POST['student-name'];
        $studentSurname = $_POST['student-surname'];
        $studentDOB = $_POST['student-dob'];
        
        if(isset($studentName) && isset($studentSurname) && isset($studentDOB)){
            $last_id = "SELECT MAX(ID_STUDENT)+1 AS ID_MAX FROM STUDENTS"; // Get a new id
            if($result = $conn->query($last_id)){
                if($row = $result->fetch_assoc()){
                    $last_id = $row['ID_MAX'];
                }
            }
            if(is_null($last_id)){
                // If there are no rows, new id = 1;
                $last_id = 1; 
            }
            $sql = "INSERT INTO STUDENTS(ID_STUDENT, STUDENT_NAME, STUDENT_SURNAME, STUDENT_DOB)
                        VALUES($last_id, '$studentName', '$studentSurname', DATE('$studentDOB'))"; // Insert the values from the form
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
        $id = $_POST['id-student-h'];
        $studentName = $_POST['student-name'];
        $studentSurname = $_POST['student-surname'];
        $studentDOB = $_POST['student-dob'];

        $sql = "UPDATE STUDENTS
                    SET STUDENT_NAME = '$studentName', STUDENT_SURNAME = '$studentSurname', STUDENT_DOB = DATE('$studentDOB')
                    WHERE ID_STUDENT = $id"; // Update the values from the form
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
