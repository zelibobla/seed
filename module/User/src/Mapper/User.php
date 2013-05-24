<?php

namespace User\Mapper;

class User extends \Core\Mapper\Item{

	protected $_name = 'user_users';

	/**
	* check if user with specified id can get specified email
	* @param $user_id – integer
	* @param $email – string
	* @return boolean
	*/
	public function canUserGetEmail( $user_id, $email ){
		$select = $this->_gateway->getSql()->select();
		$select->where( "id != " . ( int ) $user_id )
			   ->where( "email = '$email'" );
		$rowset = $this->_gateway->selectWith( $select );

		return 0 == count( $rowset );
	}
}