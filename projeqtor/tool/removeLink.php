<?php
/** ===========================================================================
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$linkId=null;
if (array_key_exists('linkId',$_REQUEST)) {
  $linkId=$_REQUEST['linkId'];
}
$linkId=trim($linkId);
if ($linkId=='') {
  $linkId=null;
} 
if ($linkId==null) {
  throwError('linkId parameter not found in REQUEST');
}
Sql::beginTransaction();
$obj=new Link($linkId);
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