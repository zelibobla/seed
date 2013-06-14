<?php
namespace Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class TranslatorController extends AbstractActionController{

	/**
	* return json array of translations
	* @return array of view values
	*/
	public function getAction(){
		$translator = $this->getServiceLocator()->get( 'translator' );
		$loader = $translator->getPluginManager()->get( 'gettext' );
		$messages = $loader->load( "ru_RU", __DIR__ . "/../../language/ru_RU.mo" );
		$this->getResponse()->getHeaders()->addHeaderLine( 'Cache-Control', 'public, max-age=3600', true );
		$this->getResponse()->getHeaders()->addHeaderLine( 'Expires', date( "D, d M Y H:i:s e", time() + 60 * 60 * 24 ), true );
		return new JsonModel( $messages );
    }
}
