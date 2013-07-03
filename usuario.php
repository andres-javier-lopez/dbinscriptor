<?php

if(file_exists('user.lock')) {
	die('1');
}

require_once '../moondragon/moondragon.database.php';
include 'conexion.php';
Database::connect('mysql', $host, $user, $password, $database);
$dbmanager = Database::getManager();

try {
	$salt = '$5$'.openssl_random_pseudo_bytes(16,$cryptostrong);
	if(!$cryptostrong) { die('2'); }
	$password = crypt($dbmanager->evalSQL(Request::getGET('password')), $salt);
}
catch(RequestException $e) {
	die('3');
}

$sql = 'INSERT INTO `users` (`username`, `password`) VALUES ("admin", "'.$password.'")';
try {
	$dbmanager->query($sql);
}
catch(QueryException $e) {
	echo $e->getMessage();
	die('4');
}


if(is_writable('.')) {
	file_put_contents('user.lock', '1');
	echo 'ok';
}
else {
	echo 'almost ok';
}
