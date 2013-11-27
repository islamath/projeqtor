<?php
/** ===========================================================================
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$versionProjectId=null;
if (array_key_exists('affectationId',$_REQUEST)) {
  $affectationId=$_REQUEST['affectationId'];
}
$affectationId=trim($affectationId);
if ($affectationId=='') {
  $affectationId=null;
} 
if ($affectationId==null) {
  throwError('affectationId parameter not found in REQUEST');
}
Sql::beginTransaction();
$obj=new Affectation($affectationId);
$result=$obj->delete();

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