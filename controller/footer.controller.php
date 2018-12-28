<?php

/**
* 
*/
class ControllerFooter extends MainController{
	
	function __construct($argument=null)
	{
		# code...
	}
	
	public function index(){
		
		$this->outputContent("footer");
	}

}