<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

// Get the otherVersion info
if (! array_key_exists('otherVersionRefType',$_REQUEST)) {
  throwError('otherVersionRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['otherVersionRefType'];
if (! array_key_exists('otherVersionRefId',$_REQUEST)) {
  throwError('otherVersionRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['otherVersionRefId'];
if (! array_key_exists('otherVersionIdVersion',$_REQUEST)) {
  throwError('otherVersionIdVersion parameter not found in REQUEST');
}
$versionId=$_REQUEST['otherVersionIdVersion'];
if (! array_key_exists('otherVersionType',$_REQUEST)) {
  throwError('otherVersionType parameter not found in REQUEST');
}
$scope=$_REQUEST['otherVersionType'];
$comment="";
if (array_key_exists('otherVersionComment',$_REQUEST)) {
    $comment=$_REQUEST['otherVersionComment'];
}
$user=$_SESSION['user'];
$arrayId=array();
if (is_array($versionId)) {
	$arrayId=$versionId;
} else {
	$arrayId[]=$versionId;
}
sort($arrayId,SORT_NUMERIC);
Sql::beginTransaction();
$result="";
// get the modifications (from request)
$obj=new $refType($refId);
$objVersionFld='id'.$scope;
$updatedMain=false;
foreach ($arrayId as $versId) {
	$crit=array('refType'=>$refType, 'refId'=>$refId, 'idVersion'=>$versId, 'scope'=>$scope);
	$otherVersion=SqlElement::getSingleSqlElementFromCriteria('OtherVersion', $crit);
	if (! $obj->$objVersionFld) {
		$obj->$objVersionFld=$versId;
		$result=$obj->save();
		$updatedMain=true;
		if ($otherVersion and $otherVersion->id) {
			$otherVersion->delete();
		}
	} else {
		if ((! $otherVersion or ! $otherVersion->id) and $versId!=$obj->$objVersionFld) {
			$otherVersion=new OtherVersion();
			$otherVersion->refType=$refType;
			$otherVersion->refId=$refId;
			$otherVersion->idVersion=$versId;
			$otherVersion->scope=$scope;
		  $otherVersion->comment=$comment;
		  $otherVersion->idUser=$user->id;
		  $otherVersion->creationDate=date("Y-m-d H:i:s"); 
		  $res=$otherVersion->save();
		  if (!$result) {
		    $result=$res;
		  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 and !$updatedMain) {
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
	}
}
if (!$result) {
	$result = i18n('messageNoChange');
	$result .= '<input type="hidden" id="lastSaveId" value="' . $refId . '" />';
	$result .= '<input type="hidden" id="lastOperation" value="update" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="NO_CHANGE" />';
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