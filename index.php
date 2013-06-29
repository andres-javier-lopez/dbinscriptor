<?php

require_once '../moondragon/moondragon.core.php';
require_once 'moondragon.database.php';
require_once 'moondragon.manager.php';
require_once 'moondragon.render.php';
include 'conexion.php';

Database::connect('mysql', $host, $user, $password, $database);

Template::addDir('templates');

$data = array();
$data['table'] = 'projects';
$data['primary'] = 'id_project';
$data['fields'] = array('name', 'database_name', 'project_route', 'database_charset', 'database_version');
ModelLoader::addModel('project', $data);

class System extends Manager {
	public function index() {
		echo Template::load('new_project');
		
		$model = ModelLoader::getModel('project');
		
		echo '<table>';
		foreach($model->read() as $row) {
			echo '<tr><td>'.$row->name.'</td></tr>';
		}
		echo '</table>';
		
		return 'lets rock';
	}
}


MoonDragon::run(new System());
