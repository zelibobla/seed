<?php
namespace Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class CronController extends AbstractActionController{

	/**
	* send all expired messages in a stack
	* @return void
	*/
	public function postalAction(){
		if( !$this->checkSecret() )
			return $this->redirect()->toRoute( 'home' );
		$this->getServiceLocator()->get( 'postal' )->flush();
		exit( 'send' );
	}
	
	/**
	* clean up some garbage staff (guest users long time inactive and so on)
	* @return void
	*/
	public function cleanup(){
		if( !$this->checkSecret() )
			return $this->redirect()->toRoute( 'home' );

		$month_ago = date( "Y-m-d H:i:s", time() - 60 * 60 * 24 * 30 );
		$day_ago = date( "Y-m-d H:i:s", time() - 60 * 60 * 24 );
		$this->getServiceLocator()->get( 'user_mapper' )->delete( array( 'role' => 'guest',
		 													   			 'active_at < ?' => $day_ago ) );
		exit( 'cleaned' );
	}
	
	/**
	* check secret key allowing to run cron tasks
	* @return boolean
	*/
	private function checkSecret(){
		$config = $this->getServiceLocator()->get( 'config' );
		$cron_secret = @$config[ 'settings' ][ 'cron_secret' ];
		if( false == $cron_secret )
			throw new Exception( "Can't run cron controller with undefined [ 'settings' ][ 'cron_secret' ] value" );
		if( false == ( $secret = $this->params()->fromQuery( 'secret' ) ) ||
		 	$secret != $cron_secret )
			return false;
		return true;
	}
}