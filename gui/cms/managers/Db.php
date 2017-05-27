<?php

	// Database abstraction layer class.
 
 
	// Db
	class Db
	{
		// members
		protected $link			= false; 	// database handle
		protected $dbserver		= false; 	// database server, e.g. 'localhost' or '127.0.0.1:5131'
		protected $dbname		= false; 	// database name
		protected $dbuser		= false; 	// user
		protected $dbpass		= false; 	// password

		
		// constructor 
		public function __construct()
		{
			global $dbHost;
			global $dbUsername;
			global $dbPassword;
			global $dbName;
			
			// set db credentials
			$this->dbserver = $dbHost;
			$this->dbname 	= $dbName;
			$this->dbuser 	= $dbUsername;
			$this->dbpass 	= $dbPassword;

			// connecting to db
			$this->link = @mysqli_connect( $this->dbserver, $this->dbuser, $this->dbpass );
			if( false == $this->link || mysqli_connect_errno() )
				return;

			// set characterset to utf8
			@mysqli_set_charset( $this->link, "utf8" );

			// selecting the DB
			if( !@mysqli_select_db( $this->link, $this->dbname ) )
			{
				@mysqli_close( $this->link );
				$this->link = false;
				return;
			}
		}
		
		
		// whether connected
		public function isConnected()
		{
			if( is_bool( $this->link ) )
				return false;
				
			return true;
		}
		
		
		// close
		public function close()
		{
			if( is_bool( $this->link ) )
				return true;
				
			@mysqli_close( $this->link );
			$this->link = false;
				
			return true;
		}

		
		// performing a MySQL query
		public function query( $query )
		{
			// trim
			$query = trim( $query );
			
			// query
			$res = @mysqli_query( $this->link, $query );
			if( is_bool( $res ) && !$res )
				return $res;
		
			// get result rows
			if( preg_match("/^SELECT |^SHOW TABLES|^SHOW DATABASES/i", $query) )
			{
				$rows = array();
				while( $row = @mysqli_fetch_array( $res, MYSQLI_ASSOC ) )
					array_push( $rows, $row );

				return $rows;
			}
			
			return $res;
		 }

	 
		// return the last insert ID of autoincrement operation
		public function lastInsertID()
		{
			return @mysqli_insert_id( $this->link );
		}
		
		
		// return the last MySQL error number
		public function errNo()
		{
			return @mysqli_errno( $this->link );
		}
		
		
		// return the last MySQL error message
		public function errMes()
		{
			return @mysqli_error( $this->link );
		}
		
		
		// return number of affected rows
		public function affRows()
		{
			return @mysqli_affected_rows( $this->link );
		}
		
		
		// escape 
		public function escape( $s )
		{
			return @mysqli_real_escape_string( $this->link, $s );
		}
		
	};
	
?>