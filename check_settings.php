<?php
$link = mysqli_connect('localhost', 'root', '', 'schoolportal');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$result = mysqli_query($link, 'SELECT * FROM global_settings WHERE id = 1');
if ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
} else {
    echo "No settings found in global_settings table.";
}
mysqli_close($link);
?>
