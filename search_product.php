<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Search Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['login'])) {
            echo "<a href='customer_login.html' class='header_link'>Customer Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }
        
        echo <<<HTML
            <a href="customer_check_p2.php" class="header_link">Customer Home</a><br><br>
            <b>Customer - Search Product</b><br><br>
        HTML;

        if (!isset($_GET['search_text'])) {
            print_error("No search keywords received.");
            die();
        }

        $search_text = $_GET['search_text'];
        
        if ($search_text == "") {
            print_error("Search keyword cannot be blank. Use '*' to see all items.");
            die();
        }

        $query = "SELECT p.id, p.name, p.description,  p.sell_price, p.quantity, v.name as vendor_name FROM 2023F_fisheral.PRODUCT p left join CPS5740.VENDOR v on (p.vendor_id = v.vendor_id)";
        
        if ($search_text != "*") {
            // add where clause to query for search keywords
            $search_words = explode(" ", $search_text);
            $num_words = count($search_words);

            $query = $query . " WHERE (p.name LIKE '%$search_words[0]%' OR p.description LIKE '%$search_words[0]%')";
            if ($num_words > 1) {
                for ($i = 1; $i < $num_words; $i++) {
                    $query = $query . " AND (p.name LIKE '%$search_words[$i]%' OR p.description LIKE '%$search_words[$i]%')";
                }
            }
        }

        $query = $query . ";";

        //echo "Query: $query<br><br>";
        echo "Keywords: $search_text<br><br>";

        try {
            $result = mysqli_query($con, $query);
        }
        catch(Exception $e) {
            print_error("DATABASE ERROR: ". $e->getMessage());
            die();
        }
        
        if (mysqli_num_rows($result) < 1) {
            print_error("No matching products found.");
            die();
        }
        
        if ($search_text == "*") {
            $table_msg = "The following table shows all products in the database.<br>";
        }
        else {
            $table_msg = "The following products match the search keywords.<br>";
        }
        echo <<<HTML
            $table_msg
            <form action="customer_order.php" method="POST">
            <table border=1>
            <tr><th>Product Name<th>Description<th>Sell Price<th>Available Quantity<th>Order Quantity<th>Vendor Name
        HTML;


        while($row = mysqli_fetch_array($result)) {
            $product_id = $row['id'];
            $product_name = $row['name'];
            $description = $row['description'];
            $sell_price = $row['sell_price'];
            $available_quantity = $row['quantity'];
            $vendor_name = $row['vendor_name'];
            echo "<tr><td>$product_name<td>$description<td>$sell_price<td>$available_quantity";
            echo "<td><input type=number name='order_quantity[]' min=0>";
            echo "<input type=hidden name='product_id[]' value='$product_id'>";
            echo "<td>$vendor_name\n";
        }

        $username = $_COOKIE['login'];
        $query = "SELECT customer_id FROM 2023F_fisheral.CUSTOMER WHERE login_id = ?;";
        $stmt = $con->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = mysqli_fetch_array($result);
        $customer_id = $row['customer_id'];

        echo <<<HTML
            </table><br>
            <input type=submit value='Place Order'>
            <input type='hidden' name='customer_id' value='$customer_id'>
            </form>
        HTML;

        mysqli_free_result($result);

        function print_error($msg) {
            echo "<span class='error'>$msg</span><br>";
        }
        
    ?>

</body>
</html>