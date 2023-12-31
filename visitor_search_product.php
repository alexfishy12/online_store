<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="visitor_search_product_form.php" class="header_link">Go Back</a><a href="index.html" class="header_link">Project Home Page</a><br><br>
    <b>Search Product Page</b><br><br>
    <?php 
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        // check if user cookie is logged in
        if (!isset($_GET['search_text'])) {
            echo "<span class='error'>No search keywords received.</span><br>";
            die();
        }

        $search_text = $_GET['search_text'];
        
        if ($search_text == "") {
            echo "<span class='error'>Search keyword cannot be blank. Use '*' to see all items.</span><br>";
            die();
        }

        $query = "SELECT p.name, p.description,  p.sell_price, p.quantity, v.name as vendor_name FROM 2023F_fisheral.PRODUCT p left join CPS5740.VENDOR v on (p.vendor_id = v.vendor_id)";
        if ($search_text == "*") {
            // show all attributes except cost if user is a customer
        }
        else {
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
            echo "<span class='error'>DATABASE ERROR: ". $e->getMessage() . "</span><br>";
            die();
        }
        
        if (mysqli_num_rows($result) < 1) {
            echo "<span class='error'>No matching products found.</span><br>";
            die();
        }
            
        echo "The following products match the search keywords.<br>";
        echo "<table border=1>\n";
        echo "<tr><th>Product Name<th>Description<th>Sell Price<th>Available Quantity<th>Vendor Name\n";

        while($row = mysqli_fetch_array($result)) {
            $product_name = $row['name'];
            $description = $row['description'];
            $sell_price = $row['sell_price'];
            $available_quantity = $row['quantity'];
            $vendor_name = $row['vendor_name'];
            echo "<tr><td>$product_name<td>$description<td>$sell_price<td>$available_quantity<td>$vendor_name\n";
        }

        echo "</table>";

        mysqli_free_result($result);
        
    ?>

</body>
</html>