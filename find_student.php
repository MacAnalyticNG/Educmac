<?php
$link = mysqli_connect('localhost', 'root', '', 'thommyadel');
if (!$link) { die("Connect error: " . mysqli_connect_error()); }

$res = mysqli_query($link, "SELECT student.register_no, login_credential.username, login_credential.password 
                            FROM student 
                            JOIN login_credential ON login_credential.user_id = student.id 
                            WHERE login_credential.role = 7 LIMIT 1");
$row = mysqli_fetch_assoc($res);
print_r($row);
