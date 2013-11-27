<?php
/** ===========================================================================
 * Move task (from before to)
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/movePlanningColumn.php');
if (! array_key_exists('orderedList',$_REQUEST)) {
  throwError('orderedList parameter not found in REQUEST');
}
$list=$_REQUEST['orderedList'];

$arrayList=explode("|", $list);
$user=$_SESSION['user'];

Sql::beginTransaction();
foreach ($arrayList as $order=>$col) {
	if (trim($col)) {
		$critArray=array('idUser'=>$user->id, 'parameterCode'=>'planningColumnOrder'.$col);
		$param=SqlElement::getSingleSqlElementFromCriteria('Parameter', $critArray);
		$param->parameterValue=$order+1;
		$result=$param->save();
	}
}
//$result="ERROR";
//$result.=" " . $idFrom . '->' . $idTo .'(' . $mode . ')';
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . '</span>';
} else { 
	Sql::commitTransaction();
  echo '<span class="messageWARNING" >' . '</span>';
}
?>