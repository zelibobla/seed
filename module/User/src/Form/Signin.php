<?

namespace User\Form;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

class Signin extends Form{
	
	public function __construct() {

		parent::__construct( 'signup' );
		$this->setAttribute( 'method', 'post' );
		$this->setAttribute( 'id', 'signin' );
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
		
		$this->setAttribute( 'header', 'Signin noun' );
		$this->setAttribute( 'submit', 'Signin' );
	}
}
