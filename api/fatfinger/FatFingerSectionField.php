<?php

	// Fat Finger Section Field - structure of Fat Finger Section Field contained in form sections.


	// FatFingerSectionField
	class FatFingerSectionField
	{
		// globals
		protected $fieldType 			= 0;
		protected $validationType 		= 0;
		protected $isNextNumberActive 	= false;		
		protected $typeLogic 			= 0;
		protected $fieldSecurity 		= array();
		protected $id 					= "";
		protected $order 				= 1;		
		protected $label	 			= "";
		protected $validationMessage 	= null;
		protected $disableSort			= -1;
		protected $disableEdit			= -1;
		protected $disableDelete		= -1;
		protected $locked				= -1;
		protected $extraFields			= array();
		

		
		
		//-- SETTERS
		
		// set field type
		public function setFieldType( $fieldType )
		{
			$this->fieldType = $fieldType;
		}
		
		
		// set validation
		public function setValidation( $validationType = 0, $validationMessage = null )
		{
			$this->validationType 		= $validationType;
			$this->validationMessage 	= $validationMessage;			
		}
		
		
		// set isNextNumberActive
		public function setIsNextNumberActive( $isNextNumberActive = false )
		{
			$this->isNextNumberActive = $isNextNumberActive;
		}
		
		
		// set type logic
		public function setTypeLogic( $typeLogic = "0" )
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
		public function setLabel( $label )
		{
			$this->label = $label;
		}
		
		
		// set disable edit
		public function setDisableEdit( $disableEdit = -1 )
		{
			$this->disableEdit = $disableEdit;
		}
		
		
		// set disable sort
		public function setDisableSort( $disableSort = -1 )
		{
			$this->disableSort = $disableSort;
		}
		
		
		// set disable delete
		public function setDisableDelete( $disableDelete = -1 )
		{
			$this->disableDelete = $disableDelete;
		}		
		
		
		// set locked
		public function setLocked( $locked = -1 )
		{
			$this->locked = $locked;
		}
		
		
		// set extra fields
		public function setExtraFields( $extraFields = array() )
		{
			$this->extraFields = $extraFields;
		}
		

		
		
		//-- OPERATIONS			
		
		// serialize
		public function serialize()
		{
			// mandatory data
			$data = array(
							"fieldType"				=> $this->fieldType,
							"validationType"		=> $this->validationType,
							"isNextNumberActive"	=> $this->isNextNumberActive,
							"typeLogic"				=> $this->typeLogic,							
							"fieldSecurity"			=> $this->fieldSecurity,
							"id"					=> $this->id,
							"order"					=> $this->order,							
							"label"					=> $this->label
						);
						
			// optional data
			if( !empty( $this->validationMessage ) )
				$data["validationMessage"] = $this->validationMessage;
						
			if( is_bool( $this->disableEdit ) )
				$data["disableEdit"] = $this->disableEdit;
			
			if( is_bool( $this->disableSort ) )
				$data["disableSort"] = $this->disableSort;
			
			if( is_bool( $this->disableDelete ) )
				$data["disableDelete"] = $this->disableDelete;
			
			if( is_bool( $this->locked ) )
				$data["locked"] = $this->locked;
			
			// extra fields
			foreach( $this->extraFields as $key => $value )
				$data[ $key ] = $value;
			
			return $data;
		}
		
	};


?>