<?php
include_once '../tool/projeqtor.php';
//echo "detailPlan.php";
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
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
}

$user=$_SESSION['user'];

$periodType=$_REQUEST['periodType'];
$periodValue=$_REQUEST['periodValue'];

// Header
$headerParameters="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $_REQUEST['idProject'])) . '<br/>';
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
if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
  $where.= ($where=='')?'':' and ';
  $where.=  " idProject in " . getVisibleProjectsList(true, $_REQUEST['idProject']) ;
}
  
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$resources=array();
$activities=array();
$realDays=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
    $result[$work->idResource]=array();
    $realDays[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource])) {
    $result[$work->idResource][$work->idProject]=array();
    $realDays[$work->idResource][$work->idProject]=array();
  }
  $ref=$work->refType . "#" . $work->refId;
  if (! array_key_exists($ref,$activities)) {
    $activities[$ref]=SqlList::getNameFromId($work->refType,  $work->refId);
  }
  if (! array_key_exists($ref,$result[$work->idResource][$work->idProject])) {
    $result[$work->idResource][$work->idProject][$ref]=array();
    $realDays[$work->idResource][$work->idProject][$ref]=array();
  }  
  if (! array_key_exists($work->day,$result[$work->idResource][$work->idProject][$ref])) {
    $result[$work->idResource][$work->idProject][$ref][$work->day]=0;
    $realDays[$work->idResource][$work->idProject][$ref][$work->day]='real';
  } 
  $result[$work->idResource][$work->idProject][$ref][$work->day]+=$work->work;
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
    $result[$work->idResource]=array();
    $realDays[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource])) {
    $result[$work->idResource][$work->idProject]=array();
    $realDays[$work->idResource][$work->idProject]=array();
  }
  $ref=$work->refType . "#" . $work->refId;
  if (! array_key_exists($ref,$activities)) {
    $activities[$ref]=SqlList::getNameFromId($work->refType,  $work->refId);
  }
  if (! array_key_exists($ref,$result[$work->idResource][$work->idProject])) {
    $result[$work->idResource][$work->idProject][$ref]=array();
    $realDays[$work->idResource][$work->idProject][$ref]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idResource][$work->idProject][$ref])) {
    $result[$work->idResource][$work->idProject][$ref][$work->day]=0;
  }
  if (! array_key_exists($work->day,$realDays[$work->idResource][$work->idProject][$ref])) { // Do not add planned if real exists 
    $result[$work->idResource][$work->idProject][$ref][$work->day]+=$work->work;
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

echo "<table width='95%' align='center'>";
echo "<tr><td><table  width='100%' align='left'><tr>";
echo "<td class='reportTableDataFull' style='width:20px;text-align:center;'>1</td>";
echo "<td width='100px' class='legend'>" . i18n('colRealWork') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '><i>1</i></td>';
echo "<td width='100px' class='legend'>" . i18n('colPlannedWork') . "</td>";
echo "<td>&nbsp;</td>";
echo "<td class='legend'>" . Work::displayWorkUnit() . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "</table>";
//echo "<br/>";

// title
echo '<table width="100%" align="left">';
echo '<tr>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Activity') . '</td>';
echo '<td colspan="' . ($nbDays+1) . '" class="reportTableHeader">' . $header . '</td>';
echo '</tr>';
echo '<tr>';
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

$globalSum=array();
for ($i=1; $i<=$nbDays;$i++) {
  $globalSum[$startDate+$i-1]='';
}
foreach ($resources as $idR=>$nameR) {
	 if ($paramTeam) {
    $res=new Resource($idR);
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
	  $sum=array();
	  for ($i=1; $i<=$nbDays;$i++) {
	    $sum[$startDate+$i-1]='';
	  }
	  echo '<tr height="20px">';
	  $cpt=0;
	  foreach ($result[$idR] as $res) { 
	    $cpt+=count($res);
	  }
	  $cpt+=1;
	  echo '<td class="reportTableLineHeader" style="width:100px;" rowspan="'. ($cpt) . '">' . htmlEncode($nameR) . '</td>';
	  foreach ($result[$idR] as $idP=>$proj) {
	    foreach ($result[$idR][$idP] as $idA=>$acti) { 
		    if (array_key_exists($idP, $projects)) {
		      echo '<td class="reportTableData" style="width:100px;text-align: left;">' . htmlEncode($projects[$idP]) . '</td>';
		      echo '<td class="reportTableData" style="width:100px;text-align: left;">' . htmlEncode($activities[$idA]) . '</td>';
	        
		      $lineSum='';
		      for ($i=1; $i<=$nbDays;$i++) {
		        $day=$startDate+$i-1;
		        $style="";
		        $ital=false;
		        if ($days[$day]=="off") {
		          $style=$weekendStyle;
		        } else {
		          if (! array_key_exists($day, $realDays[$idR][$idP][$idA]) 
		          and array_key_exists($day,$result[$idR][$idP][$idA])) {
		            $style=$plannedStyle;
		            $ital=true;
		          }
		        }
		        echo '<td class="reportTableData" ' . $style . ' valign="top">';
		        if (array_key_exists($day,$result[$idR][$idP][$idA])) {
		          echo ($ital)?'<i>':'';
		          echo Work::displayWork($result[$idR][$idP][$idA][$day]);
		          echo ($ital)?'</i>':'';
		          $sum[$day]+=$result[$idR][$idP][$idA][$day];
		          $globalSum[$day]+=$result[$idR][$idP][$idA][$day];
		          $lineSum+=$result[$idR][$idP][$idA][$day];
		        }
		        echo '</td>';
		      }
		      echo '<td class="reportTableColumnHeader">' . Work::displayWork($lineSum) . '</td>';
		      echo '</tr><tr>';
		    }
	    }
	  }
	  echo '<td class="reportTableLineHeader" colspan="2">' . i18n('sum') . '</td>';
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
	  echo '<td class="reportTableHeader" >' . Work::displayWork($lineSum) . '</td>';
	  echo '</tr>';
  }
}

echo '<tr><td colspan="' . ($nbDays+3) . '">&nbsp;</td></tr>';
echo '<tr><td class="reportTableHeader" colspan="3">' . i18n('sum') . '</td>';
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