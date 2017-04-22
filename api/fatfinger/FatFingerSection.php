<?php

	// Fat Finger Section - structure of Fat Finger Section contained in forms.


	// FatFingerSection
	class FatFingerSection
	{
		// globals
		protected $fieldType	 		= "Section";	
		protected $validationType	 	= 0;	
		protected $isNextNumberActive 	= false;	
		protected $typeLogic 			= "Invisible";	
		protected $fieldSecurity 		= array();
		protected $id 					= "";	
		protected $order 				= 0;		
		protected $label	 			= "Invisible";	
		protected $fields 				= array();
		protected $locked		 		= true;		
		

		
		
		//-- SETTERS				
		
		
		// set field type
		public function setFieldType( $fieldType = "Section" )
		{
			$this->fieldType = $fieldType;
		}
		
		
		// set validation type
		public function setValidationType( $validationType = 0 )
		{
			$this->validationType = $validationType;
		}
		
		
		// set isNextNumberActive
		public function setIsNextNumberActive( $isNextNumberActive = false )
		{
			$this->isNextNumberActive = $isNextNumberActive;
		}
		
		
		// set type logic
		public function setTypeLogic( $typeLogic = "Invisible" )
		{
			$this->typeLogic = $typeLogic;
		}
		
		
		// set field security
		public function setFieldSecurity( $fieldSecurity = array() )
		{
			$this->fieldSecurity = $fieldSecurity;
		}
		
		
		// set id
		public function setId( $id )
		{
			$this->id = $id;
		}
		
		
		// set order
		public function setOrder( $order )
		{
			$this->order = $order;
		}
		
		
		// set label
		public function setLabel( $label = "Invisible" )
		{
			$this->label = $label;
		}

		
		// add field
		public function addField( $field )
		{
			array_push( $this->fields, $field );
		}
		
		
		// set locked
		public function setLocked( $locked = false )
		{
			$this->locked = $locked;
		}
		

		
		
		//-- OPERATIONS				
		
		// serialize
		public function serialize()
		{
			// fields
			$fields2 = array();
			foreach( $this->fields as $field )
				array_push( $fields2, $field->serialize() );
			
			// section
			$data = array(
							"fieldType"				=> $this->fieldType,
							"validationType"		=> $this->validationType,
							"isNextNumberActive"	=> $this->isNextNumberActive,
							"typeLogic"				=> $this->typeLogic,
							"fieldSecurity"			=> $this->fieldSecurity,
							"id"					=> $this->id,
							"order"					=> $this->order,
							"label"					=> $this->label,
							"fields"				=> $fields2,
							"locked"				=> $this->locked
						);
			
			return $data;
		}
		
	};


?>