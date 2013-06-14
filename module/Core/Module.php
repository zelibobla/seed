<?php
namespace Core;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module{

	/**
	* catch bootstrap event and run initialize procedures
	* @param event - MvcEvent instance
	* @return void
	*/
	public function onBootstrap( MvcEvent $e ){
		$application = $e->getApplication();
		$event_manager = $application->getEventManager();
		$event_manager->attach( 'render', array( $this, 'registerJsonStrategy' ), 100 );
		$module_route_listener = new ModuleRouteListener();
		$module_route_listener->attach( $event_manager );
    }

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

	/**
	* describe json strategy
	* @param $e - instance of Zend\Mvc\MvcEvent
	* @return void
	*/
	public function registerJsonStrategy( MvcEvent $e ){
		$app          = $e->getTarget();
		$locator      = $app->getServiceManager();
		$view         = $locator->get( 'Zend\View\View' );
		$jsonStrategy = $locator->get( 'ViewJsonStrategy' );
		$view->getEventManager()->attach( $jsonStrategy, 100 );
    }

}
