<?php
// Author: Andrés Javier López <ajavier.lopez@gmail.com>

require 'conexion.php';
require 'version.php';

$mysql = new mysqli($host, $user, $password, $database);
$result = $mysql -> query('SELECT `major_version`, `minor_version`, `point_version` FROM `changelog` ORDER BY `id_changelog` DESC LIMIT 1');
$row = $result -> fetch_object();

if ($version[0] != $row -> major_version || $version[1] != $row -> minor_version || $version[2] != $row -> point_version) {
	$file = 'backups/backup.' . $row -> major_version . '.' . $row -> minor_version . '.' . $row -> point_version . '.sql.bz2';
	$command = "mysqldump --opt -h " . $host . " " . $database . " -u " . $user . " -p" . $password . " > " . $file . "";
	system($command);

	$major = $row -> major_version;
	$minor = $row -> minor_version;
	$point = $row -> point_version;
	$lastupdate = array($major, $minor, $point);
	if ($major != $version[0] || $minor != $version[1] || $point != $version[2]) {
		$updating = true;
		do {
			do {
				do {
					$point++;
					$file = 'updates/db.' . $major . '.' . $minor . '.' . $point . '.sql';
					if (file_exists($file)) {
						$command = "mysql --host=$host --user=$user --password=$password $database < $file";
						system($command);
						$lastupdate = array($major, $minor, $point);
						if(!$mysql -> real_query('INSERT INTO `changelog` VALUES(NULL,' . $major . ',' . $minor . ',' . $point . ',"' . $file . '", NOW())'))
						{
							die("Error when updating");
						}
					} else {
						$point = 0;
					}

				} while($point != 0);
				$minor++;
				$file = 'updates/db.' . $major . '.' . $minor . '.' . $point . '.sql';
				if (file_exists($file)) {
					$command = "mysql --host=$host --user=$user --password=$password $database < $file";
					system($command);
					$lastupdate = array($major, $minor, $point);
					if(!$mysql -> real_query('INSERT INTO `changelog` VALUES(NULL,' . $major . ',' . $minor . ',' . $point . ',"' . $file . '", NOW())'))
					{
						die("Error when updating");
					}
				} else {
					$minor = 0;
				}
			} while($minor != 0);
			$major++;
			$file = 'updates/db.' . $major . '.' . $minor . '.' . $point . '.sql';
			if (file_exists($file)) {
				$command = "mysql --host=$host --user=$user --password=$password $database < $file";
				system($command);
				$lastupdate = array($major, $minor, $point);
				if(!$mysql -> real_query('INSERT INTO `changelog` VALUES(NULL,' . $major . ',' . $minor . ',' . $point . ',"' . $file . '", NOW())'))
				{
					die("Error when updating");
				}
			} else {
				$updating = false;
			}

		} while($updating == true);

		if ($lastupdate[0] != $version[0] || $lastupdate[1] != $version[1] || $lastupdate[2] != $version[2]) {
			echo "Error: versions don't match";
		}
		else {
			echo "Succesful update";
		}
	}
} else {
	echo 'System is up to date';
}

