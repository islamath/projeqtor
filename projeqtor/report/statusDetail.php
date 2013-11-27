<?php 
include_once '../tool/projeqtor.php';

if (! isset($includedReport)) {
  include("../external/pChart/pData.class");  
  include("../external/pChart/pChart.class");  
  
  $paramProject='';
  if (array_key_exists('idProject',$_REQUEST)) {
    $paramProject=trim($_REQUEST['idProject']);
  }
  
  $paramIssuer='';
  if (array_key_exists('issuer',$_REQUEST)) {
    $paramIssuer=trim($_REQUEST['issuer']);
  }
  
  $paramResponsible='';
  if (array_key_exists('responsible',$_REQUEST)) {
    $paramResponsible=trim($_REQUEST['responsible']);
  }
  
  $paramRefType='';
  if (array_key_exists('refType',$_REQUEST)) {
    $paramRefType=trim($_REQUEST['refType']);
  }
  
  $showIdle=false;
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
  }
  
  $user=$_SESSION['user'];
    
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  if ($paramIssuer!="") {
    $headerParameters.= i18n("colIssuer") . ' : ' . htmlEncode(SqlList::getNameFromId('User', $paramIssuer)) . '<br/>';
  }
  if ($paramResponsible!="") {
    $headerParameters.= i18n("colResponsible") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource', $paramResponsible)) . '<br/>';
  }
  include "header.php";
}

$obj=new $refType();
$user=$_SESSION['user'];

$query = "select count(id) as nb, id" . $refType . "Type as idType, idStatus ";
$query .= " from " . $obj->getDatabaseTableName();
$query.=" where " . getAccesResctictionClause($refType,false);
if ($paramProject!='') {
  $query.=  "and idProject in " . getVisibleProjectsList(true, $paramProject) ;
}
if (! $showIdle) {
 $query .= " and idle=0 ";
}
if ($paramIssuer!="") {
 $query .= " and idUser='" . $paramIssuer;
}
if ($paramResponsible!="") {
 $query .= " and idResource='" . $paramResponsible; 
}
$query .= " group by id" . $refType . "Type, idStatus";

$result=Sql::query($query);
$arr=array();
$arrStatus=array();
while ($line = Sql::fetchLine($result)) {
	$line=array_change_key_case($line,CASE_LOWER);
  $type=$line['idtype'];
  $status=$line['idstatus'];
  $val=$line['nb'];
  if (! array_key_exists($type, $arr)) {
    $arr[$type]=array();
  }
  if (! array_key_exists($status, $arrStatus)) {
    $arrStatus[$status]=0;
  }
  $arrStatus[$status]+=$val;
  $arr[$type][$status]=$val;
}
$lstStatus=SqlList::getList('Status');
foreach ($lstStatus as $id=>$st) {
  if (! array_key_exists($id, $arrStatus)) {
    unset($lstStatus[$id]);
  }
}
$lstType=SqlList::getList($refType . 'Type');
foreach ($lstType as $id=>$st) {
  if (! array_key_exists($id, $arr)) {
    unset($lstType[$id]);
  }
}

if (count($lstStatus)>0) {

	echo '<table width="95%" align="center">';
	echo '<tr><td class="reportTableHeader" rowspan="2">' . i18n($refType . 'Type') . '</td>';
	echo '<td colspan="' . (count($lstStatus  )) . '" class="reportTableHeader">' .  i18n('colIdStatus') . '</td>';
	echo '<td class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
	echo '</tr>';
	echo '<tr>';
	foreach ($lstStatus as $id=>$status) {
	  echo '<td class="reportTableColumnHeader">' . $status . '</td>';
	}
	echo '</tr>';
	
	foreach ($lstType as $idType=>$name) {
	  $sum=0;
	  echo '<tr><td class="reportTableLineHeader" style="width:20%">' . $name . '</td>';
	  if (count($lstStatus)) {
	    $detWidth=floor(70/count($lstStatus));
	  } else {
	    $detWidth='70';
	  }
	  foreach ($lstStatus as $idStatus=>$status) {
	    echo '<td class="reportTableData" style="width:' . $detWidth . '%">';
	    if (isset($arr[$idType][$idStatus])) {
	      echo $arr[$idType][$idStatus];
	      $sum+=$arr[$idType][$idStatus];
	    }
	    echo '</td>';
	  }
	  echo '<td class="reportTableLineHeader" style="width:10%;text-align:center;">' . $sum . '</td>';
	  echo '</tr>';
	}
	
	echo '<tr><td class="reportTableHeader" >' . i18n('sum') . '</td>';
	$sum=0;
	foreach ($lstStatus as $id=>$val) {
	  echo '<td class="reportTableLineHeader" style="text-align:center;">' . $arrStatus[$id] . '</td>';
	  $sum+=$arrStatus[$id];
	}
	echo '<td class="reportTableHeader" >' . $sum . '</td>';
	echo '</tr>';
	echo '</table>';
	
	// Render graph
	// pGrapg standard inclusions     
	if (! testGraphEnabled()) { return;}
	
	$dataSet=new pData;
	$nbItem=0;
	foreach($arr as $id=>$arrType) {
	  $temp=array();
	  foreach ($lstStatus as $is=>$status) {
	    if (array_key_exists($is,$arrType)) {
	      $temp[$is]=$arrType[$is];
	    } else {
	      $temp[$is]="";
	    }
	  } 
	  $dataSet->AddPoint($temp,$id);
	  if (isset($lstType[$id])) {
	  $dataSet->SetSerieName($lstType[$id],$id);
	  $dataSet->AddSerie($id);
	  $nbItem++;
	  }
	}
	$dataSet->AddPoint($lstStatus,"status");  
	$dataSet->SetAbsciseLabelSerie("status");   
	$width=650;
	$graph = new pChart($width,250);  
	for ($i=0;$i<=$nbItem;$i++) {
	  $graph->setColorPalette($i,$rgbPalette[($i % 12)]['R'],$rgbPalette[($i % 12)]['G'],$rgbPalette[($i % 12)]['B']);
	}
	$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",10);
	$graph->drawRoundedRectangle(5,5,$width-5,248,5,230,230,230);  
	$graph->setGraphArea(40,30,$width-160,220);  
	$graph->drawGraphArea(252,252,252);  
	$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",8);  
	$graph->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_ADDALLSTART0,0,0,0,TRUE,0,1, true);  
	$graph->drawGrid(5,TRUE,230,230,230,255);  
	$graph->drawStackedBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);  
	$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",8);  
	$graph->drawLegend($width-150,35,$dataSet->GetDataDescription(),240,240,240);  
	
	$imgName=getGraphImgName("statusDetail");
	$graph->Render($imgName);
	echo '<table width="95%" align="center"><tr><td align="center">';
	echo '<img src="' . $imgName . '" />'; 
	echo '</td></tr></table>';
	echo '<br/>';
}
?>
