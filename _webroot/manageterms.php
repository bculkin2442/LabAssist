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
require_once "logic/manageterms-logic.php";


#Check to see if the users are valid
verifyUser();
verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));



$html="";

if(isset($_POST['setTerm']) && !empty($_POST['setTerm']))
{
	if(attemptTermChange(array($_POST['setTerm'])) == 0)
	{
		header('Location: manageterms.php');             //We do this to prevent form resubmission.
		exit();
	}
	else
	{
		$html.="<script>alert(\"An error occured! Please try again or contact system administrator\");</script>";
	}

}
$html.=displayAll();


printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();


?>
