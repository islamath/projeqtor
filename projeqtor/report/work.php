<?php 
include_once '../tool/projeqtor.php';
//echo "work.php";

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
$projects=array();
$resources=array();
$sumProj=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
  }
  if (! array_key_exists($work->idResource,$result)) {
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource])) {
    $result[$work->idResource][$work->idProject]=0;
  } 
  $result[$work->idResource][$work->idProject]+=$work->work;

}

if (checkNoData($result)) exit;
// title
$colWidth=round(80/count($projects));
echo '<table style="width:95%;" align="center">';
echo '<tr>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td style="width:80%" colspan="' . count($projects) . '" class="reportTableHeader">' . i18n('Project') . '</td>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
echo '</tr><tr>';
foreach ($projects as $id=>$name) {
  echo '<td style="width:'.$colWidth.'%" class="reportTableColumnHeader">' . htmlEncode($name) . '</td>';
  $sumProj[$id]=0;  
}

echo '</tr>';

$sum=0;
foreach ($resources as $idR=>$nameR) {
	if ($paramTeam) {
		$res=new Resource($idR);
	}
  if (!$paramTeam or $res->idTeam==$paramTeam) {
		$sumRes=0;
	  echo '<tr><td style="width:10%" class="reportTableLineHeader">' . htmlEncode($nameR) . '</td>';
	  foreach ($projects as $idP=>$nameP) {
	    echo '<td style="width:' . $colWidth . '%" class="reportTableData">';
	    if (array_key_exists($idR, $result)) {
	      if (array_key_exists($idP, $result[$idR])) {
	        $val=$result[$idR][$idP];
	        echo Work::displayWorkWithUnit($val);
	        $sumProj[$idP]+=$val; 
	        $sumRes+=$val; 
	        $sum+=$val;
	      } 
	    }
	    echo '</td>';
	  }
	  echo '<td style="width:10%" class="reportTableColumnHeader">' . Work::displayWorkWithUnit($sumRes) . '</td>';
	  echo '</tr>';
  }
}
echo '<tr><td class="reportTableHeader">' . i18n('sum') . '</td>';
foreach ($projects as $id=>$name) {
  echo '<td class="reportTableColumnHeader">' . Work::displayWorkWithUnit($sumProj[$id]) . '</td>';
}
echo '<td class="reportTableHeader">' . Work::displayWorkWithUnit($sum) . '</td></tr>';
echo '</table>';