<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        // attempt to connect to DB
        define("IN_CODE", 1);
        include("dbconfig.php");
        $con = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname) or die ("<span class='error'>Cannot connect to DB.</span>\n");
    ?>
    <a href="index.html" class="header_link">Project Home Page</a><br><br>
    <b>Customer - Create Account</b><br><br>

    <!-- 
        login_id varchar(15) not null unique,
        password varchar(15) not null,
        first_name varchar(15) not null,
        last_name varchar(15) not null,
        tel varchar(15) DEFAULT NULL,
        address varchar(200) DEFAULT NULL,
        city varchar(15) not null,
        zipcode varchar(10),
        state varchar(2) not null
    -->
    <form action="customer_insert_account.php" method="POST">
        Username: <input type="text" name="login_id" required maxlength=15><br>
        Password: <input type="password" name="password" required maxlength=15><br>
        Confirm Password: <input type="password" name="confirm_password" required maxlength=15><br>
        First Name: <input type="text" name="first_name" required maxlength=15><br>
        Last Name: <input type="text" name="last_name" required maxlength=15><br>
        Telephone: <input type="text" name="tel" maxlength=15><br>
        Address: <input type="text" name="address" maxlength=200><br>
        City: <input type="text" name="city" required maxlength=15><br>
        Zipcode: <input type="text" name="zipcode" maxlength=10><br>
        State: <select name="state">
            <option value="AL">Alabama</option>
            <option value="AK">Alaska</option>
            <option value="AZ">Arizona</option>
            <option value="AR">Arkansas</option>
            <option value="CA">California</option>
            <option value="CO">Colorado</option>
            <option value="CT">Connecticut</option>
            <option value="DE">Delaware</option>
            <option value="DC">District Of Columbia</option>
            <option value="FL">Florida</option>
            <option value="GA">Georgia</option>
            <option value="HI">Hawaii</option>
            <option value="ID">Idaho</option>
            <option value="IL">Illinois</option>
            <option value="IN">Indiana</option>
            <option value="IA">Iowa</option>
            <option value="KS">Kansas</option>
            <option value="KY">Kentucky</option>
            <option value="LA">Louisiana</option>
            <option value="ME">Maine</option>
            <option value="MD">Maryland</option>
            <option value="MA">Massachusetts</option>
            <option value="MI">Michigan</option>
            <option value="MN">Minnesota</option>
            <option value="MS">Mississippi</option>
            <option value="MO">Missouri</option>
            <option value="MT">Montana</option>
            <option value="NE">Nebraska</option>
            <option value="NV">Nevada</option>
            <option value="NH">New Hampshire</option>
            <option value="NJ">New Jersey</option>
            <option value="NM">New Mexico</option>
            <option value="NY">New York</option>
            <option value="NC">North Carolina</option>
            <option value="ND">North Dakota</option>
            <option value="OH">Ohio</option>
            <option value="OK">Oklahoma</option>
            <option value="OR">Oregon</option>
            <option value="PA">Pennsylvania</option>
            <option value="RI">Rhode Island</option>
            <option value="SC">South Carolina</option>
            <option value="SD">South Dakota</option>
            <option value="TN">Tennessee</option>
            <option value="TX">Texas</option>
            <option value="UT">Utah</option>
            <option value="VT">Vermont</option>
            <option value="VA">Virginia</option>
            <option value="WA">Washington</option>
            <option value="WV">West Virginia</option>
            <option value="WI">Wisconsin</option>
            <option value="WY">Wyoming</option>
        </select><br>
        <input type="submit" value="Create Account">
    </form>
</body>
</html>