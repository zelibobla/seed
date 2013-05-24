<?php
namespace Core;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module{

	/**
	* include config file
	* @return void
	*/
    public function getConfig(){
		return include __DIR__ . '/config/module.config.php';
    }

	/**
	* add `src` folder to autoloader namespaces
	* @return array
	*/
    public function getAutoloaderConfig(){
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/',
				),
			),
		);
	}
}
