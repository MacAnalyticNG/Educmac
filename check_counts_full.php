<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }

$tables = ['staff', 'student', 'parent', 'branch', 'enroll', 'login_credential'];
foreach ($tables as $table) {
    if ($res = mysqli_query($link, "SELECT COUNT(*) as cnt FROM $table")) {
        $row = mysqli_fetch_assoc($res);
        echo "$table count: " . $row['cnt'] . "\n";
    } else {
        echo "$table error: " . mysqli_error($link) . "\n";
    }
}
