<?php

namespace Core\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
* Generic class that maps item to DB laying in the fundament of ORM system
* being inherited by most other mappers.
* Class can be only abstract because ready to use inherited class should have defined _resultset_class and _name fields
*/
abstract class Item implements FactoryInterface{

	/**
	* Database table gateway
	* @var \Zend\Db\TableGateway\TableGateway
	*/
	protected $_gateway;
	
	/**
	* something like global app key from which one we're going to retrieve anything we need
	* @var \Zend\ServiceManager\ServiceLocatorInterface
	*/
	protected $_services;
	
	/**
	* name of table in database
	* @var string
	*/
	protected $_name = '';
	
	/**
	* return db table name
	* @return string
	*/
	public function getName(){
		return $this->_name;
	}
	
	/**
	* bring adapter to outside for any purposes (paginator for example)
	* @return Zend\Db\Adapter
	*/
	public function getAdapter(){
		return $this->_gateway->getAdapter();
	}
	
	/**
	* because of FactoryInterface we need to implement method that return new object
	* @param $params – array of created object options to assign (optional)
	* @return instance of \Core\Model\Item (or child) object
	*/
	public function create( array $params = null ){
		if( !$this->_services )
			throw new \Exception( "Don't going to create new object without SevriceLocator; Use new instead" );

		$class_name = str_replace( "Mapper", "Model", get_class( $this ) );
		$object = new $class_name( $params );
		$object->setServiceLocator( $this->_services );
		return $object;
	}

	/**
	* Create service
	* @param ServiceLocatorInterface $serviceLocator
	* @return mixed
	*/
	public function createService( ServiceLocatorInterface $services ){
		$this->_services = $services;

		$resultset = new ResultSet();
		$resultset->setArrayObjectPrototype( $this->create() );
		$this->_gateway = new TableGateway( $this->_name, $this->_services->get( 'db' ), null, $resultset );

		return $this;
	}
	
	/**
	* retrieve from DB related row by specified conditions
	* and build by result an object of _resultset_class instance (keep in mind inheritance)
	* @param $conditions - associative array of mysql conditions to retrieve necessary row
	*					   or integer value of primary key (in current app we apologize only integers for primary keys)
	* @return instance of any child of Core\Model\Item (since Core\Model\Item itself is abstract)
	*/
	public function build( $conditions ){
		if( is_integer( $conditions ) )
			return $this->find( $conditions );
		else
			return $this->fetchOne( $conditions );
	}

	/**
	* return represented table name as string of self (required in join)
	* @return string
	*/
	public function __toString(){
		return $this->getName();
	}
	
	/**
	* fetch from db rows by specified conditions
	* @return Zend\Db\ResultSet\ResultSet object or null if nothing found
	*/
	public function fetch( $conditions ){

		/* debug
		$sql = $this->_gateway->getSql(); 
        $select = $sql->select()->where( $conditions ); 
		print( $sql->getSqlStringForSqlObject( $select ) );exit(); */
		return $this->_gateway->select( $conditions );
	}

	/**
	* fetch only one record from DB by specified conditions
	* @param conditions – (optional) array of conditions or string
	* @return Core\Model\Item child object (since Core\Model\Item itself is an abstract)
	*/
	public function fetchOne( $conditions ){
		$result = $this->_gateway->select( $conditions );
		$quantity = count( $result );
		if( 1 == count( $quantity ) )
			return $result->current();
		elseif( 1 < count( $quantity ) )
			return $result[ 0 ];
		else
			return null;
	}

	/**
	* equal to fetch one but looking in DB for only specified primary key (apologize this is `id` field – integer)
	* @param pk_value - (integer) primary key value
	* @return Core\Model\Item child object (since Core\Model\Item itself is an abstract)
	*/
	public function find( $pk_value ){
		return $this->fetchOne( array( "id" => ( int ) $pk_value ) );
	}

	/**
	* count number of rows satisfying to provided conditions
	* @param $params – array of conditions to count
	* @return integer
	*/
	public function count( $params ){
		$gateway = new TableGateway( $this->_name, $this->_services->get( 'db' ) );
		$select = $gateway->getSql()->select()
					->where( $params )
					->columns( array( 'quantity' => new \Zend\Db\Sql\Expression( 'COUNT(*)' ) ) );

		$result = $gateway->selectWith( $select )->current();
//print( $this->_gateway->getSql()->getSqlStringForSqlObject( $select ) ); var_dump( $result[ 'quantity' ] ); exit();
		return ( int ) $result[ 'quantity' ];
	}

	/**
	* save provided item
	* @param item – instance of Core\Model\Item or child
	* @return id of affected row ( integer )
	*/
	public function save( \Core\Model\Item $item ){
		$data = $item->getOptions();
		if( false == ( $id = ( int ) $item->getId() ) ){
			$this->_gateway->insert( $data );
			return $this->_gateway->lastInsertValue;
		}

		unset( $data[ 'id' ] );
		$this->_gateway->update( $data, array( 'id' => $id ) );

		return $id;
	}
	
	/**
	* delete item by provided id
	* @param conditions - integer primary key value (assume pk column is `id`) or array of conditions
	* @return void
	*/
	public function delete( $conditions ){
		if( is_integer( $conditions ) )
			$conditions = array( 'id' => $conditions );
		elseif( !is_array( $conditions ) )
			throw new \Exception( "Array or integer expected in conditions to delete row (or rowset)" );
		$this->_gateway->delete( $conditions );
	}

}