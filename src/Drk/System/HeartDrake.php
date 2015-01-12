<?php

namespace Drk\System;

class HeartDrake{

	// Guarda uma instância da classe

    private $config = array();
    private $app;
    private $loader;
    private $aplications;
    private $uri;

    private $class;
    private $function;


    //Block methods
    public function __construct(array $aplications, \Composer\Autoload\ClassLoader $loader){
        
        $this->setLoader($loader); //Only put composer loader under private Loader
        $this->setAplications($aplications);
        $this->loadClassUnder('router','Drk\System\Router');
       // $this->loadClassUnder('auth','Drk\System\Auth');
        $this->setUri();
        $this->isApp();
        $this->loadAppConfig();
        $this->setClassToLoad();
        $this->go();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function setFunction($function)
    {
        $this->function = $function;
    }

    public function setUri()
    {
        $this->uri = $this->router->getSegments();
    }

    public function loadClassUnder($under, $class, $app = '')
    {
        $this->$under = new $class($app);
    }

    public function setAplications(array $aplications)
    {
        $this->aplications = $aplications;
        foreach ($aplications as $key => $value) {

             $this->loader->add($key, realpath('../'));

        }
    }

    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function getApplications()
    {
        return $this->aplications;
    }

    public function getUri($pos = 'all')
    {   
        if($pos === 'all')
            return $this->uri;
        else
            return $this->uri[(int)$pos];
    }

    public function setClassToLoad()
    {

        $validClass = (isset($this->uri[0]) and $this->uri[0] !== '') ? true : false;
        $validFunction = isset($this->uri[1]) and $this->uri[0] !== '' ? true : false;

        $class = $validClass ? $this->uri[0] : $this->getConfig('router.default');
        $function = isset($this->uri[1]) ? $this->uri[1] : 'index';

        $this->setClass($class);
        $this->setFunction($function);
    }


    public function isApp()
    {
        $aplications = $this->getApplications();
        $firstUri = $this->getUri(0);

        if(in_array($firstUri, $aplications)){
            $this->setApp(array_search($firstUri, $aplications));
            unset($this->uri[0]);
            $this->uri = array_values($this->uri);
            return true;
        }
        return false;
    }

    /**
     * Return config.php
     * @return array
     */
    public function getConfig($pos = null){

        if(!empty($pos)){
            if(isset($this->config[$pos]))
                return $this->config[$pos];
            else
                return false;
        }
        else{
            return $this->config;
        }
    }

    public function addConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function getApp()
    {
        return $this->app;
    }


    public function loadAppConfig()
    {
        if(!$this->getApp()){
            if(ENVIRONMENT == 'dev')
                return die(Strings::get('app_not_configured','index.php - $config[\'apps\'] = array(\'Namespace\' => \'path\')'));
            else
                return die('404 - not found');
        }else{

            if(!file_exists(realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$this->getApp().'/config.php'))){
                die('Config not found: ' . realpath($_SERVER['DOCUMENT_ROOT'].'/../').'/'.$this->getApp().'/config.php');
            }

            require_once realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$this->getApp().'/config.php');
            define('URL_LOGIN',$config['login.url']);
            define('APP',realpath($_SERVER['DOCUMENT_ROOT'].'/../'.$this->getApp().'/'));
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
    }


    public function go()
    {

        $classP = ucfirst($this->getClass());
        $functonP = $this->getFunction();
        $app = $this->getApp();


        if(file_exists(C.$classP.EXT)){
            
            $class = "{$app}\\Controller\\{$classP}";


            try {
                $class = new $class($this);
            } catch (Exception $e) {
                 echo $e->getMessage();
            }

        }

        if(method_exists($class, $this->getFunction())){
            if(method_exists($class, 'inject'))
                $class->inject($this);
            $class->$functonP();
        }else{
            throw new \Exception(Strings::get('method_not_found',$this->getFunction()));
        }
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

}


