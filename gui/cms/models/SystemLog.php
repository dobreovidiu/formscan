<?php

	// SystemLog - management of systemlog data.
 
 
	// SystemLog
	class SystemLog
	{
		// members
		private $db 				= null; // instance of class Db		
		public $id					= null; // table 'id'
		public $system 				= null; // table 'system'
		public $userID				= null; // table 'userID'
		public $message				= null; // table 'message'
		public $type				= null; // table 'type'		
		public $dateAdded			= null; // table 'dateAdded'
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
		public function loadById($id)
		{
			$record = $this->db->query( "SELECT * FROM `systemlog` WHERE `id`='" . $id . "'" );
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
			// insert
			if( $this->id == null )
			{
				$this->dateAdded = gmdate( "Y-m-d H:i:s", time() );
			
				$sql  = "INSERT INTO `systemlog` (`system`, `userID`, `message`, `type`, `dateAdded`) VALUES('";
				$sql .= $this->db->escape( $this->system ) . "', '" . 
						$this->db->escape( $this->userID ) . "', '" . 
						$this->db->escape( $this->message ) . "', '" . 
						$this->db->escape( $this->type ) . "', '" . 
						$this->db->escape( $this->dateAdded ) . "')";
				
				if( !$this->db->query( $sql ) )
					return 0;
					
				$this->id = $this->db->lastInsertID(); 
			}
			else 
			// update
			{
				$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );
				
				$sql  = "UPDATE `systemlog` SET ";
				$sql .= "`dateUpdated`='" . 	$this->db->escape( $this->dateUpdated ) . "', ";
				$sql .= "`system`='" . 			$this->db->escape( $this->system ) . "', ";				
				$sql .= "`userID`='" . 			$this->db->escape( $this->userID ) . "', ";
				$sql .= "`message`='" . 		$this->db->escape( $this->message ) . "', ";
				$sql .= "`type`='" . 			$this->db->escape( $this->type ) . "' ";
				$sql .= "WHERE `id`='" . 		$this->id . "'";
				
				if( !$this->db->query( $sql ) )
					return 0;
			}
			
			return 1;
		}
		
		
		// delete
		public function delete()
		{
			if( $this->id != null )
			{
				$sql = "DELETE FROM `systemlog` WHERE `id`='" . $this->id . "'";
				if( !$this->db->query( $sql ) )
					return false;
					
				$this->id = null;
			}
			
			return true;
		}
		
		
		// delete all
		static public function deleteAll( $type = false )
		{
			$db = new Db();
			
			if( is_bool( $type ) )
				$sql = "DELETE FROM `systemlog`";
			else
				$sql = "DELETE FROM `systemlog` WHERE `type`=" . $type;
			
			if( !$db->query( $sql ) )
				return false;

			return true;
		}
		
	}
	
?>