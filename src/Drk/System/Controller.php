<?php
namespace Drk\System;
use Drk\System\Logger;
use Drk\System\HeartDrake;
use Drk\System\Security;

/**
* Inject all Dependencies into Children Class
*/
class Controller extends HeartDrake
{

	protected $application;

	public function __construct($application)
	{

		$this->setApplication($application);
		$this->method = $_SERVER['REQUEST_METHOD'];

	}

	public function setApplication($application)
	{
		$this->application = $application;
	}

	public function getApplication()
	{
		return $this->application;
	}

	public function getUrl($u = '')
	{
		return $this->getApplication()->getConfig('url').$u;
	}

	public function isAjax(){
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
		    AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		   return true;
		}
		return false;
	}

	/* FUNCAO QUE VERIFICA SE É UM METHOD POST */
	public function isPost(){
		if($_SERVER['REQUEST_METHOD'] == 'POST')
			return true;
		else
			return false;
	}

	/*FUNCAO QUE LÊ OS POST*/
	public function post($name){
		if(isset($_POST[$name]))
			return Security::_cleanInputs($_POST[$name]);
	}

	/*Read Gets*/
	public function get($name = ''){
		if(isset($_GET[$name]))
			return Security::_cleanInputs($_GET[$name]);
	}


	public function view($view,$vars = array()){

		$vars['__URL'] = $this->getApplication()->getConfig('url');
		//$vars['__AUTH'] = $this->Auth->isAuth();

		if(!empty($vars))
		foreach ($vars as $key => $value) {

			$$key = $value;

		}

		if(file_exists(V.$view.EXT))
			include(V.$view.EXT);
		else{
			echo '404';
		}


	}

	public function getJs($script,$seg,$tipo = 'js'){

		if(Router::getSegments(0) == $seg){
			if($tipo == 'js')
				return '<script src="'.$this->getConfig('url').'public/js/'.$seg.'/'.$script.'.'.$tipo.'"></script>';
			if($tipo == 'php')
				include('public/js/'.$seg.'/'.$script.'.'.$tipo);
		}

	}



}