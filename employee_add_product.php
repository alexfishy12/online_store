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
        if (!isset($_COOKIE['login'])) {
            echo "<a href='employee_login.html' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }
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
        <?php 
            // get employee_id
            $username = $_COOKIE['login'];
            $query = "SELECT employee_id FROM CPS5740.EMPLOYEE2 WHERE login = ?;";
            $stmt = $con->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = mysqli_fetch_array($result);
            $employee_id = $row['employee_id'];
            echo "<input type='hidden' name='employee_id' value='$employee_id'>";
        ?>
        <input type="submit" value="Submit">
    </form>
</body>
</html>