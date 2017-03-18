<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Database</title>
</head>
<body>

<h1>Online Database</h1>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    Department names
    <input style="display: none;" name="query_type" value="dept_names">
    <input type="submit">
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    Titles
    <input style="display: none;" name="query_type" value="titles">
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
    
    $query_type = $_POST["query_type"];
    if ($query_type == "dept_names") {
        $query = "SELECT dept_name FROM departments;";
    }
    if ($query_type == "titles") {
        $query = "SELECT DISTINCT title FROM titles;";
    }
    
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