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

Sql::beginTransaction();
// get the modifications (from request)
$newObj=new $className();
$newObj->fillFromRequest();
$result=$newObj->save();
//var_dump($obj);

//$newObj->start();
$action="";
if (! stripos($result,'id="lastOperationStatus" value="ERROR"')>0
   and ! stripos($result,'id="lastOperationStatus" value="INVALID"')>0
   and isset($newObj->WorkElement)) {
  $action=$_REQUEST['action'];
  if ($action=='start') {
    $resultStartStop=$newObj->WorkElement->start();
  } else {
    $resultStartStop=$newObj->WorkElement->stop();
  }
  if  (! stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
    $result='<input type="hidden" id="lastSaveId" value="' . $newObj->id .'" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="OK" />';
  } else {
    $result=$resultStartStop;
  }
}
// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . formatResult($result,'') . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . formatResult($result, $action) . '</span>';
  // save the new object to session (modified status)
} else { 
	Sql::commitTransaction();
  echo '<span class="messageWARNING" >' . formatResult($result,'') . '</span>';
}

function formatResult($result, $action) {
  if ($action=='start') {
	  return i18n('workStarted') . $result;
  } else if ($action=='stop') {
    return i18n('workStopped')  . $result;
  } else {
    return $result;
  }
}	
?>