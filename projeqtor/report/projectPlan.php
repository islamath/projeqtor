<?php
include_once '../tool/projeqtor.php';
//echo "projectPlan.php";

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
$projectsColor=array();
$resources=array();
$realDays=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $projectsColor[$work->idProject]=SqlList::getFieldFromId('Project', $work->idProject, 'color');
    $realDays[$work->idProject]=array();
    $result[$work->idProject]=array();
  }
  if (! array_key_exists($work->idResource,$result[$work->idProject])) {
    $result[$work->idProject][$work->idResource]=array();
    $realDays[$work->idProject][$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idProject][$work->idResource])) {
    $result[$work->idProject][$work->idResource][$work->day]=0;
    $realDays[$work->idProject][$work->idResource][$work->day]='real';
  } 
  $result[$work->idProject][$work->idResource][$work->day]+=$work->work;
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $projectsColor[$work->idProject]=SqlList::getFieldFromId('Project', $work->idProject, 'color');
    $realDays[$work->idProject]=array();
    $result[$work->idProject]=array();
  }
  if (! array_key_exists($work->idResource,$result[$work->idProject])) {
    $result[$work->idProject][$work->idResource]=array();
    $realDays[$work->idProject][$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idProject][$work->idResource])) {
    $result[$work->idProject][$work->idResource][$work->day]=0;
  }
  if (! array_key_exists($work->day,$realDays[$work->idProject][$work->idResource])) { // Do not add planned if real exists 
    $result[$work->idProject][$work->idResource][$work->day]+=$work->work;
  }
}

if ($periodType=='month') {
  $startDate=$periodValue. "01";
  $time=mktime(0, 0, 0, $paramMonth, 1, $paramYear);
  $header=i18n(strftime("%B", $time)).strftime(" %Y", $time);
  $nbDays=date("t", $time);
}
$weekendBGColor='#cfcfcf';
$weekendFrontColor='#555555';
$weekendStyle=' style="background-color:' . $weekendBGColor . '; color:' . $weekendFrontColor . '" ';
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

if (checkNoData($result)) exit;

echo "<table width='95%' align='center'><tr>";
echo '<td><table width="100%" align="left"><tr>';
echo "<td class='reportTableDataFull' width='20px' style='text-align:center;'>";
echo "1";
echo "</td><td width='100px' class='legend'>" . i18n('colRealWork') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '>';
echo "<i>1</i>";
echo "</td><td width='100px' class='legend'>" . i18n('colPlannedWork') . "</td>";
echo "<td>&nbsp;</td>";
echo "<td class='legend'>" . Work::displayWorkUnit() . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";
//echo "<br/>";

// title
echo '<table width="100%" align="left"><tr>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td colspan="' . ($nbDays+1) . '" class="reportTableHeader">' . $header . '</td>';
echo '</tr><tr>';
$days=array();
for($i=1; $i<=$nbDays;$i++) {
  if ($periodType=='month') {
    $day=(($i<10)?'0':'') . $i;   
    if (isOffDay(substr($periodValue,0,4) . "-" . substr($periodValue,4,2) . "-" . $day)) {
      $days[$periodValue . $day]="off";
      $style=$weekendStyle;
    } else {
      $days[$periodValue . $day]="open";
      $style='';
    }
    echo '<td class="reportTableColumnHeader" ' . $style . '>' . $day . '</td>';
  }  
}
echo '<td class="reportTableHeader" >' . i18n('sum'). '</td>';
echo '</tr>';


if ($paramTeam) {
	foreach ($resources as $idR=>$ress) {
		$res=new Resource($idR);
		if ($res->idTeam!=$paramTeam) {
			unset($resources[$idR]);
		}
	}
  foreach ($projects as $idP=>$nameP) {
  	foreach ($result[$idP] as $idR=>$ress) {
  	  if (! isset($resources[$idR]) ) {
        unset  ($result[$idP][$idR]);
        if (count($result[$idP])==0 ) {
        	unset ($result[$idP]);
        	unset($projects[$idP]);
        }
      }
	  }	 
  }
}

$globalSum=array();
for ($i=1; $i<=$nbDays;$i++) {
  $globalSum[$startDate+$i-1]='';
}
foreach ($projects as $idP=>$nameP) {
  $sum=array();
  for ($i=1; $i<=$nbDays;$i++) {
    $sum[$startDate+$i-1]='';
  }
  echo '<tr height="20px"><td class="reportTableLineHeader" style="width:150px;" rowspan="'. (count($result[$idP])+1) . '">' . htmlEncode($nameP) . '</td>';
  foreach ($result[$idP] as $idR=>$ress) {
    if (array_key_exists($idR, $resources)) {
      echo '<td class="reportTableData" style="width:100px; text-align: left;">' . htmlEncode($resources[$idR]) . '</td>';
      $lineSum='';
      for ($i=1; $i<=$nbDays;$i++) {
        $day=$startDate+$i-1;
        $style="";
        $ital=false;
        if ($days[$day]=="off") {
          $style=$weekendStyle;
        } else {
          if (! array_key_exists($day, $realDays[$idP][$idR]) 
          and array_key_exists($day,$result[$idP][$idR])) {
            $style=$plannedStyle;
            $ital=true;
          }
        }
        echo '<td class="reportTableData" ' . $style . ' valign="top">';
        if (array_key_exists($day,$result[$idP][$idR])) {
          echo ($ital)?'<i>':'';
          echo Work::displayWork($result[$idP][$idR][$day]);
          echo ($ital)?'</i>':'';
          $sum[$day]+=$result[$idP][$idR][$day];
          $globalSum[$day]+=$result[$idP][$idR][$day];
          $lineSum+=$result[$idP][$idR][$day];
        }
        echo '</td>';
      }
      echo '<td class="reportTableColumnHeader">' . Work::displayWork($lineSum) . '</td>';
      echo '</tr><tr>';
    }
  }
  echo '<td class="reportTableLineHeader" >' . i18n('sum') . '</td>';
  $lineSum='';
  for ($i=1; $i<=$nbDays;$i++) {
    $style='';
    $day=$startDate+$i-1;
    if ($days[$day]=="off") {
          $style=$weekendStyle;
    }
    echo '<td class="reportTableColumnHeader" ' . $style . ' >' . Work::displayWork($sum[$startDate+$i-1]) . '</td>';
    $lineSum+=$sum[$startDate+$i-1];
  }
  echo '<td class="reportTableHeader">' . Work::displayWork($lineSum) . '</td>';
  echo '</tr>';
  
}
echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" colspan="2">' . i18n('sum') . '</td>';
$lineSum='';
for ($i=1; $i<=$nbDays;$i++) {
  $style='';
  $day=$startDate+$i-1;
  if ($days[$day]=="off") {
    $style=$weekendStyle;
  }
  echo '<td class="reportTableHeader" ' . $style . '>' . Work::displayWork($globalSum[$startDate+$i-1]) . '</td>';
  $lineSum+=$globalSum[$startDate+$i-1];
}
echo '<td class="reportTableHeader">' . Work::displayWork($lineSum) . '</td>';
echo '</tr>';
echo '</table>';
echo '</td></tr></table>';