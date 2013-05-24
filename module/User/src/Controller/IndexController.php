<?php

namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController{

	/**
	* clean up some garbage staff (guest users long time inactive and so on)
	* should be run by cron
	* @return void
	*/
	public function cleanAction(){
		$month_ago = date( "Y-m-d H:i:s", time() - 60 * 60 * 24 * 30 );
		$day_ago = date( "Y-m-d H:i:s", time() - 60 * 60 * 24 );
		$this->_services->get( 'user_mapper' )->delete( array( 'role' => 'guest',
		 													   'active_at < ?' => $day_ago ) );
		exit( 'ok!' );
	}

    public function indexAction(){
        return new ViewModel();
    }
}
