<?php
namespace User;

use Core\Model\DbTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication;
use Zend\Db\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;

class Module{
	
	protected $_services;
	
	/**
	* catch bootstrap event and run initialize procedures
	* @param event - MvcEvent instance
	* @return void
	*/
    public function onBootstrap( MvcEvent $event ){
		$this->_services = $event->getApplication()->getServiceManager();
        $eventManager = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach( $eventManager );
		$this->initUser();
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
	* since we need to retrieve a serviceLocator instance inside of viewHelper
	* @return array
	*/
	public function getViewHelperConfig(){
		return array(
			'factories' => array(
				'isAllowed' => function( $sm ) {
	          		$locator = $sm->getServiceLocator();
	          		return new \User\Helper\IsAllowed( $locator );
	        	}
	      	)
	    );
	}

	/**
	* define additional services
	* @return array
	*/
	public function getServiceConfig(){
		return array(
			'factories' => array(
				/**
				* retrieve id of currently authenticated user from session and build from DB whole object
				* if there is no such a record in session new user will be created and returned
				*/
				'user' => function( $services ){
					$auth = new AuthenticationService();
					if( false == ( $user_id = $auth->getStorage()->read() ) )
						return $services->get( 'user_mapper' )->create()->actDefaults( null, true );
					return $services->get( 'user_mapper' )->build( ( int ) $user_id );
			  	},
				/**
				* since we have got users we should have access control list (ACL)
				* @return Zend\Permissions\Acl\Acl with defined table of roles, resources and permissions
				*/
				'acl' => function( $services ){
					if( false == ( $permissions = $services->get( 'permissions_mapper' )->fetch( array() ) ) )
						throw new Exception( "Unable to run application without of any permission record" );
					$acl = new Acl();
					foreach( $permissions as $p ){
						if( false == $acl->hasRole( $p->getRole() ) )
							$acl->addRole( new GenericRole( $p->getRole() ) );
						if( false == $acl->hasResource( $p->getResource() ) ) 
							$acl->addResource( new GenericResource( $p->getResource() ) );
						$acl->allow( $p->getRole(), $p->getResource(), $p->getPrivilege() );
					}
					return $acl;	
				}
			),
		);
	}

	/**
	* instantiate user anyway even if he didn't sign up
	* thus if signed in and cookie proves – authenticate him
	* @return mixed
	*/
	public function initUser(){
		/* if stored session exists	*/
		$auth = new AuthenticationService();
    	if( $auth->hasIdentity() &&
 			true == ( $user_id = ( int ) $auth->getIdentity() ) &&
 			true == ( $user = $this->_services->get( 'user_mapper' )->build( $user_id ) ) ){
			return $user->setActiveAt( date( "Y-m-d H:i:s" ) )
						->save();
		}
		else
			// if session exists but we couldn't retrieve user from DB:
			$auth->clearIdentity();

		/* if user saved in cookies	*/
		if( true == ( $hash = addslashes( @$_COOKIE[ 'hash' ] ) ) &&
		 	true == ( $user = $this->_services->get( 'user_mapper' )->build( array( 'password_hash' => $hash ) ) ) )
			return $user->writeToSession()
						->setActiveAt( date( "Y-m-d H:i:s" ) )
						->save();

		/* if not logged, create temporary user and login him automatically	*/
		$new_user = $this->_services->get( 'user_mapper' )->create();
		$new_user->actDefaults( null, true );
	}
}
