<?php
$mysqli = new mysqli("localhost", "root", "", "thommyadel");

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check student table columns
$result = $mysqli->query("SHOW COLUMNS FROM student");
$cols = [];
while ($row = $result->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "Student cols: " . implode(', ', $cols) . "\n";

// Alter wallet table
$mysqli->query("ALTER TABLE wallet MODIFY student_id INT NULL");
$mysqli->query("ALTER TABLE wallet ADD parent_id INT NULL AFTER student_id");

// Check wallet table columns
$result = $mysqli->query("SHOW COLUMNS FROM wallet");
$cols = [];
while ($row = $result->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "Wallet cols: " . implode(', ', $cols) . "\n";
