<?php
#Begin Session
session_start();


#Import needed files

require_once "logic/portal-base-logic.php";
require_once "logic/common/commonFunctions.php";
require_once "page/portal-base-page.php";
require_once "page/default-layout-page.php";
require_once "navigation.php";

#Page Specific import
require_once "logic/managelabhours-logic.php";

$error="";

#Check to see if the users are valid
verifyUser();

verifyUserLevelAccess($_SESSION['username'],basename($_SERVER['PHP_SELF']));

#Since the user is not logged in nor completed a successful login requiest, render the form.

$html="";

if(isset($_POST['formSubmit']) && !empty($_POST['formSubmit']))
{
    attemptSetLabTimes();
    
}
else if(isset($_POST['formSubmit']) && !empty($_POST['formSubmit']))
{
    
}
else
{
    $html .= genLabTimeForm();
}



printHeader();
printStartBody();
printPortalHead();
printNavBar(getUserInfo(),createNavigation());
printContent($html);
printEndBody();

?>
