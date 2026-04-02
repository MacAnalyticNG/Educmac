<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$tables = ['student', 'enroll', 'parent', 'fees_allocation', 'fees_type'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $result = mysqli_query($link, "DESCRIBE `$table` ");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "Error or missing\n";
    }
}
$result = mysqli_query($link, "SHOW TABLES");
echo "--- ALL TABLES ---\n";
while($row = mysqli_fetch_array($result)) {
    echo $row[0] . "\n";
}

mysqli_close($link);
?>
