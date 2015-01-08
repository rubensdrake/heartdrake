<?php

namespace Drk\System;

class HeartDrake{

	// Guarda uma instância da classe
    private static $instance;
    private $dependencies = array();
    private $config = array();
    private $app;
    private $loader;

   //Used by another class that extends her
    protected function inject($deps = array()){


    	$this->register(
    		$this->getInstance()
    			 ->dependencies
    	);

        $deps[get_called_class()] = $deps;

        if(count($deps))
            $this->register(
                $deps
        );

    }

    public function addConfig($name, $value)
    {
        $this->config[$name] = $value;

    }

    // O método getInstance
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     * Register all dependencies configured in index.php
     */
    public function register($deps = array()){

        if(count($deps) == 0){
            $deps = $this->getConfig();
            $deps = $deps['deps'];
        }

    	if(isset($deps) and count($deps) > 0){
    		foreach ($deps as $key => $d) {
                if($key == 'GLOBAL' or $key == get_called_class())
                    if(count($d) > 0)
                        foreach ($d as $i => $dep) {
                                if(!method_exists($this, $dep)){
                                    if(class_exists($i)){
                                        $this->$dep = new $i;
                                        $this->dependencies[$key][$i] = $dep;
                                    }else
                                        throw new \Exception(Strings::get('class_not_found',$i.' - '.Strings::get('called_by',get_called_class())));

                                }
                        }

            }
    	}
    }

    /**
     * Return config.php
     * @return array
     */
    public function getConfig($pos = null){

        $config = $this->getInstance();

        if(!empty($pos)){
            if(isset($config->config[$pos]))
                return $config->config[$pos];
            else
                return false;
        }
        else{
            return $config->config;
        }
    }

    public static function getStaticConfig($pos = null)
    {
        $h = HeartDrake::getInstance();

        if(!empty($pos))
            return $h->config[$pos];
        else
            return $h->config;

    }


    public function getAppUri()
    {
        $apps = $this->getConfig('apps');

        foreach ($apps as $key => $value) {

             $this->loader->add($key, realpath($_SERVER['DOCUMENT_ROOT'].'/../'));

        }

        $c = Router::getSegments(0,false);

        if(in_array($c, $apps)){
            $app = array_search($c, $apps);
        }else{
            $app = array_search('', $apps);
        }



        $this->setApp($app);

        if(!$this->getApp()){
            if(ENVIRONMENT == 'dev')
                return die(Strings::get('app_not_configured','index.php - $config[\'apps\'] = array(\'Namespace\' => \'path\')'));
            else
                return die('404 - not found');
        }else{

            if(!file_exists(realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$app.'/config.php'))){
                die('Config not found: ' . realpath($_SERVER['DOCUMENT_ROOT'].'/../').'/'.$app.'/config.php');
            }

            require_once realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$app.'/config.php');
            define('URL_LOGIN',$config['login.url']);
            define('APP',realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$app.'/'));
            define('URL', $config['url']);
            define('RTD', $config['router.default']);
            define('M', APP.'/Models/');
            define('V', APP.'/View/');
            define('C', APP.'/Controller/');
            define('PATH', realpath(__DIR__.'/../'));
            define('EXT', '.php');
            define('LANG', $config['lang']);
            if($config['debug'] === 'dev'){
                /*Set on to display errors*/
                ini_set('display_erros', 'on');
                /*Enable the error reporting for ALL Errors*/
                error_reporting(E_ALL);
                /*Reg Whoops*/
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->register();

            }elseif($config['debug'] === 'prod'){
                ini_set('display_erros', 'off');
                error_reporting(0);
            }

            foreach ($config as $key => $value) {
                $this->addConfig($key,$value);
            }

        }
        return $app;

    }

    public function go()
    {
        $this->Router->route();
        exit();
    }

    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    public function setApp($app = '')
    {
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }

    /**
     * Return config.php
     * @return array
     */
    public function setConfig($config = array()){
        $this->config = $config;
    }

    public function redirect($url) {
        if(!headers_sent()) {
            //If headers not sent yet... then do php redirect
            header('Location: '.$url);
            exit;
        } else {
            //If headers are sent... do javascript redirect... if javascript disabled, do html redirect.
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
            echo '</noscript>';
            exit;
        }
}



    //Block methods
    private function __construct(){}
    private function __clone()		{trigger_error('Clone is not allowed.', E_USER_ERROR);}


}


