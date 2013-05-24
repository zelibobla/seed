<?

namespace User\Form;
use Zend\Form\Form;

class Profile extends Form{

	public function __construct() {

		parent::__construct( 'profile' );
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
			'name' => 'is_photographer',
			'attributes' => array(
				'type'  => 'checkbox',
				'id'	=> 'is_photographer',
				'value' => '1'
			),
			'options' => array(
				'label' => 'I am a photographer'
			)
		) );
		$this->add( array(
			'name' => 'city_id',
			'attributes' => array(
				'type'  => 'hidden',
				'id'	=> 'city_id',
			)
		) );
		$this->add( array(
			'name' => 'city',
			'attributes' => array(
				'type'  => 'text',
				'id'	=> 'city',
				'class' => 'dependant'
			),
			'options' => array(
				'label' => 'City',
			)
		) );
		$this->add( array(
			'name' => 'portfolio_link',
			'attributes' => array(
				'type'  => 'text',
				'id'	=> 'portfolio_link',
				'class' => 'dependant'
			),
			'options' => array(
				'label' => 'Portfolio url',
			)
		) );
		$this->add( array(
			'name' => 'phone',
			'attributes' => array(
				'type'  => 'text',
				'id'	=> 'phone',
				'class' => 'dependant'
			),
			'options' => array(
				'label' => 'Phone',
			)
		) );
		$this->add( array(
			'name' => 'photo',
			'attributes' => array(
				'type'  => 'hidden',
				'id'	=> 'photo',
			)
		) );

		$this->setAttribute( 'id', 'profile' );
		$this->setAttribute( 'header', 'Personal data' );
		$this->setAttribute( 'submit', 'Save' );
	}
}
