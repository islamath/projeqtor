<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

// Get the link info
if (! array_key_exists('originOriginType',$_REQUEST)) {
  throwError('originOriginType parameter not found in REQUEST');
}
$originOriginTypeObj=New Originable($_REQUEST['originOriginType']);
$originOriginType=$originOriginTypeObj->name;

if (! array_key_exists('originOriginId',$_REQUEST)) {
  throwError('originOriginId parameter not found in REQUEST');
}
$originOriginId=$_REQUEST['originOriginId'];

if (! array_key_exists('originRefType',$_REQUEST)) {
  throwError('originRefType parameter not found in REQUEST');
}
$originRefType=$_REQUEST['originRefType'];
if (! array_key_exists('originRefId',$_REQUEST)) {
  throwError('originRefId parameter not found in REQUEST');
}
$originRefId=$_REQUEST['originRefId'];

$originId=null;

Sql::beginTransaction();
// get the modifications (from request)
$critArray=array('refType'=>$originRefType,'refId'=>$originRefId);
$origin=SqlElement::getSingleSqlElementFromCriteria('Origin', $critArray);

$origin->originId=$originOriginId;
$origin->originType=$originOriginType;
$origin->refId=$originRefId;
$origin->refType=$originRefType;

$result=$origin->save();

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