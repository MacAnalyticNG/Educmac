<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }
$result = mysqli_query($link, 'SELECT count(*) as total FROM login_credential');
$row = mysqli_fetch_assoc($result);
echo "Total users: " . $row['total'] . "\n";
if ($row['total'] > 0) {
    $result = mysqli_query($link, 'SELECT username, password, role, active FROM login_credential LIMIT 5');
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
}
mysqli_close($link);
?>
