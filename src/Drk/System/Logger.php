<?php
namespace Drk\System; 

class Logger extends HeartDrake{

	private $log;
	private $file;

	function __construct()
	{

		$config = parent::getInstance()->getConfig();

		if(!file_exists(PATH.'/'.$config['log.path']))
			mkdir(PATH.'/'.$config['log.path'],0775,true);

		$this->file = PATH.'/'.$config['log.path']."app.log";


		$this->log = new \Monolog\Logger('log');
		
		if(is_readable(PATH.'/'.$config['log.path'])){
			$this->log->pushHandler(
				new \Monolog\Handler\StreamHandler(
						$this->file, \Monolog\Logger::WARNING
					)
			);
		}else{
			throw new \Exception("Verifique as permissões do arquivo: ".$file, 1);
			
		}
	}

	public function readLog(){
		return file_get_contents($this->file);
	}

	public function addError($txt = 'ERROR'){
		return $this->log->addError($_SERVER['REQUEST_URI'].' '.$txt);
	}

	public function addWarning($txt = 'WARNING'){
		return $this->log->addWarning($_SERVER['REQUEST_URI'].' '.$txt);
	}

	public function resetLog(){
		file_put_contents($this->file, '');
		return $this->addWarning('Log Reseted');
	}
}