<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Update Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        // attempt to connect to DB
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['login'])) {
            echo "<a href='employee_login.html' class='header_link'>Employee Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }

        // CHECK THAT ALL FORM VARIABLES ARE SET //////////////////////////////////////////////////////
        $variable_not_set = false;
        $error_string = "";
        if (!isset($_POST['id'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product ID not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['name'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product name not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['description'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product description not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['cost'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product cost not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['sell_price'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product sell price not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['quantity'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product quantity not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['vendor_id'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Product not received.</span><br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['employee_id'])) {
            $error_string = $error_string . "<span class='error'>Form submit error: Employee ID not received.</span><br>";
            $variable_not_set = true;
        }

        // if any of the variables weren't set, kill program
        if ($variable_not_set) {
            print_error($error_string);
            die();
        }

        // get variables from form
        $name = $_POST['name'];
        $description = $_POST['description'];
        $cost = $_POST['cost'];
        $sell_price = $_POST['sell_price'];
        $quantity = $_POST['quantity'];
        $vendor_id = $_POST['vendor_id'];
        $employee_id = $_POST['employee_id'];
        
        // check that all variables are valid
        $variable_not_valid = false;
        $error_string = "";
        if (strlen($name) > 50) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product name must be no more than 50 characters.</span><br>";
            $variable_not_valid = true;
        }
        if (strlen($description) > 100) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product description must be no more than 200 characters.</span><br>";
            $variable_not_valid = true;
        }
        if ($cost < 0) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product cost must be 0 or greater.</span><br>";
            $variable_not_valid = true;
        }
        if ($sell_price < 0) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product sell price must be 0 or greater.</span><br>";
            $variable_not_valid = true;
        }
        if ($quantity < 0) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product quantity must be 0 or greater.</span><br>";
            $variable_not_valid = true;
        }
        if ($cost > $sell_price) {
            $error_string = $error_string . "<span class='error'>Insert failed: Product cost must be less than or equal to sell price.</span><br>";
            $variable_not_valid = true;
        }
        if ($variable_not_valid) {
            print_error($error_string);
            die();
        }

        // CHECK FOR DUPLICATES
        $query = "SELECT name FROM 2023F_fisheral.PRODUCT WHERE name = ?;";
        $stmt = $con->prepare($query);
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            echo "<a href='employee_add_product.php' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>Insert failed: Product name already exists in database.</span><br>";
            die();
        }
    
        // INSERT PRODUCT INTO DATABASE //////////////////////////////////////////////////////

        $query = "INSERT INTO 2023F_fisheral.PRODUCT (name, description, cost, sell_price, quantity, vendor_id, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $con->prepare($query);
        $stmt->bind_param('ssddiii', $name, $description, $cost, $sell_price, $quantity, $vendor_id, $employee_id);
        
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
        
        echo "<a href='employee_check.php' class='header_link'>Employee Home</a><br><br>";
        echo "<span class='success'>Inserted product successfully.</span><br>";
        
        $stmt->close();
        die();

        function print_error($msg) {
            echo "<a href='employee_add_product.php' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>$msg</span><br>";
        }
    ?>
</body>
</html>