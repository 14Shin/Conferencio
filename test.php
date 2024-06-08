<?php
$password = 'motdepasse_test';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
