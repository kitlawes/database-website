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
    <input style="display: none;" name="query_type" value="department_names">
    <input type="submit" value="Query">
    Department names
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="titles">
    <input type="submit" value="Query">
    Titles
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="employees_per_department">
    <input type="submit" value="Query">
    Amount of employees per department
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="employees_per_title">
    <input type="submit" value="Query">
    Amount of employees per title
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="present_average_salaries_per_department">
    <input type="submit" value="Query">
    Present average salaries per department
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="present_average_salaries_per_title">
    <input type="submit" value="Query">
    Present average salaries per title
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="employees_per_year">
    <input type="submit" value="Query">
    Amount of employees per year
</form>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
    <input style="display: none;" name="query_type" value="average_salaries_per_year_for_department">
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
    <input style="display: none;" name="query_type" value="average_salaries_per_year_for_title">
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
        if ($query_type == "department_names") {
            $query = "SELECT dept_name FROM departments;";
        }
        if ($query_type == "titles") {
            $query = "SELECT DISTINCT title FROM titles;";
        }
        if ($query_type == "employees_per_department") {
            $query = "SELECT dept_name, COUNT(*) as count FROM departments INNER JOIN dept_emp ON departments.dept_no = dept_emp.dept_no GROUP BY departments.dept_no ORDER BY count DESC;";
        }
        if ($query_type == "employees_per_title") {
            $query = "SELECT title, COUNT(*) as count FROM employees INNER JOIN titles ON employees.emp_no = titles.emp_no GROUP BY titles.title ORDER BY count DESC;";
        }
        if ($query_type == "present_average_salaries_per_department") {
            $query = "CREATE OR REPLACE VIEW dept_emp_nos AS SELECT dept_name, emp_no FROM departments INNER JOIN dept_emp ON departments.dept_no = dept_emp.dept_no;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW present_salaries AS SELECT emp_no, salary FROM salaries WHERE to_date > CURDATE();";
            mysqli_query($connection, $query);
            $query = "SELECT dept_name, AVG(salary) as average_salary FROM dept_emp_nos JOIN present_salaries ON dept_emp_nos.emp_no = present_salaries.emp_no GROUP BY dept_name ORDER BY average_salary DESC;";
        }
        if ($query_type == "present_average_salaries_per_title") {
            $query = "CREATE OR REPLACE VIEW present_salaries AS SELECT emp_no, salary FROM salaries WHERE to_date > CURDATE();";
            mysqli_query($connection, $query);
            $query = "SELECT title, AVG(salary) as average_salary FROM titles JOIN present_salaries ON titles.emp_no = present_salaries.emp_no GROUP BY title ORDER BY average_salary DESC;";
        }
        if ($query_type == "employees_per_year") {
            $query = "CREATE OR REPLACE VIEW dept_emp_years AS SELECT YEAR(from_date) as year FROM dept_emp UNION SELECT YEAR(to_date) as year FROM dept_emp WHERE to_date <> \"9999-01-01\";";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW dept_emp_max_year AS SELECT MAX(year) + 1 as year FROM dept_emp_years;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW dept_emp_years_extended AS SELECT * FROM dept_emp_years UNION SELECT * FROM dept_emp_max_year;";
            mysqli_query($connection, $query);
            $query = "CREATE OR REPLACE VIEW dept_emp_dates AS SELECT DATE_ADD(\"0000-01-01\", INTERVAL year YEAR) as date FROM dept_emp_years_extended;";
            mysqli_query($connection, $query);
            $query = "SELECT date, COUNT(*) as employees FROM dept_emp JOIN dept_emp_dates ON dept_emp.from_date <= date AND dept_emp.to_date > date GROUP BY date ORDER BY date DESC;";
        }
        if ($query_type == "average_salaries_per_year_for_department") {
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
        if ($query_type == "average_salaries_per_year_for_title") {
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