<?php
require_once "../tool/projeqtor.php";
if (! array_key_exists('user',$_SESSION)) {
	echo "noUser";
	return;
}
if (! array_key_exists('idAlert',$_REQUEST)) {
	return;
}
$remind=0;
if (array_key_exists('remind',$_REQUEST)) {
  $remind=$_REQUEST['remind'];
}
Sql::beginTransaction();
$idAlert=$_REQUEST['idAlert'];
$alert=new Alert($idAlert);
if ($remind) {
	$alert->alertDateTime= (addDelayToDatetime(date('Y-m-d H:i'), ($remind/60), 'HH'));
	$alert->readFlag='0';
} else {
  $alert->readFlag='1';
  $alert->alertReadDateTime=date('Y-m-d H:i:s');
}
$result=$alert->save();
Sql::commitTransaction();