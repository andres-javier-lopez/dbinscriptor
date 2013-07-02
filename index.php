<?php

require_once '../moondragon/moondragon.core.php';
require_once 'moondragon.database.php';
require_once 'moondragon.manager.php';
require_once 'moondragon.render.php';
require_once 'moondragon.session.php';
include 'conexion.php';

Database::connect('mysql', $host, $user, $password, $database);
Session::init('dbinscriptor');
Template::addDir('templates');
define('CLEAN_URL', false);

$data = array();
$data['table'] = 'projects';
$data['primary'] = 'id_project';
$data['fields'] = array('name', 'database_name', 'project_route', 'database_charset', 'database_version');
ModelLoader::addModel('project', $data);

$data = array();
$data['table'] = 'install_scripts';
$data['primary'] = 'id_script';
$data['fields'] = array('name');
$data['relations'] = array('projects.id_project');
ModelLoader::addModel('scripts', $data);

$data = array();
$data['table'] = 'projects_changelog';
$data['primary'] = 'id_project_changelog';
$data['fields'] = array('mayor_version', 'minor_version', 'point_version', 'script_file', 'timestamp');
$data['relations'] = array('projects.id_project');
ModelLoader::addModel('changelog', $data);

class System extends Manager {
	
	public function __construct() {
		parent::__construct();
		
		if(Session::get('authorized') !== true && $this->getTask() != 'login' && $this->getTask() != 'login_process') {
			$this->doTask('login');
		}
	}
	
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
					$status = '<a href="?task=updatedb&id='.$row->id_project.'"><strong>Update!</strong></a>';
				}
				else {
					$status = '<strong>Database don\'t match</strong>';
				} 
			}
			else {
				$status = '<strong>Project missing</strong>';
			}
			echo '<tr data-id="'.$row->id_project.'"><td><a href="?task=json_edit&id='.$row->id_project.'" class="edit_button">E</a> <a href="?task=delete" class="delete_button" data-id="'.$row->id_project.'">D</a><td>'.$row->name.'</td><td>'.$row->database_name.'</td><td>'.$row->database_version.'</td><td>'.$status.'</td><td>'.$row->project_route.'</td></tr>';
			
			
		}
		echo '</table>';
		echo $this->addScript();
	}

	public function login() {
		echo 'Ingrese por favor';
		echo Template::load('login');
	}
	
	public function login_process() {
		try {
			$usuario = Request::getPOST('usuario');
			$password = Request::getPOST('password');
		}
		catch(RequestException $e) {
			
		}
		
		// Proceso de login va a aquí
		
		Session::set('authorized', true);
		$this->doTask('index');
	}

	public function updatedb() {
		try {
			$id = Request::getGET('id');
			$model = ModelLoader::getModel('project');
			$data = $model->getData($id);
			
			echo 'Actualizando '.$data->name.'...<br/>';
			$route = str_replace('//', '/', $data->project_route.'/database');
			include 'conexion.php';
			$database = $data->database_name;
			
			if($data->database_version == '0.0.0') {
				echo 'Instalando base de datos inicial...<br/>';
				
				$scripts = ModelLoader::getModel('scripts')->getReader()->addWhere('id_project', $id)->getRows();
				foreach($scripts as $script) {
					$command = "mysql --host=$host --user=$user --password=$password $database < {$route}/{$script->name}.sql";
					//echo $command.'<br/>';
					system($command);
				}
				$this->saveProjectVersion($id, "1.0.0", "Initial Install");
				list($major, $minor, $point) = array(1,0,0);
			}
			else {
				list($major, $minor, $point) = explode('.', $data->database_version);
			}
			
			$actual_version = explode('.', trim(file_get_contents($route.'/version.txt')));
			echo 'Actualizando...<br/>';
			$updating = true;
			do{
				do {
					do {
						$point++;
						$file = $route.'/updates/db.'.$major.'.'.$minor.'.'.$point.'.sql';
						if(file_exists($file)) {
							$command = "mysql --host=$host --user=$user --password=$password $database < $file";
							echo $command.'<br/>';
							system($command);
							$this->saveProjectVersion($id, $major.'.'.$minor.'.'.$point, $file);
						}
						else {
							$point = 0;
						}
					} while($point != 0);
					$minor++;
					$file = $route.'/updates/db.'.$major.'.'.$minor.'.'.$point.'.sql';
					if(file_exists($file)) {
						$command = "mysql --host=$host --user=$user --password=$password $database < $file";
						echo $command.'<br/>';
						system($command);
						$this->saveProjectVersion($id, $major.'.'.$minor.'.'.$point, $file);
					}
					else {
						$minor = 0;
					}
				} while($minor != 0);
				$major++;
				$file = $route.'/updates/db.'.$major.'.'.$minor.'.'.$point.'.sql';
				if(file_exists($file)) {
					$command = "mysql --host=$host --user=$user --password=$password $database < $file";
					//echo $command.'<br/>';
					system($command);
					$this->saveProjectVersion($id, $major.'.'.$minor.'.'.$point, $file);
				}
				else {
					$updating = false;
				}
			} while($updating == true);
		}
		catch(RequestException $e) {
			return $e->getMessage();
		}
		catch(QueryException $e) {
			return $e->getMessage();
		}
		
		echo 'Actualizaci&oacute;n finalizada.<br/>';
		echo '<a href="?task=index">Regresar</a><br/>';
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
			$data_array['project']['scripts'] = $this->getDBScripts($id_data);
			
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
			$scripts = Request::getPOST('scripts');
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
				$id = $model->create($dataset);
				
			}
			else {
				$model->update($id, $dataset);
			}
			$this->setDBScripts($id, $scripts);
			
			$this->doTask('index');
		}
		catch(QueryException $e) {
			return $e->getMessage();
		}
	}

	public function delete() {
		try {
			$id = Request::getPOST('id');
			$model = ModelLoader::getModel('project');
			$model->delete($id);
			return 1;
		}
		catch(RequestException $e) {
			return $e->getMessage();
		}
		catch(QueryException $e) {
			return $e->getMessage();
		}
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
	
	protected function getDBScripts($id_project) {
		$model = ModelLoader::getModel('scripts');
		
		try {
			$rows = $model->getReader()->addWhere('id_project', $id_project)->getRows();
			$scripts = array();
			foreach($rows as $row) {
				$scripts[] = $row->name;
			}
			return implode(',', $scripts);
		}
		catch(QueryException $e) {
			return '';
		}
	}
	
	protected function setDBScripts($id_project, $scripts) {
		$model = ModelLoader::getModel('scripts');
		
		$old_scripts = $this->getDBScripts($id_project);
		
		if($scripts == '') {
			$scripts = 'baseline';
		}
		
		if($scripts == $old_scripts) {
			return true;
		}
		
		try {
			$model->deleteWhere('id_project', $id_project);
			
			$scripts_array = explode(',', $scripts);
			foreach($scripts_array as $script) {
				$dt = $model->getDataset();
				$dt->name = $script;
				$dt->id_project = $id_project;
				$model->create($dt);
			}
			
			return true;
		}
		catch(QueryException $e) {
			return false;
		}
	}
	
	protected function saveProjectVersion($id_project, $version, $scriptfile) {
		$modelP = ModelLoader::getModel('project');
		$modelC = ModelLoader::getModel('changelog');
		
		$versionA = explode('.', $version); 
		
		if(count($versionA) != 3){
			return false;
		}
		
		try {
			$data = $modelP->getData($id_project);
			$data->database_version = $version;
			$modelP->update($id_project, $data);
			
			$data = $modelC->getDataset();
			$data->id_project = $id_project;
			$data->major_version = $versionA[0];
			$data->minor_version = $versionA[1];
			$data->point_version = $versionA[2];
			$data->script_file = $scriptfile;
			$modelC->create($data);
		}
		catch(QueryException $e) {
			return false;
		}
		
		return true;
	}
}

Buffer::start('content');
MoonDragon::run(new System());
Buffer::end('content');
$content = Buffer::getContent('content');

echo Template::load('main', array('content' => $content), true);

