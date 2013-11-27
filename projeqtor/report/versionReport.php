<?php
include_once '../tool/projeqtor.php';
//echo 'versionReport.php';

if (! isset($includedReport)) {
  include("../external/pChart/pData.class");  
  include("../external/pChart/pChart.class");  
  
  $paramProject='';
  if (array_key_exists('idProject',$_REQUEST)) {
    $paramProject=trim($_REQUEST['idProject']);
  };
  
  $paramTicketType='';
  if (array_key_exists('idTicketType',$_REQUEST)) {
    $paramTicketType=trim($_REQUEST['idTicketType']);
  };
  
  $paramResponsible='';
  if (array_key_exists('responsible',$_REQUEST)) {
    $paramResponsible=trim($_REQUEST['responsible']);
  };
  
  $paramOtherVersion=false;
  if (array_key_exists('otherVersions',$_REQUEST)) {
    $paramOtherVersion=true;
  };
  
  $user=$_SESSION['user'];
  
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  if ($paramTicketType!="") {
    $headerParameters.= i18n("colIdTicketType") . ' : ' . htmlEncode(SqlList::getNameFromId('TicketType', $paramTicketType)) . '<br/>';
  }
  if ($paramResponsible!="") {
    $headerParameters.= i18n("colResponsible") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource', $paramResponsible)) . '<br/>';
  }
  if ($paramOtherVersion!="") {
    $headerParameters.= i18n("colOtherVersions") . ' : ' . i18n('displayYes') . '<br/>';
  }
  include "header.php";
}

$where=getAccesResctictionClause('Ticket',false);
if ($paramProject!="") {
  $where.=" and idProject='" . Sql::fmtId($paramProject) . "'";
}
if ($paramTicketType!="") {
  $where.=" and idTicketType='" . Sql::fmtId($paramTicketType) . "'";
}
if ($paramResponsible!="") {
  $where.=" and idResource='" . Sql::fmtId($paramResponsible) . "'";
}

$order="";

$ticket=new Ticket();
$lstTicket=$ticket->getSqlElementsFromCriteria(null,false, $where, $order);

$lstVersion=SqlList::getList('Version');
$lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
if ($paramTicketType!="") {
	$lstType=array($paramTicketType=>SqlList::getNameFromId('TicketType', $paramTicketType));
} else {
  $lstType=SqlList::getList('TicketType');
}

$arrType=array();
foreach($lstType as $code=>$name) {
  $arrType[$code]=array('name'=>$name,'count'=>0,'estimated'=>0,'real'=>0,'left'=>0);
}
$version=array();
foreach($lstVersion as $code=>$name) {
  $version[$code]=$arrType;
}
$version['0']=$arrType;

if (count($lstType)) {
  $medWidth=floor(65/count($lstType));
} else {
  $medWidth="65";
}

foreach ($lstTicket as $t) {
	$ticket=new Ticket($t->id);
	$vers=($t->idTargetVersion)?$t->idTargetVersion:'0';
  if (isset($version[$vers][$t->idTicketType])) {
	  $version[$vers][$t->idTicketType]['count']+=1;
    $version[$vers][$t->idTicketType]['estimated']+=$ticket->WorkElement->plannedWork;
    $version[$vers][$t->idTicketType]['real']+=$ticket->WorkElement->realWork;
    $version[$vers][$t->idTicketType]['left']+=$ticket->WorkElement->leftWork;
  }
  if ($paramOtherVersion) {
  	//$ot=new OtherVersion();
  	//$crit=array('refType'=>'Ticket', 'refId'=>$t->id, 'scope'=>'TargetVersion');
  	//$otList=$ot->getSqlElementsFromCriteria($crit);
  	foreach ($ticket->_OtherTargetVersion as $ot) {
  		$vers=($ot->idVersion)?$ot->idVersion:'0';
  	  if (isset($version[$vers][$t->idTicketType])) {
		    $version[$vers][$t->idTicketType]['count']+=1;
		    $version[$vers][$t->idTicketType]['estimated']+=$ticket->WorkElement->plannedWork;
		    $version[$vers][$t->idTicketType]['real']+=$ticket->WorkElement->realWork;
		    $version[$vers][$t->idTicketType]['left']+=$ticket->WorkElement->leftWork;
		  }  		
  	}
  }
  
}

if (checkNoData($lstTicket)) exit;
  
// title
echo '<table width="95%" align="center">';
echo '<tr><td class="reportTableHeader" rowspan="2" colspan="2">' . i18n('Version') . '</td>';
echo '<td colspan="' . (count($lstType)+1) . '" class="reportTableHeader">' . i18n('TicketType') . '</td>';
echo '</tr><tr>';
foreach ($lstType as $type) {
  echo '<td class="reportTableColumnHeader">' . $type . '</td>';
}
echo '<td class="reportTableHeader" >' . i18n('sum') . '</td>';
echo '</tr>';

$arrSum=$arrType;
foreach ($version as $idVersion=>$arrayVers) {
  echo '<tr><td style="font-size:25%;">&nbsp;</td></tr>';
  echo '<tr>';
  echo '<td class="reportTableLineHeader" style="width:10%;" rowspan="4">' . $lstVersion[$idVersion] . '</td>'; 
  // count
  $arrLines=array('count','estimated','real','left');
  foreach ($arrLines as $val) {
  	$sum=0;
  	if ($val!='count') {echo '<tr>';}
    echo '<td class="reportTableLineHeader" style="width:10%;" >' . i18n($val) . '</td>';
    foreach ($arrayVers as $idType=>$arrayType) {
  	  echo '<td class="reportTableData" style="width:' . $medWidth . '%;">' . $arrayType[$val] . '</td>';
  	  $sum+=$arrayType[$val];
    }
    echo '<td class="reportTableData" style="width:' . $medWidth . '%;">' . $sum . '</td></tr>';
  }  
}  
  
echo '</table>';
echo '<br/>';
 