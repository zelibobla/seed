<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Model;

/**
* class representing a message being send to user via email or sms
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

		$mail = new \Zend\Mail\Message();
		$mail->setEncoding( 'UTF-8' )
			 ->addReplyTo( $config[ 'settings' ][ 'admin_email' ] )
			 ->setFrom( $config[ 'settings' ][ 'admin_email' ], sprintf( $t->translate( 'Robot of site Default.ru', '' ) ) )
			 ->setSender( $config[ 'settings' ][ 'admin_email' ], sprintf( $t->translate( 'Robot of site Default.ru', '' ) ) )
			 ->addTo( $owner->getEmail(), $owner->__toString() )
			 ->setSubject( @$data[ 'subject' ] );
		$mail->setBody( sprintf( $t->translate( "Dear %s!\n\n %s\n\nBest regards!\Default.ru robot", '' ),
								 $owner->__toString(),
								 @$data[ 'header' ] . $data[ 'body' ] . @$data[ 'footer' ] ) );
		if( isset( $data[ 'attachments' ] ) &&
			!empty( $data[ 'attachments' ] ) )
			foreach( $data[ 'attachments' ] as $fullname )
				if( true == ( $filecontents = file_get_contents( $fullname ) ) ){
					$file = $mail->createAttachment( $filecontents );
					$file->filename = $shot;
				}

		$transport = new \Zend\Mail\Transport\Sendmail();
		$transport->send( $mail );
		$this->setIsActive( false )
			 ->setSentAt( date( "Y-m-d H:i:s" ) )
			 ->save();
	}
}