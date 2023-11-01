<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        //attempt to connect to database
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<span class='error'>Cannot connect to DB.</span><br>\n");

        // check if user cookie is logged in
        if (!isset($_COOKIE['employee_id']) && !isset($_POST['username']) && !isset($_POST['password'])) {
            echo "<a href='employee_login.html' class='header_link'>Employee Login</a><br><br>";
            echo "<span class='error'>Not logged in.</span><br>";
            die();
        }

        if (!isset($_COOKIE['employee_id'])) {
            // get form data
            $username = $_POST['username'];
            $password = $_POST['password'];

            // check if username exists
            // Prepare statement
            $stmt = $con->prepare("SELECT login FROM CPS5740.EMPLOYEE2 where login = ?");

            // bind parameters
            $stmt->bind_param('s', $username);

            // Execute statement
            $stmt->execute();

            // get result
            $result = $stmt->get_result();

            // if username doesn't exist, kill program
            if (mysqli_num_rows($result) < 1) {
                echo "<a href='employee_login.html' class='header_link'>Go Back</a><br><br>";
                echo "<span class='error'>Username doesn't exist.</span><br>";
                die();
            }

            // check if password is correct
            // Prepare statement
            $stmt = $con->prepare("SELECT employee_id FROM CPS5740.EMPLOYEE2 where login = ? and password = SHA2(?, 256)");

            // bind parameters
            $stmt->bind_param('ss', $username, $password);

            // Execute statement
            $stmt->execute();

            // get result
            $result = $stmt->get_result();

            // if password is incorrect, kill program
            if (mysqli_num_rows($result) < 1) {
                echo "<a href='employee_login.html' class='header_link'>Go Back</a><br><br>";
                echo "<span class='error'>Username exists, but password is incorrect.</span><br>";
                die();
            }

            // set cookie
            $employee_id = mysqli_fetch_array($result)['employee_id'];
            setcookie("employee_id", $employee_id, time() + 3600);
        }
        else {
            $employee_id = $_COOKIE['employee_id'];
        }
    ?>
    <a href="logout.php" class='header_link'>Logout</a><br><br>
    <b>Employee - Home</b><br><br>
    <?php 
        // LOGIN SUCCESSFUL, GENERATE PAGE CONTENT

        // get employee information
        $stmt = $con->prepare("SELECT name, role FROM CPS5740.EMPLOYEE2 where employee_id = ?");

        // bind parameters
        $stmt->bind_param('i', $employee_id);

        // Execute statement
        $stmt->execute();

        // get result
        $result = $stmt->get_result();

        // get employee name and role
        if (!$result) {
            echo "<span class='error'>Error getting employee information.</span><br>";
            die();
        }
        $row = mysqli_fetch_array($result);
        $name = $row['name'];
        $role = $row['role'];
        
        if ($role == "M") {
            $role = "Manager";
        }
        if ($role == "E") {
            $role = "Employee";
        }

        echo "Welcome, $role: $name<br><br>";
        echo <<<HTML
        <a href="employee_add_product.php">Add a product</a><br>
        <a href="employee_view_vendors.php">View all vendors</a><br>
        <a href="employee_search_product.php">Search and update product</a><br>
        HTML;

        if ($role == "Manager") {
            echo <<<HTML
            <form action="manager_view_reports.php" method="post" id="view_report"></form>
            View reports - period 
                <select name="report_period" form='view_report'>
                    <option value="all_time" selected>All time</option>
                    <option value="past_week">Past week</option>
                    <option value="current_month">Current Month</option>
                    <option value="past_month">Past Month</option>
                </select>, by: 
                <select name="report_type" form='view_report'>
                    <option value="all_sales" selected>all sales</option>
                    <option value="products">products</option>
                    <option value="vendors">vendors</option>
                </select>
                <button type='submit' form='view_report'>Submit</button>
            HTML;
        }

    ?>
</body>
</html>