<?php

namespace User\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class User extends AbstractPlugin{

	/**
	* shorthand to get current user
	* @return User\Model\User object
	*/
    public function __invoke( $pass = null ){
		return $this->getController()->getServiceLocator()->get( 'user' );
    }
}
