<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee - Update Product</title>
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

    ?>
    <a href="employee_search_product.php" class="header_link">Go Back</a><a href="employee_check.php" class="header_link">Employee Home</a><br><br>
    <b>Employee - Update Product</b><br><br>
    <?php

        $num_updated_products = 0;

        // get id from form
        $id = $_POST['id'];
        $num_products_to_update = count($id);

        // get employee id of user who is logged in and updating the products
        $employee_id = $_POST['employee_id'];
        
        // try update for each item separately
        for ($i = 0; $i < $num_products_to_update; $i++) {
            // get variables from form
            $id = $_POST['id'][$i];
            $name = $_POST['name'][$i];
            $description = $_POST['description'][$i];
            $cost = $_POST['cost'][$i];
            $sell_price = $_POST['sell_price'][$i];
            $quantity = $_POST['quantity'][$i];
            $vendor_id = $_POST['vendor_id'][$i];


            // check that all variables are valid
            $variable_not_valid = false;
            $error_array = array();
            if (strlen($name) > 50) {
                array_push($error_array, "Product name must be no more than 50 characters.");
                $variable_not_valid = true;
            }
            if (strlen($description) > 100) {
                array_push($error_array, "Product description must be no more than 200 characters.");
                $variable_not_valid = true;
            }
            if ($cost < 0) {
                array_push($error_array, "Product cost must be 0 or greater.");
                $variable_not_valid = true;
            }
            if ($sell_price < 0) {
                array_push($error_array, "Product sell price must be 0 or greater.");
                $variable_not_valid = true;
            }
            if ($quantity < 0) {
                array_push($error_array, "Product quantity must be 0 or greater.");
                $variable_not_valid = true;
            }
            if ($cost > $sell_price) {
                array_push($error_array, "Product cost must be less than or equal to sell price.");
                $variable_not_valid = true;
            }
            if (strlen($name) == 0) {
                array_push($error_array, "Product name cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen($description) == 0) {
                array_push($error_array, "Product description cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen(strval($cost)) == 0) {
                array_push($error_array, "Product cost cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen(strval($sell_price)) == 0) {
                array_push($error_array, "Product sell price cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen(strval($quantity)) == 0) {
                array_push($error_array, "Product quantity cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen(strval($vendor_id)) == 0) {
                array_push($error_array, "Product vendor cannot be blank.");
                $variable_not_valid = true;
            }
            if (strlen(strval($employee_id)) == 0) {
                array_push($error_array, "Employee ID cannot be blank.");
                $variable_not_valid = true;
            }
            if ($variable_not_valid) {
                print_update_failed_error($id, $error_array);
                continue;
            }

            // CHECK TO SEE IF ANY FIELDS WERE CHANGED
            $query = "SELECT name FROM 2023F_fisheral.PRODUCT where id = ? AND name = ? AND description = ? AND cost = ? AND sell_price = ? AND quantity = ? AND vendor_id = ?;";
            $stmt = $con->prepare($query);
            $stmt->bind_param('issddii', $id, $name, $description, $cost, $sell_price, $quantity, $vendor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (mysqli_num_rows($result) > 0) {
                // NO FIELDS WERE CHANGED, DO NOT UPDATE, CONTINUE TO NEXT PRODUCT
                continue;
            }
    
            // CHECK FOR DUPLICATE PRODUCT NAME
            $query = "SELECT name FROM 2023F_fisheral.PRODUCT WHERE name = ? AND NOT(id=?);";
            $stmt = $con->prepare($query);
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (mysqli_num_rows($result) > 0) {
                print_update_failed_error($id, array("There is a different product in the database named '$name' already."));
                continue;
            }
        
            // INSERT PRODUCT INTO DATABASE //////////////////////////////////////////////////////
    
            $query = "UPDATE 2023F_fisheral.PRODUCT SET name = ?, description = ?, cost = ?, sell_price = ?, quantity = ?, vendor_id = ?, employee_id = ? WHERE id = ?;";
            $stmt = $con->prepare($query);
            $stmt->bind_param('ssddiiii', $name, $description, $cost, $sell_price, $quantity, $vendor_id, $employee_id, $id);
            
            if (!$stmt) {
                print_update_failed_error($id, array("Prepared statement failed: (" . $con->errno . ") " . $con->error));
                continue;
            }
            
            if (!$stmt->execute()) {
                print_update_failed_error($id, array("Execute failed: (" . $stmt->errno . ") " . $stmt->error));
                continue;
            }
            
            if ($stmt->affected_rows == 0) {
                print_update_failed_error($id, array("0 affected rows."));
                continue;
            }
            
            echo "<span class='success'>Updated product $id successfully.</span><br>";
            $num_updated_products++;
            
            $stmt->close();
        }

        echo "<br>";
        if ($num_updated_products == 0) {
            echo "<b>No products were updated.</b><br>";
        }
        else {
            echo "<b>Updated $num_updated_products product(s) successfully.</b><br>";
        }

        die();

        function print_error($msg) {
            echo "<a href='employee_search_product.php' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>$msg</span><br>";
        }

        function print_update_failed_error($product_id, $error_array) {
            echo "<span class='error'>Update failed for product $product_id for the following reasons:<ul>";
            foreach ($error_array as $error_msg) {
                echo "<li>$error_msg</li>";
            }
            echo "</ul></span>";
        }
    ?>
</body>
</html>