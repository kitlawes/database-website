<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Website</title>
</head>
<body>

Database Website
<br />
<br />

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    Name: <input type="text" name="name">
    <input type="submit">
</form>
<br />

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $server_name = "localhost";
    $username = "root";
    $password = "password";
    $database = "employees";
    
    $connection = mysqli_connect($server_name, $username, $password, $database);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    $name = $_POST["name"];
    $query = "SELECT * FROM employees WHERE first_name LIKE '%" . $name . "%' LIMIT 100;";
    $result = mysqli_query($connection, $query);
  
    $fields = mysqli_fetch_fields($result);
    echo "<table border=\"1\"><tr>";
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<td>" . $row[$field->name] . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
        
    mysqli_close($connection);

}

?>

</body>
</html>