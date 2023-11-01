<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager - View Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['employee_id'])) {
            echo "<a href='employee_login.html' class='header_link'>Employee Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }
        $employee_id = $_COOKIE['employee_id'];
        
        echo <<<HTML
            <a href="employee_check.php" class="header_link">Employee Home</a><br><br>
            <b>Manager - View Reports</b><br><br>
        HTML;

        // get form data
        if (!isset($_POST['report_period'])) {
            print_error("Form submit error: Report period not received.");
            die();
        }
        if (!isset($_POST['report_type'])) {
            print_error("Form submit error: Report type not received.");
            die();
        }
        $report_period = $_POST['report_period'];
        $report_type = $_POST['report_type'];
        
        echo "Report by <b>$report_type</b> during period: <b>$report_period</b><br><br>";

        // set filter for report period 
        switch ($report_period) {
            case "all_time":
                $report_period_where_clause = "";
                break;
            case "past_week":
                $report_period_where_clause = "o.date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND o.date <= NOW()";
                break;
            case "current_month":
                $report_period_where_clause = "MONTH(o.date) = MONTH(NOW()) AND YEAR(o.date) = YEAR(NOW())";
                break;
            case "past_month":
                $report_period_where_clause = "o.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND o.date <= NOW()";
                break;
        }
   
        if ($report_type == "all_sales") {
            /* If the report type is by all sales, please list all orders’ information - product name, current quantity in 
            stock, quantity was sold, unit cost, price was sold, customer name who bought the items, net profit of the order, order 
            date, and the total of sub-total and total profit for all sales. If a product was never sold, don’t list it. (Figure 18)*/
            

            // get all orders within the report period
            $query = "SELECT o.id as order_id, o.date, c.first_name, c.last_name FROM 2023F_fisheral.ORDER o left join 2023F_fisheral.CUSTOMER c on (o.customer_id = c.customer_id)";
            if ($report_period_where_clause != "") {
                $query = $query . " WHERE " . $report_period_where_clause;
            }
            $query = $query . " ORDER BY DATE DESC;";
            //echo "<b>Query:</b> $query<br><br>";
            
            try {
                $result = mysqli_query($con, $query);
            }
            catch(Exception $e) {
                print_error("DATABASE ERROR: ". $e->getMessage());
                die();
            }

            if(!$result) {
                print_error("Query getting order history failed: " . mysqli_error($con));
                die();
            }

            if (mysqli_num_rows($result) == 0) {
                print_error("No orders found for this period.");
                die();
            }
            
            echo "<table border=1>";
            // PRINT OUT INFORMATION FOR EACH ORDER //////////////////////////////
            $grand_total_gross = 0;
            $grand_total_profit = 0;
            while ($row = mysqli_fetch_array($result)) {
                $order_id = $row['order_id'];
                $date = $row['date'];
                $customer_name = $row['first_name'] . " " . $row['last_name'];
                
                echo "<tr><td><b>Order #:</b> $order_id<td colspan=3><b>Customer:</b> $customer_name<td colspan=4><b>Date:</b> $date";
                
                $query = "SELECT p.name, v.name as vendor_name, p.cost, p.sell_price, p.quantity as quantity_current, po.quantity as quantity_sold, (p.sell_price * po.quantity) as subtotal, ((p.sell_price - p.cost) * po.quantity) as profit FROM 2023F_fisheral.PRODUCT_ORDER po left join 2023F_fisheral.PRODUCT p on (po.product_id = p.id) left join CPS5740.VENDOR2 v on (p.vendor_id = v.vendor_id) WHERE po.order_id = $order_id;";
                $products_result = mysqli_query($con, $query);
                
                if(!$products_result) {
                    echo "<tr><td colspan=4>" . print_error("Query getting order info failed: " . mysqli_error($con)) . "</td></tr></table>";
                    continue;
                }
        
                if (mysqli_num_rows($products_result) < 1) {
                    echo "<tr><td colspan=4>" . print_error("No products found for this order.") . "</td></tr></table>";
                    continue;
                }

                $total_gross = 0;
                $total_profit = 0;
                echo "<tr><th>Product Name<th>Vendor Name<th>Unit Cost<th>Current Quantity<th>Sold Quantity<th>Sold Unit Price<th>Subtotal<th>Profit";
                while ($row = mysqli_fetch_array($products_result)) {
                    $product_name = $row['name'];
                    $vendor_name = $row['vendor_name'];
                    $unit_cost = $row['cost'];
                    $unit_price = $row['sell_price'];
                    $quantity_current = $row['quantity_current'];
                    $quantity_sold = $row['quantity_sold'];
                    $subtotal = $row['subtotal'];
                    $profit = $row['profit'];
                    echo "<tr><td>$product_name<td>$vendor_name<td>$unit_cost<td>$quantity_current<td>$quantity_sold<td>$unit_price<td>$subtotal<td>$profit";
        
                    $total_gross = $total_gross + $subtotal;
                    $total_profit = $total_profit + $profit;
                }
                $grand_total_gross = $grand_total_gross + $total_gross;
                $grand_total_profit = $grand_total_profit + $total_profit;
                echo "<tr><td colspan=6><b>Order Total</b><td><b>$total_gross</b><td><b>$total_profit</b><tr><td colspan=8>_";
            }
            echo "<tr><td colspan=6><b>Grand Total</b><td><b>$grand_total_gross</b><td><b>$grand_total_profit</b></table><br>";
            
            die();
        }

        if ($report_type == "products") {
            /* If the report type is by products, please list ALL products and sale information – product name, vendor 
            name, unit cost, quantity in stock, quantity sold, unit selling price, profit for each product, and the total of sub-total and 
            total profit for all products. Every product should be output (even never sold) with one row only. (Figure 19)*/

            

            if ($report_period_where_clause == "") {
                $query = "SELECT p.id, p.name, v.name as vendor_name, p.cost, p.quantity as quantity_current, 
                (select SUM(po.quantity) FROM PRODUCT_ORDER po WHERE product_id = p.id) as quantity_sold, p.sell_price, 
                (select SUM(p.sell_price * po.quantity) FROM PRODUCT_ORDER po WHERE product_id = p.id) as subtotal, 
                (select SUM((p.sell_price - p.cost) * po.quantity) FROM PRODUCT_ORDER po WHERE product_id = p.id) as profit 
                FROM 2023F_fisheral.PRODUCT p left join CPS5740.VENDOR2 v on (p.vendor_id = v.vendor_id)";
            }
            else {
                $query = "SELECT p.id, p.name, v.name as vendor_name, p.cost, p.quantity as quantity_current, 
                (select SUM(po.quantity) FROM PRODUCT_ORDER po LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE product_id = p.id AND $report_period_where_clause) as quantity_sold, p.sell_price, 
                (select SUM(p.sell_price * po.quantity) FROM PRODUCT_ORDER po LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE product_id = p.id AND $report_period_where_clause) as subtotal, 
                (select SUM((p.sell_price - p.cost) * po.quantity) FROM PRODUCT_ORDER po LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE product_id = p.id AND $report_period_where_clause) as profit 
                FROM 2023F_fisheral.PRODUCT p left join CPS5740.VENDOR2 v on (p.vendor_id = v.vendor_id)";
            }
            $query = $query . ";";
            //echo "<b>Query:</b> $query<br><br>";
            $products_result = mysqli_query($con, $query);
                
            if(!$products_result) {
                print_error("Query getting product info failed: " . mysqli_error($con));
                die();
            }
    
            if (mysqli_num_rows($products_result) < 1) {
                print_error("No products found for this order.");
                die();
            }

            $total_gross = 0;
            $total_profit = 0;
            echo "<table border=1><tr><th>ID<th>Product Name<th>Vendor Name<th>Unit Cost<th>Current Quantity<th>Sold Quantity<th>Sold Unit Price<th>Subtotal<th>Profit";
            while ($row = mysqli_fetch_array($products_result)) {
                $product_id = $row['id'];
                $product_name = $row['name'];
                $vendor_name = $row['vendor_name'];
                $unit_cost = $row['cost'];
                $unit_price = $row['sell_price'];
                $quantity_current = $row['quantity_current'];
                $quantity_sold = $row['quantity_sold'];
                $subtotal = $row['subtotal'];
                $profit = $row['profit'];
                echo "<tr><td>$product_id<td>$product_name<td>$vendor_name<td>$unit_cost<td>$quantity_current<td>$quantity_sold<td>$unit_price<td>$subtotal<td>$profit";
    
                $total_gross = $total_gross + $subtotal;
                $total_profit = $total_profit + $profit;
            }
            echo "<tr><td colspan=7><b>Total</b><td><b>$total_gross</b><td><b>$total_profit</b>";
            echo "</table><br>";

            die();
        }

        if ($report_type == "vendors") {
            /* If the report type is by vendors, please list the product sales information for ALL vendors – vendor name, 
            product name, total quantity of all items in stock, amount needs to pay to the vendor for items which have been sold, 
            subtotal of sales, and the profit from selling this item. At the end, please show the total amount needs to pay to all 
            vendors and total profit from items/products which have been sold. Every vendor should be output (even no product 
            was sold) with one row only. (Figure 20)*/
            if ($report_period_where_clause == "") {
                $query = "SELECT v.vendor_id, v.name, (SELECT SUM(p.quantity) FROM PRODUCT p WHERE p.vendor_id = v.vendor_id) as quantity_current, 
                (SELECT SUM(p.cost * po.quantity) FROM PRODUCT_ORDER po LEFT JOIN PRODUCT p on (po.product_id = p.id) WHERE p.vendor_id = v.vendor_id) as amount_owed, 
                (SELECT SUM(po.quantity) FROM PRODUCT_ORDER po LEFT JOIN PRODUCT p on (po.product_id = p.id) WHERE p.vendor_id = v.vendor_id) as quantity_sold, 
                (SELECT SUM(p.sell_price * po.quantity) FROM PRODUCT p LEFT JOIN PRODUCT_ORDER po on (p.id = po.product_id) WHERE p.vendor_id = v.vendor_id) as subtotal, 
                (SELECT SUM((p.sell_price - p.cost) * po.quantity) FROM PRODUCT p LEFT JOIN PRODUCT_ORDER po on (p.id = po.product_id) WHERE p.vendor_id = v.vendor_id) as profit 
                FROM CPS5740.VENDOR2 v;";
            }
            else {
                $query = "SELECT v.vendor_id, v.name, (SELECT SUM(p.quantity) FROM PRODUCT p WHERE p.vendor_id = v.vendor_id) as quantity_current, 
                (SELECT SUM(p.cost * po.quantity) FROM PRODUCT_ORDER po LEFT JOIN PRODUCT p on (po.product_id = p.id) LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE p.vendor_id = v.vendor_id AND $report_period_where_clause) as amount_owed, 
                (SELECT SUM(po.quantity) FROM PRODUCT_ORDER po LEFT JOIN PRODUCT p on (po.product_id = p.id) LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE p.vendor_id = v.vendor_id AND $report_period_where_clause) as quantity_sold, 
                (SELECT SUM(p.sell_price * po.quantity) FROM PRODUCT p LEFT JOIN PRODUCT_ORDER po on (p.id = po.product_id) LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE p.vendor_id = v.vendor_id AND $report_period_where_clause) as subtotal, 
                (SELECT SUM((p.sell_price - p.cost) * po.quantity) FROM PRODUCT p LEFT JOIN PRODUCT_ORDER po on (p.id = po.product_id) LEFT JOIN `ORDER` o on (po.order_id = o.id) WHERE p.vendor_id = v.vendor_id AND $report_period_where_clause) as profit 
                FROM CPS5740.VENDOR2 v;";
            }
            
            //echo "<b>Query:</b> $query<br><br>";
            $vendors_result = mysqli_query($con, $query);
                
            if(!$vendors_result) {
                print_error("Query getting product info failed: " . mysqli_error($con));
                die();
            }
    
            if (mysqli_num_rows($vendors_result) < 1) {
                print_error("No products found for this order.");
                die();
            }

            $total_gross = 0;
            $total_profit = 0;
            $total_owed = 0;
            echo "<table border=1><tr><th>ID<th>Vendor Name<th>Quantity in Stock<th>Amount Owed to Vendor<th>Sold Quantity<th>Subtotal<th>Profit";
            while ($row = mysqli_fetch_array($vendors_result)) {
                $vendor_id = $row['vendor_id'];
                $vendor_name = $row['name'];
                $quantity_current = $row['quantity_current'];
                $amount_owed = $row['amount_owed'];
                $quantity_sold = $row['quantity_sold'];
                $subtotal = $row['subtotal'];
                $profit = $row['profit'];
                echo "<tr><td>$vendor_id<td>$vendor_name<td>$quantity_current<td>$amount_owed<td>$quantity_sold<td>$subtotal<td>$profit";
    
                $total_gross = $total_gross + $subtotal;
                $total_profit = $total_profit + $profit;
                $total_owed = $total_owed + $amount_owed;
            }
            echo "<tr><td colspan=3><b>Total</b><td><b>$total_owed</b><td><td><b>$total_gross</b><td><b>$total_profit</b>";
            echo "</table><br>";

            die();
        }

        //mysqli_free_result($result);

        function print_error($msg) {
            echo "<span class='error'>$msg</span><br>";
        }
        
    ?>

</body>
</html>