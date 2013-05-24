<?php

namespace Core\Controller;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController{

	public function indexAction(){
		return $this->view();
    }
}
