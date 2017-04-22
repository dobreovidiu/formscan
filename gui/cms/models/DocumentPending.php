<?php

	// DocumentPending - management of documentpending data.
 
 
	// DocumentPending
	class DocumentPending
	{
		// members
		private $db 				= null; // instance of class Db
		public $id					= null; // table 'id'
		public $userID				= null; // table 'userID'
		public $filename			= null; // table 'filename'
		public $filepath			= null; // table 'filepath'
		public $status				= null; // table 'status'			
		public $dateAdded			= null; // table 'dateAdded'

		
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
			$record = $this->db->query( "SELECT * FROM `documentpending` WHERE `id`='" . $id . "'" );
			if( is_bool( $record ) || count( $record ) <= 0 )
				return 0;
			
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
				
			return 1;    
		}
		
		
		// load pending
		public function loadPending( $userID )
		{
			$record = $this->db->query( "SELECT * FROM `documentpending` WHERE `userID`='" . $userID . "' AND `status`=2 ORDER BY `dateAdded` DESC LIMIT 1" );
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
			
				$sql  = "INSERT INTO `documentpending` (`userID`, `filename`, `filepath`, `status`, `dateAdded`) VALUES('" .
							$this->db->escape( $this->userID ) . "', '" . 
							$this->db->escape( $this->filename ) . "', '" .
							$this->db->escape( $this->filepath ) . "', '" . 
							$this->db->escape( $this->status ) . "', '" . 
							$this->db->escape( $this->dateAdded ) . "')";
				
				if( !$this->db->query( $sql ) )
					return 0;
				
				$this->id = $this->db->lastInsertID(); 
			}
			else 
			// update
			{
				$sql  = "UPDATE `documentpending` SET ";
				$sql .= "`userID`='" . 			$this->db->escape( $this->userID ) . "', ";					
				$sql .= "`filename`='" . 		$this->db->escape( $this->filename ) . "', ";
				$sql .= "`filepath`='" . 		$this->db->escape( $this->filepath ) . "', ";
				$sql .= "`status`='" . 			$this->db->escape( $this->status ) . "' ";				
				$sql .= "WHERE `id`='" . 		$this->id . "'";
				
				if( !$this->db->query( $sql ) )
					return 0;
			}
			
			return 1;  
		}
		
		
		// update processed
		public function updateProcessed()
		{
			$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );
				
			$sql = "UPDATE `documentpending` SET `status`=1 WHERE `id`='" . $this->id . "'";
			if( !$this->db->query( $sql ) )
				return false;

			return true;
		}
		
		
		// delete
		public function delete()
		{
			if( $this->id != null )
			{
				$sql = "DELETE FROM `documentpending` WHERE `id`='" . $this->id . "'";
				if( !$this->db->query( $sql ) )
					return false;
					
				$this->id = null;
			}
			
			return true;
		}
		
	}
	
?>