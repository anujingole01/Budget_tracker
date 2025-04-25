<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finance_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Create database
if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname") === TRUE) {
    echo "✅ Database '$dbname' ready.<br>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($dbname);

// Create users table
$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($usersTable)) {
    die("❌ Error creating users table: " . $conn->error);
}
echo "✅ Users table ready.<br>";

// Create transactions table with recurring fields
$transactionsTable = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Other',
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_interval INT DEFAULT NULL,
    last_recurrence DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!$conn->query($transactionsTable)) {
    die("❌ Error creating transactions table: " . $conn->error);
}
echo "✅ Transactions table ready.<br>";

// Create budgets table
$budgetsTable = "CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY user_category_month_year (user_id, category, month, year)
)";

if (!$conn->query($budgetsTable)) {
    die("❌ Error creating budgets table: " . $conn->error);
}
echo "✅ Budgets table ready.<br>";

// Create stored procedure for recurring transactions (without DELIMITER)
$recurringProcedure = "
CREATE PROCEDURE IF NOT EXISTS ProcessRecurringTransactions()
BEGIN
    -- Copy recurring transactions that are due
    INSERT INTO transactions (user_id, type, category, amount, description, created_at)
    SELECT user_id, type, category, amount, description, NOW()
    FROM transactions
    WHERE is_recurring = TRUE
    AND (
        (last_recurrence IS NULL AND DATEDIFF(NOW(), created_at) >= recurrence_interval)
        OR 
        (last_recurrence IS NOT NULL AND DATEDIFF(NOW(), last_recurrence) >= recurrence_interval)
    );
    
    -- Update last recurrence date
    UPDATE transactions 
    SET last_recurrence = NOW()
    WHERE is_recurring = TRUE
    AND (
        (last_recurrence IS NULL AND DATEDIFF(NOW(), created_at) >= recurrence_interval)
        OR 
        (last_recurrence IS NOT NULL AND DATEDIFF(NOW(), last_recurrence) >= recurrence_interval)
    );
END";

// Drop procedure first to avoid errors if it exists
$conn->query("DROP PROCEDURE IF EXISTS ProcessRecurringTransactions");

if (!$conn->query($recurringProcedure)) {
    die("❌ Error creating recurring transactions procedure: " . $conn->error);
}
echo "✅ Recurring transactions procedure ready.<br>";

// Add any missing columns to existing tables
$alterQueries = [
    "ALTER TABLE transactions ADD COLUMN IF NOT EXISTS is_recurring BOOLEAN DEFAULT FALSE",
    "ALTER TABLE transactions ADD COLUMN IF NOT EXISTS recurrence_interval INT DEFAULT NULL",
    "ALTER TABLE transactions ADD COLUMN IF NOT EXISTS last_recurrence DATETIME DEFAULT NULL"
];

foreach ($alterQueries as $query) {
    if (!$conn->query($query)) {
        echo "⚠️ Warning: " . $conn->error . "<br>";
    }
}
echo "✅ Database schema updates complete.<br>";

$conn->close();
echo "🚀 Setup completed successfully!";
?>

<?php 
// Redirect browser
header("Location: http://localhost/pmf/p/dashboard.php");
exit;
?>