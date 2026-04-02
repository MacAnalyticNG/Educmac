<?php
$mysqli = new mysqli("localhost", "root", "", "thommyadel");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$queries = [
    // Task 4: Wallet System
    "CREATE TABLE IF NOT EXISTS wallet (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        balance DECIMAL(10,2) DEFAULT 0.00,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        wallet_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    // Task 5: Extracurricular Activities
    "CREATE TABLE IF NOT EXISTS extracurricular_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT
    )",
    "CREATE TABLE IF NOT EXISTS extracurricular_enrollment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        activity_id INT NOT NULL,
        enrollment_date DATE
    )",
    // Task 6: Notification Center
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role VARCHAR(50) NOT NULL,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($queries as $sql) {
    if (!$mysqli->query($sql)) {
        echo "Error: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    } else {
        echo "Successfully executed query.\n";
    }
}

$mysqli->close();
echo "Tables created successfully.\n";
?>
