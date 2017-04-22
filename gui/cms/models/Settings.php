<?php

	// Settings - management of settings data.
 
 
	// Settings
	class Settings
	{
		// members
		private $db 				= null; // instance of class Db		
		public $id 					= null; // table 'id'	
		public $name 				= null; // table 'name'
		public $description			= null; // table 'description'		
		public $value				= null; // table 'value'		
		public $dateAdded 			= null; // table 'dateAdded'		
		public $dateUpdated			= null; // table 'dateUpdated'

		
		// constructor
		public function __construct( $db = null )
		{
			if( !isset( $db ) )
				$this->db = new Db();
			else
				$this->db = $db;
		}
		
		
		// load by id		
		public function loadById( $id )
		{
			$record = $this->db->query( "SELECT * FROM `settings` WHERE `id`='" . $id . "'" );
			if( is_bool( $record ) || count( $record ) <= 0 )
				return 0;
			
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
				
			return 1;    
		}
		
		
		// load by name
		public function loadByName( $name )
		{
			$query = "SELECT * FROM `settings` WHERE `name`='" . $this->db->escape( $name ) . "'";	
		
			$record = $this->db->query( $query );
			if( is_bool( $record ) || count( $record ) <= 0 )
				return 0;
			
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
				
			return 1;    
		}	
		
		
		// load by data
		public function loadByData( $dataArray )
		{
			foreach( $dataArray as $key => $value )
				$this->$key = $value;
				
			return 1;
		}
		
		
		// save
		public function save()
		{
			// escape all
			$name 			= $this->db->escape( $this->name );
			$description 	= $this->db->escape( $this->description );			
			$value 			= $this->db->escape( $this->value );
			
			// insert
			if( $this->id == null )
			{
				$this->dateAdded = gmdate( "Y-m-d H:i:s", time() );
			
				$sql  = "INSERT INTO `settings` (`name`, `description`, `value`, `dateAdded`) ";
				$sql .= "VALUES('$name', '$description', '$value', '$this->dateAdded')";
				
				if( !$this->db->query( $sql ) )
					return 0;
					
				$this->id = $this->db->lastInsertID(); 
			}
			else 
			// update
			{
				$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );
				
				$sql  = "UPDATE `settings` SET ";
				$sql .= "`dateUpdated`='" . 	$this->dateUpdated . "', ";	
				$sql .= "`name`='" . 			$name . "', ";
				$sql .= "`description`='" . 	$description . "', ";				
				$sql .= "`value`='" . 			$value . "' ";							
				$sql .= "WHERE `id`='" . 		$this->id . "'";
				
				if( !$this->db->query( $sql ) )
					return 0;
			}
			
			return 1;  
		}
		
		
		// get value
		static public function getValue( $name )
		{
			$db = new Db();
			
			// query
			$sql = "SELECT `value` FROM `settings` WHERE `name`='" . $name . "'";
			
			$result = $db->query( $sql );
			if( count( $result ) <= 0 )
				return false;
			
			return $result[0]["value"];
		}
		
		
		// set value
		static public function setValue( $name, $value )
		{
			$record = new Settings();
			
			if( !$record->loadByName( $name ) )
				return false;
			
			$record->value = $value;
			
			if( !$record->save() )
				return false;
			
			return true;
		}
		
		
		// delete
		public function delete()
		{
			if( $this->id != null )
			{
				// bank
				$sql = "DELETE FROM `settings` WHERE `id`='" . $this->id . "'";
				if( !$this->db->query( $sql ) )
					return false;	
					
				$this->id = null;
			}
			
			return true;
		}
		
	}
	
?>