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
			$database_dir = $row->project_route.'/database';
			$version_file = $database_dir.'/version.txt';
			if(file_exists($row->project_route) && file_exists($database_dir) && file_exists($version_file)) {
				$version_txt = file_get_contents($version_file);
				if(trim($version_txt) == $row->database_version) {
					$status = 'All up to date';
				}
				elseif($this->check_update($row->database_version, trim($version_txt))) {
					$status = '<strong>Update!</strong>';
				}
				else {
					$status = '<strong>Database don\'t match</strong>';
				} 
			}
			else {
				$status = '<strong>Project missing</strong>';
			}
			echo '<tr><td><a href="?task=json_edit&id='.$row->id_project.'" class="edit_button">E</a> <a href="?task=delete&id='.$row->id_project.'">D</a><td>'.$row->name.'</td><td>'.$row->database_name.'</td><td>'.$row->database_version.'</td><td>'.$status.'</td><td>'.$row->project_route.'</td></tr>';
			
			
		}
		echo '</table>';
		echo $this->addScript();
	}

	public function json_edit() {
		header('Content-Type: application/json');
		$model = ModelLoader::getModel('project');
		
		try {
			$id_data = Request::getGET('id');
			$data = $model->getData($id_data);
			
			$data_array['project']['id_project'] = $id_data;
			$data_array['project']['name'] = $data->name;
			$data_array['project']['database_name'] = $data->database_name;  
			$data_array['project']['project_route'] = $data->project_route;
			$data_array['project']['database_charset'] = $data->database_charset;
			$data_array['project']['database_version'] = $data->database_version;
			
			return json_encode($data_array);
		}
		catch(RequestException $e) {
			return '{"project": null}';
		}
		catch(QueryException $e) {
			return '{"project": null}';
		}
	}
	
	public function save() {
		try {
			$id = Request::getPOST('id');
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
			if($id == 0) {
				$model->create($dataset);
			}
			else {
				$model->update($id, $dataset);
			}
			MoonDragon::redirect('?task=index');
		}
		catch(QueryException $e) {
			return $e->getMessage();
		}
	}

	public function delete() {
		echo 'deleting';
		$this->index();
	}
	
	protected function addScript() {
		echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>';
		echo '<script type="text/javascript" src="js/form.js" /></script>';
	} 
	
	protected function check_update($current_version, $new_version) {
		$cver = explode('.', $current_version);
		$nver = explode('.', $new_version);
		
		if($nver[0] > $cver[0]) {
			return true;
		}
		elseif ($nver[0] == $cver[0] && $nver[1] > $cver[1]) {
			return true;
		}
		elseif ($nver[0] == $cver[0] && $nver[1] == $cver[1] && $nver[2] > $cver[2]) {
			return true;
		}
		else {
			return false;
		}
	}
}

MoonDragon::run(new System());
