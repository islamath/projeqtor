<?php
require_once "../tool/projeqtor.php";
scriptLog("adminFunctionalities.php");
if (array_key_exists('adminFunctionality', $_REQUEST)) {
	$adminFunctionality=$_REQUEST['adminFunctionality'];
}
if (! isset($adminFunctionality)) {
	echo "ERROR - functionality not defined";
	return;
}
Sql::beginTransaction();
$nbDays=(array_key_exists('nbDays', $_REQUEST))?$_REQUEST['nbDays']:'';
if ($adminFunctionality=='sendAlert') {
	$result=sendAlert();
} else if ($adminFunctionality=='maintenance') {
	$result=maintenance();
} else if ($adminFunctionality=='updateReference') {
	$element=null;
	if (array_key_exists('element', $_REQUEST)) {
	  $element=$_REQUEST['element'];
	}
	if ($element=='*') {
		$element=null;
	}	else {
		if (intval($element)>0) {
			$elt=new Referencable($element);
			$element=$elt->name;
		}
	}
	$result=updateReference($element);
} else if ($adminFunctionality=='disconnectAll') {
  $audit=new Audit();
  $list=$audit->getSqlElementsFromCriteria(array("idle"=>"0"));
  $result="";
  foreach($list as $audit) {
  	if ($audit->sessionId!=session_id()) {
      $audit->requestDisconnection=1;     
  	} 
  	$res=$audit->save();
  	if ($result=="" or stripos($res,'id="lastOperationStatus" value="OK"')>0) {
  		$msgEnd=strpos($res,'<');
      $result=i18n('colRequestDisconnection').substr($res,$msgEnd);
  	}
  }
} else if ($adminFunctionality=='setApplicationStatusTo') { 
	$newStatus=$_REQUEST['newStatus'];
	$crit=array('idUser'=>null, 'idProject'=>null, 'parameterCode'=>'applicationStatus');
  $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
  $obj->parameterValue=$newStatus;
  $result=$obj->save();
  $param=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>null, 'idProject'=>null, 'parameterCode'=>'msgClosedApplication'));
  $param->parameterValue=$_REQUEST['msgClosedApplication'];
  $param->save();
  Parameter::clearGlobalParameters();
} else {
	$result="ERROR - functionality '$adminFunctionality' not defined";
}

// Message for result
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
	Sql::commitTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}


function sendAlert(){
  $alertSendTo=(array_key_exists('alertSendTo', $_REQUEST))?$_REQUEST['alertSendTo']:'';
  $alertSendDate=(array_key_exists('alertSendDate', $_REQUEST))?$_REQUEST['alertSendDate']:'';
  $alertSendTime=(array_key_exists('alertSendTime', $_REQUEST))?$_REQUEST['alertSendTime']:'';
  $alertSendType=(array_key_exists('alertSendType', $_REQUEST))?$_REQUEST['alertSendType']:'';
  $alertSendTitle=(array_key_exists('alertSendTitle', $_REQUEST))?$_REQUEST['alertSendTitle']:'';
  $alertSendMessage=(array_key_exists('alertSendMessage', $_REQUEST))?$_REQUEST['alertSendMessage']:'';
  $ctrl="";
  if (! trim($alertSendTitle)) {
    $ctrl.= i18n("messageMandatory", array(i18n('colTitle'))).'<br/>';
  }
  if (! trim($alertSendMessage)) {
   $ctrl.=i18n("messageMandatory", array(i18n('colMessage'))).'<br/>';
  }
  if ($ctrl) {
  	$returnValue= $ctrl;
    $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
    return $returnValue;
  }
  $lstUser=array();
  if ($alertSendTo=='*') {
    $lstUser=SqlList::getList('User');
  } else {
 	  $lstUser[$alertSendTo]='';
  }
  //Sql::beginTransaction();
  foreach ($lstUser as $id=>$name) {
 	  $alert=new Alert();
 	  $alert->idUser=$id;
    $alert->alertType=$alertSendType;
    $alert->alertInitialDateTime=$alertSendDate . " " . substr($alertSendTime,1);
    $alert->alertDateTime=$alertSendDate . " " . substr($alertSendTime,1);
    $alert->title=ucfirst(i18n($alertSendType)) . ' - ' . $alertSendTitle;
    $alert->message=$alertSendMessage;  
    $alert->save();
  }
  $returnValue= i18n('sentAlertTo',array(count($lstUser)));
  $returnValue .= '<input type="hidden" id="lastOperation" value="insert" />';
  $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
  //Sql::commitTransaction();
  return $returnValue;
}

function maintenance() {
	$operation=(array_key_exists('operation', $_REQUEST))?$_REQUEST['operation']:'';
	$item=(array_key_exists('item', $_REQUEST))?$_REQUEST['item']:'';
	$nbDays=(array_key_exists('nbDays', $_REQUEST))?$_REQUEST['nbDays']:'';
	$ctrl="";
  if (! trim($operation) or ($operation!='delete' and $operation!='close')) {
    $ctrl.='ERROR<br/>';
  }
  if (! trim($item) or ($item!='Alert' and $item!='Mail' and $item!='Audit')) {
    $ctrl.='ERROR<br/>';
  }
  if ( trim($nbDays)=='' or (intval($nbDays)=='0' and $nbDays!='0')) {
    $ctrl.= i18n("messageMandatory", array(i18n('days'))) .'<br/>';
  }
  //echo '|'.$operation.'|'.$item.'|'.intval($nbDays).'|';
  if ($ctrl) {
    $returnValue= $ctrl;
    $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
    return $returnValue;
  }
  $targetDate=addDaysToDate(date('Y-m-d'), (-1)*$nbDays ) . ' ' . date('H:i');
  $obj=new $item();
  $clauseWhere="1=0";
  if ($item=="Alert") {
  	$clauseWhere="alertInitialDateTime<'" . $targetDate . "'"; 
  } else if ($item=="Mail") {
  	$clauseWhere="mailDateTime<'" . $targetDate . "'";
  } else if ($item=="Audit") {
    $clauseWhere="disconnection<'" . $targetDate . "'";
   
  }
  if ($operation=="close") {
  	return $obj->close($clauseWhere);
  }
  if ($operation=="delete") {
    return $obj->purge($clauseWhere);
  }
}

function updateReference($element) {
	$arrayElements=array();
	if ($element) {
		$arrayElements[]=ucfirst($element);
	} else {
		$list=SqlList::getListNotTranslated('Referencable');
		foreach ($list as $ref) {		
			$arrayElements[]=$ref;
		}
	}
	foreach ($arrayElements as $elt) {
		$obj=new $elt();
		$request="update " . $obj->getDatabaseTableName() . " set reference=null";
		SqlDirectElement::execute($request); 
		$lst=$obj->getSqlElementsFromCriteria(null, false);
	  foreach ($lst as $object) {
		  $object->setReference(true);
		}
	}
	$element=(!$element)?'all':$element;
	$returnValue=i18n('updatedReference',array(i18n($element)));	
	$returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
  $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
  return $returnValue;
}