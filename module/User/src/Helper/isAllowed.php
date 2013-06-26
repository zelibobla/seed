<?php

namespace User\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface as ServiceLocator;

/**
* shorthand to check if currently logged user is allowed to do specified privilege over specified resource
*/
class IsAllowed extends AbstractHelper{
	
	protected $_services;
	
	public function __construct( ServiceLocator $services ){
		$this->_services = $services;
	}
	
	public function __invoke( $resource, $privilege ){
		$user = $this->_services->get( 'user' );
		return $this->_services->get( 'acl' )->isAllowed( $user->getRole(), $resource, $privilege );
	}
}