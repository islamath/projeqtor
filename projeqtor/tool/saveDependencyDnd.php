<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

// Get the link info
if (! array_key_exists('ref1Type',$_REQUEST)) {
  throwError('ref1Type parameter not found in REQUEST');
}
$ref1Type=$_REQUEST['ref1Type'];

if (! array_key_exists('ref1Id',$_REQUEST)) {
  throwError('ref1Id parameter not found in REQUEST');
}
$ref1Id=$_REQUEST['ref1Id'];

if (! array_key_exists('ref2Type',$_REQUEST)) {
  throwError('ref2Type parameter not found in REQUEST');
}
$ref2Type=$_REQUEST['ref2Type'];

if (! array_key_exists('ref2Id',$_REQUEST)) {
  throwError('ref2Id parameter not found in REQUEST');
}
$ref2Id=$_REQUEST['ref2Id'];

$dependencyDelay=0;
if (array_key_exists('dependencyDelay',$_REQUEST)) {
  $dependencyDelay=$_REQUEST['dependencyDelay'];
}
Sql::beginTransaction();
$result="";
$critPredecessor=array("refType"=>$ref1Type,"refId"=>$ref1Id);
$critSuccessor=array("refType"=>$ref2Type,"refId"=>$ref2Id);

$successor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critSuccessor);
$predecessor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critPredecessor);;
		
$dep=new Dependency();
$dep->successorId=$successor->id;
$dep->successorRefType=$successor->refType;
$dep->successorRefId=$successor->refId;
$dep->predecessorId=$predecessor->id;
$dep->predecessorRefType=$predecessor->refType;
$dep->predecessorRefId=$predecessor->refId;
$dep->dependencyType='E-S';
$dep->dependencyDelay=$dependencyDelay;
$result=$dep->save();

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