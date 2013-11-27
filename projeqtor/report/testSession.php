<?php
include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
};
  
$paramProduct='';
if (array_key_exists('idProduct',$_REQUEST)) {
  $paramProduct=trim($_REQUEST['idProduct']);
};
$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
  $paramVersion=trim($_REQUEST['idVersion']);
};
$paramSession='';
if (array_key_exists('idTestSession',$_REQUEST)) {
  $paramSession=trim($_REQUEST['idTestSession']);
};
$paramDetail=false;
if (array_key_exists('showDetail',$_REQUEST)) {
  $paramDetail=trim($_REQUEST['showDetail']);
}

$user=$_SESSION['user'];
  
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProduct!="") {
  $headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
}
if ($paramSession!="") {
  $headerParameters.= i18n("colIdTestSession") . ' : ' . htmlEncode(SqlList::getNameFromId('TestSession', $paramSession)) . '<br/>';
}
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
include "header.php";

$where=getAccesResctictionClause('TestSession',false);

$order="";

if ($paramProject) {
  $lstProject=array($paramProject=>SqlList::getNameFromId('Project',$paramProject));
  $where.=" and idProject=".Sql::fmtId($paramProject);
} else {
  $lstProject=SqlList::getList('Project','name',null,true);
  $lstProject[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramProduct) {
  $lstProduct=array($paramProduct=>SqlList::getNameFromId('Product',$paramProduct));
  $where.=" and idProduct=".Sql::fmtId($paramProduct);
} else {
  $lstProduct=SqlList::getList('Product','name',null,true);
  $lstProduct[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramSession) {
  $lstSession=array($paramSession=>SqlList::getNameFromId('TestSession',$paramSession));
  $where.=" and id=".Sql::fmtId($paramSession);
} else {
  $lstSession=SqlList::getList('TestSession');
  $lstSession[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
  $where.=" and idVersion=".Sql::fmtId($paramVersion);
} else {
  $lstVersion=SqlList::getList('Version','name',null,true);
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$lstType=SqlList::getList('TestSessionType','name',null,true);

$ts=new TestSession();
$lst=$ts->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) exit;

foreach ($lst as $ts) {
	$tcr=new TestCaseRun();
  if ($ts->idTestSessionType and ! isset($lstType[$ts->idTestSessionType])) {
    $tstype=new TestCaseType($ts->idTestSessionType);
    $lstType[$ts->idTestSessionType]=$tstype->name;
  }
	
  $crit=array('idTestSession'=>$ts->id);
  $lstTcr=$tcr->getSqlElementsFromCriteria($crit,true, false, 'idTestCase');
  echo '<table style="width:' . ((isset($outMode) and $outMode=='pdf')?'90':'95') . '%" align="center">';
	echo '<tr>';
	echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProject') . '</td>';
	echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProduct') . '</td>';
	echo '<td class="reportTableHeader" style="width:10%" rowspan="2" >' . i18n('colIdVersion') . '</td>';
	echo '<td class="reportTableHeader" style="width:9%" rowspan="2" >' . i18n('colType') . '</td>';
	echo '<td class="reportTableHeader" style="width:40%" colspan="2" rowspan="2" >' . i18n('TestSession') . '</td>';
	echo '<td class="reportTableHeader" style="width:25%" colspan="5" >' .  i18n('TestCase') . " / " . i18n('sectionProgress') . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountTotal') . '</td>';
	echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPlanned') . '</td>';
	echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPassed') . '</td>';
	echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountBlocked') . '</td>';
	echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountFailed') . '</td>';
	echo '</tr>';
  echo '<tr>';
  echo '<td class="reportTableData" style="width:8%">' . (($ts->idProject)?$lstProject[$ts->idProject]:'') . '</td>';
  echo '<td class="reportTableData" style="width:8%">' . (($ts->idProduct)?$lstProduct[$ts->idProduct]:'') . '</td>';
  echo '<td class="reportTableData" style="width:10%">' . (($ts->idVersion)?$lstVersion[$ts->idVersion]:'') . '</td>';
  echo '<td class="reportTableData" style="width:9%">' . (($ts->idTestSessionType)?$lstType[$ts->idTestSessionType]:'') . '</td>';
  echo '<td class="reportTableData" style="width:5%">#' . $ts->id . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:35%;">' . htmlEncode($ts->name) . '</td>';
  echo '<td class="reportTableData" style="width:5%">' . $ts->countTotal . '</td>';
  echo '<td class="reportTableData" style="width:5%">' . ($ts->countTotal-$ts->countPassed-$ts->countBlocked-$ts->countFailed) . '</td>';
  echo '<td class="reportTableData" style=""width:5%;' . (($ts->countPassed and $ts->countPassed==$ts->countTotal)?'color:green;':'') . '">' . $ts->countPassed . '</td>';
  echo '<td class="reportTableData" style=""width:5%;' . (($ts->countBlocked)?'color:orange;':'') . '">' . $ts->countBlocked . '</td>';
  echo '<td class="reportTableData" style=""width:5%;' . (($ts->countFailed)?'color:red;':'') . '">' . $ts->countFailed . '</td>';
  echo '</tr>';
  if (count($lstTcr)>0) {
  	echo '<tr><td></td><td colspan="10">';
  	echo '<table style="width:100%">';
  	echo '<tr>';
  	echo '<td class="largeReportHeader" colspan="2" style="width:' . (($paramDetail)?'25':'85') . '%">' . i18n('TestCase') . '</td>';
  	echo '<td class="largeReportHeader" colspan="2" style="width:15%">' . i18n('colResult') . '</td>';
  	if ($paramDetail) {
  		echo '<td class="largeReportHeader" style="width:20%">' . i18n('colDescription') . '</td>';
  		echo '<td class="largeReportHeader" style="width:20%">' . i18n('colPrerequisite') . '</td>';
  		echo '<td class="largeReportHeader" style="width:20%">' . i18n('colExpectedResult') . '</td>';
  	} else {
  		echo '<td style="width:0%" colspan="3"></td>';
  	}
  	echo '</tr>';
    foreach ($lstTcr as $tcr) {
      $tc=new TestCase($tcr->idTestCase);
    	echo '<tr>';
      echo '<td class="largeReportData" style="text-align: center;width:5%">' . (($tc->id)?'#':'') . $tc->id . '</td>';
      echo '<td class="largeReportData" style="width:' . (($paramDetail)?'20':'80') . '%" >' . htmlEncode($tc->name) . '</td>';
      $st=new RunStatus($tcr->idRunStatus);
      echo '<td class="largeReportData" style="text-align: left;width:7%" >' . (($tcr->id)?colorNameFormatter(i18n($st->name) . '#split#' . $st->color):'') . '</td>';
      echo '<td class="largeReportData" style="text-align: center;font-size:75%;width:8%" >' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
      if ($paramDetail) {
        echo '<td class="largeReportData" style="width:20%">' . htmlEncode($tc->description,'print') . '</td>';
        echo '<td class="largeReportData" style="width:20%">' . htmlEncode($tc->prerequisite,'print') . '</td>';
        echo '<td class="largeReportData" style="width:20%">' . htmlEncode($tc->result,'print') . '</td>';
      } else {
        echo '<td style="width:0%" colspan="3"></td>';
      }
      echo '</tr>';
    
    }

    echo '<tr><td colspan="6" style="font-size:3px;">&nbsp;</td></tr>';
    echo '</table>';
    echo '</td></tr>';
  }
  echo '</table>';
  echo '<br/>'; 
  
}

