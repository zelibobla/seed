<?

namespace User\Form;
use Zend\Form\Form;

class Signup extends Form{

	public function __construct() {

		parent::__construct( 'signup' );
		$this->setAttribute( 'method', 'post' );
		$this->add( array(
			'name' => 'name',
			'attributes' => array(
				'type'  => 'text',
				'id'	=> 'name'
			),
			'options' => array(
				'label' => 'name',
			)
		) );
		$this->add( array(
			'name' => 'email',
			'attributes' => array(
				'type'  => 'text',
				'id'	=> 'email'
			),
			'options' => array(
				'label' => 'email'
			)
		) );
		$this->add( array(
			'name' => 'password',
			'attributes' => array(
				'type'  => 'password',
				'id'	=> 'password'
			),
			'options' => array(
				'label' => 'password'
			)
		) );
		$this->add( array(
			'name' => 'password_repeat',
			'attributes' => array(
				'type'  => 'password',
				'id'	=> 'password_repeat',
				'class'	=> 'small'
			),
			'options' => array(
				'label' => 'password repeat'
			)
		) );
		
		$this->setAttribute( 'id', 'signup' );
		$this->setAttribute( 'header', 'Signup noun' );
		$this->setAttribute( 'submit', 'Ready' );
	}
}
