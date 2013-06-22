<?php

namespace User\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController{

	/**
	* bring user signin form
	* @return mixed json array( 'header' => ..., 'body' => ... and etc ) if request is GET or POST submitted form is invalid
	*				  or array( 'result' => true ) if submitted form is valid
	*/
	public function signinAction(){
		if( !$this->user()->isAllowed( 'account_self', 'login' ) ){
			$this->user()->notify( $this->_( 'You are already locgged into your account' ) );
			$this->getResponse()->setStatusCode( 400 );
			return new JsonModel( array() );
		}
		$form = new \User\Form\Signin();
		$form->setInputFilter( $this->user()->getInputFilter() );
		/* no post data? return form to submit */
		if( false == ( $data = $this->params()->fromPost() ) )
			return $this->view()->jsonForm( $form );

		/* validate post data */
		$form->setData( $data );
		if( !$form->isValid() ){
			$this->user()->notify( $this->_( 'Invalid data provided' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} elseif( false == ( $user = $this->serviceLocator->get( 'user_mapper' )
										  ->build( array( 'email' => $form->get( 'email' )->getValue() ) ) ) ){
			$this->user()->notify( $this->_( 'Specified email does not exist' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} elseif( $user->getPasswordHash() != $user->encrypt( $form->get( 'password' )->getValue() ) ){
			$this->user()->notify( $this->_( 'Password is wrong' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		}
		$user->writeToSession();
		$user->notify( sprintf( $this->_( 'Welcome %s' ), $user->getName() ) );
		return new JsonModel( array( 'reload' => true ) );
	}

	/**
	* bring user signup form
	* @return mixed json array( 'header' => ..., 'body' => ... and etc ) if request is GET or POST submitted form is invalid
	*				  or array( 'result' => true ) if submitted form is valid
	*/
	public function signupAction(){
		if( !$this->user()->isAllowed( 'account_self', 'create' ) ){
			$this->getResponse()->setStatusCode( 400 );
			$this->user()->notify( $this->_( 'You are already logged into your account' ) );
			return new JsonModel( array() );
		}
		$form = new \User\Form\Signup();
		$form->setInputFilter( $this->user()->getSignupInputFilter() );
		/* no post data? return form to submit */
		if( false == ( $data = $this->params()->fromPost() ) )
	        return $this->view()->jsonForm( $form );
	
		/* validate post data */
		$form->setData( $data );
		if( !$form->isValid() ){
			$this->user()->notify( $this->_( 'Invalid data provided' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} elseif( true == ( $user = $this->serviceLocator->get( 'user_mapper' )
		 								->build( array( 'email' => $form->get( 'email' )->getValue() ) ) ) ){
			$this->user()->notify( $this->_( 'Specified email is busy' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} else {
			$new_user = $this->serviceLocator->get( 'user_mapper' )->create(
				array( 'created_at' => date( "Y-m-d H:i:s" ),
					   'updated_at' => date( "Y-m-d H:i:s" ),
					   'name' => $form->get( 'name' )->getValue(),
				 	   'email' => $form->get( 'email' )->getValue(),
				 	   'role' => 'user' )
			);
			$id = $new_user->generateRandomSalt()
					 ->setPasswordHash( $new_user->encrypt( $form->get( 'password' )->getValue() ) )
					 ->save();
			$new_user->setId( $id )->writeToSession();
			$new_user->pushApprovalEmail()
					 ->save();
			$new_user->notify( sprintf( $this->_( 'Approve your email %s please' ), $new_user->getEmail() ), 'email_approval', null, true );
			return new JsonModel( array( 'reload' => true ) );
		}
	}
	
	/**
	* clear session from current user credentials
	* @return redirection directive to `home` route
	*/
	public function logoutAction(){
		$this->user()->removeFromSession();
		return $this->redirect()->toRoute( 'home' );
	}
	
	/**
	* show user account to edit (if it is owner) or to show
	* @return \Zend\View\Model\ViewModel object
	*/
	public function indexAction(){
		if( !$this->user()->isAllowed( 'account_self', 'edit' ) ){
			$this->user()->notify( $this->_( "You can't edit your profile. Signup first" ) );
			return $this->redirect()->toRoute( 'home' );
		}
		return $this->view( array() );
	}
	
	/**
	* handle email approval link clicking
	* @return \Zend\View\Model\ViewModel object
	*/
	public function approveAction(){
		if( false == ( $hash = $this->params()->fromQuery( 'hash' ) ) ||
		 	false == ( $user = $this->getServiceLocator()->get( 'user_mapper' )->build( array( 'email_approval_hash' => $hash ) ) ) ){
			$this->user()->notify( $this->_( 'Email approval failed' ) );
			return $this->redirect()->toRoute( 'home' );
		}
		$user->writeToSession();
		$user->notify( $this->_( 'Email approval success' ) )
			 ->unpinNote( 'email_approval' )
			 ->setIsEmailApproved( true )
			 ->setEmailApprovalHash( null )
			 ->save();
		return $this->redirect()->toRoute( 'account' );
	}

	/**
	* show user profile tab
	* @return \Zend\View\Model\ViewModel object
	*/
	public function profileAction(){
		if( !$this->user()->isAllowed( 'account_self', 'edit' ) ){
			$this->user()->notify( $this->_( "You can't edit your profile. Signup first" ) );
			return new JsonModel( array( 'redirect' => "/" ) );
		}
		$form = new \User\Form\Profile();
		$form->setInputFilter( $this->user()->getProfileInputFilter() );
		$options = $this->user()->getOptions();
		if( true == ( $city = $this->user()->getCity() ) )
			$options[ 'city' ] = $city->__toString();

		$form->setData( $options );
		/* no post data? return form */
		if( false == ( $data = $this->params()->fromPost() ) )
			return $this->view()->jsonForm( $form );
		if( 0 == ( int ) $data[ 'city_id' ] )
			$data[ 'city_id' ] = null;

		/* validate provided data */
		$form->setData( $data );
		if( !$form->isValid() ){
			$this->user()->notify( $this->_( 'Invalid data provided' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} elseif( !$this->serviceLocator->get( 'user_mapper' )->canUserGetEmail( $this->user()->getId(),
																				 $form->get( 'email' )->getValue() ) ) {
			$this->user()->notify( $this->_( 'Specified email is busy' ) );
			$this->getResponse()->setStatusCode( 406 );
			return $this->view()->jsonForm( $form );
		} else {
			$data = $form->getData();
			$link = false === strpos( $data[ 'portfolio_link' ], 'http://' ) &&
					false === strpos( $data[ 'portfolio_link' ], 'https://' )
				  ? 'http://' . $data[ 'portfolio_link' ]
				  : $data[ 'portfolio_link' ];
			$data[ 'portfolio_link' ] = filter_var( $link, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE );

			$config = $this->getServiceLocator()->get( 'config' );
			$config = $config[ 'upload_policies' ];
			$path = getcwd() . "/public/" . $config[ 'dir' ] . '/' . $this->user()->getId() . '/';
			if( false !== strpos( $data[ 'photo' ], 'temp' ) ){
				$temp_filename = $path . $data[ 'photo' ];
				$data[ 'photo' ] = str_replace( "temp", "avatar" , $data[ 'photo' ] );
				$real_filename = $path . $data[ 'photo' ];
				$res = rename( $temp_filename, $real_filename );
			} elseif( !isset( $data[ 'photo' ] ) ||
					  !$data[ 'photo' ] ){
				$mask = $path . 'temp.*';
				array_map( "unlink", glob( $mask ) );
				$mask = $path . 'avatar.*';
				array_map( "unlink", glob( $mask ) );
				$data[ 'photo' ] = '';
			}
			$this->user()->setOptions( $data )
						  ->setUpdatedAt( date( "Y-m-d H:i:s" ) )
						  ->save();
			$this->user()->notify( $this->_( 'Your profile successfully updated' ) );
			return new JsonModel( array( 'reload' => true ) );
		}
	}
	
	/**
	* ajax request handle user profile image upload
	* @return json
	*/
	public function imageAction(){ 
		$config = $this->getServiceLocator()->get( 'config' );
		$config = $config[ 'upload_policies' ];
		$folder = getcwd() . "/public/" . $config[ 'dir' ];
		if( !is_writable( $folder ) ){
			$this->user()->notify( $this->_( "Server error. Upload directory isn't writable." ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}
		
		$folder = $folder . '/' . $this->user()->getId();
		if( !is_dir( $folder ) &&
			!mkdir( $folder ) ){
			$this->user()->notify( $this->_( "Server error. Can not create directory for user" ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}

		if( false == ( $filename = $_FILES[ 'files' ][ 'tmp_name' ][ 0 ] ) ){
			$this->user()->notify( $this->_( 'No files were uploaded.' ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}

		if( 0 == ( $size = filesize( $filename ) ) ||
			$size > $config[ 'avatar' ][ 'max_file_size' ] ){
			$this->user()->notify( $this->_( 'File is empty or too large' ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}
        
		$type = $_FILES[ 'files' ][ 'type' ][ 0 ];
		if( !in_array( $type, array_keys( $config[ 'avatar' ][ 'extensions' ] ) ) ){
			$this->user()->notify( $this->_( "File has an invalid extension: $type" ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}
			
		$new_filename = $folder . '/temp.' . $config[ 'avatar' ][ 'extensions' ][ $type ];
		if( false == move_uploaded_file( $filename, $new_filename ) ){
			$this->user()->notify( $this->_( 'Could not save uploaded file.' ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}
		
		try{
			list( $width, $height ) = getimagesize( $new_filename );
			$height_diff = $config[ 'avatar' ][ 'height' ] - $height;
			if( 0 > ( $width_diff = $config[ 'avatar' ][ 'width' ] - $width ) ||
			 	0 > $height_diff ){
				$proportion = abs( $height_diff ) > abs( $width_diff )
							? $config[ 'avatar' ][ 'height' ] / $height
							: $config[ 'avatar' ][ 'width' ] / $width;
				$new_width = ( int ) ( $width * $proportion );
				$new_height = ( int ) ( $height * $proportion );

				switch( $type ){
					case "image/png" :$image = imagecreatefrompng( $new_filename ); break;
					case "image/gif" : $image = imagecreatefromgif( $new_filename ); break;
					case "image/jpeg" : $image = imagecreatefromjpeg( $new_filename ); break;
				}
				$resampled_image = imagecreatetruecolor( $new_width, $new_height );
				imagecopyresampled( $resampled_image, 	//dst_image
									$image,				//src_image
									0,					//dst_x
									0,					//dst_y
									0,			  		//src_x
									0,			  		//src_y
									$new_width,			//dst_w
									$new_height,		//dst_h
									$width,				//src_w
									$height );			//src_h
				switch( $type ){
					case "image/png" : imagepng( $resampled_image, $new_filename ); break;
					case "image/gif" : imagegif( $resampled_image, $new_filename ); break;
					case "image/jpeg" : imagejpeg( $resampled_image, $new_filename, 90 ); break;
				}
			}
		} catch( Exception $e ){
			$this->user()->notify( $this->_( 'Could resize uploaded file.' ) );
			$this->getResponse()->setStatusCode( 406 );
			return;
		}

		$public_filename = $config[ 'dir' ] . '/' . $this->user()->getId() . '/temp.' . $config[ 'avatar' ][ 'extensions' ][ $type ];
		return new JsonModel( array( 'full_filename' => $public_filename,
		 							 'filename' => 'temp.' . $config[ 'avatar' ][ 'extensions' ][ $type ] ) );
	}
}
