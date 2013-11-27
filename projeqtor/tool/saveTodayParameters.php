<?php
/** ============================================================================
 * Save Today displayed info list
 */
require_once "../tool/projeqtor.php";

Sql::beginTransaction();
$user=$_SESSION['user'];
$crit=array('idUser'=>$user->id);
$today=new Today();
$todayList=$today->getSqlElementsFromCriteria($crit, false, 'sortOrder asc');
foreach ($todayList as $item) {
	if (isset($_REQUEST['dialogTodayParametersDelete' . $item->id]) and $_REQUEST['dialogTodayParametersDelete' . $item->id]=='1') {
		$item->delete();
	} else {
		if (isset($_REQUEST['dialogTodayParametersIdle' . $item->id])) {
			$item->idle=0;
		} else {
			$item->idle=1;
		}
		if (isset($_REQUEST['dialogTodayParametersOrder' . $item->id])) {
			$item->sortOrder=$_REQUEST['dialogTodayParametersOrder' . $item->id];
		}
		$item->save();
	}
}
// PeriodDays
$crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodDays');
$tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
if (isset($_REQUEST['todayPeriodDays'])) {
	$tp->parameterValue=$_REQUEST['todayPeriodDays'];
} else {
	$tp->parameterValue='';
}
$tp->save();

//PeriodNotSet
$crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodNotSet');
$tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
if (isset($_REQUEST['todayPeriodNotSet'])) {
  $tp->parameterValue=1;
} else {
  $tp->parameterValue=0;
}
$tp->save();

Sql::commitTransaction();

include "../view/today.php";
?>