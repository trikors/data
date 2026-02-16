<?php
    require_once 'login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    $tableName = "myTable";
    if($conn->connect_error) die ("No Connection Fatal Error");
    $query = "SHOW TABLES LIKE '$tableName'"; //need to use '' here for $tableName
    $result = $conn->query($query);
    if(!$result) die ("No Result Fatal Error");

    $rows = $result->num_rows;

    //see if a table has been created, create one if not
    if($rows > 0)
        echo "there is a table inside database\n";
    else{
        echo "table not found inside database\n";
        echo "type CREATE to create table\n"; //create table
        $command = trim(fgets(STDIN));
        if($command == "CREATE"){
            echo "creating table...\n";
            $query = "CREATE TABLE $tableName(
                ID int NOT NULL AUTO_INCREMENT,
                Title varchar(255),
                Description TEXT,
                Status varchar(255),
                PRIMARY KEY(ID)
                )";
            $result = $conn->query($query);
            if(!$result) die ("Table Creation Failed\n");
            else echo "Table Created\n";
        }
    }

    $instructions = "\n
                    Enter POST to enter a task(title, description, status)\n
                    Enter GET to get a list of all the tasks in the table\n
                    Enter GETID to get an individual task by the ID specified\n
                    Enter PUT to update a task\n
                    Enter DELETE to delete a specified by ID task
                    Enter QUIT to exit the program\n";

    echo $instructions;

    while(TRUE){
        echo "Please enter a task?\n";
        $command = trim(fgets(STDIN));
        switch($command){
            case "POST":  //input a piece of data into the system
                echo "Enter Task: Title, Description, Status\n";
                $title = '';   //create separate items for logging data
                $description = '';
                $status = '';
                while(strlen($title) == 0){ //loop while size is 0
                    echo "Enter title:\n";
                    $title = trim(fgets(STDIN));
                }
                while(strlen($description) == 0){
                    echo "Enter Description:\n";
                    $description = trim(fgets(STDIN));
                }
                while(strlen($status) == 0){
                    echo "Enter Status:\n";
                    $status = trim(fgets(STDIN));
                }
                $query = "INSERT INTO $tableName(Title, Description, Status) VALUES ('$title', '$description', '$status')";
                $result = $conn->query($query);
                if(!$result) die ("POST input failed\n");
                else echo "Data Inputted\n";
                break;
            case "GET":   //get all the data from table, all rows
                $query = "SELECT * FROM $tableName";
                $result = $conn->query($query);
                if(!$result) die ("Getting data failed\n");
                else{
                    $rows = $result->num_rows;
                    echo "number of rows: $rows\n";
                    for($i = 0; $i < $rows; $i++){
                        $row = $result->fetch_array(MYSQLI_ASSOC);
                        echo 'ID: ' . $row['ID'] . "\n";
                        echo 'Title: ' . $row['Title'] . "\n";
                        echo 'Description: ' . $row['Description'] . "\n";
                        echo 'Status: ' . $row['Status'] . "\n";
                    }
                }
                break;
            case "GETID": //get a specific piece of data
                echo "enter the desired ID to select\n";
                $ID = fgets(STDIN);
                //check that ID is in range
                $status = checkIdNumber($ID, $tableName, $conn);

                if($status == 1)
                    break;

                $query = "SELECT * FROM $tableName WHERE ID = $ID";
                $result = $conn->query($query);
                if(!$result) die ("Getting data by ID failed\n");
                else{
                    $data = $result->fetch_array(MYSQLI_ASSOC);
                    echo 'ID: ' . $data['ID'] . "\n";
                    echo 'Title: ' . $data['Title'] . "\n";
                    echo 'Description: ' . $data['Description'] . "\n";
                    echo 'Status: ' . $data['Status'] . "\n";
                }
                break;
            case "PUT":  //update a piece of data based on iD
                echo "enter the desired ID to select\n";
                $ID = fgets(STDIN);
                //check the iD
                $status = checkIdNumber($ID, $tableName, $conn);
                if($status == 1)
                    break;
                echo "enter the fields you want to change, Title, Description, Status\n";
                $title = '';
                $description = '';
                $status = '';

                while(strlen($title) == 0){
                    echo "Please enter new title: \n";
                    $title = trim(fgets(STDIN));
                }
                while(strlen($description) == 0){
                    echo "Please enter new description: \n";
                    $description = trim(fgets(STDIN));
                }
                while(strlen($status) == 0){
                    echo "Please enter new status: \n";
                    $status = trim(fgets(STDIN));
                }

                $query = "UPDATE $tableName
                SET Title = '$title', Description = '$description', Status = '$status'
                WHERE ID = '$ID'";

                $result = $conn->query($query);
                if(!$result) die ("Failed to Update the data in table\n");
                else echo "Table data successfuly Updated\n";
                break;
            case "DELETE": //delete a row from the table
                echo "enter the desired ID to select:\n";
                $ID = fgets(STDIN);
                //check the id
                $status = checkIdNumber($ID, $tableName, $conn);
                if($status == 1)
                    break;

                $query = "DELETE FROM $tableName WHERE ID = '$ID'";
                $result = $conn->query($query);
                if(!$result) die ("Failed to Delete the row in the table\n");
                else echo "Table row successfuly Deleted\n";
                break;
            case "QUIT":
                return 0;
            default:
                echo "Please Enter Something...\n$instructions\n";
                $command = trim(fgets(STDIN));
                if($command == "QUIT")
                    return 0;
                break;
        }
    }

    function checkIdNumber($ID, $tableName, $conn){ //function to check whether ID number is in table
        $checkQuery = "SELECT * FROM $tableName WHERE ID = '$ID'"; //check ID exists
        $validate = $conn->query($checkQuery);
        //$methods = get_class_methods($validate);
        //print_r($methods);
        //echo "number of rows " . $validate->num_rows . "\n";

        if($validate->num_rows > 0){  //the ID exists
            return 0;
        }
        else{
            echo "Entered wrong ID number\n";  //ID doesnt exist
            return 1;
        }
    }

    $conn->close();
?>
