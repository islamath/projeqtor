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
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
include "header.php";

$where=getAccesResctictionClause('Requirement',false);

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

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
  $where.=" and idTargetVersion=".Sql::fmtId($paramVersion);
} else {
  $lstVersion=SqlList::getList('Version','name',null,true);
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$lstType=SqlList::getList('RequirementType','name',null,true);

$req=new Requirement();
$lst=$req->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) exit;

echo '<table style="width:' . ((isset($outMode) and $outMode=='pdf')?'90':'95') . '%" align="center">';
echo '<tr>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProject') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProduct') . '</td>';
echo '<td class="reportTableHeader" style="width:12%" rowspan="2" >' . i18n('colIdVersion') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colType') . '</td>';
//echo '<td class="reportTableHeader" style="width:4%" rowspan="2" >' . i18n('colId') . '</td>';
//echo '<td class="reportTableHeader" style="width:12%" rowpan="2" >' . i18n('colReference') . '</td>';
echo '<td class="reportTableHeader" style="width:40%" colspan="2" rowspan="2" >' . i18n('Requirement') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" colspan="5" >' .  i18n('TestCase') . " / " . i18n('sectionProgress') . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountLinked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPlanned') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPassed') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountBlocked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountFailed') . '</td>';
echo '</tr>';
$sumPlanned=0;
$sumLinked=0;
$sumPassed=0;
$sumBlocked=0;
$sumFailed=0;
$cpt=0;
$sumReal='';
  
if ($paramDetail) {
  echo '<tr><td colspan="10" style="font-size:3px;">&nbsp;</td></tr>';
}
foreach ($lst as $req) {
 if ($req->idRequirementType and ! isset($lstType[$req->idRequirementType])) {
    $rtype=new RequirementType($req->idRequirementType);
    $lstType[$req->idRequirementType]=$rtype->name;
  }
  echo '<tr>';
  echo '<td class="reportTableData" style="width:8%">' . (($req->idProject)?$lstProject[$req->idProject]:'') . '</td>';
  echo '<td class="reportTableData" style="width:8%">' . (($req->idProduct)?$lstProduct[$req->idProduct]:'') . '</td>';
  echo '<td class="reportTableData" style="width:12%">' . (($req->idTargetVersion)?$lstVersion[$req->idTargetVersion]:'') . '</td>';
  echo '<td class="reportTableData" style="width:8%">' . (($req->idRequirementType)?$lstType[$req->idRequirementType]:'') . '</td>';
  echo '<td class="reportTableData" style="width:5%">#' . $req->id . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:35%;">' . htmlEncode($req->name) . '</td>';
  echo '<td class="reportTableData" style="width:5%">' . $req->countLinked . '</td>';
  echo '<td class="reportTableData" style="width:5%;">' . $req->countPlanned . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($req->countPassed and $req->countPassed==$req->countPlanned)?'color:green;':'') . '">' . $req->countPassed . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($req->countBlocked)?'color:orange;':'') . '">' . $req->countBlocked . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($req->countFailed)?'color:red;':'') . '">' . $req->countFailed . '</td>';
  echo '</tr>';
  $sumLinked+=$req->countLinked;
  $sumPlanned+=$req->countPlanned;
  $sumPassed+=$req->countPassed;
  $sumBlocked+=$req->countBlocked;
  $sumFailed+=$req->countFailed;
  $cpt+=1;
  if ($paramDetail) {
  	$link=new Link();
  	$crit=array('ref1Type'=>'Requirement', 'ref1Id'=>$req->id, 'ref2Type'=>'TestCase');
  	$lst=$link->getSqlElementsFromCriteria($crit, null, null, 'ref2id');
  	if (count($lst)>0) {
	  	echo '<tr><td style="width:8%"></td><td style="width:92%" colspan="10">';
	  	echo '<table style="width:100%">';
	  	echo '<tr>';
	  	echo '<td class="largeReportHeader" colspan="2" style="width:45%">' . i18n('TestCase') . '</td>';
	  	echo '<td class="largeReportHeader" colspan="2" style="width:40%">' . i18n('TestSession') . '</td>';
	  	echo '<td class="largeReportHeader" colspan="2" style="width:15%">' . i18n('colResult') . '</td>';
	  	echo '</tr>';
	  	
  	  foreach ($lst as $link) {
        $tcr=new TestCaseRun();
        $crit=array('idTestCase'=>$link->ref2Id);
        $lstTcr=$tcr->getSqlElementsFromCriteria($crit,true, false, 'idTestSession');
        foreach ($lstTcr as $tcr) {
        	echo '<tr>';
        	echo '<td class="largeReportData" style="width:5%" style="text-align: center;">#' . $tcr->idTestCase . '</td>';
        	echo '<td class="largeReportData style="width:40%"">' . SqlList::getNameFromId('TestCase',$tcr->idTestCase) . '</td>';
        	echo '<td class="largeReportData" style="width:5%" style="text-align: center;">' . (($tcr->idTestSession)?'#':'') . $tcr->idTestSession . '</td>';
        	echo '<td class="largeReportData" style="width:35%" >' . (($tcr->idTestSession)?SqlList::getNameFromId('TestSession', $tcr->idTestSession):'') . '</td>';
          $st=new RunStatus($tcr->idRunStatus);
        	echo '<td class="largeReportData" style="text-align: left;width:7%" >' . (($tcr->id)?colorNameFormatter(i18n($st->name) . '#split#' . $st->color):'') . '</td>';
        	echo '<td class="largeReportData" style="text-align: center;font-size:75%;width:8%" >' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
        	echo '</tr>';
        }
      }
      echo '<tr><td colspan="6" style="font-size:3px;" style="width:100%">&nbsp;</td></tr>';
      echo '</table>';
      echo '</td></tr>';
  	}
  }
}
echo '<tr>';
echo '<td colspan="6"></td>';
echo '<td class="largeReportHeader" >' . $sumLinked . '</td>';
echo '<td class="largeReportHeader" >' . $sumPlanned . '</td>';
echo '<td class="largeReportHeader" >' . $sumPassed . '</td>';
echo '<td class="largeReportHeader" >' . $sumBlocked . '</td>';
echo '<td class="largeReportHeader" >' . $sumFailed . '</td>';
echo '</tr>';
echo '</table>';
echo '<br/>';
