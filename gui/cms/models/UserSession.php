<?php

	// UserSession - management of usersession data.
 
 
	// UserSession
	class UserSession
	{
		// members
		private $db 				= null; // instance of class Db
		public $id					= null; // table 'id'
		public $userID				= null; // table 'userID'
		public $token				= null; // table 'token'
		public $ipAddress			= null; // table 'ipAddress'
		public $status				= null; // table 'status'			
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
		public function loadById( $id )
		{
			$record = $this->db->query( "SELECT * FROM `usersession` WHERE `id`='" . $id . "'" );
			if( is_bool( $record ) || count( $record ) <= 0 )
				return 0;
			
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
				
			return 1;    
		}
		
		
		// load by user
		public function loadByUser( $userID, $token )
		{
			$record = $this->db->query( "SELECT * FROM `usersession` WHERE `userID`='" . $userID . "' AND `token`='" . $this->db->escape( $token ) . "'" );
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
			
				$sql  = "INSERT INTO `usersession` (`userID`, `token`, `ipAddress`, `status`, `dateAdded`) VALUES('" .
							$this->db->escape( $this->userID ) . "', '" . 
							$this->db->escape( $this->token ) . "', '" .
							$this->db->escape( $this->ipAddress ) . "', '" . 
							$this->db->escape( $this->status ) . "', '" . 
							$this->db->escape( $this->dateAdded ) . "')";
				
				if( !$this->db->query( $sql ) )
					return 0;
				
				$this->id = $this->db->lastInsertID(); 
			}
			else 
			// update
			{
				$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );
				
				$sql  = "UPDATE `usersession` SET ";
				$sql .= "`dateUpdated`='" . 	$this->db->escape( $this->dateUpdated ) . "', ";
				$sql .= "`userID`='" . 			$this->db->escape( $this->userID ) . "', ";					
				$sql .= "`token`='" . 			$this->db->escape( $this->token ) . "', ";
				$sql .= "`ipAddress`='" . 		$this->db->escape( $this->ipAddress ) . "', ";
				$sql .= "`status`='" . 			$this->db->escape( $this->status ) . "' ";				
				$sql .= "WHERE `id`='" . 		$this->id . "'";
				
				if( !$this->db->query( $sql ) )
					return 0;
			}
			
			return 1;  
		}
		
		
		// update last login
		public function updateLastLogin()
		{
			$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );
				
			$sql = "UPDATE `usersession` SET `dateUpdated`='" . $this->db->escape( $this->dateUpdated ) . "' WHERE `id`='" . $this->id . "'";		
			if( !$this->db->query( $sql ) )
				return false;

			return true;
		}
		
		
		// delete
		public function delete()
		{
			if( $this->id != null )
			{
				$sql = "DELETE FROM `usersession` WHERE `id`='" . $this->id . "'";
				if( !$this->db->query( $sql ) )
					return false;
					
				$this->id = null;
			}
			
			return true;
		}
		
		
		// clear user
		static public function clearUser( $userID )
		{
			$db = new Db();
			
			$sql = "DELETE FROM `usersession` WHERE `userID`='" . $userID . "'";
			if( !$db->query( $sql ) )
				return false;
				
			return true;
		}
		
	}
	
?>