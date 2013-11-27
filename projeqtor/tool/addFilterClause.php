<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

$user=$_SESSION['user'];

$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
  $comboDetail=true;
}

if (! $comboDetail and ! $user->_arrayFilters) {
  $user->_arrayFilters=array();
} else if ($comboDetail and ! $user->_arrayFiltersDetail) {
  $user->_arrayFiltersDetail=array();
}


// Get the filter info
if (! array_key_exists('idFilterAttribute',$_REQUEST)) {
  throwError('idFilterAttribute parameter not found in REQUEST');
}
$idFilterAttribute=$_REQUEST['idFilterAttribute'];

if (! array_key_exists('idFilterOperator',$_REQUEST)) {
  throwError('idFilterOperator parameter not found in REQUEST');
}
$idFilterOperator=$_REQUEST['idFilterOperator'];

if (! array_key_exists('filterDataType',$_REQUEST)) {
  throwError('filterDataType parameter not found in REQUEST');
}
$filterDataType=$_REQUEST['filterDataType'];

if (! array_key_exists('filterValue',$_REQUEST)) {
  throwError('filterValue parameter not found in REQUEST');
}
$filterValue=$_REQUEST['filterValue'];

if (array_key_exists('filterValueList',$_REQUEST)) {
  $filterValueList=$_REQUEST['filterValueList'];
} else {
  $filterValueList=array();
}

if (! array_key_exists('filterValueDate',$_REQUEST)) {
  throwError('filterValueDate parameter not found in REQUEST');
}
$filterValueDate=$_REQUEST['filterValueDate'];

if (! array_key_exists('filterValueCheckbox',$_REQUEST)) {
  $filterValueCheckbox=false;
} else {
  $filterValueCheckbox=true;
}

if (! array_key_exists('filterSortValueList',$_REQUEST)) {
  throwError('filterSortValueList parameter not found in REQUEST');
}
$filterSortValue=$_REQUEST['filterSortValueList']; 

if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

$name="";
if (array_key_exists('filterName',$_REQUEST)) {
  $name=$_REQUEST['filterName'];
}
trim($name);

// Get existing filter info
if (!$comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ($comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
  $filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
  $filterArray=array();
}

$obj=new $filterObjectClass();
// Add new filter
if ($idFilterAttribute and $idFilterOperator) {
  $arrayDisp=array();
  $arraySql=array();
  $dataType=$obj->getDataType($idFilterAttribute);
  $dataLength=$obj->getDataLength($idFilterAttribute);
  $split=explode('_',$idFilterAttribute);
  if (count($split)>1 ) {
  	$externalClass=$split[0];
    $externalObj=new $externalClass();
    $arrayDisp["attribute"]=$externalObj->getColCaption($split[1]);
  } else {
  	//echo  $idFilterAttribute . "=>" . $obj->getColCaption($idFilterAttribute);
    if (substr($idFilterAttribute,0,9)=='idContext') {
      $arrayDisp["attribute"]=SqlList::getNameFromId('ContextType',substr($idFilterAttribute,9));
    } else {
      $arrayDisp["attribute"]=$obj->getColCaption($idFilterAttribute);
    }
  }
  $arraySql["attribute"]=$obj->getDatabaseColumnName($idFilterAttribute);
  if ($idFilterOperator=="=" or $idFilterOperator==">=" or $idFilterOperator=="<="  or $idFilterOperator=="<>") {
    $arrayDisp["operator"]=$idFilterOperator;
    $arraySql["operator"]=$idFilterOperator;
    if ($filterDataType=='date') {
      $arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . "'";
      $arraySql["value"]="'" . $filterValueDate . "'";
    } else if ($filterDataType=='bool') {
        $arrayDisp["value"]=($filterValueCheckbox)?i18n("displayYes"):i18n("displayNo");
        $arraySql["value"]=($filterValueCheckbox)?1:0;
    } else {
      $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
      $arraySql["value"]="'" . htmlEncode($filterValue) . "'";
    }
  } else if ($idFilterOperator=="LIKE") {
    $arrayDisp["operator"]=i18n("contains");
    $arraySql["operator"]=(Sql::isMysql())?'LIKE':'ILIKE';
    $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
    $arraySql["value"]="'%" . htmlEncode($filterValue) . "%'";
  } else if ($idFilterOperator=="NOT LIKE") {
    $arrayDisp["operator"]=i18n("notContains");
    $arraySql["operator"]=(Sql::isMysql())?'NOT LIKE':'NOT ILIKE';
    $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
    $arraySql["value"]="'%" . htmlEncode($filterValue) . "%'";
  } else if ($idFilterOperator=="IN" or $idFilterOperator=="NOT IN") {
    $arrayDisp["operator"]=($idFilterOperator=="IN")?i18n("amongst"):i18n("notAmongst");
    $arraySql["operator"]=$idFilterOperator;
    $arrayDisp["value"]="";
    $arraySql["value"]="(";
    foreach ($filterValueList as $key=>$val) {
      $arrayDisp["value"].=($key==0)?"":", ";
      $arraySql["value"].=($key==0)?"":", ";
      $arrayDisp["value"].="'" . SqlList::getNameFromId(substr($idFilterAttribute,2),$val) . "'";
      $arraySql["value"].= $val ;
    }
    //$arrayDisp["value"].=")";
    $arraySql["value"].=")";
  } else if ($idFilterOperator=="isEmpty") {
      $arrayDisp["operator"]=i18n("isEmpty");
      $arraySql["operator"]="is null";
      $arrayDisp["value"]="";
      $arraySql["value"]="";
  } else if ($idFilterOperator=="isNotEmpty") {
      $arrayDisp["operator"]=i18n("isNotEmpty");
      $arraySql["operator"]="is not null";
      $arrayDisp["value"]="";
      $arraySql["value"]="";
  } else if ($idFilterOperator=="SORT") {  
    $arrayDisp["operator"]=i18n("sortFilter");
    $arraySql["operator"]=$idFilterOperator;
    $arrayDisp["value"]=htmlEncode(i18n('sort' . ucfirst($filterSortValue) ));
    $arraySql["value"]=$filterSortValue;
  } else if ($idFilterOperator=="<=now+") {  
    $arrayDisp["operator"]="<= " . i18n('today') . (($filterValue>0)?' +':' ');
    $arraySql["operator"]="<=";
    $arrayDisp["value"]=htmlEncode($filterValue) . ' ' . i18n('days');
    $arraySql["value"]= "ADDDATE(NOW(), INTERVAL (" . $filterValue . ") DAY)";
  } else if ($idFilterOperator==">=now+") {  
    $arrayDisp["operator"]=">= " . i18n('today') . (($filterValue>0)?' +':' ');
    $arraySql["operator"]=">=";
    $arrayDisp["value"]=htmlEncode($filterValue) . ' ' . i18n('days');
    $arraySql["value"]= "ADDDATE(NOW(), INTERVAL (" . $filterValue . ") DAY)";
  } else {
     echo htmlGetErrorMessage(i18n('incorrectOperator'));
     exit;
  } 
  $filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql);
  if (! $comboDetail) {
    $user->_arrayFilters[$filterObjectClass]=$filterArray;
  } else {
  	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
  }
}

//$user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
if (! $comboDetail) {
  $user->_arrayFilters[$filterObjectClass . "FilterName"]="";
} else {
  $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]="";	
}
htmlDisplayFilterCriteria($filterArray,$name); 

// save user (for filter saving)
$_SESSION['user']=$user;


?>