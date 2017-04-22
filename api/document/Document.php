<?php

	// Document - document abstraction layer.


	// Document
	class Document
	{
		// globals
		protected $type	 		= "";			
		protected $title 		= "";	
		protected $sections 	= array();	
		

		
		
		//-- SETTERS
		
		// set type
		public function setType( $type )
		{
			$this->type = $type;
		}
		
		
		// set title
		public function setTitle( $title )
		{
			$this->title = $title;
		}
		
		
		// add section
		public function addSection( $section )
		{
			array_push( $this->sections, $section );
		}
		
		
		
		
		//-- GETTERS
		
		// get type
		public function getType()
		{
			return $this->type;
		}
		
		
		// get title
		public function getTitle()
		{
			return $this->title;
		}
		
		
		// get sections
		public function getSections()
		{
			return $this->sections;
		}
		
		
		// get field count
		public function getFieldCount()
		{
			$no = 0;
			
			foreach( $this->sections as $section )
			{
				$no += $section->getFieldCount();
			}
			
			return $no;
		}
		
		
		
		
		//-- OPERATIONS
		
		// build from array
		public function buildFromArray( &$sections )
		{
			// add sections to document
			foreach( $sections as $section )
			{
				$docSection = new DocumentSection();
				
				// add rows to sections
				foreach( $section["rows"] as $key => $row )
				{
					$docRow = new DocumentSectionRow();
					
					foreach( $row as $cell )
					{
						// value
						$value = trim( $cell[1], " \r\n" );
						if( strlen( $value ) <= 0 )
						{
							$value = trim( $cell[1], "\r\n" );
						}
						
						// width
						$width = false;
						if( isset( $cell[2] ) )
							$width = DocumentUtils::cleanupWidth( $cell[2] );
						
						$docRow->addCell( $cell[0], $value, $width );
					}
					
					// add row
					$docSection->addRow( $docRow );
				}
				
				// add section
				$this->addSection( $docSection );
			}
			
			return true;
		}
		
		
		
		
		//-- LOGGING		
		
		// output rows
		public function outputRows( $serialize = true )
		{
			$result = "";
			
			$result .= "\n\nDOCUMENT ROWS\n\n";
			
			$sectionIdx = 1;
			
			// traverse sections
			foreach( $this->sections as $section )
			{
				$result .= "Section " . ($sectionIdx++) . "\n";
				
				$rows = $section->getRows();
				
				$rowIdx = 1;
				
				// traverse rows
				foreach( $rows as $row )
				{
					$result .= "   Row " . ($rowIdx++) . "\n";

					$cells = $row->getCells();
					
					$cellIdx = 1;
					
					// traverse cells
					foreach( $cells as $cell )
					{
						$result .= "   Cell " . ($cellIdx++) . " - " . $cell["name"] . ": " . $cell["value"];
						
						if( !is_bool( $cell["width"] ) )
							$result .= "  (width: " . $cell["width"] . ")";
						
						$result .= "\n";
					}
				}
				
				$result .= "\n\n";
			}
			
			// output result
			if( !$serialize )
				echo $result;
			
			return $result;
		}
		
		
		// output fields
		public function outputFields( $isAnalysis = 0 )
		{
			$result = "\n\nDOCUMENT FIELDS\n\n";
			
			$sectionIdx = 1;
			
			$types = array( "", "single text", "multi text", "numeric", "single select", "multi select", "date time", "date only", "time only", 
							"label", "boolean", "trilean", "location", "photo", "signature", "image", "link", "trigger", "barcode" );
			
			// traverse sections
			foreach( $this->sections as $section )
			{
				$result .= "Section " . ($sectionIdx++) . "\n";
				
				$fields = $section->getFields();
					
				$fieldIdx = 1;
					
				// traverse fields
				foreach( $fields as $field )
				{
					$result .= "\n   Field " . ($fieldIdx++) . "\n";

					$label = $field->getLabel();
					if( !empty( $label ) )
						$result .= "   " . $label;
					
					$type = $field->getType();
					if( !empty( $type ) )
						$result .= "  (" . strtolower( $types[ $type ] ) . ")";
					
					$av = $field->getAllowedValues();
					if( !empty( $av ) )
						$result .= "\n   values:\n   " . $av;
					
					$value = $field->getValue();
					if( !is_bool( $value ) )
						$result .= "\n   default value: " . $value;
					
					$result .= "\n";
				}
				
				$result .= "\n\n";
			}
			
			if( !$isAnalysis )
				echo $result;
			else
				DocumentUtils::logAnalysis( $result );
		}
		
	};


?>