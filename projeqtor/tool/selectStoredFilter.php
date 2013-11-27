<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/selectStoredFilter.php');
$user=$_SESSION['user'];

$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
  $comboDetail=true;
}

if (! $comboDetail) {
  if (! $user->_arrayFilters) {
    $user->_arrayFilters=array();
  }
} else {
  if (! $user->_arrayFiltersDetail) {
    $user->_arrayFiltersDetail=array();
  }
}

// Get the filter info
if (! array_key_exists('idFilter',$_REQUEST)) {
  throwError('idFilter parameter not found in REQUEST');
}
$idFilter=$_REQUEST['idFilter'];
if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

$filterArray=array();
$filter=new Filter($idFilter);
$arrayDisp=array();
$arraySql=array();

// Transform FilterCriteria Object as Array
if (is_array($filter->_FilterCriteriaArray)) {
  foreach ($filter->_FilterCriteriaArray as $filterCriteria) {
    $arrayDisp["attribute"]=$filterCriteria->dispAttribute;
    $arrayDisp["operator"]=$filterCriteria->dispOperator;
    $arrayDisp["value"]=$filterCriteria->dispValue;
    $arraySql["attribute"]=$filterCriteria->sqlAttribute;
    $arraySql["operator"]=$filterCriteria->sqlOperator;
    $arraySql["value"]=$filterCriteria->sqlValue;
    $filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql);
  }
} 

if (! $comboDetail) {
  $user->_arrayFilters[$filterObjectClass]=$filterArray;
  $user->_arrayFilters[$filterObjectClass . "FilterName"]=$filter->name;
} else {
	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
  $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]=$filter->name;
}

if (array_key_exists('context',$_REQUEST) and $_REQUEST['context']=='directFilterList') {
	include "../tool/displayFilterList.php";
} else {
  htmlDisplayFilterCriteria($filterArray,$filter->name);
}

?>