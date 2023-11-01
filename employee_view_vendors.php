<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee - View All Vendors</title>
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
        $employee_id = $_COOKIE['employee_id'];

    ?>
    <a href="employee_check.php">Employee Home</a><br><br>
    <b>Employee - View All Vendors</b><br><br>
    <?php 

        $query = "SELECT * FROM CPS5740.VENDOR;";
    

        echo "<b>The following vendors are in the database.</b><br>";

        try {
            $result = mysqli_query($con, $query);
        }
        catch(Exception $e) {
            echo "ERROR: ". $e->getMessage();
            die();
        }
        
        if (mysqli_num_rows($result) < 1) {
            echo "No vendors were found in the database.";
            die();
        }

        echo "<table border=1>\n";
        echo "<tr><th>ID<th>Name<th>Address<th>City<th>State<th>Zipcode<th>Location(Latitude, Longitude)\n";
        
        $location_array = array();
        while($row = mysqli_fetch_array($result)) {
            $id = $row['vendor_id'];
            $name = $row['name'];
            $address = $row['address'];
            $city = $row['city'];
            $state = $row['state'];
            $zipcode = $row['zipcode'];
            $latitude = $row['latitude'];
            $longitude = $row['Longitude'];
            array_push($location_array, array('vendor_id' => $id, 'lat' => $latitude, 'long' => $longitude));
            echo "<tr><td>$id<td>$name<td>$address<td>$city<td>$state<td>$zipcode<td>$latitude, $longitude\n";
        }

        echo "</table>";

        $location_array = json_encode($location_array);

        mysqli_free_result($result);
        
    ?>
<div id="googleMap" style="width:800px;height:400px;"></div>

<script>
    function myMap() {
        // initialize map

        // set center
        var myCenter = new google.maps.LatLng(39.8283,-98.5795);
        var mapProp= {
            center: myCenter,
            zoom:4,
        };
        var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
        
        var location_array = `<?php echo $location_array; ?>`;
        
        // parse json
        location_array = JSON.parse(location_array);
        console.log(location_array);

        // set markers
        for (i in location_array) {
            var point = new google.maps.LatLng(location_array[i]['lat'], location_array[i]['long']);
            var marker = new google.maps.Marker({
                label: location_array[i]['vendor_id'],
                position: point
            });
            marker.setMap(map);
        }
    }
</script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjsqkhbx58Ml9aWxeK_D7Rpt_m3gT-p7c&callback=myMap"></script>
</body>
</html>