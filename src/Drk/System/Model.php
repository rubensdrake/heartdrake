<?php
namespace Drk\System;
use Drk\System\Strings;

class Model extends HeartDrake{

	public function __construct(){
		
		$config = HeartDrake::getInstance()->getConfig();


		if($config['db']['driver'] == 'mongodb'){
			$this->db = new Mongo($config['db']);
		}

		if($config['db']['driver'] == 'pdo_mysql'){
			$this->db = Entity::getInstance($config['db']);
		}
	}

	public function isLoaded(){
		return Strings::get('model_is_loaded',get_called_class());
	}
}