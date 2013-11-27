<?php
include_once '../tool/projeqtor.php';
//echo "workDetail.php";

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
}
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
}
$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
  $paramYear=$_REQUEST['yearSpinner'];
};

$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
  $paramMonth=$_REQUEST['monthSpinner'];
};

$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
  $paramWeek=$_REQUEST['weekSpinner'];
};

$user=$_SESSION['user'];

$periodType=$_REQUEST['periodType'];
$periodValue=$_REQUEST['periodValue'];

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . htmlEncode(SqlList::getNameFromId('Team', $paramTeam)) . '<br/>';
}
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
include "header.php";

$where=getAccesResctictionClause('Activity',false);
//$where="1=1 ";
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
if ($paramProject!='') {
  $where.=  "and idProject in " . getVisibleProjectsList(true, $paramProject) ;
}
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$activities=array();
$project=array();
$description=array();
$parent=array();
$resources=array();
$sumActi=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
  }
  $refType=$work->refType;
  $refId=$work->refId;
  $key=$refType . "#" . $refId;
  if (! array_key_exists($key,$activities)) {
  	if ($refType) {
      $obj=new $refType($refId);
  	} else {
  		$obj=new Ticket();
  	}
    $activities[$key]=$obj->name;
    $description[$key]=$obj->description;
    if ($refType=='Project') {
      $parent[$key]="[" . i18n('Project') . "]";
    } else {
      if (property_exists($obj,'idActivity') and $obj->idActivity) {
        $parent[$key]=SqlList::getNameFromId('Activity', $obj->idActivity);
      } else {
        $parent[$key]="";
      }
    }
    $project[$key]=SqlList::getNameFromId('Project', $obj->idProject);
  }
  if (! array_key_exists($work->idResource,$result)) {
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($key,$result[$work->idResource])) {
    $result[$work->idResource][$key]=0;
  } 
  $result[$work->idResource][$key]+=$work->work;
}

if (checkNoData($result)) exit;

// title
echo '<table style="width:95%" align="center">';
echo '<tr>';
echo '<td class="reportTableHeader" rowspan="2" style="width:20%">' . i18n('Resource') . '</td>';
echo '<td class="reportTableHeader" rowspan="2" style="width:10%">' . i18n('colWork') . '</td>';
echo '<td class="reportTableHeader" colspan="3">' . i18n('Activity') . '</td>';
echo '</tr><tr>';
echo '<td class="reportTableColumnHeader" style="width:20%">' . i18n('colIdProject') . '</td>';
echo '<td class="reportTableColumnHeader" style="width:25%">' . i18n('colName') . '</td>';
//echo '<td class="reportTableColumnHeader" style="width:25%">' . i18n('colDescription') . '</td>';
echo '<td class="reportTableColumnHeader" style="width:25%">' . i18n('colParentActivity') . '</td>';
echo '</tr>';

$sum=0;
foreach ($resources as $idR=>$nameR) {
	if ($paramTeam) {
    $res=new Resource($idR);
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
	  $sumRes=0;
	  echo '<tr>';
	  echo '<td class="reportTableLineHeader" style="width:20%" rowspan="' . (count($result[$idR]) +1) . '">' . htmlEncode($nameR) . '</td>';
	  foreach ($activities as $key=>$nameA) {
	    if (array_key_exists($idR, $result)) {
	      if (array_key_exists($key, $result[$idR])) {
	        $val=$result[$idR][$key];
	        $sumRes+=$val; 
	        $sum+=$val;
	        echo '<td class="reportTableData" style="width:10%">' . Work::displayWorkWithUnit($val). '</td>';
	        echo '<td class="reportTableData" style="width:20%; text-align:left;">' . htmlEncode($project[$key]) . '</td>';
	        echo '<td class="reportTableData" style="width:25%; text-align:left;">' . htmlEncode($nameA) . '</td>'; 
	//        echo '<td class="reportTableData" style="width:25%; text-align:left;">' . htmlEncode($description[$key]) . '</td>'; 
	        echo '<td class="reportTableData" style="width:25%; text-align:left;" >' . htmlEncode($parent[$key]) . '</td>'; 
	        echo '</tr><tr>';
	      } 
	    }
	  }
    echo '<td class="reportTableColumnHeader">' . Work::displayWorkWithUnit($sumRes) . '</td>';
    echo '<td class="reportTableColumnHeader" style="text-align:left;" colspan="4">' . i18n('sum') . " " . $nameR . '</td>';
    echo '</tr>';
  }
}
echo '<tr>';
echo '<td class="reportTableHeader">' . i18n('sum') . '</td>';
echo '<td class="reportTableHeader">' . Work::displayWorkWithUnit($sum) . '</td>';
echo '<td colspan="4"></td>';
echo '</tr>';
echo '</table>';