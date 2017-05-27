<?php

	// Fat Finger Form - structure of Fat Finger Form contained in request.


	// FatFingerForm
	class FatFingerForm
	{
		// globals
		protected $sections 	= array();			
		protected $aiLogic 		= "";			
		protected $formName	 	= "";	
		

		
		
		//-- SETTERS
		
		// set form name
		public function setFormName( $formName )
		{
			$this->formName = $formName;
		}
		
		
		// set ai logic
		public function setAiLogic( $aiLogic )
		{
			$this->aiLogic = $aiLogic;
		}
		
		
		// add section
		public function addSection( $section )
		{
			array_push( $this->sections, $section );
		}
		

		
		
		//-- OPERATIONS		
		
		// serialize
		public function serialize()
		{
			$sections2 = array();
			foreach( $this->sections as $section )
				array_push( $sections2, $section->serialize() );
			
			$data = array(
							"sections"	=> $sections2,
							"aiLogic"	=> $this->aiLogic,							
							"formName"	=> $this->formName						
						);
			
			return $data;
		}
		
	};


?>