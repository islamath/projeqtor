<?php
/** ===========================================================================
 * Acknowledge an operation
 */
if (array_key_exists('resultAck',$_REQUEST)) {
  $result=$_REQUEST['resultAck'];
  $result=str_replace('\"','"',$result);
  $result=str_replace("\'","'",$result);
  echo $result;
} else if (array_key_exists('resultAckDocumentVersion',$_REQUEST)) {
  $result=$_REQUEST['resultAckDocumentVersion'];
  $result=str_replace('\"','"',$result);
  $result=str_replace("\'","'",$result);
  echo $result;
} else {
	echo("ack type not recognized");
  echo 'errorAck';
}
?>
