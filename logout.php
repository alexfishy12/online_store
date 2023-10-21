<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Logout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="index.php">Project Home Page</a><br><br>
    <?php
        // delete login cookie
        setcookie("login", "", time() - 3600);
        
        echo "<span class='success'>Logout successful.</span><br>";
    ?>
</body>
<html>
</html>