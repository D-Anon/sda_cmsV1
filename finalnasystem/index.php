<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clock In and Out</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Welcome to the Time Management System</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="clock_in_out.php">Clock In/Out</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Clock In and Out</h2>
        <form action="clock_in_out.php" method="POST">
            <label for="employee_id">Employee ID:</label>
            <input type="text" id="employee_id" name="employee_id" required>
            <button type="submit">Clock In/Out</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2025 Time Management System</p>
    </footer>
    <script src="js/scripts.js"></script>
</body>
</html>