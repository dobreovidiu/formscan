<?php		// UserApiKey - management of userapikey data.	
		// UserApiKey
	class UserApiKey
	{		// members
		private $db 				= null; // instance of class Db		public $id 					= null; // table 'id'
		public $userID 				= null; // table 'userID'		public $name				= null; // table 'name'		public $key					= null; // table 'key'					public $status				= null; // table 'status'						public $dateAdded 			= null; // table 'dateAdded'		public $dateUpdated	 		= null; // table 'dateUpdated'		
						// constructor
		public function __construct( $db = null )
		{
			if( !isset( $db ) )
				$this->db = new Db();
			else				$this->db = $db;		}		
				// load by id
		public function loadById( $id )
		{
			$record = $this->db->query( "SELECT * FROM `userapikey` WHERE `id`='" . $id . "'" );			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
			return 1;    		}		
				// load by name
		public function loadByName( $userID, $name )
		{
			$record = $this->db->query( "SELECT * FROM `userapikey` WHERE `userID`='" . mysql_real_escape_string( $userID ) . "' AND `name`='" . mysql_real_escape_string( $name ) . "'" );
			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
			return 1;    		}						// load by admin key		public function loadAdminKey()		{			$record = $this->db->query( "SELECT * FROM `userapikey` WHERE `userID`='1' LIMIT 1" );			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;						foreach( $record[0] as $key => $value )				$this->$key = $value;			return 1;    		}						// load by key		public function loadByKey( $key )		{			$record = $this->db->query( "SELECT * FROM `userapikey` WHERE `key`='" . mysql_real_escape_string( $key ) . "'" );			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;			foreach( $record[0] as $key => $value )				$this->$key = $value;			return 1;    		}		
				// load by data
		public function loadByData( $dataArray )
		{
			foreach( $dataArray as $key => $value )				$this->$key = $value;							return 1;		}
				// save
		public function save()
		{
			// insert
			if( $this->id == null )
			{				$this->dateAdded = date( "Y-m-d H:i:s", time() );				
				$sql = "INSERT INTO `userapikey` (`userID`, `name`, `key`, `status`, `dateAdded`) VALUES('" .					   mysql_real_escape_string( $this->userID ) . "', '" . 										   mysql_real_escape_string( $this->name ) . "', '" . 							   mysql_real_escape_string( $this->key ) . "', '" . 						   					   mysql_real_escape_string( $this->status ) . "', '" . 						   					   mysql_real_escape_string( $this->dateAdded ) . "')";								//echo $sql;
				if( !$this->db->query( $sql ) )					return 0;				
				$this->id = $this->db->lastInsertID();
			}
			else 			// update
			{
				$sql = "UPDATE `userapikey` SET " 	.					   "`userID`= '"		. 	mysql_real_escape_string( $this->userID ) . "', " .					   "`name`= '"			. 	mysql_real_escape_string( $this->name ) . "', " .					   "`key`= '"			. 	mysql_real_escape_string( $this->key ) . "', " .	   					   "`status`= '"		. 	mysql_real_escape_string( $this->status ) . "' " .					   "WHERE `id`='" 		. 	$this->id . "'";								//echo $sql;
				if( !$this->db->query( $sql ) )					return 0;
			} 
			return 1;
		}				// delete
		public function delete()
		{
			if( $this->id != null )
			{				// userapikey
				$sql = "DELETE FROM `userapikey` WHERE `id`='" . $this->id . "'";
				if( !$this->db->query( $sql ) )					return false;					
				$this->id = null;
			}						return true;
		}		
	};
?>