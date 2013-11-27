<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveAffectation.php');
// Get the info
if (! array_key_exists('affectationId',$_REQUEST)) {
  throwError('affectationId parameter not found in REQUEST');
}
$id=($_REQUEST['affectationId']);

$idTeam=null;
if (array_key_exists('affectationIdTeam',$_REQUEST)) {
	$idTeam=$_REQUEST['affectationIdTeam'];
}

if (! array_key_exists('affectationProject',$_REQUEST)) {
  throwError('affectationProject parameter not found in REQUEST');
}
$project=($_REQUEST['affectationProject']);

if (! array_key_exists('affectationResource',$_REQUEST) and !$idTeam) {
  throwError('affectationResource parameter not found in REQUEST');
}
$resource=($_REQUEST['affectationResource']);

if (! array_key_exists('affectationRate',$_REQUEST)) {
  throwError('affectationRate parameter not found in REQUEST');
}
$rate=($_REQUEST['affectationRate']);

$idle=false;
if (array_key_exists('affectationIdle',$_REQUEST)) {
  $idle=1;
}
Sql::beginTransaction();
if (! $idTeam) {
	$affectation=new Affectation($id);
	
	$affectation->idProject=$project;
	$affectation->idResource=$resource;
	
	$affectation->idle=$idle;
	$affectation->rate=$rate;
	
	$result=$affectation->save();
} else {
	$crit=array('idTeam'=>$idTeam);
	$ress=new Resource();
	$list=$ress->getSqlElementsFromCriteria($crit, false);
	$nbAff=0;
	foreach ($list as $ress) {
		$affectation=new Affectation($id);
    $affectation->idProject=$project;
    $affectation->idResource=$ress->id;
    $affectation->idle=$idle;
    $affectation->rate=$rate;
    $res=$affectation->save();
    if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
      $nbAff++;
	  }
	}
	if ($nbAff) {
    $result='<b>' . i18n('menuAffectation') . ' ' . i18n('resultInserted') . ' : ' . $nbAff . '</b>';
    $result .= '<input type="hidden" id="lastSaveId" value="" />';
    $result .= '<input type="hidden" id="lastOperation" value="insert" />';
    $result .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
	} else {
		$result=i18n('Affectation') . ' ' . i18n('resultInserted') . ' : 0';
    $result .= '<input type="hidden" id="lastSaveId" value="" />';
    $result .= '<input type="hidden" id="lastOperation" value="control" />';
    $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
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