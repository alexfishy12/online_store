<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        // attempt to connect to DB
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['customer_id'])) {
            echo "<a href='customer_login.html' class='header_link'>Customer Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }

        $customer_id = $_COOKIE['customer_id'];
        // CHECK THAT ALL FORM VARIABLES ARE SET //////////////////////////////////////////////////////
        $variable_not_set = false;
        $error_string = "";
        if (!isset($_POST['product_id'])) {
            $error_string = $error_string . "Form submit error: Product ID not received.<br>";
            $variable_not_set = true;
        }
        if (!isset($_POST['order_quantity'])) {
            $error_string = $error_string . ">Form submit error: Order Quantity not received.<br>";
            $variable_not_set = true;
        }

        // if any of the variables weren't set, kill program
        if ($variable_not_set) {
            print_error($error_string);
            die();
        }

    ?>
    <a href="customer_check_p2.php" class="header_link">Customer Home</a><br><br>
    <b>Customer - Order</b><br><br>
    <?php

        $num_ordered_products = 0;
        $products_ordered = array();
        $order_errors = array();

        // get product_id from form
        $id = $_POST['product_id'];
        $num_products_in_list = count($id);

        // get items that the customer is ordering
        for ($i = 0; $i < $num_products_in_list; $i++) {
            // get variables from form
            $id = $_POST['product_id'][$i];
            $order_quantity = $_POST['order_quantity'][$i];

            if ($order_quantity < 1) {
                continue;
            }
            array_push($products_ordered, array("id" => $id, "order_quantity" => $order_quantity));
        }

        if (count($products_ordered) == 0) {
            print_error("No products were set with a quantity greater than 0 to be ordered.");
            die();
        }

        // variable to track if order failed
        $order_failed = false;
        // check to see that each item is available to be ordered
        for ($i = 0; $i < count($products_ordered); $i++) {
            // get variables from form
            $id = $products_ordered[$i]['id'];
            $order_quantity = $products_ordered[$i]['order_quantity'];

            // CHECK TO SEE IF PRODUCT IS ABLE TO BE PURCHASED
            $query = "SELECT quantity >= ? as has_enough_quantity FROM 2023F_fisheral.PRODUCT where id = ?;";
            $stmt = $con->prepare($query);
            $stmt->bind_param('ii', $order_quantity, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (mysqli_num_rows($result) == 0) {
                // PRODUCT DOESN'T EXIST, ORDER FAILED, CONTINUE TO NEXT PRODUCT FOR MORE ERROR FINDING
                $order_failed = true;
                array_push($order_errors, array("product_id" => $id, "msg" => "Product not found."));
                continue;
            }
            $product = mysqli_fetch_array($result);
            if (!$product['has_enough_quantity']) {
                // PRODUCT DOESN'T HAVE ENOUGH QUANTITY, ORDER FAILED, CONTINUE TO NEXT PRODUCT FOR MORE ERROR FINDING
                $order_failed = true;
                array_push($order_errors, array("product_id" => $id, "msg" => "Not enough quantity."));
            }
        }

        // if order failed, print error and die
        if ($order_failed) {
            print_order_failed_error($order_errors);
            die();
        }

        // ORDER IS POSSIBLE, UPDATE DATABASE //////////////////////////////////////////////////////
    
        // 1) Deduct quantity from PRODUCT table

        for ($i = 0; $i < count($products_ordered); $i++) {
            // get variables from form
            $id = $products_ordered[$i]['id'];
            $order_quantity = $products_ordered[$i]['order_quantity'];

            // UPDATE ITEM QUANTITY IN DATABASE
            $query = "UPDATE 2023F_fisheral.PRODUCT SET quantity = (quantity - ?) WHERE id = ?;";
            $stmt = $con->prepare($query);
            $stmt->bind_param('ii', $order_quantity, $id);
            $stmt->execute();
            if ($stmt->affected_rows == 0) {
                print_order_failed_error($id, array("0 affected rows during PRODUCT table quantity update."));
                die();
            }
        }

        // 2) Insert order information into ORDER table

        $query = "INSERT INTO 2023F_fisheral.ORDER (customer_id, date) VALUES (?, NOW());";
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            echo "<span class='error'>0 affected rows during ORDER table insert.</span><br>";
            die();
        }

        // 3) Get order_id from ORDER table, (last_submit_id), then insert each product from order into PRODUCT_ORDER table
        
        $order_id = $con->insert_id;
        
        // begin query with first product
        $id = $products_ordered[0]['id'];
        $order_quantity = $products_ordered[0]['order_quantity'];

        $query = "INSERT INTO 2023F_fisheral.PRODUCT_ORDER (order_id, product_id, quantity) VALUES ($order_id, $id, $order_quantity)";
        for ($i = 1; $i < count($products_ordered); $i++) {
            // get variables from form
            $id = $products_ordered[$i]['id'];
            $order_quantity = $products_ordered[$i]['order_quantity'];

            // INSERT EACH PRODUCT INTO PRODUCT_ORDER TABLE
            $query = $query. ", ($order_id, $id, $order_quantity)";
        }
        $query = $query . ";";
        $result = mysqli_query($con, $query);

        if(!$result) {
            print_error("Query failed: " . mysqli_error($con));
            die();
        }

        if (mysqli_affected_rows($con) == 0) {
            print_order_failed_error($id, array("0 affected rows during PRODUCT_ORDER table insert."));
            die();
        }

        // ORDER COMPLETE, PRINT OUT ORDER INFORMATION //////////////////////////////////////////////////////
        echo "<span class='success'>Order placed successfully.</span><br><br>";
        echo "<b>Order Information:</b><br>";

        $query = "SELECT ";

        $query = "SELECT p.name, p.sell_price, po.quantity, (p.sell_price * po.quantity) as subtotal FROM 2023F_fisheral.PRODUCT_ORDER po left join 2023F_fisheral.PRODUCT p on (po.product_id = p.id) WHERE po.order_id = $order_id;";
        $result = mysqli_query($con, $query);

        if(!$result) {
            print_error("Query getting order information failed: " . mysqli_error($con));
            die();
        }

        $total = 0;
        echo "<table border=1>";
        echo "<tr><th>Product Name<th>Unit Price<th>Quantity Ordered<th>Subtotal";
        while ($row = mysqli_fetch_array($result)) {
            $product_name = $row['name'];
            $unit_price = $row['sell_price'];
            $quantity_ordered = $row['quantity'];
            $subtotal = $row['subtotal'];
            echo "<tr><td>$product_name<td>$unit_price<td>$quantity_ordered<td>$subtotal";

            $total = $total + $subtotal;
        }
        echo "<tr><td colspan=3><b>Total</b><td><b>$total</b>";
        echo "</table>";


        die();

        function print_error($msg) {
            echo "<a href='customer_check_p2.php' class='header_link'>Customer Home</a><br><br>";
            echo "<span class='error'>$msg</span><br>";
        }

        function print_order_failed_error($error_array) {
            echo "<span class='error'>Order failed for the following reasons:<ul>";
            foreach ($error_array as $error) {
                $product_id = $error['product_id'];
                $msg = $error['msg'];
                echo "<li>Error ordering product with ID $product_id: $msg</li>";
            }
            echo "</ul></span>";
        }
    ?>
</body>
</html>