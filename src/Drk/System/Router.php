<?php

namespace Drk\System;
use Drk\System\Strings;
use Drk\System\HeartDrake;
use Drk\System\Security;

class Router extends HeartDrake{

	static function getSegments($n = -1,$route = true){


    	$url = !defined('URL') ? $_SERVER['HTTP_HOST'] : URL;

    	$url = str_replace('http://', '', $url);

		/*$url = str_replace($_SERVER['HTTP_HOST'], '', $url);*/

		if($url != '/')
			$url = str_replace($url, '', $_SERVER['REQUEST_URI']);
		else{
			$url = $_SERVER['REQUEST_URI'];
		}

		$segments = explode('/', $url);

		$segments = Security::_cleanInputs($segments);


		if(empty($segments[0])){
			array_shift($segments);
		}


		$apps = HeartDrake::getStaticConfig('apps');
		$inc = 0;
		$c = $segments[0];

		if(in_array($c, $apps) and $route){
			$inc++;
		}

		if($n != -1){
	    	if(!empty($segments[$n+$inc])){
				return $segments[$n+$inc];
	    	}
    	}else{
    		return $segments;
    	}
    }




	public function route(){

		$apps = $this->getConfig('apps');


		$inc = 0;


		if($this->getSegments(0,false)){

			$c = $this->getSegments(0,false);

			if(in_array($c, $apps)){

				$app = array_search($c, $apps);
				$inc++;

			}else{
				$app = array_search('', $apps);
			}


			if($this->getSegments(0+$inc,false)){
				$class = ucfirst($this->getSegments(0+$inc,false));
			}elseif(RTD){
				$class = RTD;
			}



		}elseif(RTD){

			$c = '';

			if(in_array($c, $apps)){

				$app = array_search($c, $apps);
				$inc++;

			}

			$class = RTD;

		}
		else{
			throw new \Exception(Strings::get('class_not_found',$app.'\Controller\\'.$class));
		}

		$class = explode('?', $class);
		$class = $class[0];
		$className = $class;

		if($this->getSegments(1+$inc,false))
			$method = $this->getSegments(1+$inc,false);
		else
			$method = 'index';

		$method = explode('?', $method);
		$method = $method[0];


		if(!empty($class) && !empty($method)){

				if(file_exists(C.$class.EXT)){
					$class = "{$app}\\Controller\\{$class}";

					try {
						$class = new $class;
					} catch (Exception $e) {
						 echo $e->getMessage();
					}

				}

				if(method_exists($class, $method)){
					$class->$method();
				}else{
					throw new \Exception(Strings::get('method_not_found',$method));
				}
		}

	}

}