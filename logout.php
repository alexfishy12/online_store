<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="index.html">Project Home Page</a><br><br>
    <?php
        // delete login cookie
        setcookie("customer_id", "", time() - 3600);
        setcookie("employee_id", "", time() - 3600);
        
        echo "<span class='success'>Logout successful.</span><br>";
    ?>
</body>
<html>
</html>