<?php

	// Document Section - document section abstraction.


	// DocumentSection
	class DocumentSection
	{
		// globals
		protected $name	 	= "";
		protected $fields 	= array();	
		protected $rows		= array();
		

		
		
		//-- SETTERS
		
		// set name
		public function setName( $name )
		{
			$this->name = $name;
		}
		
		
		// add field
		public function addField( 	$label = "", 			$type = "", 			$allowedValues = "", 	$value = false, 
									$disableSort = -1, 		$disableEdit = -1, 		$disableDelete = -1, 	$locked = -1,
									$security = array() )
		{
			$field = new DocumentSectionField( $label, $type, $allowedValues, $value, $disableSort, $disableEdit, $disableDelete, $locked, $security );
			
			array_push( $this->fields, $field );
		}
		
		
		// add row
		public function addRow( $row )
		{
			array_push( $this->rows, $row );
		}
		
		
		
		
		//-- GETTERS		
		
		// get name
		public function getName()
		{
			return $this->name;
		}
		
		
		// get fields
		public function getFields()
		{
			return $this->fields;
		}
		
		
		// get rows
		public function getRows()
		{
			return $this->rows;
		}
		
		
		// get field count
		public function getFieldCount()
		{
			return count( $this->fields );
		}
		
	};


?>