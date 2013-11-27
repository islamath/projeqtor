<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

// Get the link info
if (! array_key_exists('approverRefType',$_REQUEST)) {
  throwError('approverRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['approverRefType'];

if (! array_key_exists('approverRefId',$_REQUEST)) {
  throwError('approverRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['approverRefId'];

if (! array_key_exists('approverId',$_REQUEST)) {
  throwError('approverId parameter not found in REQUEST');
}
$approverId=$_REQUEST['approverId'];

$linkId=null;

$arrayId=array();
if (is_array($approverId)) {
	$arrayId=$approverId;
} else {
	$arrayId[]=$approverId;
}
Sql::beginTransaction();
$result="";
// get the modifications (from request)
foreach ($arrayId as $approverId) {
	$approver=new Approver();
  $approver->refId=$refId;
  $approver->refType=$refType;
  $approver->idAffectable=$approverId;
  $res=$approver->save();
  if (!$result) {
    $result=$res;
  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
  	if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  		$deb=stripos($res,'#');
  		$fin=stripos($res,' ',$deb);
  		$resId=substr($res,$deb, $fin-$deb);
  		$deb=stripos($result,'#');
      $fin=stripos($result,' ',$deb);
      $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
  	} else {
  	  $result=$res;
  	} 
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