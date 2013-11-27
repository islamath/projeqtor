<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListOrigin.php');
$refType=$_REQUEST['originRefType'];
$refId=$_REQUEST['originRefId'];
$originTypeObj=new Originable($_REQUEST['originOriginType']);
$originType=$originTypeObj->name;
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}

if ($originType) {
  $obj=new $refType($refId);
  $crit = array ( 'idle'=>'0', 'idProject'=>$obj->idProject);
	$objList=new $originType();
  $list=$objList->getSqlElementsFromCriteria($crit,false,null);
} else {
	$list=array();
}

?>
<select id="originOriginId" size="14" name="originOriginId"
onchange="enableWidget('dialogOriginSubmit');"  ondblclick="saveOrigin();"
class="selectList" >
 <?php
 $found=false;
 foreach ($list as $lstObj) {
   $sel="";
   if ($lstObj->id==$selected) {
    $sel=" selected='selected' ";
    $found=true;
   }
   echo "<option value='$lstObj->id'" . $sel . ">#".$lstObj->id." - ".htmlEncode($lstObj->name)."</option>";
 }
 if ($selected and ! $found) {
   $lstObj=new $originType($selected);
   echo "<option value='$lstObj->id' selected='selected' >#".$lstObj->id." - ".htmlEncode($lstObj->name)."</option>";
 }
 ?>
</select>