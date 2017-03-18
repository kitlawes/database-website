<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Database</title>
</head>
<body>

<?php
    $server_name = "localhost";
    $username = "root";
    $password = "password";
    $database = "employees";
    $connection = mysqli_connect($server_name, $username, $password, $database);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>

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

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="salaries_per_year_for_dept">
    <input type="submit" value="Query">
    Average salaries per year for department
    <select name="dept_name">
    
    <?php
        $query = "SELECT dept_name FROM departments;";
        $result = mysqli_query($connection, $query);
        while ($row = $result->fetch_assoc()) {
            echo "<option value=\"" . $row["dept_name"] . "\">" . $row["dept_name"] . "</option>";
        }
    ?>
    
    </select>
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="salaries_per_year_for_title">
    <input type="submit" value="Query">
    Average salaries per year for title
    <select name="title">
    
    <?php
        $query = "SELECT DISTINCT title FROM titles;";
        $result = mysqli_query($connection, $query);
        while ($row = $result->fetch_assoc()) {
            echo "<option value=\"" . $row["title"] . "\">" . $row["title"] . "</option>";
        }
    ?>
    
    </select>
</form>

<br />

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
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
        if ($query_type == "salaries_per_year_for_dept") {
            $query = "CREATE OR REPLACE VIEW department AS SELECT * FROM departments WHERE dept_name = \"" . $_POST["dept_name"] . "\";";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW dept_emp_nos AS SELECT dept_name, emp_no FROM department INNER JOIN dept_emp ON department.dept_no = dept_emp.dept_no;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW dept_salaries AS SELECT dept_name, salary, YEAR(from_date) AS from_year, YEAR(to_date) AS to_year FROM dept_emp_nos INNER JOIN salaries ON dept_emp_nos.emp_no = salaries.emp_no;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW salary_years AS SELECT from_year as year FROM dept_salaries UNION SELECT to_year as year FROM dept_salaries WHERE to_year <> 9999;";
            mysqli_query($connection, $query);
            $query = "SELECT dept_name, year, AVG(salary) as average_salary FROM salary_years JOIN dept_salaries ON from_year <= year AND year <= to_year GROUP BY year ORDER BY year DESC;";
        }
        if ($query_type == "salaries_per_year_for_title") {
            $query = "CREATE OR REPLACE VIEW title_emp_nos AS SELECT title, emp_no FROM titles WHERE title = \"" . $_POST["title"] . "\";";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW title_salaries AS SELECT title, salary, YEAR(from_date) AS from_year, YEAR(to_date) AS to_year FROM title_emp_nos INNER JOIN salaries ON title_emp_nos.emp_no = salaries.emp_no;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW salary_years AS SELECT from_year as year FROM title_salaries UNION SELECT to_year as year FROM title_salaries WHERE to_year <> 9999;";
            mysqli_query($connection, $query);
            $query = "SELECT title, year, AVG(salary) as average_salary FROM salary_years JOIN title_salaries ON from_year <= year AND year <= to_year GROUP BY year ORDER BY year DESC;";
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
    
    }

?>

<?php
    mysqli_close($connection);
?>

</body>
</html>