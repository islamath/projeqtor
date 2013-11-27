<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicLisOtherVersion.php');
$refType=$_REQUEST['otherVersionRefType'];
$refId=$_REQUEST['otherVersionRefId'];
$versionType=$_REQUEST['otherVersionType'];

//otherVersionId
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$obj=new $refType($refId);

$list=array();
$proj=null;
$prod=null;
if (property_exists($refType, "idProject")) {
	$proj=$obj->idProject;
}
if (property_exists($refType, "idProject")) {
  $proj=$obj->idProject;
}
if (property_exists($refType, "idProduct")) {
  $prod=$obj->idProduct;
}
$crit=array();
if ($prod) {
  $crit=array( 'idProduct'=>$prod);
} else if ($proj) { 
	$crit=array( 'idProject'=>$proj);
}  
$list=SqlList::getListWithCrit($versionType, $crit);
?>
<select id="otherVersionIdVersion" size="14"" name="otherVersionIdVersion[]" multiple
onchange="selectOtherVersionItem();"  ondblclick="saveOtherVersion();"
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $id=>$lst) {
   $sel="";
   if (in_array($id,$selectedArray)) {
    $sel=" selected='selected' ";
    $found[$id]=true;
   }
   echo "<option value='$id'" . $sel . ">#$id - ".htmlEncode($lst)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $lstObj=new $versionType($selected);
	   echo "<option value='$lstObj->id' selected='selected' >#".$lstObj->id." - ".htmlEncode($lstObj->name)."</option>";
	 }
 }
 ?>
</select>