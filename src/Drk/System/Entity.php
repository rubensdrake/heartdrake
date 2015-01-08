<?php
namespace Drk\System;

use Doctrine\ORM\Tools\Setup,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\Common\Cache\ArrayCache as Cache,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\ClassLoader;

class Entity extends HeartDrake{

	public $em;
	public $count = 0;

	private static $instance;

	// O método getInstance
	/**
	* Previnir que seja feita uma conexão a todo momento
	**/ 
    public static function getInstance($configBd = array())
    {
    	
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c($configBd);
        }
        
        return self::$instance;
    }

	private function __construct($configBd){

		$loader = new ClassLoader('Entity',__DIR__.'/../../'.APP);
		$loader->register();
		$loader = new ClassLoader('EntityProxy',__DIR__.'/../../'.APP);
		$loader->register();

		//configuration
		$config = new Configuration();
		$cache = new Cache();
		$config->setQueryCacheImpl($cache);
		$config->setProxyDir(__DIR__.'/../../'.APP.'Entity/EntityProxy');
		$config->setProxyNamespace('App\Entity\EntityProxy');
		$config->setAutoGenerateProxyClasses(true);

		
		//mapping (example uses annotations, could be any of XML/YAML or plain PHP)
		AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
		$driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
		    new \Doctrine\Common\Annotations\AnnotationReader(),
		    array(__DIR__.'/../../'.APP."Entity")
		);
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);

		//getting the EntityManager
		$em = EntityManager::create(
		    $configBd,
		    $config
		);

		
		$this->em = $em;

	}

}