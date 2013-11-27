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


if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

// Get existing filter info
if (!$comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ($comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
  $filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
  $filterArray=array();
}

$name="";
if (! $comboDetail and array_key_exists($filterObjectClass . "FilterName", $user->_arrayFilters)) {
  $name=$user->_arrayFilters[$filterObjectClass . "FilterName"];
} else if ($comboDetail and array_key_exists($filterObjectClass . "FilterName", $user->_arrayFiltersDetail)) {
  $name=$user->_arrayFiltersDetail[$filterObjectClass . "FilterName"];
}

htmlDisplayFilterCriteria($filterArray,$name); 

?>