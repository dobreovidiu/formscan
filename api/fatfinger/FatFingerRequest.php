<?php

	// Fat Finger Request - structure of Fat Finger Request.


	// FatFingerRequest
	class FatFingerRequest
	{
		// globals
		protected $form = false;
		

		
		
		//-- SETTERS	
		
		// set form
		public function setForm( $form )
		{
			$this->form = $form;
		}
		
		
		// add section
		public function addSection( $section )
		{
			if( !$this->form )
				return false;
			
			$this->form->addSection( $section );
		}
		

		
		
		//-- OPERATIONS		
		
		// serialize
		public function serialize()
		{
			if( is_bool( $this->form ) )
				return false;
			
			$data = $this->form->serialize();
			
			return $data;
		}
		
	};


?>