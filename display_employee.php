<?php 
    define("IN_CODE", 1);
    include("dbconfig.php");
    $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<br>Cannot connect to DB.\n");

    $query = "SELECT employee_id, login, password, name, role FROM CPS5740.EMPLOYEE2;";

    echo "<b>Display all customers</b><br><br>";
    echo "Query: $query<br><br>";

    try {
        $result = mysqli_query($con, $query);
    }
    catch(Exception $e) {
        echo "ERROR: ". $e->getMessage();
        die();
    }
    
    if (mysqli_num_rows($result) < 1) {
        echo "There are zero employees in the database.";
        die();
    }
        
    echo "The following employees are in the database.<br>";
    echo "<table border=1>\n";
    echo "<tr><th>ID<th>Login<th>Password<th>Name<th>Role\n";

    while($row = mysqli_fetch_array($result)) {
        $id = $row['employee_id'];
        $login_id = $row['login'];
        $password = $row['password'];
        $name = $row['name'];
        $role = $row['role'];
        echo "<tr><td>$id<td>$login_id<td>$password<td>$name<td>$role\n";
    }

    echo "</table>";

    mysqli_free_result($result);
    
?>