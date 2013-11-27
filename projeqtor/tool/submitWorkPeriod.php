<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

if (! array_key_exists('action',$_REQUEST)) {
  throwError('action parameter not found in REQUEST');
}
$action=$_REQUEST['action'];

if (! array_key_exists('rangeType',$_REQUEST)) {
  throwError('rangeType parameter not found in REQUEST');
}
$rangeType=$_REQUEST['rangeType'];

if (! array_key_exists('rangeValue',$_REQUEST)) {
  throwError('rangeValue parameter not found in REQUEST');
}
$rangeValue=$_REQUEST['rangeValue'];

if (! array_key_exists('resource',$_REQUEST)) {
  throwError('resource parameter not found in REQUEST');
}
$resource=$_REQUEST['resource'];

Sql::beginTransaction();
// get the modifications (from request)
$period=new WorkPeriod();
$crit=array('idResource'=>$resource, 'periodRange'=>$rangeType,'periodValue'=>$rangeValue);
$period=SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', $crit);
if ($action=='submit') {
	$period->submitted=1;
	$period->submittedDate=date('Y-m-d H:i:s');
} if ($action=='unsubmit') {
  $period->submitted=0;
  $period->submittedDate=null;
} if ($action=='validate') {
  $period->validated=1;
  $period->validatedDate=date('Y-m-d H:i:s');
  $user=$_SESSION['user'];
  $period->idLocker=$user->id;
} if ($action=='unvalidate') {
	$period->validated=0;		
  $period->validatedDate=null;
}
$result=$period->save();

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>