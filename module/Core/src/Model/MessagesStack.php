<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */


namespace Core\Model;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
* stack of email messages needed to make a little delay before message was put in stack and have been sent
* this delay gives us two advantages:
* 1. we can join two messages related to one issue (several items in order changed status for example)
* 2. we can remove message from stack in case of undo was performed
* 3. we pass a delay which we have in case of immediate delivery (mail functions are slow)
* 4. we have a log of all ever sent messages
*/
class MessagesStack implements FactoryInterface{

	/**
	* link to ServiceLocator (something like a global app key, through which one we're going to extract anything we need)
	* @var
	*/
	protected $_services;

	/**
	* Create service
	* @param ServiceLocatorInterface $serviceLocator
	* @return mixed
	*/
	public function createService( ServiceLocatorInterface $services ){
		$this->_services = $services;
		return $this;
	}

	/**
	* create new message and push it to stack
	* @param recipient instance of \User\Model\User object (recipient of message)
	* @param subject instance of \Core\Model\Entity object
	* @param data - array of message data is ( 'subject' => , 'header' => , 'body' => , 'footer' => ,'is_html' => ) for email
	* @param delay => delay after which one message should be send ( in minutes )
	* @return void
	*/
	public function push( \User\Model\User $recipient, Entity $subject, array $data, $delay = Message::DEFAULT_DELAY ){
		if( false == @$data[ 'body' ] )
			throw new \Exception( "Message data should be array and have at least 'body' value" );

		/* if there is any not sent message in stack for the same recipient, same subject –
		   join it with new one */
		if( true == ( $message = $this->getActive( $recipient, $subject ) ) ){
			$exist = $message->getName();
			$data[ 'body' ] =  $exist[ 'body' ] . "\n" . $data[ 'body' ];
			$message->delete();
		}

		$message = $this->_services->get( 'message_mapper' )
						->create( array( 'owner_id' => $recipient->getId(),
										 'subject_id' => $subject->getId(),
										 'subject_class' => get_class( $subject ),
										 'name' => $data,
										 'delay' => $delay ) );
		$message->save();
	}

	/**
	* retrieve from stack active message by specified subject (freshness is defined by self::delay value) and type
	* @param user – instance of \User\Model\User object (recipient of message)
	* @param subject – instance of \Core\Model\Entity which message subject we are looking for
	* @return \Core\Model\Message object or null if nothing found
	*/
	public function getActive( \User\Model\User $user, Entity $subject ){
		return $this->_services->get( 'message_mapper' )
						->fetchOne( array( 'owner_id' => $user->getId(),
										'subject_id' => $subject->getId(),
										'subject_class' => get_class( $subject ) ) );
	}

	/**
	* retrieve from stack messages with expired delay and send them
	* @return void
	*/
	public function flush(){
		if( !count( $messages = $this->_services->get( 'message_mapper' )->getExpired() ) )
			return;

		foreach( $messages as $message )
			$message->send();
	}
}
