<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="employee_search_product.php" class="header_link">Go Back</a><a href="employee_check.php" class='header_link'>Employee Home</a><br><br>
    <b>Search Product Page</b><br><br>
    <?php 
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<span class='error'>Cannot connect to DB.</span>\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['login'])) {
            print_error("Not logged in.");
            die();
        }

        if (!isset($_POST['search_text'])) {
            print_error("No search keywords received.");
            die();
        }

        $search_text = $_POST['search_text'];
        
        if ($search_text == "") {
            print_error("Search keyword cannot be blank. Use '*' to see all items.");
            die();
        }

        $query = "SELECT p.id, p.name, p.description, p.cost, p.sell_price, p.quantity, v.name as vendor_name, e.name as employee_name 
            FROM 2023F_fisheral.PRODUCT p 
            left join CPS5740.VENDOR v on (p.vendor_id = v.vendor_id) 
            left join CPS5740.EMPLOYEE2 e on (p.employee_id = e.employee_id)";
        

        if ($search_text != "*") {
            // add where clause to query for search keywords if keyword isn't '*'
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

        echo "Query: $query<br><br>";
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
        
        // get all vendors
        $vendors_query = "SELECT vendor_id, name FROM CPS5740.VENDOR;";
        $vendors_result = mysqli_query($con, $vendors_query);

        if (!$vendors_result) {
            print_error("Error getting vendors from database.");
            die();
        }

        echo "The following products match the search keywords.<br>";
        echo "<form action=employee_update_product.php method=POST>\n";
        echo "<table border=1>\n";
        echo "<tr><th>Product ID<th>Product Name<th>Description<th>Cost<th>Sell Price<th>Available Quantity<th>Vendor Name<th>Last Update By\n";

        while($row = mysqli_fetch_array($result)) {
            $id = $row['id'];
            $product_name = $row['name'];
            $description = $row['description'];
            $cost = $row['cost'];
            $sell_price = $row['sell_price'];
            $available_quantity = $row['quantity'];
            $vendor_name = $row['vendor_name'];
            $employee_name = $row['employee_name'];

            echo <<<HTML
                <tr><td><input type=hidden name='id[]' value='$id'>$id
                    <td><input type=text name='name[]' value='$product_name'>
                    <td><input type=text name='description[]' value='$description'>
                    <td><input type=number name='cost[]' step=0.01 value='$cost'>
                    <td><input type=number name='sell_price[]' step=0.01 value='$sell_price'>
                    <td><input type=number name='quantity[]' value='$available_quantity'>
                    <td><select name="vendor_id[]">
            HTML;
            
            // display all vendors in dropdown
            while ($vendor_row = mysqli_fetch_array($vendors_result)) {
                if ($vendor_row['name'] == $vendor_name)
                    echo "<option value='" . $vendor_row['vendor_id'] . "' selected>" . $vendor_row['name'] . "</option>";
                else
                    echo "<option value='" . $vendor_row['vendor_id'] . "'>" . $vendor_row['name'] . "</option>";
            }
            
            echo <<<HTML
                    </select>
                    <td>$employee_name
            HTML;

            mysqli_data_seek($vendors_result, 0);
        }

        $username = $_COOKIE['login'];
        $query = "SELECT employee_id FROM CPS5740.EMPLOYEE2 WHERE login = ?;";
        $stmt = $con->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = mysqli_fetch_array($result);
        $employee_id = $row['employee_id'];

        echo <<<HTML
            </table><br>
            <input type=submit value='Update Product(s)'>
            <input type='hidden' name='employee_id' value='$employee_id'>
            </form>
        HTML;

        mysqli_free_result($result);

        function print_error($msg) {
            echo "<span class='error'>$msg</span><br>";
        }
        
    ?>

</body>
</html>