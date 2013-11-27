<?php
require_once "../tool/projeqtor.php";

if (! array_key_exists('idAudit',$_REQUEST)) {
	throwError('idAudit parameter not found in SESSION');
}
$idAudit=$_REQUEST['idAudit'];

$audit=new Audit($idAudit);

Sql::beginTransaction();
$audit->requestDisconnection=1;
$result=$audit->save();
$msgEnd=strpos($result,'<');
$result=i18n('colRequestDisconnection').substr($result,$msgEnd);
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