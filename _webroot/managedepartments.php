<?php
#Begin Session
session_start();


#Base imports
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "logic/portal-base-logic.php";
require_once "page/default-layout-page.php";
require_once "navigation.php";


#Page Specific imports
require_once "logic/managedepartments-logic.php";


#Check to see if the users are valid
verifyUser();
verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));


$html="";

if(isset($_POST['submitEdit']) && !empty($_POST['submitEdit']))
{
	$result=databaseSubmitEdits(array($_POST['deptid'],$_POST['deptname'],$_POST['submitEdit']));

	if(is_array($result))
	{
		//Form submission complete. Tell user and clear post as we are need to go back to 
		// a completely clean slate to avoid re-submission on a page refresh.
		$_POST = array();
		header('Location: managedepartments.php');
	}
	else
	{
		$_POST['edit']=$_POST['submitEdit'];
		$html.="<script>alert(\"An error occured submitting the form. Please verify all values and try again.\");</script>";
	}
}
else if(isset($_POST['cancelEdit']) && !empty($_POST['cancelEdit']))
{
	header('Location: managedepartments.php');
}


//if its an edit user event show code for edit user event
//otherwise show the main listing.
if(isset($_POST['edit']) && !empty($_POST['edit']))
{
	$_SESSION['editID']=$_POST['edit'];
	$html .= editEntry($_SESSION['editID']);
}
else if (isset($_GET['searchSubmit']) && !empty($_GET['searchSubmit']))
{
	$html .= searchResults();
}
else if (isset($_GET['searchReset']) && !empty($_GET['searchReset']))
{
	header('Location: manageclasses.php');
}
else
{
	$html .= displayAll();
}


printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();


?>
