<?php

namespace Core\Helper;
use Zend\View\Helper\AbstractHelper;

class Date extends AbstractHelper{
	
	public function __invoke( $date ){
		if( is_string( $date ) )
			$date = strtotime( $date );
		$month = $this->getView()->translate( "of " . date( "F", $date ), '' );
		if( date( "Y", $date ) == date( "Y" ) )
			return date( "j ", $date ) . $month;
		else
			return date( "j ", $date ) . " $month " . date( "Y", $date );
	}
}