<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
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

// Get the assignment info
if (! array_key_exists('idExpense',$_REQUEST)) {
  throwError('idExpense parameter not found in REQUEST');
}
$idExpense=$_REQUEST['idExpense'];


$expenseDetailName=null;
if (array_key_exists('expenseDetailName',$_REQUEST)) {
  $expenseDetailName=$_REQUEST['expenseDetailName'];
}

$expenseDetailDate=null;
if (array_key_exists('expenseDetailDate',$_REQUEST)) {
  $expenseDetailDate=$_REQUEST['expenseDetailDate'];
}

$expenseDetailType=null;
if (array_key_exists('expenseDetailType',$_REQUEST)) {
  $expenseDetailType=$_REQUEST['expenseDetailType'];
}

$expenseDetailAmount=null;
if (array_key_exists('expenseDetailAmount',$_REQUEST)) {
  $expenseDetailAmount=$_REQUEST['expenseDetailAmount'];
}

$expenseDetailValue01=null;
$expenseDetailValue02=null;
$expenseDetailValue03=null;
$expenseDetailUnit01=null;
$expenseDetailUnit02=null;
$expenseDetailUnit03=null;
if (array_key_exists('expenseDetailValue01',$_REQUEST)) {
  $expenseDetailValue01=$_REQUEST['expenseDetailValue01'];
}
if (array_key_exists('expenseDetailValue02',$_REQUEST)) {
  $expenseDetailValue02=$_REQUEST['expenseDetailValue02'];
}
if (array_key_exists('expenseDetailValue03',$_REQUEST)) {
  $expenseDetailValue03=$_REQUEST['expenseDetailValue03'];
}
if (array_key_exists('expenseDetailUnit01',$_REQUEST)) {
  $expenseDetailUnit01=$_REQUEST['expenseDetailUnit01'];
}
if (array_key_exists('expenseDetailUnit02',$_REQUEST)) {
  $expenseDetailUnit02=$_REQUEST['expenseDetailUnit02'];
}
if (array_key_exists('expenseDetailUnit03',$_REQUEST)) {
  $expenseDetailUnit03=$_REQUEST['expenseDetailUnit03'];
}

Sql::beginTransaction();
// get the modifications (from request)
$expenseDetail=new ExpenseDetail($expenseDetailId);

$expenseDetail->idExpense=$idExpense; 
$expenseDetail->idExpenseDetailType=$expenseDetailType; 
$expenseDetail->name=$expenseDetailName;
//$expenseDetail->description;
$expenseDetail->expenseDate=$expenseDetailDate; 
$expenseDetail->amount=$expenseDetailAmount;
$expenseDetail->value01=$expenseDetailValue01;
$expenseDetail->value02=$expenseDetailValue02;
$expenseDetail->value03=$expenseDetailValue03;
$expenseDetail->unit01=$expenseDetailUnit01;
$expenseDetail->unit02=$expenseDetailUnit02;
$expenseDetail->unit03=$expenseDetailUnit03;

$expense=new Expense($idExpense);
$expenseDetail->idProject=$expense->id; 
$expenseDetail->idle=$expense->idle;

$result=$expenseDetail->save();

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