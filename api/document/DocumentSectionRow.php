<?php

	// Document Section Row - document section row abstraction.


	// DocumentSectionRow
	class DocumentSectionRow
	{
		// globals
		protected $cells = array();
		

		
		//-- SETTERS
		
		// add cell
		public function addCell( $name, $value, $width = false )
		{
			// add cell
			$cell = array( 	"name"		=> $name,
							"value"		=> $value,
							"width"		=> $width,
							"used"		=> 0
						 );
			
			array_push( $this->cells, $cell );
		}
		
		
		// set cells
		public function setCells( $cells )
		{
			$this->cells = $cells;
		}

		
		
		
		//-- GETTERS		
		
		// get cells
		public function getCells()
		{
			return $this->cells;
		}
		
	};


?>