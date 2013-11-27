<?php
/** ============================================================================
 * Save some information about planning columns status.
 */
require_once "../tool/projeqtor.php";
Sql::beginTransaction();
$user=$_SESSION['user'];
if (! array_key_exists('action',$_REQUEST)) {
  throwError('action parameter not found in REQUEST');
}
$action=$_REQUEST['action'];
if ($action=='status') {
  if (! array_key_exists('status',$_REQUEST)) {
	throwError('status parameter not found in REQUEST');
  }
  $status=$_REQUEST['status'];
  if (! array_key_exists('item',$_REQUEST)) {
	throwError('item parameter not found in REQUEST');
  }
  $item=$_REQUEST['item'];
  $cs=new ColumnSelector($item);
  if (! $cs->id) {
  	errorLog("ERROR in saveSelectedColumn, impossible to retrieve ColumnSelector($item)");
  } else {
  	$cs->hidden=($status=='hidden')?1:0;
    $cs->save();
  }
} else if ($action=='reset') {
  if (! array_key_exists('objectClass',$_REQUEST)) {
	throwError('objectClass parameter not found in REQUEST');
  }
  $objectClass=$_REQUEST['objectClass'];
  $clause="scope='list' and objectClass='$objectClass' and idUser=$user->id ";
  $cs=new ColumnSelector();
  $resPurge=$cs->purge($clause); 
} else if ($action=='width') {
  if (! array_key_exists('width',$_REQUEST)) {
    throwError('width parameter not found in REQUEST');
  }
  $width=$_REQUEST['width'];
  if (! array_key_exists('item',$_REQUEST)) {
    throwError('item parameter not found in REQUEST');
  }
  $item=$_REQUEST['item'];
  $cs=new ColumnSelector($item);
  if (! $cs->id) {
    errorLog("ERROR in saveSelectedColumn, impossible to retrieve ColumnSelector($item)");
  } else {
    $cs->widthPct=$width;
    $cs->save();
  }
}
Sql::commitTransaction();
?>