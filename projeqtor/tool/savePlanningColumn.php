<?php
/** ============================================================================
 * Save some information about planning columns status.
 */
require_once "../tool/projeqtor.php";

Sql::beginTransaction();
$user=$_SESSION['user'];
$action=$_REQUEST['action'];
if ($action=='status') {
  $status=$_REQUEST['status'];
  $item=$_REQUEST['item'];
  $crit=array('idUser'=>$user->id, 'idProject'=>null, 'parameterCode'=>'planningHideColumn'.$item);
  $param=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
  if ($param and $param->id) {
  	if ($status=='hidden') {
  		$param->parameterValue='1';
  		$param->save();
  	} else {
  		$param->delete();
  	}
  } else {
  	if ($status=='hidden') {
  		$param=new Parameter();
  		$param->idUser=$user->id;
  		$param->idProject=null;
  		$param->parameterCode='planningHideColumn'.$item;
  		$param->parameterValue='1';
  		$param->save();
  	}
  }
}  
Sql::commitTransaction();
?>