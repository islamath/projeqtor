<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListApprover.php');
$refType=$_REQUEST['approverRefType'];
$refId=$_REQUEST['approverRefId'];

$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$obj=new $refType($refId);

$objList=new Affectable();
$aff=new Affectation();
$critWhere = "idle='0' and exists(select 'x' from " . $aff->getDatabaseTableName() . " aff ";
$critWhere .= " where aff.idResource=" . $objList->getDatabaseTableName() . ".id ";
$critWhere .= ($obj->idProject)?" and aff.idProject='" . Sql::fmtId($obj->idProject) . "'":"";
$critWhere .= ")";
$list=$objList->getSqlElementsFromCriteria(null,false,$critWhere, 'name asc');

?>
<select id="approverId" size="14"" name="approverId[]" multiple
onchange="selectApproverItem();"  ondblclick="saveApprover();"
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $lstObj) {
   $sel="";
   if (in_array($lstObj->id,$selectedArray)) {
    $sel=" selected='selected' ";
    $found[$lstObj->id]=true;
   }
   $name=($lstObj->name)?$lstObj->name:$lstObj->userName;
   echo "<option value='$lstObj->id'" . $sel . ">#".$lstObj->id." - ".htmlEncode($name)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $lstObj=new Affectable($selected);
	   $name=($lstObj->name)?$lstObj->name:$lstObj->userName;
	   echo "<option value='$lstObj->id' selected='selected' >#".$lstObj->id." - ".htmlEncode($name)."</option>";
	 }
 }
 ?>
</select>