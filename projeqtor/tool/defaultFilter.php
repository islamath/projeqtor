<?php
/** ===========================================================================
 * Save filter from User to Session to be able to restore it
 * Retores it if cancel is set
 * Cleans it if clean is set
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";
//traceLog("defaultFilter.php");

$user=$_SESSION['user'];

if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];
$name="";
if (array_key_exists('filterName',$_REQUEST)) {
  $name=$_REQUEST['filterName'];
}

/*
$filterName='stockFilter' . $filterObjectClass;
if ($cancel) {
  if (array_key_exists($filterName,$_SESSION)) {
    $user->_arrayFilters[$filterObjectClass]=$_SESSION[$filterName];
    $_SESSION['user']=$user;
  } else {
    if (array_key_exists($filterObjectClass, $user->_arrayFilters)) {
      unset($user->_arrayFilters[$filterObjectClass]);
      $_SESSION['user']=$user;
    }
  }
} 
if ($clean or $cancel or $valid) {
   if (array_key_exists($filterName,$_SESSION)) {
     unset($_SESSION[$filterName]);
   }
}
if ( ! $clean and ! $cancel and !$valid) {
  if (array_key_exists($filterObjectClass,$user->_arrayFilters)) {
    $_SESSION[$filterName]= $user->_arrayFilters[$filterObjectClass];
  } else {
    $_SESSION[$filterName]=array();
  }
}

if ($valid or $cancel) {
  $user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
  $_SESSION['user']=$user;
}
*/
Sql::beginTransaction();
echo '<table width="100%"><tr><td align="center">';
$crit=array();
$crit['idUser']=$user->id;
$crit['idProject']=null;
$crit['parameterCode']="Filter" . $filterObjectClass;
$param=SqlElement::getSingleSqlElementFromCriteria('Parameter',$crit);
if ($name) {
  $critFilter=array("refType"=>$filterObjectClass, "name"=>$name, "idUser"=>$user->id);
  $filter=SqlElement::getSingleSqlElementFromCriteria("Filter", $critFilter);
  if (! $filter->id) {
    echo '<span class="messageERROR">' . i18n('defaultFilterError', array($name)) . '</span>';
  } else {
    $param->parameterValue=$filter->id;
    $param->save();
    echo '<span class="messageOK">' . i18n('defaultFilterSet', array($name)) . '</span>';
  }
} else {
  $param->delete();
  echo '<span class="messageOK">' . i18n('defaultFilterCleared') . '</span>';
}
echo '</td></tr></table>';
Sql::commitTransaction();
$flt=new Filter();
$crit=array('idUser'=> $user->id, 'refType'=>$filterObjectClass );
$filterList=$flt->getSqlElementsFromCriteria($crit, false);
htmlDisplayStoredFilter($filterList,$filterObjectClass);
?>