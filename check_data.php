<?php
$link = mysqli_connect('localhost', 'root', '', 'schoolportal');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$tables = ['class', 'student', 'staff', 'subject', 'branch'];
foreach ($tables as $table) {
    $result = mysqli_query($link, "SELECT count(*) as total FROM `$table` ");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "Table $table: " . $row['total'] . " rows\n";
    } else {
        echo "Table $table: Error or missing\n";
    }
}
mysqli_close($link);
?>
