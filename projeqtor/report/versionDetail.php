<?php
include_once '../tool/projeqtor.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
};
  
$paramResponsible='';
if (array_key_exists('responsible',$_REQUEST)) {
  $paramResponsible=trim($_REQUEST['responsible']);
};
$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
  $paramVersion=trim($_REQUEST['idVersion']);
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
if ($paramResponsible!="") {
  $headerParameters.= i18n("colResponsible") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource', $paramResponsible)) . '<br/>';
}
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
if ($paramOtherVersion!="") {
    $headerParameters.= i18n("colOtherVersions") . ' : ' . i18n('displayYes') . '<br/>';
  }
include "header.php";

$where=getAccesResctictionClause('Ticket',false);

$order="";

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
} else {
  $lstVersion=SqlList::getList('Version');
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

if (checkNoData($lstVersion)) exit;

$lstObj=array(new Ticket(), new Activity(), new Milestone(), new Requirement(), new TestSession());

foreach ($lstVersion as $versId=>$versName) {
  echo '<table width="95%" align="center">';
  echo '<tr>';
  $version=new Version($versId);
  //$versDate = ' (' . htmlFormatDate(SqlList::getFieldFromId('Version', $versId, 'plannedEisDate')) . ')';
  //if ($versDate=='')
  echo '<td class="reportTableHeader" style="width:40%" colspan="3">' . htmlEncode($version->name) . '</td>';
  echo '<td class="largeReportHeader" style="width:10%;text-align:center;" rowspan="2">' . i18n('colIdStatus') . '</td>';
  echo '<td class="largeReportHeader" style="width:10%;text-align:center;" rowspan="2">' . i18n('colResponsible') . '</td>';
  echo '<td class="largeReportHeader" style="width:10%;text-align:center;" rowspan="2">' . i18n('colIdPriority') . '</td>';
  echo '<td class="largeReportHeader" colspan="4" style="width:20%;text-align:center;">' . i18n('colWork') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;" rowspan="2">' . i18n('colHandled') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;" rowspan="2">' . i18n('colDone') . '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="largeReportHeader" style="width:10%;text-align:center;">' . i18n('colId') . '</td>';
  echo '<td class="largeReportHeader" style="width:10%;text-align:center;">' . i18n('colType') . '</td>';
  echo '<td class="largeReportHeader" style="width:20%;text-align:center;">' . i18n('colName') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colInitial') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colReal') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colLeft') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . strtolower(i18n('sum')) . '</td>';
  echo '</tr>';
  $sumInitial='';
  $sumReal='';
  $sumLeft='';
  $sumPlanned='';
  $sumHandled='';
  $sumDone='';
  $cpt=0;
	foreach ($lstObj as $obj) {
		if (property_exists($obj, 'idTargetVersion')) {
			$crit="(".$obj->getDatabaseColumnName('idTargetVersion')."=$versId";
			$scope='TargetVersion';
		} else if (property_exists($obj, 'idVersion')) {
      $crit="(".$obj->getDatabaseColumnName('idVersion')."=$versId";
      $scope='Version';
			
		}
		if ($paramOtherVersion) {
			
      $vers=new OtherVersion();
      $crit.=" or exists (select 'x' from ".$vers->getDatabaseTableName()." VERS "
        ." where VERS.refType='".get_class($obj)."' and VERS.refId=".$obj->getDatabaseTableName().".id and scope='".$scope."'"
        ." and VERS.idVersion=".$versId
        .")";
		}
	  $crit.=')';
	  if ($paramResponsible) {
	    $crit.=" and ".$obj->getDatabaseColumnName('idResource')."=$paramResponsible";
	  }
	  if ($paramProject) {
	    $crit.=" and ".$obj->getDatabaseColumnName('idProject')."=$paramProject";
	  }
    $lst=$obj->getSqlElementsFromCriteria(null,null,$crit);
    $type='id'.get_class($obj).'Type';
    foreach ($lst as $item) {
      $class=get_class($item);
      $item=new $class($item->id);
      $initial=0;
      $real='';
      $left='';
      $planned='';
      $cpt++;
      $pe=get_class($item).'PlanningElement';
      if (isset($item->WorkElement)) {
        $initial=$item->WorkElement->plannedWork;
        $real=$item->WorkElement->realWork;
        $left=$item->WorkElement->leftWork;
        $planned=$real+$left;
      } else if (isset($item->$pe)) {
        $initial=$item->$pe->assignedWork;
        $real=$item->$pe->realWork;
        $left=$item->$pe->leftWork;
        $planned=$real+$left;
      }
      echo '<tr>';
      echo '<td class="largeReportData" style="text-align: center;width:10%">' . i18n(get_class($item)) . ' #' . $item->id . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:10%">' . SqlList::getNameFromId('Type',$item->$type) . '</td>';
      echo '<td class="largeReportData" style="width:20%;text-align:left;">' . htmlEncode($item->name) . '</td>';
      echo '<td class="largeReportData" style="width:10%">' . (($item->idStatus)?formatColor('Status', $item->idStatus):'') . '</td>';
      echo '<td class="largeReportData" style="text-align:left;text-align: center;width:10%">' . SqlList::getNameFromId('Resource',$item->idResource) . '</td>';
      echo '<td class="largeReportData" style="width:100px">' . ((isset($item->idPriority))?formatColor('Priority', $item->idPriority):'') . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%">' .  Work::displayWorkWithUnit($initial) . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%">' .  Work::displayWorkWithUnit($real) . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%">' .  Work::displayWorkWithUnit($left) . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%">' .  Work::displayWorkWithUnit($planned) . '</td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%"><img style="width: 10px" src="../view/img/checked' . (($item->handled)?'OK':'KO') . '.png" /></td>';
      echo '<td class="largeReportData" style="text-align: center;width:5%"><img style="width: 10px" src="../view/img/checked' . (($item->done)?'OK':'KO') . '.png" /></td>';
      echo '</tr>';
      $sumInitial+=$initial;
      $sumReal+=$real;
      $sumLeft+=$left;
      $sumPlanned+=$planned;
      $sumHandled+=($item->handled)?1:0;
      $sumDone+=($item->done)?1:0;
    }
  }
  $progress=0;
  if ($sumPlanned>0) {
    $progress=round($sumReal/$sumPlanned*100,0);
  }
  echo '<tr>';
  echo '<td class="reportTableHeader" colspan="2">' . i18n('sum') . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . $cpt . '</td>';
  echo '<td class="largeReportHeader" colspan="3" style="text-align:center;">' . i18n('progress') . ' : ' . $progress . '%</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . Work::displayWorkWithUnit($sumInitial) . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . Work::displayWorkWithUnit($sumReal) . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . Work::displayWorkWithUnit($sumLeft) . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . Work::displayWorkWithUnit($sumPlanned) . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . $sumHandled . '</td>';
  echo '<td class="largeReportHeader" style="text-align:center;">' . $sumDone . '</td>';
  echo '</tr>';
  echo '</table>';
  echo '<br/>';
}