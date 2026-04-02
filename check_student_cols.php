<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$result = mysqli_query($link, "SHOW COLUMNS FROM student");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . "\n";
}
