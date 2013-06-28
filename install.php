<?php
// Author: Andrés Javier López <ajavier.lopez@gmail.com>

if(file_exists('db.lock')) {
	echo 'Installation Locked';
	die();
}

require 'conexion.php';

$command = "mysql --host=$host --user=$user --password=$password $database < changelog.sql";
system($command);
$command = "mysql --host=$host --user=$user --password=$password $database < baseline.sql";
system($command);

$mysql = new mysqli($host, $user, $password, $database);
$result = $mysql->real_query('INSERT INTO `changelog` VALUES(NULL,1,0,0,"initial install", NOW())');

if($result){
	echo 'Success';
	file_put_contents('db.lock', 'locked');
}
else {
	echo 'Fail: '.$mysql->error;
}
