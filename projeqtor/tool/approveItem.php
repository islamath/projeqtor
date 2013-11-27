<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

if (! array_key_exists('approverId',$_REQUEST)) {
  throwError('approverId parameter not found in REQUEST');
}
$approverId=$_REQUEST['approverId'];

$approver=new Approver($approverId);
$approver->approved=1;
$approver->approvedDate=date('Y-m-d H:i');
Sql::beginTransaction();
$result=$approver->save();

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . i18n('approved') . '<div style="display:none;">' . $result . '</div></span>';
} else {
	Sql::commitTransaction(); 
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>