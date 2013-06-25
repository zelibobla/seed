<?php

namespace User\Model;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

class User
	extends \Core\Model\Entity{

	protected $name = 'noname';
	protected $phone;
	protected $email;
	protected $password_hash;
	protected $password_salt;
	protected $role = 'guest';
	protected $active_at;
	protected $settings;
	protected $photo;
	protected $is_active = 1;
	protected $is_email_approved = 0;
	
	/**
	* shorthand to check ACL record
	* @param resource – (string) – key of resource to check for ACL record
	* @param action – (string) – key of action to check for ACL record
	* @return boolean
	*/
	public function isAllowed( $resource, $action ){
		return $this->getServiceLocator()->get( 'acl' )->isAllowed( $this->getRole(), $resource, $action );
	}

	/**
	* shorthand to push notification for user
	* @param message – string message to push
	* @param subject – (string, optional, null by default) string with subject of message
	* @param class – (string, optional, 'error' by default) class of message (to find it later and unpin if needed)
	* @param is_pinned – (boolean, optional, false by default) if true – message will be shown any time until it unpinned manually
	* @return void
	*/
	public function notify( $message, $subject = null, $class = 'error', $is_pinned = false ){
		$note = $this->getServiceLocator()->get( 'note_mapper' )->create(
					array( 'body' => $message,
						   'subject' => $subject,
						   'class' => $class,
						   'is_pinned' => $is_pinned,
						   'owner_id' => $this->getId() ) );
		$note->save();
		return $this;
	}
	
	/**
	* shorthand to unpin note for current user
	* @param $subject – string subject of note
	* @return this
	*/
	public function unpinNote( $subject ){
		if( false == $this->getId() )
			throw new \Exception( "Can't unpin message from user with undefined id" );
		if( true == ( $note = $this->getServiceLocator()->get( 'note_mapper' )->build(
									array( 'owner_id' => $this->getId(),
										   'is_pinned' => true,
		 								   'subject' => $subject ) ) ) ){
			$note->setIsPinned( false )
				 ->setIsActive( false )
				 ->save();
		}
		return $this;
	}
	
	/**
	* return public link to avatar image
	* @return string
	*/
	public function getAvatar(){
		if( !$this->getId() ||
			!$this->getPhoto() )
			return;
		$config = $this->getServiceLocator()->get( 'config' );
		$path_from_siteroot = $config[ 'upload_policies' ][ 'dir' ] . "/" . $this->getId() . "/" . $this->getPhoto();
		$path_from_system_root = getcwd() . "/public/" . $path_from_siteroot;
		if( is_file( $path_from_system_root ) )
			return $path_from_siteroot;
	}

	/**
	* fill new user fields by specified params and auth him if needed
	* @param params - associative array of params with keys named by fields of current class
	* @param auth - (optional, false by default) should to authenticate him or not
	* @return instance on new User\Model\User object with set params (id is already assigned too)
	*/
	public function actDefaults( array $params = null, $auth = false ){
		$password = self::generateRandomString( 6, $with_capitals = false );
		$email = self::generateRandomString( 9 );

		$this->setOptions( $params )
			 ->generateRandomSalt()
			 ->setPasswordHash( $this->encrypt( $password ) )
			 ->setEmail( $email )
			 ->setCreatedAt( date( "Y-m-d H:i:s" ) )
			 ->setActiveAt( date( "Y-m-d H:i:s" ) );
		$this->setId( $this->save() );

		/* now authenticate user if required */
		if( true == $auth ){
			$auth_adapter = new AuthAdapter(
						$this->_services->get( 'db' ),
						'user_users',
						'email',
						'password_hash' );
			$auth_adapter->setIdentity( $this->getEmail() )
						 ->setCredential( $this->encrypt( $password ) );
			if( false == $auth_adapter->authenticate()->isValid() )
				throw new \Exception( 'Unable to create and authenticate user' );
			$this->writeToSession();
		}
		return $this;
	}
	
	/**
	* write self identity (id) to storage
	* @return this
	*/
	public function writeToSession(){
		if( false == $this->getId() )
			throw new \Exception( "Unable to write into session user with undefined id" );
		$auth = new AuthenticationService();
		$auth->getStorage()->write( $this->getId() );
		return $this;
	}
	
	/**
	* remove self identity (id) from storage
	* @return this
	*/
	public function removeFromSession(){
		if( false == $this->getId() ) return;
		$auth = new AuthenticationService();
		$auth->getStorage()->clear();
		return $this;
	}
	
	/**
	* reset user password
	* @return self;
	*/
	public function resetPassword(){
		$password = self::generateRandomString( 6, $with_capitals = false );
		$this->generateRandomSalt()
			 ->generatePasswordHash( $password )
			 ->save();

		return $this;
	}

	/**
	* return if self is editable for specified user
	* @return boolean
	*/
	public function isEditableFor( User $user ){
		return $user->getId() == $this->getId() ||
			   $this->isAllowed( 'user', 'edit' );
	}

	/**
	* generate random string of four symbols and put it into self::password_salt field
	* @return $this
	*/
	public function generateRandomSalt(){
		$this->setPasswordSalt( self::generateRandomString( 4 ) );
		return $this;
	}
	
	/**
	* generate random string of 32 symbols and put it into self::email_approval_hash field
	* @return this
	*/
	public function generateEmailApprovalHash(){
		$this->setEmailApprovalHash( self::generateRandomString( 32 ) );
		return $this;
	}

	/**
	* generate encrypted password and put encryption result to self::password_hash field
	* !warning: you can use blank password to your own responsibility
	* @param $password - string of password
	* @return $this
	*/
	public function encrypt( $password ){
		return hash( 'sha512', 'we are the champignons' . $password . $this->getPasswordSalt() );
	}
	
	/**
	* return icon filename counting from webroot folder
	* @return string
	*/
	public function getIconWebPath(){
		return "/uploads/user/{$this->getId()}/{$this->getPhoto()}";
	}

	/**
	* return path to files storage
	* @return string
	*/
	public static function getStoragePath(){
		return APPLICATION_PATH . "/../public/uploads/user";
	}

	/**
	* 
	*
	*/
	public function setInputFilter( InputFilterInterface $inputFilter ) {
		throw new \Exception( "Not used" );
	}

	/**
	* return input filter to validate any outside data
	* @return object with InputFilterInterface
	*/
	public function getInputFilter(){
		if( !$this->_input_filter ){
			$input_filter = new \Zend\InputFilter\InputFilter();
			$factory     = new \Zend\InputFilter\Factory();
			$input_filter->add( $factory->createInput(
				array(
					'name'     => 'email',
					'required' => true,
					'filters'  => array(
						array( 'name' => 'StripTags' ),
						array( 'name' => 'StringTrim' ),
					),
					'validators' => array(
						array( 'name' => 'NotEmpty',
							   'options' => array(
									'messages' => array(
										\Zend\Validator\NotEmpty::IS_EMPTY => $this->_( 'Field can not be empty' )
									 )
								)
							),
						array(
							'name'    => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min'      => 5,
								'max'      => 100,
								'messages' => array(
									\Zend\Validator\StringLength::TOO_SHORT => $this->_( 'String is less than %min%' ),
									\Zend\Validator\StringLength::TOO_LONG => $this->_( 'String is longer than %max%' ),
								)
							),
						),
					),
				) ) );
			$input_filter->add( $factory->createInput(
				array(
					'name'		=> 'password',
					'required'	=> true,
					'validators' => array(
						array( 'name' => 'NotEmpty',
							   'options' => array(
									'messages' => array(
										\Zend\Validator\NotEmpty::IS_EMPTY => $this->_( 'Field can not be empty' )
									 )
								)
							),
						array(
							'name'	=> 'StringLength',
							'options' => array(
								'min'	=> 5,
								'max'	=> 255,
								'messages' => array(
									\Zend\Validator\StringLength::TOO_SHORT => $this->_( 'String is less than %min%' ),
									\Zend\Validator\StringLength::TOO_LONG => $this->_( 'String is longer than %max%' ),
								)
							),
						)
					)
				)
			) );
			$this->_input_filter = $input_filter;
		}
		return $this->_input_filter;
	}
	
	/**
	* return input filter to validate outside signup data
	* @return \Zend\InputFilter\InputFilter object
	*/
	public function getSignupInputFilter(){
		$factory = new \Zend\InputFilter\Factory();
		$input_filter = $this->getInputFilter();
		$input_filter->add( $factory->createInput(
			array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array( 'name' => 'StripTags' ),
					array( 'name' => 'StringTrim' )
				),
				'validators' => array(
					array( 'name' => 'NotEmpty',
					 	   'options' => array(
					 	   		'messages' => array(
					 	   			\Zend\Validator\NotEmpty::IS_EMPTY => $this->_( 'Field can not be empty' )
					 	   		)
					 	   ),
				 	),
					array(
						'name'    => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 2,
							'max'      => 32,
							'messages' => array(
								\Zend\Validator\StringLength::TOO_SHORT => $this->_( 'String is less than %min%' ),
								\Zend\Validator\StringLength::TOO_LONG => $this->_( 'String is longer than %max%' ),
							)
						),
					)
				) )
		) );
		$input_filter->add( $factory->createInput(
			array(
				'name'		=> 'password_repeat',
				'validators' => array(
					array( 'name' => 'NotEmpty',
					 	   'options' => array(
					 	   		'messages' => array(
					 	   			\Zend\Validator\NotEmpty::IS_EMPTY => $this->_( 'Field can not be empty' )
					 	   		)
					 	   ),
				 	),
					array( 'name' => 'Identical',
						   'options' => array(
								'token' => 'password',
								'messages' => array(
									\Zend\Validator\Identical::NOT_SAME => $this->_( 'Password is not same' )
								 )
							)
						)
					)
				)
		) );
		return $input_filter;
	}
	
	/**
	* put mail to approve user`s email address in messages stack
	* @return this
	*/
	public function pushApprovalEmail(){
		$this->setIsEmailApproved( false )
			 ->generateEmailApprovalHash();
		$translator = $this->getServiceLocator()->get( 'translator' );
		$settings = $this->getServiceLocator()->get( 'config' );
		if( false == ( $base = $settings[ 'settings' ][ 'public_site' ] ) )
			throw new \Exception( "[ 'settings' ][ 'public_site' ] should be defined to generate email approval link" );
		$link = $base . "/account/approve?hash={$this->getEmailApprovalHash()}";
		$text = sprintf( $translator->translate( 'Approve your email please by clicking this link %s', '' ), $link );
		$this->getServiceLocator()->get( 'postal' )->push(
			$this,
			$this,
			array( 'subject' => $translator->translate( 'Email approval' ), 'body' => $text ),
			1 );
		return $this;
	}
	
	/**
	* return input filter to validate outside profile data
	* @return \Zend\InputFilter\InputFilter object
	*/
	public function getProfileInputFilter(){
		$factory = new \Zend\InputFilter\Factory();
		$input_filter = $this->getSignupInputFilter();
		
		$input_filter->get( 'password' )->setRequired( false );
		$input_filter->get( 'password_repeat' )->setRequired( false );
		
		$input_filter->add( $factory->createInput(
			array(
				'name' => 'phone',
				'required' => $required,
				'filters' => array(
					array( 'name' => 'StripTags' ),
					array( 'name' => 'StringTrim' )
				),
				'validators' => array(
					array(
						'name'    => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 2,
							'max'      => 32,
							'messages' => array(
								\Zend\Validator\StringLength::TOO_SHORT => $this->_( 'String is less than %min%' ),
								\Zend\Validator\StringLength::TOO_LONG => $this->_( 'String is longer than %max%' ),
							)
						),
					)
				) )
		) );
		return $input_filter;
	}

	/**
	* for several purposes readable random string of various length needed
	* @param length – number of symbols in a string
	* @return string
	*/
	private static function generateRandomString( $length ){
		$pool = "abcdefghijkmonpqrstuvwxyz123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
		$len = strlen( $pool );
		$res = "";
		for( $i = 0; $i < $length; $i++ )
			$res .= substr( $pool, rand( 0, $len ), 1 );
		return $res;
	}
}