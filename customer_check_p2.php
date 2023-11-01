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
        if (!isset($_COOKIE['customer_id']) && !isset($_POST['username']) && !isset($_POST['password'])) {
            echo "<a href='customer_login.html' class='header_link'>Go Back</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }

        if (!isset($_COOKIE['customer_id'])) {
            // get form data
            $username = $_POST['username'];
            $password = $_POST['password'];

            // check if username exists
            // Prepare statement
            $stmt = $con->prepare("SELECT customer_id FROM 2023F_fisheral.CUSTOMER where login_id = ?");

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
            $stmt = $con->prepare("SELECT customer_id FROM 2023F_fisheral.CUSTOMER where login_id = ? and password = ?");

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

            $customer_id = mysqli_fetch_array($result)['customer_id'];
            // set cookie
            setcookie("customer_id", $customer_id, time() + 3600);
        }
        else {
            $customer_id = $_COOKIE['customer_id'];
        }
    ?>
    <a href="logout.php" class='header_link'>Logout</a><br><br>
    <b>Customer - Home</b><br><br>
    <?php 
        // LOGIN SUCCESSFUL, GENERATE PAGE CONTENT

        // get employee information
        $stmt = $con->prepare("SELECT first_name, last_name, address, city, state, zipcode FROM 2023F_fisheral.CUSTOMER where customer_id = ?");

        // bind parameters
        $stmt->bind_param('i', $customer_id);

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
        <a href="customer_order_history.php">View My Order History</a><br>
        Search for a product using keywords. (Type '*' to see all products.)<br>
        <form action="search_product.php" method="GET">
            <input type='text' name='search_text' required>
            <input type='submit' value='Search'>
        </form><br><br>
        HTML;

        // get customer's recent search keywords
        $query = "SELECT keyword FROM 2023F_fisheral.CUSTOMER_RECENT_SEARCH_KEYWORD WHERE customer_id = $customer_id;";
       
        try {
            $result = mysqli_query($con, $query);
        }
        catch (Exception $e) {
            echo "<span class='error'>Error getting recent search keywords.</span><br>";
            die();
        }

        // get default advertisement        
        $ad_query = "SELECT image, description, url FROM CPS5740.Advertisement WHERE category = 'OTHER';";
        try {
            $ad_result = mysqli_query($con, $ad_query);
            $row = mysqli_fetch_array($ad_result);
            $ad_image = $row['image'];
            $ad_description = $row['description'];
            $ad_url = $row['url'];
        }
        catch (Exception $e) {
            echo "<span class='error'>Error getting advertisement.</span><br>";
            die();
        }
        
        if (mysqli_num_rows($result) > 0) {
            // recent search keywords
            while ($row = mysqli_fetch_array($result)) {
                $keyword = $row['keyword'];
                
                // build query for advertisement
                $ad_query = "SELECT image, description, url FROM CPS5740.Advertisement WHERE category LIKE '%$keyword%' OR description LIKE '%$keyword%';";
                try {
                    $ad_result = mysqli_query($con, $ad_query);
                    if (mysqli_num_rows($ad_result) == 0) {
                        continue;
                    }
                    else {
                        $row = mysqli_fetch_array($ad_result);
                        $ad_image = $row['image'];
                        $ad_description = $row['description'];
                        $ad_url = $row['url'];
                        break;
                    }
                }
                catch (Exception $e) {
                    echo "<span class='error'>Error getting advertisement.</span><br>";
                    die();
                }
            }
        }

        $image_base64 = base64_encode($ad_image);

        if ($ad_url != "") {
            echo "<a href='$ad_url' target='_blank'><img src='data:image/jpeg;charset=utf-8;base64,$image_base64' width='200' height='200'></a><br>";
        }
        else {
            echo "<img src='data:image/jpeg;charset=utf-8;base64,$image_base64' width='200' height='200'><br>";
        }
        echo $ad_description;
    ?>
</body>
</html>