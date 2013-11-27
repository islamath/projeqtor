<?php
//echo "availabilityPlan.php";

include_once '../tool/projeqtor.php';
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
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . SqlList::getNameFromId('Team', $paramTeam) . '<br/>';
}
include "header.php";

$where=getAccesResctictionClause('Affectation',false);

$resources=array();
$aff=new Affectation();
$affLst=$aff->getSqlElementsFromCriteria(null,false, $where);
foreach($affLst as $aff){
	$ress=new Resource($aff->idResource);
	if ($ress->id and !$ress->idle) {
    $resources[$ress->id]=htmlEncode($ress->name);
	}
}

$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
//$resources=array();

$capacity=array();
foreach ($resources as $id=>$name) {
	$capacity[$id]=SqlList::getFieldFromId('Resource', $id, 'capacity');
  $result[$id]=array();
}

$real=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
    $capacity[$work->idResource]=SqlList::getFieldFromId('Resource', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idResource,$real)) {
  	$real[$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=0;
    $real[$work->idResource][$work->day]=true;
  }
  $result[$work->idResource][$work->day]+=$work->work;
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
    $capacity[$work->idResource]=SqlList::getFieldFromId('Resource', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idResource,$real)) {
    $real[$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=0;
  }
  //if (! array_key_exists($work->day,$real)) { // Do not add planned if real exists 
    $result[$work->idResource][$work->day]+=$work->work;
  //}
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

//if (checkNoData($result)) exit;


echo '<table width="95%" align="center">';
echo '<tr><td>';
echo '<table width="100%" align="left">';
echo '<tr>';
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
echo '</td></tr>';
echo '<tr><td>';
//echo '<br/>';
// title

echo '<table width="100%" align="left"><tr>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('colCapacity') . '</td>';
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
echo '<td class="reportTableHeader" style="width:5%">' . i18n('sum') . '</td>';
echo '</tr>';

foreach ($resources as $idR=>$nameR) {
	if ($paramTeam) {
    $res=new Resource($idR);
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
		$sum=0;
	  echo '<tr height="20px">';
	  echo '<td class="reportTableLineHeader" style="width:20%">' . $nameR . '</td>';
	  echo '<td class="reportTableLineHeader" style="width:5%;text-align:center;">' . ($capacity[$idR]*1) . '</td>';
	  for ($i=1; $i<=$nbDays;$i++) {
	    $day=$startDate+$i-1;
	    $style="";
	    $italic=false;
	    if ($days[$day]=="off") {
	      $style=$weekendStyle;
	    } else {
	      if (array_key_exists($day,$result[$idR])) {
	        $val=$capacity[$idR]-$result[$idR][$day];
	      } else {
	        $val=$capacity[$idR]*1;
	      }
	      $style=' style="text-align:center;';
	      //if (! array_key_exists($day,$real) and array_key_exists($day,$result[$idR])) {
	      if (array_key_exists($idR,$real) and ! array_key_exists($day,$real[$idR]) and array_key_exists($day,$result[$idR])) {
	        $style.='background-color:' . $plannedBGColor . ';';
	        $italic=true;
	      }
	      if ($val>0) {
	        $style.='color: #00AA00;';      	
	      } else if ($val < 0) {
	      	$style.='color: #FF0000;';
	      } else {
	      	$style.='color: ' . $plannedFrontColor . ';';
	      }
	      $style.='"';  
	    }
	    if ($style==$weekendStyle) {$val="";}
	    echo '<td class="reportTableDataFull" ' . $style . ' valign="middle">';    
	     if ($italic) {
	     	 echo '<i>' . Work::displayWork($val) . '</i>';
	     } else { 
	     	 echo Work::displayWork($val);
	     }
	  	echo '</td>';
	  	if ($val>0) {
	  		$sum+=$val;
	  	}
	  }
	  echo '<td class="reportTableColumnHeader" style="width:5%">' . Work::displayWork($sum) . '</td>';
	  echo '</tr>';
  }
}

echo '</table>';

echo '</td></tr></table>';