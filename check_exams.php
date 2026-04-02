<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$res = mysqli_query($link, "SELECT id, name FROM exam");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['id'] . ": " . $row['name'] . "\n";
}
