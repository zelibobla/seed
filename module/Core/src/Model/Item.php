<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
* fundamental system primitive class have only name property
* any other ORM items are children of current
* properties starting from underscore – are not mapped to DB (useful to keep some temporary data)
* others – do (awaiting the same fields names as properties)
* magic methods some_property => getSomeProperty, setSomeProperty are performed (but be careful due to validation reasons)
*/
abstract class Item
	implements ServiceLocatorAwareInterface{

	/**
	* link to ServiceLocator (something like a global app key, through which one we're going to extract anything we need)
	* @var
	*/
	protected $_services;

	/**
	* guess any item in application should have name at least
	* @var string
	*/
	protected $name;

	/**
	* Input filter for filtration and validation outside data purposes
	* @var \Zend\InputFilter\InputFilter
	*/
	protected $_input_filter;

	/**
	* Сlass can be instantiated with array of fields and it's values.
	* @param $params – ( optional ) array of key => value pairs
	* @return this
	*/
	public function __construct( $params = array() ){
		if( !isset( $params[ 'created_at' ] ) )
			$params[ 'created_at' ] = date( "Y-m-d H:i:s" );
		if( !isset( $params[ 'is_active' ] ) )
			$params[ 'is_active' ] = true;
		return $this->setOptions( $params );
	}

	/**
	* these getter and setter are created due to a ServiceLocatorAwareInterface conditions
	* setter is run first time class is being accessed, thus in future we can use _services to access anything in app
	*/
    public function setServiceLocator( ServiceLocatorInterface $services ){
		$this->_services = $services;
	}
        
    public function getServiceLocator(){
		return $this->_services;
	}

	/**
	* shorthand for translator
	* @param $string – (string) string you want to translate
	* @return translation of specified string or string itself if no translation found
	*/
	protected function _( $string ){
		return $this->getServiceLocator()->get( 'translator' )->translate( $string, '' );
	}

	/**
	* due to Zend\Db\Adapter\AdapterAwareInterface make an alias of setOptions
	* @return this
	*/
	public function exchangeArray( $data ){
		return $this->setOptions( $data );
	}

	/**
	* since mapper name and current class name has relation lets automatize mapper retrieval from services
	* @return FactoryInterface object (mapper actually)
	*/
	public function getMapper(){
		$class_name = get_class( $this );
		$entity_name = lcfirst( substr( $class_name, strrpos( $class_name, "\\" ) + 1 ) );
		return $this->_services->get( "{$entity_name}_mapper" );
	}

	/**
	* save current object to database
    * @return mixed The primary key value(s), as an associative array if the
    *		  key is compound, or a scalar if the key is single-column.
	*/
	public function save(){
		return $this->getMapper()->save( $this );
	}

	/**
	* delete self from database
    * @return void
	*/
	public function delete(){
		if( false == $this->getId() ) return;
		return $this->getMapper()->delete( ( int ) $this->getId() );
	}

    /**
    * Setters and getters routine decreasing
    *
    * @param string $method
    * @return mixed
    */
    public function __call( $method, array $args ){

        $matches = array();

        /* recognize setter */
        if( preg_match( '/^set/', $method ) &&
			preg_match_all( '/([A-Z][a-z]*)/', $method, $matches ) ) {
            $property = strtolower( implode( '_', $matches[ 1 ] ) );
			$this->$property = $args[ 0 ];
            return $this;
        }

        /* recognize getter */
        if( preg_match( '/^get/', $method ) &&
			preg_match_all( '/([A-Z][a-z]*)/', $method, $matches ) ) {
            $property = strtolower( implode( '_', $matches[ 1 ] ) );
            return $this->$property;
        }

		if( method_exists( $this, $method ) )
			$this->$method( $args );
		else
			throw new \Exception( "Object of class" . get_class( $this ) . " has no method '$method'" );

    }

	/**
	* return self name
	* @return string representation of self
	*/
	public function __toString(){
		return ( string ) $this->getName();
	}

	/**
	* set self fields from input array
	* @param $options - option => value array
	* @return $this
	*/
	public function setOptions( array $options = null ) {
		if( empty( $options ) ) return $this;

		$methods = get_class_methods( $this );
		foreach( $options as $key => $value ) {
			if( !property_exists( get_called_class(), $key ) ) continue;
			if( is_string( $value ) ){
				$attempt = @unserialize( $value );
				if( is_array( $attempt ) ||
					 is_object( $attempt ) ){
					$value = $attempt;	 
				}
			}

			$key_parts = explode( "_", $key );
			foreach( $key_parts as $index => $val ){
				$key_parts[ $index ] = ucfirst( $val );
			}
			$method = 'set' . implode( $key_parts );
			$this->$method( $value );
		}
		return $this;
	}

	/**
	* return self fields values in associative array (useful for DB mapping)
	* @return array of self fields
	*/
	public function getOptions(){
		$vars = get_object_vars( $this );
		$result = array();
		foreach( $vars as $key => $value ) {
			if( "_" === substr( $key, 0, 1 ) ) continue;
			$key_parts = explode( "_", $key );
			foreach( $key_parts as $index => $val ){
				$key_parts[ $index ] = ucfirst( $val );
			}
			$method = 'get' . implode( $key_parts );
			
			$value = $this->$method();
			if( is_array( $value ) ||
				 is_object( $value ) ){
				 $value = serialize( $value );
			}
			$result[ $key ] = $value;
		}
		return $result;
	}
	
	/**
	* temporary properties (beginning from underscore as you remember) shouldn't be serialized
	* @return array of non-temporary properties
	*/
	public function __sleep(){
		return array_keys( $this->getOptions() );
	}
	
}