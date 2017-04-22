<?php

	// Document Section Field - document section field abstraction.


	// DocumentSectionField
	class DocumentSectionField
	{
		// field types
		const INPUTTEXT				= 1;
		const TEXTBOX				= 2;
		const NUMERIC				= 3;
		const SINGLESELECT			= 4;
		const MULTISELECT			= 5;
		const DATETIME				= 6;
		const DATEONLY				= 7;
		const TIMEONLY				= 8;		
		const LABEL					= 9;
		const BOOLEAN				= 10;
		const TRILEAN				= 11;
		const LOCATION				= 12;
		const PHOTO					= 13;
		const SIGNATURE				= 14;		
		const IMAGEVIEWER			= 15;
		const WEBVIEWER				= 16;
		const TRIGGERSECTION		= 17;
		const BARCODE				= 18;
		
		// globals
		protected $label			= "";		
		protected $type 			= "";
		protected $allowedValues 	= "";		
		protected $value 			= false;
		protected $security			= array();
		protected $disableSort		= -1;
		protected $disableEdit		= -1;
		protected $disableDelete	= -1;
		protected $locked			= -1;
		
		
		
		// constructor
		function __construct( 	$label = "", 			$type = "", 			$allowedValues = "", 	$value = false, 
								$disableSort = -1, 		$disableEdit = -1, 		$disableDelete = -1, 	$locked = -1,
								$security = array() ) 
		{
			$this->label			= $label;			
			$this->type				= $type;
			$this->allowedValues	= $allowedValues;
			$this->value			= $value;
			$this->disableSort		= $disableSort;
			$this->disableEdit		= $disableEdit;
			$this->disableDelete	= $disableDelete;
			$this->locked			= $locked;
			$this->security			= $security;
		}
		

		
		
		//-- SETTERS		
		
		// set label
		public function setLabel( $label )
		{
			$this->label = $label;
		}
		
		
		// set type
		public function setType( $type )
		{
			$this->type = $type;
		}
		
		
		// set allowed values
		public function setAllowedValues( $allowedValues )
		{
			$this->allowedValues = $allowedValues;
		}
		
		
		// set value
		public function setValue( $value )
		{
			$this->value = $value;
		}
		
		
		// set disableSort
		public function setDisableSort( $disableSort )
		{
			$this->disableSort = $disableSort;
		}
		
		
		// set disableEdit
		public function setDisableEdit( $disableEdit )
		{
			$this->disableEdit = $disableEdit;
		}
		
		
		// set disableDelete
		public function setDisableDelete( $disableDelete )
		{
			$this->disableDelete = $disableDelete;
		}
		
		
		// set locked
		public function setLocked( $locked )
		{
			$this->locked = $locked;
		}
		
		
		// set security
		public function setSecurity( $security )
		{
			$this->security = $security;
		}
		

		
		
		//-- GETTERS		
		
		// get label
		public function getLabel()
		{
			return $this->label;
		}
		
		
		// get type
		public function getType()
		{
			return $this->type;
		}
		
		
		// get allowed values
		public function getAllowedValues()
		{
			return $this->allowedValues;
		}
		
		
		// get value
		public function getValue()
		{
			return $this->value;
		}
		
		
		// get disableSort
		public function getDisableSort()
		{
			return $this->disableSort;
		}
		
		
		// get disableEdit
		public function getDisableEdit()
		{
			return $this->disableEdit;
		}
		
		
		// get disableDelete
		public function getDisableDelete()
		{
			return $this->disableDelete;
		}
		
		
		// get locked
		public function getLocked()
		{
			return $this->locked;
		}
		
		
		// get security
		public function getSecurity()
		{
			return $this->security;
		}
		
	};


?>