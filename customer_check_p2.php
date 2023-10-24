<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        //attempt to connect to database
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<span class='error'>Cannot connect to DB.</span><br>\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['login']) && !isset($_POST['username']) && !isset($_POST['password'])) {
            echo "<a href='employee_login.html' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }

        if (!isset($_COOKIE['login'])) {
            // get form data
            $username = $_POST['username'];
            $password = $_POST['password'];

            // check if username exists
            // Prepare statement
            $stmt = $con->prepare("SELECT login_id FROM 2023F_fisheral.CUSTOMER where login_id = ?");

            // bind parameters
            $stmt->bind_param('s', $username);

            // Execute statement
            $stmt->execute();

            // get result
            $result = $stmt->get_result();

            // if username doesn't exist, kill program
            if (mysqli_num_rows($result) < 1) {
                echo "<a href='customer_login.html' class='header_link'>Go Back</a><br><br>";
                echo "<span class='error'>Username doesn't exist.</span><br>";
                die();
            }

            // check if password is correct
            // Prepare statement
            $stmt = $con->prepare("SELECT login_id FROM 2023F_fisheral.CUSTOMER where login_id = ? and password = ?");

            // bind parameters
            $stmt->bind_param('ss', $username, $password);

            // Execute statement
            $stmt->execute();

            // get result
            $result = $stmt->get_result();

            // if password is incorrect, kill program
            if (mysqli_num_rows($result) < 1) {
                echo "<a href='customer_login.html' class='header_link'>Go Back</a><br><br>";
                echo "<span class='error'>Username exists, but password is incorrect.</span><br>";
                die();
            }

            // set cookie
            setcookie("login", $username, time() + 3600);
        }
        else {
            $username = $_COOKIE['login'];
        }
    ?>
    <a href="logout.php" class='header_link'>Logout</a><br><br>
    <b>Customer - Home</b><br><br>
    <?php 
        // LOGIN SUCCESSFUL, GENERATE PAGE CONTENT

        // get employee information
        $stmt = $con->prepare("SELECT first_name, last_name, address, city, state, zipcode FROM 2023F_fisheral.CUSTOMER where login_id = ?");

        // bind parameters
        $stmt->bind_param('s', $username);

        // Execute statement
        $stmt->execute();

        // get result
        $result = $stmt->get_result();

        // get employee name and role
        if (!$result) {
            echo "<span class='error'>Error getting customer information.</span><br>";
            die();
        }
        $row = mysqli_fetch_array($result);
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $address = $row['address'];
        $city = $row['city'];
        $state = $row['state'];
        $zipcode = $row['zipcode'];

        $client_ip = $_SERVER['REMOTE_ADDR'];
        

        echo <<<HTML
        Welcome, Customer: <b>$first_name $last_name</b><br>
        $address, $city, $state $zipcode<br>
        Your IP: $client_ip<br>
        I don't know if you are from Kean University or not.<br><br>
        <a href="customer_update_account.php">Update My Account</a><br>
        <a href="customer_order_history.php">View My Order History</a><br>
        Search for a product using keywords. (Type '*' to see all products.)<br>
        <form action="search_product.php" method="GET">
            <input type='text' name='search_text' required>
            <input type='submit' value='Search'>
        </form><br><br>
        HTML;

        // check if last keyword matches any advertisement
        $query = "SELECT * from CPS5740.Advertisement WHERE category like '%'";
    ?>
</body>
</html>