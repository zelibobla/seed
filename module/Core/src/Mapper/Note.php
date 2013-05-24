<?php

namespace Core\Mapper;

class Note extends Item{
	
	protected $_name = "core_notes";

	/**
	* fetch notifications by specified params
	* @param owner_id – required integer; subject – optional string;
	* @return Zend\Db\ResultSet\ResultSet of Core\Note objects or null if nothing found
	*/
	public function fetchVisibleByOwnerId( $owner_id ){
		$select = $this->_gateway->getSql()->select()->where( "owner_id = " . ( int ) $owner_id )
								   					 ->where( "( is_active = 1 OR is_pinned = 1 )" )
								   					 ->order( "created_at DESC" )
								   					 ->limit( 5 );
		return $this->_gateway->selectWith( $select );
	}
}