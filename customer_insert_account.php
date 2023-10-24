<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Insert Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        // attempt to connect to DB
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // CHECK THAT ALL FORM VARIABLES ARE SET //////////////////////////////////////////////////////
        $variable_not_set = false;
        $error_string = "";
        if (!isset($_POST['login_id'])) {
            $error_string = $error_string . "Form submit error: Username not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['password'])) {
            $error_string = $error_string . "Form submit error: Password not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['confirm_password'])) {
            $error_string = $error_string . "Form submit error: Confirm password not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['first_name'])) {
            $error_string = $error_string . "Form submit error: First name not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['last_name'])) {
            $error_string = $error_string . "Form submit error: Last name not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['tel'])) {
            $error_string = $error_string . "Form submit error: Telephone number not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['address'])) {
            $error_string = $error_string . "Form submit error: Address not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['city'])) {
            $error_string = $error_string . "Form submit error: City not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['zipcode'])) {
            $error_string = $error_string . "Form submit error: Zipcode not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['state'])) {
            $error_string = $error_string . "Form submit error: State not received.<br>";
            $variable_not_set = true;
        }

        // if any of the variables weren't set, kill program
        if ($variable_not_set) {
            print_error($error_string);
            die();
        }

        // get variables from form
        $login_id = $_POST['login_id'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $tel = $_POST['tel'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $zipcode = $_POST['zipcode'];
        $state = $_POST['state'];
        
        // check that all variables are valid
        $variable_not_valid = false;
        $error_string = "";

        if (strlen($login_id) > 15) {
            $error_string = $error_string . "Insert failed: Username must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($password) > 15) {
            $error_string = $error_string . "Insert failed: Password must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($first_name) > 15) {
            $error_string = $error_string . "Insert failed: First name must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($last_name) > 15) {
            $error_string = $error_string . "Insert failed: Last name must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($tel) > 15) {
            $error_string = $error_string . "Insert failed: Telephone number must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($address) > 200) {
            $error_string = $error_string . "Insert failed: Address must be no more than 200 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($city) > 15) {
            $error_string = $error_string . "Insert failed: City must be no more than 15 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($zipcode) > 10) {
            $error_string = $error_string . "Insert failed: Zipcode must be no more than 10 characters.<br>";
            $variable_not_valid = true;
        }
        if (strlen($state) > 2) {
            $error_string = $error_string . "Insert failed: State must be no more than 2 characters.<br>";
            $variable_not_valid = true;
        }
        if ($password != $confirm_password) {
            $error_string = $error_string . "Insert failed: Confirm password does not match password.<br>";
            $variable_not_valid = true;
        }
        if ($variable_not_valid) {
            print_error($error_string);
            die();
        }

        // CHECK FOR DUPLICATES
        $query = "SELECT login_id FROM 2023F_fisheral.CUSTOMER WHERE login_id = ?;";
        $stmt = $con->prepare($query);
        $stmt->bind_param('s', $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            print_error("Insert failed: Username already exists in database.<br>");
            die();
        }
    
        // INSERT PRODUCT INTO DATABASE //////////////////////////////////////////////////////

        // Prepare statement
        $query = "INSERT INTO 2023F_fisheral.CUSTOMER (login_id, password, first_name, last_name, tel, address, city, zipcode, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $stmt = $con->prepare($query);
        $stmt->bind_param('sssssssss', $login_id, $password, $first_name, $last_name, $tel, $address, $city, $zipcode, $state);
        
        if (!$stmt) {
            print_error("Insert failed (prepared statement failed): (" . $con->errno . ") " . $con->error);
            die();
        }
        
        if (!$stmt->execute()) {
            print_error("Insert failed (Execute failed): (" . $stmt->errno . ") " . $stmt->error);
            die();
        }
        
        if ($stmt->affected_rows == 0) {
            print_error("Insert failed, 0 affected rows.");
            die();
        }
        
        echo "<a href='customer_login.html' class='header_link'>Customer Login</a><br><br>";
        echo "<span class='success'>Account created successfully.</span><br>";
        
        $stmt->close();
        die();

        function print_error($msg) {
            echo "<a href='customer_create_account.php' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>$msg</span><br>";
        }
    ?>
</body>
</html>