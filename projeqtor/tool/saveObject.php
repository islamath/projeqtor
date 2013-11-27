<?php
/** ===========================================================================
 * Save the current object : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 * The old values are fetched in $currentObject of $_SESSION
 * Only changed values are saved. 
 * This way, 2 users updating the same object don't mess.
 */

require_once "../tool/projeqtor.php";
// Get the object class from request
if (! array_key_exists('className',$_REQUEST)) {
  throwError('className parameter not found in REQUEST');
}
$className=$_REQUEST['className'];

if ($className=="Workflow") {
//echo ini_get('max_input_vars');
    ini_set('max_input_vars', 5000);
}
//var_dump($_REQUEST);
$ext="";
if (! array_key_exists('comboDetail', $_REQUEST)) {
	// Get the object from session(last status before change)
	if (isset($_REQUEST['directAccessIndex'])) {
		if (! isset($_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']])) {
			throwError('currentObject parameter not found in SESSION');
		}
		$obj=$_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']];
	} else {
	  if (! array_key_exists('currentObject',$_SESSION)) {
	    throwError('currentObject parameter not found in SESSION');
	  }
	  $obj=$_SESSION['currentObject'];
	}
	if (! is_object($obj)) {
	  throwError('last saved object is not a real object');
	}
  // compare expected class with object class
  if ($className!=get_class($obj)) {
    throwError('last save object (' . get_class($obj) . ') is not of the expected class (' . $className . ').'); 
  }	
} else {
	$ext="_detail";
}
Sql::beginTransaction();
// get the modifications (from request)
$newObj=new $className();
$newObj->fillFromRequest($ext);
if ($newObj->id=='0') {$newObj->id=null;}
if ($newObj->id and $obj->id and $newObj->id!=$obj->id) {
	throwError('last save object (' . get_class($obj) . ' #'.$obj->id.') is not the expected object (' . $className . ' #'.$newObj->id.').');
}
// save to database
$result=$newObj->save();

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . formatResult($result) . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . formatResult($result) . '</span>';
  // save the new object to session (modified status)
  if (! array_key_exists('comboDetail', $_REQUEST)) {
  	if (isset($_REQUEST['directAccessIndex'])) {
      $_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']]=new $className($newObj->id);
    } else {
      $_SESSION['currentObject']=new $className($newObj->id);
    }
  }
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . formatResult($result) . '</span>';
}

function formatResult($result) {
	if (array_key_exists('comboDetail', $_REQUEST)) {
		$res=$result;
	  $res=str_replace('"lastOperationStatus"', '"lastOperationStatusComboDetail"', $res);
	  $res=str_replace('"lastSaveId"', '"lastSaveIdComboDetail"', $res);
    $res=str_replace('"lastOperation"', '"lastOperationComboDetail"', $res);    
    return $res;
	} else {
	  return $result;
  }	
}	
?>