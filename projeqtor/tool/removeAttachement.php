<?php
/** ===========================================================================
 * Delete the current attachement : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$attachementId=null;
if (array_key_exists('attachementId',$_REQUEST)) {
  $attachementId=$_REQUEST['attachementId'];
}
$attachementId=trim($attachementId);
if ($attachementId=='') {
  $attachementId=null;
} 
if ($attachementId==null) {
  throwError('attachementId parameter not found in REQUEST');
}
$obj=new Attachement($attachementId);
$subDirectory=str_replace('${attachementDirectory}', Parameter::getGlobalParameter('paramAttachementDirectory'), $obj->subDirectory);
if (file_exists($subDirectory . $obj->fileName)) {
  unlink($subDirectory . $obj->fileName);
  purgeFiles($subDirectory, null);
  rmdir($subDirectory);
}
Sql::beginTransaction();
$result=$obj->delete();

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