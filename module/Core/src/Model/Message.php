<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Model;

/**
* class representing a message being send to user via email
*/
class Message extends Entity{

	protected $sent_at;
	protected $delay = 5;
	protected $subject_id;
	protected $subject_class;
	protected $failed_attempts = 0;
	
	const MAX_FAILED_ATTEMPTS = 5;
	
	const DEFAULT_DELAY = 5; //minutes
	
	/**
	* send the message
	* @return true in case of success of false otherwise
	*/
	public function send(){
		if( false == ( $owner = $this->getOwner() ) ) return false;
		$data = $this->getName();
		$config = $this->getServiceLocator()->get( 'config' );
		$t = $this->getServiceLocator()->get( 'translator' );
		
		$site_name = $config[ 'public_site' ];

		$mail = new \Zend\Mail\Message();
		$mail->setEncoding( 'UTF-8' )
			 ->setHeaderEncoding( Zend_Mime::ENCODING_BASE64 )
			 ->setFrom( $config[ 'settings' ][ 'admin_email' ], sprintf( $t->_( "Robot of site $site_name" ) ) )
			 ->addTo( $owner->getEmail(), $owner->__toString() )
			 ->setSubject( $data[ 'subject' ] );
			if( true == @$data[ 'is_html' ] )
				$mail->setBodyHtml( @$data[ 'header' ] . $data[ 'body' ] . @$data[ 'footer' ] );
			else
				$mail->setBodyText( sprintf( $t->_( "Dear %s!\n\n %s\n\nBest regards!\n$site_name robot" ),
											 $owner->__toString(),
											 @$data[ 'header' ] . $data[ 'body' ] . @$data[ 'footer' ] ) );
		if( isset( $data[ 'attachments' ] ) &&
			!empty( $data[ 'attachments' ] ) )
			foreach( $data[ 'attachments' ] as $fullname )
				if( true == ( $filecontents = file_get_contents( $fullname ) ) ){
					$file = $mail->createAttachment( $filecontents );
					$file->filename = $shot;
				}
		$mail->send();
		$this->setIsActive( false )
			 ->sentAt( date( "Y-m-d H:i:s" ) )
			 ->save();
	}
}