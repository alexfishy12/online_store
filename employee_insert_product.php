<?php 
    // attempt to connect to DB
    define("IN_CODE", 1);
    include("dbconfig.php");
    $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

    // check if user cookie is logged in
    if (!isset($_COOKIE['login'])) {
        echo "Not logged in.<br>";
        //die();
    }

    // check that 


    // check that all variables passed through using form

    if (!isset($_POST['name'])) {
        echo "Form submit error: Product name not received.";
        die();
    }
    if (!isset($_POST['description'])) {
        echo "Form submit error: Product name not received.";
        die();
    }

        $search_text = $_GET['search_text'];
    
    if ($search_text == "") {
        echo "Search keyword cannot be blank. Use '*' to see all items.";
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

    echo "<b>Product search results</b><br><br>";
    echo "Query: $query<br><br>";
    echo "Keywords: $search_text<br><br>";

    try {
        $result = mysqli_query($con, $query);
    }
    catch(Exception $e) {
        echo "ERROR: ". $e->getMessage();
        die();
    }
    
    if (mysqli_num_rows($result) < 1) {
        echo "No matching products found.";
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