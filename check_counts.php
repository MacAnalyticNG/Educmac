<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }

echo "Staff count: " . mysqli_num_rows(mysqli_query($link, "SELECT id FROM staff")) . "\n";
echo "Student count: " . mysqli_num_rows(mysqli_query($link, "SELECT id FROM student")) . "\n";
echo "Parent count: " . mysqli_num_rows(mysqli_query($link, "SELECT id FROM parent")) . "\n";

$res = mysqli_query($link, "SELECT * FROM global_settings WHERE id = 1");
$row = mysqli_fetch_assoc($res);
print_r($row);
