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

    function connection(){
        return new mysqli("localhost", "root", "", "MYDB"); //Connect to db
    }

    // Create variables for all the fields in the table
    $idPart = "";
    $idQuestion = "";
    $question = "";
    $sampleAnswer = "";
    $marks = "";
    $idExam = $_GET['id-exam'];
    
    if(strpos($_SERVER['QUERY_STRING'], "=delete&")){
        list($question, $sampleAnswer, $marks) = getValues($_GET['id']);
        deleteRow($_GET['id'], $idPart); // Delete field
        $idPart = "";
        $question = "";
        $sampleAnswer = "";
        $marks = "";
    }
    
    if(strpos($_SERVER['QUERY_STRING'], "=edit&")){
        $idQuestion = $_GET['id'];
        $idPart = $_GET['id-part'];
        list($question, $sampleAnswer, $marks) = getValues($_GET['id']);
        // editRow(); // Update table
    }
    
    if(isset($_POST['edit-row'])){
        editRow($idExam); // Update table
    }

    if(isset($_POST['add-row']) && strlen($_POST['id-question-h']) == 0){
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
        <h1>Questions Manager</h1>
        <div id="search">
            <form class="box" action=<?php echo "exam-questions.php?id-exam=" . $idExam . "&id=" . $idQuestion;?> method="post">
                <input type="text" name="search" placeholder="Search">
                <button type="submit" name="submit-search" value="Search"><i class="fa fa-search" style="font-size: 18px; color: white"></i></button>
                <button type="submit" name="home" value="Home"><i class="fa fa-home" style="font-size: 18px; color: white"></i></button>
            </form>
        <div>
        <div id="header-div">
            <table class="exam-header">
                <thead>
                    <th>ID Exam</th>
                    <th>Exam&nbsp;Date</th>
                    <th>Exam&nbsp;Duration</th>
                    <th>Exam Time</th>
                    <th>Module Name</th>
                    <!-- <th>Action</th> -->
                </thead>
                <tbody>
                    <?php
                        getExam($idExam);
                    ?>
                </tbody>
            </table>
        <div>
        <div id="edit-table-questions-container">
            <form class='edit-table-questions' action=<?php echo "exam-questions.php?id-exam=" . $idExam . "&id=" . $idQuestion;?> method='post'>
                <input type="text" name="id-question" placeholder="ID Question" disabled value = <?php echo $idQuestion;?>><i class="fa fa-lock" style="margin-left: -10px"></i>
                <input type="text" name="id-question-h" hidden value = <?php echo $idQuestion;?>>
                <input type="text" name="id-question-part-h" hidden value = <?php echo $idPart;?>>
                <input type="text" name="question" placeholder="Question" value="<?php echo $question;?>">
                <input type="text" name="sample-answer" placeholder="Sample Answer" value="<?php echo $sampleAnswer;?>">
                <input type="text" name="marks" placeholder="Marks" value="<?php echo $marks;?>">
                <!-- <input type="text" name="id-module" placeholder="ID Module" value="<?php //echo $idModule;?>"> -->
                <!-- <select name="id-module" id="dropdown"> -->
                <?php
                    if(strpos($_SERVER['QUERY_STRING'], "=edit&")){
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
        <div id="table-questions-div">
            <table class="content-questions-table">
                <thead>
                    <th>ID&nbsp;Question</th>
                    <th>Question</th>
                    <th>Sample&nbsp;Answer</th>
                    <th>Marks</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php
                        mainPanel($idExam);
                    ?>
                </tbody>
            </table>
        <div>
    </body>
</html>

<?php  
    function getExam($idExam){
        // Get exam header
        $conn = connection();
        $sql = "SELECT ID_EXAM, EXAM_DATE, EXAM_DURATION, EXAM_TIME, MODULES.MODULE_NAME AS MODULE_NAME FROM EXAMS
                INNER JOIN MODULES ON EXAMS.ID_MODULE = MODULES.ID_MODULE
                WHERE ID_EXAM = $idExam";

        if($result = $conn->query($sql)){
            if($row = $result->fetch_assoc()){
                echo "<tr>";
                    echo "<td>" . $row['ID_EXAM'] . "</td>";
                    echo "<td>" . $row['EXAM_DATE'] . "</td>";
                    echo "<td>" . $row['EXAM_DURATION'] . "</td>";
                    echo "<td>" . $row['EXAM_TIME'] . "</td>";
                    echo "<td>" . $row['MODULE_NAME'] . "</td>";
                echo "</tr>";
            }
        }
        return $idExam;
        $conn->close();
    }

    function mainPanel($idExam){
        $conn = connection();
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        if(isset($_POST['search'])){
            // Search for anything the user types
            $search = $_POST["search"];
            $sql = "SELECT ID_PART, ID_QUESTION, QUESTION_DESC, SAMPLE_ANSWER, QUESTION_MARKS FROM EXAM_QUESTIONS
                    WHERE ID_EXAM = $idExam AND (CONVERT(ID_QUESTION, CHAR) = '$search' OR QUESTION_DESC LIKE '%$search%' OR SAMPLE_ANSWER LIKE '%$search%' OR QUESTION_MARKS = '$search')";
        }
        else{
            // If nothing is search select all the contents on the table
            $sql = "SELECT ID_PART, ID_QUESTION, QUESTION_DESC, SAMPLE_ANSWER, QUESTION_MARKS FROM EXAM_QUESTIONS WHERE ID_EXAM = $idExam";
        }

        // echo $sql;
        if($result = $conn->query($sql)){
            // Display contents of the select query
            $row_count = $result->num_rows;
            for($i = 0; $i < $row_count; $i++){
                if($row = $result->fetch_assoc()){
                    echo "<tr>";
                        echo "<td>" . $row['ID_QUESTION'] . "</td>";
                        echo "<td>" . $row['QUESTION_DESC'] . "</td>";
                        echo "<td>" . $row['SAMPLE_ANSWER'] . "</td>";
                        echo "<td>" . $row['QUESTION_MARKS'] . "</td>";
                        echo "<td id='action'>" . "<a href='exam-questions.php?id-exam=" . $idExam . "&mode=edit&id=" . $row['ID_QUESTION'] . "&id-part=" . $row['ID_PART'] . "'>" . "<i class='fa fa-edit'" . "style='font-size: 23px; color: black; text-align: center'></i>" . "</a>&nbsp;&nbsp;";
                        echo "<a href='exam-questions.php?id-exam=" . $idExam . "&mode=delete&id=" . $row['ID_QUESTION'] . "&id-part=" . $row['ID_PART'] . "'>". "<i class='fa fa-trash'" . "style='font-size: 23px; color: #ff7675;'></i>" . "</a></td>";
                    echo "</tr>";
                }
            }
        }
        $conn->close();
    }

    function getValues($id){
        $conn = connection();
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $idExam = $_GET['id-exam'];
        $idPart = $_GET['id-part'];
        $sql = "SELECT QUESTION_DESC, SAMPLE_ANSWER, QUESTION_MARKS FROM EXAM_QUESTIONS
                    WHERE ID_EXAM = $idExam AND ID_QUESTION = $id AND ID_PART = $idPart"; // Select the conents of the row that is going to be edited -> So this contents can be displayed in the form
        // echo $sql;
        if($result = $conn->query($sql)){
            if($row = $result->fetch_assoc()){
                // Save the values in the variabled below
                $question = $row['QUESTION_DESC'];
                $sampleAnswer = $row['SAMPLE_ANSWER'];
                $marks =  $row['QUESTION_MARKS'];
            }
        }

        $idExam = $_GET['id-exam'];
        $idQuestion = $_GET['id'];
        $idPart = $_GET['id-part'];

        // echo $sql;
        $conn->close();
        return array($question, $sampleAnswer, $marks); // Return all the values
    }

    function deleteRow($id){
        $conn = connection();
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $idPart = $_GET['id-part'];
        $sql = "DELETE FROM EXAM_QUESTIONS WHERE ID_QUESTION = $id AND ID_PART = $idPart"; // Delete selected row

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
        $conn = connection();
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Save the values from the form into variables
        $question = $_POST['question'];
        $sampleAnswer = $_POST['sample-answer'];
        $marks =  $_POST['marks'];
        $idExam = $_GET['id-exam']; 

        if(isset($question) && isset($sampleAnswer) && isset($marks)){
            $last_id = "SELECT MAX(ID_QUESTION)+1 AS ID_MAX FROM EXAM_QUESTIONS"; // Get a new id
            if($result = $conn->query($last_id)){
                if($row = $result->fetch_assoc()){
                    $last_id = $row['ID_MAX'];
                }
            }
            if(is_null($last_id)){
                // If there are no rows, new id = 1;
                $last_id = 1; 
            }

            $sql = "INSERT INTO EXAM_QUESTIONS(ID_QUESTION, ID_PART, QUESTION_DESC, SAMPLE_ANSWER, QUESTION_MARKS, ID_EXAM, ID_MODULE) 
                        VALUES($last_id, 1, '$question', '$sampleAnswer', $marks, $idExam, $idExam)"; // Insert the values from the form

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

    function editRow($idExam){
        $conn = connection();
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Save the values from the form into variables
        $idQuestion = $_POST['id-question-h'];
        $idPart = $_POST['id-question-part-h'];
        $question = $_POST['question'];
        $sampleAnswer = $_POST['sample-answer'];
        $marks =  $_POST['marks'];
    
        $sql = "UPDATE EXAM_QUESTIONS
                    SET ID_QUESTION = '$idQuestion', QUESTION_DESC = '$question', SAMPLE_ANSWER = '$sampleAnswer', QUESTION_MARKS = $marks
                    WHERE ID_QUESTION = $idQuestion AND ID_EXAM = $idExam AND ID_PART = $idPart"; // Update the values from the form

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