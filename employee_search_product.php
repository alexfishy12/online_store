<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee - Search Product</title>
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
    ?>
    <a href="employee_check.php">Employee Home</a><br><br>
    <b>Employee - Search Product</b><br><br>
    Search for a product using keywords. (Type '*' to see all products.)<br><br>
    <form action="employee_display_product.php" method="POST">
        <input type='text' name='search_text' required>
        <input type='submit' value='Search'>
    </form>
</body>
</html>