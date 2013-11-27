<?php
/** ===========================================================================
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$id=null;
if (array_key_exists('otherVersionId',$_REQUEST)) {
  $id=$_REQUEST['otherVersionId'];
}
$id=trim($id);
if ($id=='') {
  $id=null;
} 
if ($id==null) {
  throwError('linkId parameter not found in REQUEST');
}
Sql::beginTransaction();
$vers=new OtherVersion($id);
$refType=$vers->refType;
$refId=$vers->refId;
$scope=$vers->scope;
$fld='id'.$vers->scope;
$fldArray='_Other'.$vers->scope;
$obj=new $refType($refId);
$mainVers=$obj->$fld;
$otherVers=$vers->idVersion;
// save new main
$obj->$fld=$otherVers;
$result=$obj->save();
// save new other
$vers=new OtherVersion();
$vers->refType=$refType;
$vers->refId=$refId;
$vers->scope=$scope;
$vers->creationDate=date('Y-m-d H:i:s');
$user=$_SESSION['user'];
$vers->idUser=$user->id;
$vers->idVersion=$mainVers;
$res=$vers->save();
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