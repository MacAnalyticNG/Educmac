<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$result = mysqli_query($link, "SHOW TABLES");
while($row = mysqli_fetch_array($result)) {
    echo $row[0] . "\n";
}
mysqli_close($link);
?>
