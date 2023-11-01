<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Order History</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
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
        
        echo <<<HTML
            <a href="customer_check_p2.php" class="header_link">Customer Home</a><br><br>
            <b>Customer - Order History</b><br><br>
        HTML;

        // get total amount spent by customer
        $query = "SELECT SUM(po.quantity * p.sell_price) AS total from 2023F_fisheral.PRODUCT_ORDER po LEFT JOIN 2023F_fisheral.PRODUCT p ON (po.product_id = p.id) WHERE po.order_id IN (SELECT id FROM 2023F_fisheral.`ORDER` WHERE customer_id = $customer_id);";
        $result = mysqli_query($con, $query);

        if (!$result) {
            print_error("Query getting total spent failed: " . mysqli_error($con));
            die();
        }

        $total_spent = mysqli_fetch_array($result)['total'];

        // get all orders made by this customer
        $query = "SELECT id as order_id, date from 2023F_fisheral.`ORDER` WHERE customer_id = $customer_id ORDER BY date desc;";
        $result = mysqli_query($con, $query);

        if(!$result) {
            print_error("Query getting order history failed: " . mysqli_error($con));
            die();
        }

        if (mysqli_num_rows($result) < 1) {
            print_error("You have not made any orders on this account.");
            die();
        }

        $num_orders = mysqli_num_rows($result);
        
        echo "You have made a total of <b>$num_orders</b> orders and have spent a total of <b>$total_spent</b> with us.<br><br>";


        // PRINT OUT INFORMATION FOR EACH ORDER //////////////////////////////
        while ($row = mysqli_fetch_array($result)) {
            $order_id = $row['order_id'];
            $date = $row['date'];
            
            echo "<table border=1>";
            echo "<tr><td colspan=2><b>Order #:</b> $order_id<td colspan=2><b>Date:</b> $date";
            
            $query = "SELECT p.name, p.sell_price, po.quantity, (p.sell_price * po.quantity) as subtotal FROM 2023F_fisheral.PRODUCT_ORDER po left join 2023F_fisheral.PRODUCT p on (po.product_id = p.id) WHERE po.order_id = $order_id;";
            $products_result = mysqli_query($con, $query);
            
            if(!$products_result) {
                echo "<tr><td colspan=4>" . print_error("Query getting order info failed: " . mysqli_error($con)) . "</td></tr></table>";
                continue;
            }
    
            if (mysqli_num_rows($products_result) < 1) {
                echo "<tr><td colspan=4>" . print_error("No products found for this order.") . "</td></tr></table>";
                continue;
            }

            $total = 0;
            echo "<tr><th>Product Name<th>Unit Price<th>Quantity Ordered<th>Subtotal";
            while ($row = mysqli_fetch_array($products_result)) {
                $product_name = $row['name'];
                $unit_price = $row['sell_price'];
                $quantity_ordered = $row['quantity'];
                $subtotal = $row['subtotal'];
                echo "<tr><td>$product_name<td>$unit_price<td>$quantity_ordered<td>$subtotal";
    
                $total = $total + $subtotal;
            }
            echo "<tr><td colspan=3><b>Order Total<td><b>$total</b>";
            echo "</table><br><br>";
        }

        //mysqli_free_result($result);

        function print_error($msg) {
            echo "<span class='error'>$msg</span><br>";
        }
        
    ?>

</body>
</html>