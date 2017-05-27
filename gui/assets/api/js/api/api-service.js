
	
	// API general-purpose functions.


	// globals
	var confirmCallback			= false;
	var confirmCallbackId		= false;
	var loginPopupCallback		= false;
	var loginPopupParam			= false;
	var isAjaxRun				= false;
	
	
	
	
	//-- INITIALIZE
	
	// initialize
	function initializeApp()
	{
		// custom
		if( window.initializeCustom != undefined )
			initializeCustom();	
	}
	
	
	// start ajax flag
	function startAjax()
	{
		waitAjaxEnd();
		
		isAjaxRun = true;
	}
	
	
	// end ajax flag
	function endAjax()
	{
		isAjaxRun = false;
	}	
	
	
	// is ajax flag
	function isAjaxStarted()
	{
		return isAjaxRun;
	}
	
	
	// wait ajax to end
	function waitAjaxEnd()
	{
		if( !isAjaxRun )
			return;
			
		setTimeout( waitAjaxEnd, 100 );
	}
	
	
	function stopSpins()
	{			
		window.setTimeout( stopSpinsPerform, 300 );		
	}

	function stopSpinsPerform()
	{
		Ladda.stopAll();
	}
	
	
	
	
	//-- SESSION
	
	
	// on login
	function onLogin()
	{
		// username
		var username = document.getElementById("username").value;
		if( username == "" )
		{
			stopSpins();				
			showStatus( "Oops", "Please enter your Username." );
			return;
		}
		
		// password
		var password = document.getElementById("password").value;
		if( password == "" )
		{
			stopSpins();				
			showStatus( "Oops", "Please enter your Password." );
			return;
		}
		
		// remember
		var remember = 0;
		if( document.getElementById("remember").checked )
			remember = 1;
					
		// build request
		var dataString  = '{"action":"login", "username":"' + encodeURIComponent(username) + 
						  '", "password":"' + encodeURIComponent(password) + 
						  '", "remember":"' + remember + '"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/SessionController.php',
			complete: function(data)
			{
				endAjax();
				
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){
					stopSpins();					
					return;
				}
				
				if( response.success == "false" )
				{
					stopSpins();					
					showStatus( "Oops", response.exception );
					return;
				}

				document.location = response.redirect;
			}
		});
	}
	
	
	// on logout
	function onLogout()
	{
		// build request
		var dataString  = '{"action":"logout"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/SessionController.php',
			complete: function(data)
			{
				endAjax();
				
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){
					return;
				}
				
				if( response.success == "false" )
				{
					showStatus( "Oops", response.exception );
					return;
				}

				document.location = response.redirect;
			}
		});
	}

	
	
	
	
	//-- LOCK SCREEN
	
	// on lock screen
	function onLockScreen()
	{
		// build request
		dataString  = '{"action":"lockscreen"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/UserController.php',
			complete: function(data)  
			{
				endAjax();
				
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){
					return;
				}
				
				if( response.success == "false" )
				{
					showStatus( "Oops", response.exception );
					return;
				}
				
				// go to index
				document.location = "lockscreen.php";
			}
		});
	}
	
	
	// on unlock screen
	function onUnlockScreen()
	{
		// password
		var password = document.getElementById("reloginPass").value;
		if( password == "" )
		{
			stopSpins();
			showStatus( "Oops", "Please enter your Password." );
			return;
		}
	
		// build request
		dataString  = '{"action":"unlockscreen", "password":"' + encodeURIComponent( password ) + '"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/UserController.php',
			complete: function(data)  
			{
				endAjax();
			
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){
					stopSpins();
					return;
				}
				
				if( response.success == "false" )
				{
					stopSpins();				
					showStatus( "Oops", response.exception );
					return;
				}
				
				// go to index
				document.location = response.returnPage;
			}
		});
	}	

	
	// key in unlock screen
	function handleKeyUnlockScreen(code)
	{
		if( code == 13 )
			$("#unlockBut").trigger("click");
	}	

	
	// key in password
	function handleKeyPassword(code)
	{
		if( code == 13 )
			$("#loginBut").trigger("click");
	}	

	
	
	
	
	// -- STATUS POPUPS
	
	
	// on confirmed
	function onConfirmed()
	{
		$('#confirmDlg').modal('hide');		
		
		if( confirmCallback )
			confirmCallback( confirmCallbackId );
	}
	
	
	function onConfirmedSticky()
	{		
		if( confirmCallback )
			confirmCallback( confirmCallbackId );
	}	
	
	
	function hideConfirmed()
	{
		$('#confirmDlg').modal('hide');	
	}
	
	
	// show status
	function showStatus( title, msg )
	{
		document.getElementById("statusDlgTitle").innerHTML 	= title;
		document.getElementById("statusDlgMsg").innerHTML 		= msg;		
	
		$('#statusDlg').modal('show');
	}
	
	
	// show status 2
	function showStatus2( title, msg )
	{
		document.getElementById("statusDlgTitle2").innerHTML 	= title;
		document.getElementById("statusDlgMsg2").innerHTML 		= msg;		
	
		$('#statusDlg2').modal('show');
	}
	
	
	// show status 3
	function showStatus3( title, msg )
	{
		document.getElementById("statusDlgTitle3").innerHTML 	= title;
		document.getElementById("statusDlgMsg3").innerHTML 		= msg;		
	
		$('#statusDlg3').modal('show');
	}	
	
	
	
	
	//-- HELPER FUNCTIONS
	
	
	// whether natural
	function isNatural( n ) 
	{
		for( var i = 0; i < n.length; i++ )
		{
			Char = n.charAt(i); 
			if( Char < '0' || Char > '9' )
				return false;
		}
		
		return true;
	}
	
	
	// whether number
	function isNumber( n ) 
	{
		for( var i = 0; i < n.length; i++ )
		{
			Char = n.charAt(i); 
			if( ( Char < '0' || Char > '9' ) && Char != '.' )
				return false;
		}
		
		return true;
	}
	
	
	// valid year
	function isValidYear( year )
	{
		if( year == "" )
			return false;
			
		if( !isNatural( year ) )
			return false;
		
		year = parseInt( year );
		if( ( year < 1900 ) || ( year > currentYear ) )
			return false;
			
		return true;
	}

	
	// is valid password
	function isValidPassword( username, n )
	{
		if( n.length < 8 )
			return false;
			
		if( n.indexOf( username ) >= 0 )
			return false;
		
		var isUpper 	= false;
		var isLower		= false;
		var isDigit		= false;
		var isSymbol	= false;
	
		for( var i = 0; i < n.length; i++ )
		{
			Char = n.charAt(i); 
			
			if( Char >= 'A' && Char <= 'Z' )
				isUpper = true;
			else
			if( Char >= 'a' && Char <= 'z' )
				isLower = true;
			else	
			if( Char >= '0' && Char <= '9' )
				isDigit = true;
			else
				isSymbol = true;
		}		
		
		if( !isUpper || !isLower || !isDigit || !isSymbol )
			return false;
			
		return true;
	}
	
	