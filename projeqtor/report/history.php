<?php 
// Header
include_once '../tool/projeqtor.php';

$refType="";
if (array_key_exists('refType',$_REQUEST) and trim($_REQUEST['refType'])!="") {
  $refType=trim($_REQUEST['refType']);
}
$refId="";
if (array_key_exists('refId',$_REQUEST)) {
  $refId=trim($_REQUEST['refId']);
}
$scope=$_REQUEST['scope'];
$headerParameters="";

if ($refType!="") {
  $headerParameters.= i18n("colElement") . ' : ' . i18n($refType) . ' #' . $refId . '<br/>';
}
include "header.php";

$accessRightRead=securityGetAccessRight('menuProject', 'read');
  
$where='';
if ($scope=="deleted") {
  $where.= ($where=='')?'':' and ';
  $where.= " operation='delete' ";
  $where.= " and refType in ('Ticket','Activity','Milestone', 'Risk', 'Action', 'Issue', 'Meeting', 'Decision', 'Question', 'Project' )";
} else {
  $where = " (refType, refId) in ( ('$refType',".Sql::fmtId($refId).")";
  $obj=new $refType($refId);
  foreach ($obj as $fld=>$val) {
  	if (is_object($val) and isset($val->id)) {
  		$where.=", ('$fld',".Sql::fmtId($val->id).")";
  	}
  }
  $where .= ")";
}

$order = ' operationDate desc, id asc';
$hist=new History();
$historyList=$hist->getSqlElementsFromCriteria(null,false,$where,$order);

if (checkNoData($historyList)) exit;

echo '<table width="95%" align="center">';
echo '<tr>';
if ($scope=='deleted') {
  echo '<td class="historyHeader" style="width:20%">' . i18n('colOperation'). '</td>';
  echo '<td class="historyHeader" style="width:30%">' . i18n('colElement'). '</td>';
  echo '<td class="historyHeader" style="width:30%">' . i18n('colDate') . '</td>';
  echo '<td class="historyHeader" style="width:20%">' . i18n('colUser'). '</td>';
} else {
  echo '<td class="historyHeader" style="width:10%">' . i18n('colOperation'). '</td>';
  echo '<td class="historyHeader" style="width:15%">' . i18n('colColumn'). '</td>';
  echo '<td class="historyHeader" style="width:25%">' . i18n('colValueBefore'). '</td>';
  echo '<td class="historyHeader" style="width:25%">' . i18n('colValueAfter'). '</td>';
  echo '<td class="historyHeader" style="width:15%">' . i18n('colDate') . '</td>';
  echo '<td class="historyHeader" style="width:10%">' . i18n('colUser'). '</td>';
  
}
echo '</tr>';
$stockDate=null;
$stockUser=null;
$stockOper=null;
foreach($historyList as $hist) {
	if (substr($hist->colName,0,25)=='subDirectory|Attachement|' 
	 or substr($hist->colName,0,19)=='idTeam|Attachement|') {
		continue;
	}
  $colName=($hist->colName==null)?'':$hist->colName;
  $split=explode('|', $colName);
  if (count($split)==3) {
    $colName=$split[0];
    $refType=$split[1];
    $refId=$split[2];
    $refObject='';
  } else if (count($split)==4) {
    $refObject=$split[0];
    $colName=$split[1];
    $refType=$split[2];
    $refId=$split[3];
  } else {
    $refType='';
    $refId='';
    $refObject='';
  }
  $curObj=null; $dataType=""; $dataLength=0;
  $hide=false;
  $oper=i18n('operation' . ucfirst($hist->operation) );
  $user=$hist->idUser;
  $user=SqlList::getNameFromId('User',$user);
  $date=htmlFormatDateTime($hist->operationDate);
  $class="NewOperation";
  if ($stockDate==$hist->operationDate 
  and $stockUser==$hist->idUser
  and $stockOper==$hist->operation) {
    $oper="";
    $user="";
    $date="";
    $class="ContinueOperation";
  }
  if ($colName!='' or $refType!="") {
    if ($refType) {
      if ($refType=="TestCase") {
        $curObj=new TestCaseRun();
      } else {
        $curObj=new $refType();
      }
    } else {
      $curObj=new $hist->refType();
    }
    if ($curObj) {
      if ($refType) {
        $colCaption=i18n($refType). ' #' . $refId . ' ' . $curObj->getColCaption($colName);
        if ($refObject) {
          $colCaption=i18n($refObject) . ' - ' . $colCaption;
        }
      } else {
        $colCaption=$curObj->getColCaption($colName);
      }
      $dataType=$curObj->getDataType($colName);
      $dataLength=$curObj->getDataLength($colName);
      if (strpos($curObj->getFieldAttributes($colName), 'hidden')!==false) {
        $hide=true;
      }
    }
  } else {
    $colCaption='';
  }
  if (substr($hist->refType,-15)=='PlanningElement' and $hist->operation=='insert') {
    $hide=true;
  }
  if ($scope=='deleted') {
  	if (trim($colCaption)) {
  		$colCaption=i18n($hist->refType). ' #' . $hist->refId." => ".$colCaption;
  	} else {
  		$colCaption=i18n($hist->refType). ' #' . $hist->refId;
  	}
  }
  if (! $hide) {
    echo '<tr>';
    echo '<td class="historyData'. $class .'" width="10%">' . $oper . '</td>';      
    echo '<td class="historyData" width="14%">' . $colCaption . '</td>';
	  $oldValue=$hist->oldValue;
	  $newValue=$hist->newValue;
    if ($dataType=='int' and $dataLength==1) { // boolean
      $oldValue=htmlDisplayCheckbox($oldValue);
      $newValue=htmlDisplayCheckbox($newValue);
    } else if (substr($colName,0,2)=='id' and strlen($colName)>2
               and strtoupper(substr($colName,2,1))==substr($colName,2,1)) {
      if ($oldValue!=null and $oldValue!='') {
        if ($oldValue==0 and $colName=='idStatus') {
          $oldValue='';
        } else {
          $oldValue=htmlEncode(SqlList::getNameFromId(substr($colName,2),$oldValue));
        }
      }
      if ($newValue!=null and $newValue!='') {
        $newValue=htmlEncode(SqlList::getNameFromId(substr($colName,2),$newValue));
      }
    } else if ($colName=="color") {
      $oldValue=htmlDisplayColored("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$oldValue);
      $newValue=htmlDisplayColored("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$newValue);
    } else if ($dataType=='date') {
      $oldValue=htmlFormatDate($oldValue);
      $newValue=htmlFormatDate($newValue);
    } else if ($dataType=='datetime') {
      $oldValue=htmlFormatDateTime($oldValue);
      $newValue=htmlFormatDateTime($newValue);
    } elseif ($dataType=='decimal' and substr($colName, -4,4)=='Work') {
      $oldValue = Work::displayWork($oldValue) . ' ' . Work::displayShortWorkUnit();
      $newValue = Work::displayWork($newValue) . ' ' . Work::displayShortWorkUnit();
    } else {
      $oldValue=htmlEncode($oldValue,'print');
      $newValue=htmlEncode($newValue,'print');
    }
    if ($scope!='deleted') {    
      echo '<td class="historyData" width="23%">' . $oldValue . '</td>';
      echo '<td class="historyData" width="23%">' . $newValue . '</td>';
    }
    echo '<td class="historyData'. $class .'" width="15%">' . $date . '</td>';
    echo '<td class="historyData'. $class .'" width="15%">' . $user . '</td>';
    echo '</tr>';
    $stockDate=$hist->operationDate;
    $stockUser=$hist->idUser;
    $stockOper=$hist->operation;
  }
}
echo '<tr>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '<td class="historyDataClosetable">&nbsp;</td>';
echo '</tr>';
echo '</table>';

?>
