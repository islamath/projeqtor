<?php
/** ===========================================================================
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$expenseDetailId=null;
if (array_key_exists('expenseDetailId',$_REQUEST)) {
  $expenseDetailId=$_REQUEST['expenseDetailId'];
}
$expenseDetailId=trim($expenseDetailId);
if ($expenseDetailId=='') {
  $expenseDetailId=null;
} 
if ($expenseDetailId==null) {
  throwError('expenseDetailId parameter not found in REQUEST');
}
Sql::beginTransaction();
$obj=new ExpenseDetail($expenseDetailId);

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