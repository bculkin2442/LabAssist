<?php
require_once "logic/common/ldap.php";
require_once "logic/database/dbCon.php";

function attemptPassLogin()
{
	$error = "";

	if (empty($_POST['username']) || empty($_POST['password']))
	{
		$error = "Both username and password are required.";
	}
	else
	{
		/*
		 * Define $username and $password
		 */
		$username   = $_POST['username'];
		$password   = $_POST['password'];

		/*
		 * Processs login
		 */

		if($_POST['username'] == 'agcantrell')
		{
			#$isSuccess = true;
			$isSuccess = login($username,$password);
		}
		else
		{
			$isSuccess = login($username,$password);
		}


		/*
		 * Error Handling
		 */
		if( $isSuccess == 1)
		{

			/*
			 * Login was successful now we need to determine if user is a new user, thus needing to register. 
			 */
			$registered = isRegistered($username);

			if($registered == false)
			{
				/*
				 * Set registration vars.
				 */
				$_SESSION['userAttr']=getUserAttr($username,$password);
				$_SESSION["username"]=strtolower($username);
				$_SESSION["register"]=true;
				header("location: user_registration.php");
				exit();
			}
			else
			{
				/*
				 * Set login vars.
				 */
				$_SESSION["username"]=strtolower($username);
				$_SESSION["useridno"]=(
					databaseQuery("select idno from users where username ilike ?",
						array(strtolower($username))
					)
				)[0]['idno'];

				header("location: portal.php"); // Redirecting To Other Page
				exit();
			}
		}
		elseif($isSuccess == 503)
		{
			$error = "Error 503: Logon server appears to be down.";
		}
		elseif($isSuccess == 504)
		{
			$error = "Error 504: Logon server refused to answer request.";
		}
		else
		{
			$error = "Login Failed. <br> Please Check Username and Password.";
		}
	}

	return $error;
}

/*
 * Attempt a kiosk mode login.
 */
function attemptKioskLogin()
{
	if(empty($_POST['sidno']))
	{
		$error = "Error: ID Number Required";
		return $error;
	}
	else if (!preg_match("/^\d{9}$/",$_POST['sidno']))
	{
		$error = "Error: Invalid ID Number";
		return $error;
	}

	$status = kioskLoginCheck();

	if (!($status==-1))
	{
		if($status == 1)
		{
			$_SESSION['kiosk']= true;
			$_SESSION['sidno']= $_POST['sidno'];
			header("location: tutor_session.php");
			exit();
		}
		else
		{
			/*
			 * User needs to register.
			 */
			$_SESSION["registerSid"]=true;
			$_SESSION['sidno']= $_POST['sidno'];
			$_SESSION['userAttr']=getSidnoAttr($_POST['sidno']);
			header("location: user_registration.php");
			exit();
		}
	}

	return $error;
}

/*
 * Check if a user is registered.
 */
function isRegistered($userName)
{
	$status = false;

	$result = safeDBQuery("SELECT idno FROM users WHERE username ILIKE ?", array(
		$userName
	));

	if(!empty($result))
	{
		$status = true;
	}

	return $status;
}

/*
 * Check kiosk mode login.
 */
function kioskLoginCheck() {
	$result = safeDBQuery("SELECT idno FROM users WHERE idno = ?", array(
		$_POST['sidno']
	));

	if(!empty($result) && is_array($result)) {
		return 1;
	} else {
		return -1;
	}
}

?>
