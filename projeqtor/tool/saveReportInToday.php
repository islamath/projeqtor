<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the note info
if (! array_key_exists('reportId',$_REQUEST)) {
  throwError('reportId parameter not found in REQUEST');
}
$reportId=$_REQUEST['reportId'];

Sql::beginTransaction();
$item=new Today();
$user=$_SESSION['user'];
$item->idUser=$user->id;
$item->scope='report';
$item->idReport=$reportId;
$item->staticSection=null;
$item->idle=0;
$lst=$item->getSqlElementsFromCriteria(array('idUser'=>$user->id));
$item->sortOrder=count($lst)+1;
$result=$item->save();
$rpt=new Report($reportId);
$params=TodayParameter::returnReportParameters($rpt,true);
foreach ($params as $pName=>$pValue) {
	$reqValue='';
	if (isset($_REQUEST[$pName])) {
		$reqValue=$_REQUEST[$pName];
	}
	if (trim($reqValue)!=trim($pValue)) {
		$tp=new TodayParameter();
		$tp->idUser=$item->idUser;
		$tp->idReport=$item->idReport;
		$tp->idToday=$item->id;
		$tp->parameterName=$pName;
		$tp->parameterValue=$reqValue;
		$res=$tp->save();		
	}
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