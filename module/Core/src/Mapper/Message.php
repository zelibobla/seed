<?php

namespace Core\Mapper;

class Message extends Item{
	
	protected $_name = "core_messages";
	
	/**
	* return set of messages that should be send right now (meaning delay is expired)
	* @return Zend\Db\Resultset\Resultset of Core\Model\Message objects
	*/
	public function getExpired(){
		$select = $this->_gateway->getSql()->select();
		$select->where( 'is_active = 1' )
			   ->where( 'created_at + INTERVAL delay MINUTE < NOW()' );
		//print( $select->getSqlString() );exit();
		return $this->_gateway->selectWith( $select );
	}
}