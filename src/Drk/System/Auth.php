<?php

namespace Drk\System;
use Drk\System\Logger;
use Drk\System\HeartDrake;
use Drk\System\Router;


session_start();
class Auth extends HeartDrake{

	private $paths;

	public function __construct(HeartDrake $app)
	{

		$this->paths = $app->getConfig('authenticate');

		$router = new Router($app);
		$path = $router->getSegments();



		$loginUrl = array_search($path[0], $this->paths);

		$redirect = explode('/', $loginUrl);

		if($redirect == $path)
			return true;



		/*autenticação - todo: melhorar segurança*/

		if(in_array($path[0], $this->paths)){

				if(!$this->isAuth()){
					if(!empty($this->getConfig('login.url')))
						$this->redirect($this->getConfig('url').$redirect[1]);

					throw new \Exception(Strings::get('permission_denied'));
				}
		}




	}

	public function isAuth(){
		if(empty($_SESSION['userdata'])){
			return false;
		}else{

			if($this->getConfig('useRoles')){

					$roles = $this->getConfig('roles');
					$role = explode(',', $this->getData('roles'));


					if(!empty($roles)){
						foreach ($roles as $key => $r) {
							if(in_array($r, $role))
								return true;
							else
								return false;
						}
					}
			}

			return true;
		}
	}

	public function create($data){
		$_SESSION['userdata'] = $data;
	}

	public function destroy(){
		session_destroy();
	}

	public function getData($pos = null){
		if(isset($_SESSION['userdata'][$pos]))
			return $_SESSION['userdata'][$pos];
	}

	public function get_md5(){
		$token = md5(uniqid(""));
		return $token;
	}

}