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
	
	public function save() {
		try {
			$name = Request::getPOST('name');
			$route = Request::getPOST('route');
			$database = Request::getPOST('database');
		}
		catch(RequestException $e) {
			return $e->getMessage();
		}

		try {
			$model = ModelLoader::getModel('project');
			$dataset = $model->getDataset();
			$dataset->name = $name;
			$dataset->project_route = $route;
			$dataset->database_name = $database;
			$model->create($dataset);
			MoonDragon::redirect('?task=index');
		}
		catch(QueryException $e) {
			return $e->getMessage();
		}
	}
}

MoonDragon::run(new System());
