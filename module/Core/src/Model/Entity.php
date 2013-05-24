<?php

namespace Core\Model;

/**
* generic core class being direct parent of most rich entities of the application
* like a user for example
*/
class Entity
	extends Item {

	protected $id;
	protected $created_at;
	protected $updated_at;
	protected $owner_id;
	protected $is_active = true;
	
	protected $_owner;

	/**
	* set default created_at and updated_at params if not defined in provided options
	* @param $params – class options to assign after created
	* @return void
	*/
	public function __construct( array $params = null ){
		$date = date( "Y-m-d H:i:s", time() );
		if( false == @$params[ 'created_at' ] )
			$this->setCreatedAt( $date );
		if( false == @$params[ 'updated_at' ] )
			$this->setUpdatedAt( $date );
		parent::__construct( $params );
	}
	
	/**
	* get owner of current entity 
	* @return \User\Model\User or null
	*/
	public function getOwner(){
		if( false == $this->getOwnerId() ) return null;
		if( !$this->_owner )
			$this->_owner = $this->getServiceLocator()->get( 'user_mapper' )->build( $this->getOwnerId() );
		return $this->_owner;
	}
	
}