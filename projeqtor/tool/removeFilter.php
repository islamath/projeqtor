<?php
/** ===========================================================================
 * Save a filter : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the filter info
if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

if (! array_key_exists('idFilter',$_REQUEST)) {
  throwError('idFilter parameter not found in REQUEST');
}
$idFilter=$_REQUEST['idFilter'];
Sql::beginTransaction();
$filter=new Filter($idFilter);
$filter->delete();

$flt=new Filter();
$crit=array('idUser'=> $user->id, 'refType'=>$filterObjectClass );
$filterList=$flt->getSqlElementsFromCriteria($crit, false);
htmlDisplayStoredFilter($filterList,$filterObjectClass);
Sql::commitTransaction();
?>