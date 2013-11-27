<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveResourceCost.php');
$id=null;
if (array_key_exists('resourceCostId',$_REQUEST)) {
  $id=trim($_REQUEST['resourceCostId']);
}
if ($id=='') {
  $id=null;
}

// Get the assignment info
if (! array_key_exists('resourceCostIdResource',$_REQUEST)) {
  throwError('resourceCostIdResource parameter not found in REQUEST');
}
$idResource=$_REQUEST['resourceCostIdResource'];



$idRole=null;
if (array_key_exists('resourceCostIdRole',$_REQUEST)) {
  $idRole=$_REQUEST['resourceCostIdRole'];
}

if (! array_key_exists('resourceCostValue',$_REQUEST)) {
  throwError('resourceCostValue parameter not found in REQUEST');
}
$value=$_REQUEST['resourceCostValue'];

$startDate=null;
if (array_key_exists('resourceCostStartDate',$_REQUEST)) {
  $startDate=trim($_REQUEST['resourceCostStartDate']);
}
if ($startDate=='') {
  $startDate=null;
}

Sql::beginTransaction();
// get the modifications (from request)
$rc=new ResourceCost($id);

$rc->id=$id;
$rc->idResource=$idResource;
if ($idRole) {
  $rc->idRole=$idRole;
}
$rc->cost=$value;
if ($startDate) {
  $rc->startDate=$startDate;
}
$result=$rc->save();

$rcb = new ResourceCost($id);

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