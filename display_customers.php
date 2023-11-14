<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Customers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="index.html">Project Home Page</a><br><br>
    <b>Display Customers</b><br><br>
    <?php 
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

        $query = "SELECT * FROM 2023F_fisheral.CUSTOMER;";

        //echo "Query: $query<br><br>";

        try {
            $result = mysqli_query($con, $query);
        }
        catch(Exception $e) {
            echo "ERROR: ". $e->getMessage();
            die();
        }
        
        if (mysqli_num_rows($result) < 1) {
            echo "There are zero customers in the database.";
            die();
        }
            
        echo "The following customers are in the database.<br>";
        echo "<table border=1>\n";
        echo "<tr><th>Customer ID<th>Login ID<th>Password<th>First Name<th>Last Name<th>Telephone<th>Address<th>City<th>State<th>Zipcode\n";

        while($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'];
            $login_id = $row['login_id'];
            $password = $row['password'];
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $telephone = $row['tel'];
            $address = $row['address'];
            $city = $row['city'];
            $state = $row['state'];
            $zipcode = $row['zipcode'];
            echo "<tr><td>$customer_id<td>$login_id<td>$password<td>$first_name<td>$last_name<td>$telephone<td>$address<td>$city<td>$state<td>$zipcode\n";
        }

        echo "</table>";

        mysqli_free_result($result);
        
    ?>
</body>
</html>