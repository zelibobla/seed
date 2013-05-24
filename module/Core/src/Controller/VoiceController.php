<?php

namespace Core\Controller;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class VoiceController extends AbstractRestfulController{

	/**
	* return json array of notes addressed to currently logged in user
	* @return array of view values
	*/
	public function getList(){
		$result = array();
		$user = $this->getServiceLocator()->get( 'user' );
		$messages = $this->getServiceLocator()->get( 'note_mapper' )->fetchVisibleByOwnerId( $user->getId() );
		if( count( $messages ) )
			foreach( $messages as $message )
				$result[] = array(  "type"	=> $message->getClass(),
									"body"	=> $message->getBody(),
									"subject" => $message->getSubject(),
									"id"		=> $message->getId() );
		return new JsonModel( $result );
    }

	/**
	* the only one reason to update item – is to mark note as inactive
	* @return empty array
	*/
	public function update( $id, $data ){
		if( true == ( $message = $this->getServiceLocator()->get( 'note_mapper' )->find( $id ) ) &&
		 	true == ( $user = $this->getServiceLocator()->get( 'user' ) ) &&
		 	$message->getOwnerId() == $user->getId() )
			$message->setIsActive( false )
					->save();
		return new JsonModel( array() );
	}

	/* all other actions are blank */
	public function get( $id ){}
	public function create( $data ){}
	public function delete( $id ){}
}
