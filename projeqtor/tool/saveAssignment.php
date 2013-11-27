<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

$assignmentId=null;
if (array_key_exists('assignmentId',$_REQUEST)) {
  $assignmentId=$_REQUEST['assignmentId'];
}
$assignmentId=trim($assignmentId);
if ($assignmentId=='') {
  $assignmentId=null;
}

// Get the assignment info
if (! array_key_exists('assignmentRefType',$_REQUEST)) {
  throwError('assignmentRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['assignmentRefType'];

if (! array_key_exists('assignmentRefId',$_REQUEST)) {
  throwError('assignmentRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['assignmentRefId'];

$idResource=null;
if (array_key_exists('assignmentIdResource',$_REQUEST)) {
  $idResource=$_REQUEST['assignmentIdResource'];
}

$idRole=null;
if (array_key_exists('assignmentIdRole',$_REQUEST)) {
  $idRole=$_REQUEST['assignmentIdRole'];
}

$cost=null;
if (array_key_exists('assignmentDailyCost',$_REQUEST)) {
  $cost=$_REQUEST['assignmentDailyCost'];
}

if (! array_key_exists('assignmentRate',$_REQUEST)) {
  throwError('assignmentRate parameter not found in REQUEST');
}
$rate=$_REQUEST['assignmentRate'];

if (! array_key_exists('assignmentAssignedWork',$_REQUEST)) {
  throwError('assignmentAssignedWork parameter not found in REQUEST');
}
$assignedWork=$_REQUEST['assignmentAssignedWork'];

if (! array_key_exists('assignmentRealWork',$_REQUEST)) {
  throwError('assignmentRealWork parameter not found in REQUEST');
}
$realWork=$_REQUEST['assignmentRealWork'];

if (! array_key_exists('assignmentLeftWork',$_REQUEST)) {
  throwError('assignmentLeftWork parameter not found in REQUEST');
}
$leftWork=$_REQUEST['assignmentLeftWork'];

if (! array_key_exists('assignmentPlannedWork',$_REQUEST)) {
  throwError('assignmentPlannedWork parameter not found in REQUEST');
}
$plannedWork=$_REQUEST['assignmentPlannedWork'];
if (! array_key_exists('assignmentComment',$_REQUEST)) {
  throwError('assignmentComment parameter not found in REQUEST');
}
$comment=$_REQUEST['assignmentComment'];

Sql::beginTransaction();
// get the modifications (from request)
$assignment=new assignment($assignmentId);
$oldCost=$assignment->dailyCost;

$assignment->refId=$refId;
$assignment->refType=$refType;
if (! $realWork && $idResource) {
  $assignment->idResource=$idResource;
}
$assignment->idRole=$idRole;
$assignment->dailyCost=$cost;
if (! $oldCost or $assignment->dailyCost!=$oldCost) {
  $assignment->newDailyCost=$cost;
}
$assignment->rate=$rate;
$assignment->assignedWork=Work::convertWork($assignedWork);
$assignment->realWork=Work::convertWork($realWork);
$assignment->leftWork=Work::convertWork($leftWork);
$assignment->plannedWork=Work::convertWork($plannedWork);
$assignment->comment=htmlEncodeJson($comment);

if (! $assignment->idProject) {
  $refObj=new $refType($refId);
  $assignment->idProject=$refObj->idProject;
}

$result=$assignment->save();

$elt=new $assignment->refType($assignment->refId);
if ($assignmentId) {
  $elt->sendMailIfMailable(false,false,false,false,false,false,false,false,false,false,true,false);
} else {
  $elt->sendMailIfMailable(false,false,false,false,false,false,false,false,false,true,false,false);
}
  
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