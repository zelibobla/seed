<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class View extends AbstractPlugin{

	/**
	* default action is view
	* @param pass – array of values to pass into view
	* @return Zend\View\Model\ViewModel object
	*/
    public function __invoke( $pass = null ){
        if( $pass === null )
            return $this;
        return $this->view( $pass );
    }

	/**
	* shorthand to get view renderer
	* @return Zend\View\Renderer\PhpRenderer object with values defined for currently dispatched request
	*/
	public function renderer(){
		return $this->getController()->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer' );
	}
	
	/**
	* return view model with inserted `user` and `acl` objects to act with them in a view
	* @param pass – array of values to pass into view
	* @return Zend\View\Model\ViewModel object
	*/
	public function view( $pass = null ){
		if( isset( $pass[ 'user' ] ) ||
		 	isset( $pass[ 'acl' ] ) )
			throw new \Exception( 'Unable to init view passing variables `user` or `acl` – names are reserved' );
		if( !is_array( $pass ) )
			$pass = array();
		$pass[ 'user' ] = $this->getController()->user();
		$pass[ 'acl' ] = $this->getController()->getServiceLocator()->get( 'acl' );
		$this->getController()->layout()->setVariables( $pass );
		return new ViewModel( $pass );
	}
	
	/**
	* emit form to json array
	* @param $form – instance of Zend\Form
	* @return array( 'header' => form header string, 'body' => string html rendered form, 'submit_text' => string text of submit button )
	*/
	public function jsonForm( \Zend\Form\Form $form ){
		$view = new ViewModel( array( 'form' => $form, 'user' => $this->getController()->user() ) );
		$view->setTemplate( $form->getAttribute( 'id' ) );
		return new JsonModel( array(  'header' => $this->getController()->_( $form->getAttribute( 'header' ) ),
									  'body' => $this->renderer()->render( $view ),
						 			  'submit_text' => $this->getController()->_( $form->getAttribute( 'submit' ) ) ) );
	}
}
