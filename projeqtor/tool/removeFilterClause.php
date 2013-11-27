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
if (! array_key_exists('filterClauseId',$_REQUEST)) {
  throwError('filterClauseId parameter not found in REQUEST');
}
$filterClauseId=$_REQUEST['filterClauseId'];
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

if ($filterClauseId=='all') {
  $filterArray=array();
} else {
  unset($filterArray[$filterClauseId]);
}

if (! $comboDetail) {
  $user->_arrayFilters[$filterObjectClass]=$filterArray;
  $user->_arrayFilters[$filterObjectClass . "FilterName"]="";
} else {
	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
  $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]="";
}

htmlDisplayFilterCriteria($filterArray,$name);

// save user (for filter saving)
$_SESSION['user']=$user;


?>