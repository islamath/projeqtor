<?php
// Artefact to avoid scriptLog display even if debug level = 4. Comment the line to have it displayed again.
$noScriptLog=true;
require_once "../tool/projeqtor.php";
// Save Audit
//Audit::updateAudit(); done on projeqtor.php

//scriptLog('   ->/tool/checkAlertToDisplay.php');
if (! array_key_exists('user',$_SESSION)) {
	echo "noUser";
	return;
}
$user=$_SESSION['user'];
$crit=array('idUser'=>$user->id,'readFlag'=>'0', 'idle'=>'0');
$alert=new Alert();
$lst=$alert->getSqlElementsFromCriteria($crit, false, null, 'id asc');
if (count($lst)==0) {
	return;
}
$date=date('Y-m-d H:i:s');
foreach($lst as $alert) {
	if ($alert->alertDateTime<=$date) {
	  echo '<b>' . htmlEncode($alert->title) . '</b>';
	  echo '<br/><br/>';
	  echo  $alert->message;
	  echo '<input type="hidden" id="idAlert" name="idAlert" value="' . $alert->id . ' " ./>';
	  echo '<input type="hidden" id="alertType" name="alertType" value="' . $alert->alertType . '" ./>';
	  return;
	}
}
