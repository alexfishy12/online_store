<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee - Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        // attempt to connect to DB
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<span class='error'>Cannot connect to DB.</span>\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['employee_id'])) {
            echo "<a href='employee_login.html' class='header_link'>Employee Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }
        $employee_id = $_COOKIE['employee_id'];
    ?>
    <a href="employee_check.php" class="header_link">Go Back</a><br><br>
    <b>Employee - Add Product</b><br><br>

    <form action="employee_insert_product.php" method="POST">
        Name: <input type="text" name="name"><br>
        Description: <input type="text" name="description"><br>
        Cost: <input type="number" name="cost" step="0.01"><br>
        Sell Price: <input type="number" name="sell_price" step="0.01"><br>
        Quantity: <input type="number" name="quantity"><br>
        Select vendor: <select name="vendor_id">
            <?php
                // get all vendors
                $query = "SELECT vendor_id, name FROM CPS5740.VENDOR;";
                $result = mysqli_query($con, $query);

                // display all vendors in dropdown
                while ($row = mysqli_fetch_array($result)) {
                    echo "<option value='" . $row['vendor_id'] . "'>" . $row['name'] . "</option>";
                }
            ?>
        </select>
        <input type="submit" value="Submit">
    </form>
</body>
</html>