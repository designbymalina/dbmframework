<?php
 $password = 'Test123';

 if (defined('PASSWORD_ARGON2ID')) {
  $hash = password_hash($password, PASSWORD_ARGON2ID);
 } else {
  $hash = password_hash($password, PASSWORD_DEFAULT, array('time_cost' => 10, 'memory_cost' => '2048k', 'threads' => 6));
 }

 echo $hash;
?>
