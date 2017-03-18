<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Database</title>
</head>
<body>

<h1>Online Database</h1>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="dept_names">
    <input type="submit" value="Query">
    Department names
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="titles">
    <input type="submit" value="Query">
    Titles
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="emps_per_dept">
    <input type="submit" value="Query">
    Amount of employees per department
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="emps_per_title">
    <input type="submit" value="Query">
    Amount of employees per title
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
    if ($query_type == "emps_per_dept") {
        $query = "SELECT dept_name, COUNT(*) as count FROM departments INNER JOIN dept_emp ON departments.dept_no = dept_emp.dept_no GROUP BY departments.dept_no ORDER BY count DESC;";
    }
    if ($query_type == "emps_per_title") {
        $query = "SELECT title, COUNT(*) as count FROM employees INNER JOIN titles ON employees.emp_no = titles.emp_no GROUP BY titles.title ORDER BY count DESC;";
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