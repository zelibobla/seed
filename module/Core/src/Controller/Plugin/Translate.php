<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Translate extends AbstractPlugin{

	/**
	* shorthand to translate
	* @param string – key to search for a translation
	* @return string
	*/
	protected function __invoke( $string ){
		return $this->getController()->getServiceLocator()->get( 'translator' )->translate( $string, '' );
	}
}
