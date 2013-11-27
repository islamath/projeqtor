<?php
  /*$list=$audit->getSqlElementsFromCriteria(array("sessionId"=>session_id()));
  $result="";
  foreach($list as $audit) {
    $audit->requestDisconnection=1;     
    $res=$audit->save();
    if ($result=="" or stripos($res,'id="lastOperationStatus" value="OK"')>0) {
      $msgEnd=strpos($res,'<');
      $result=i18n('colRequestDisconnection');
    }
  }*/
  unset($_SESSION['user']);
  unset($_REQUEST['objectClass']);
  unset($_REQUEST['objectId']);
  $_REQUEST['lostConnection']=true;
  $clean=ob_get_clean ();
  session_destroy();
  include 'index.php';
  exit;
?>