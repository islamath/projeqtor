<?php
/** ============================================================================
 * 
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListDependency.php');
$refType=$_REQUEST['dependencyRefType'];
$refId=$_REQUEST['dependencyRefId'];
//$refTypeDep=SqlList::getNameFromId('Dependable', $_REQUEST['dependencyRefTypeDep']);
$refTypeDepObj=new Dependable($_REQUEST['dependencyRefTypeDep']);
$refTypeDep=$refTypeDepObj->name;
//$id=$_REQUEST['id'];
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
	$selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$crit = array ( 'idle'=>'0');

if ($refType) {
  $obj=new $refType($refId);
  if ($refTypeDep<>"Project") {
    $crit['idProject']=$obj->idProject;
  }
}

if (class_exists ($refTypeDep) ) {
  $objList=new $refTypeDep();
  
  $list=$objList->getSqlElementsFromCriteria($crit,false,null);
} else {
  $list=array();
}
if ($refType=="Project") {
  $wbsList=SqlList::getList('Project','sortOrder');
  $sepChar=Parameter::getUserParameter('projectIndentChar');
  if (!$sepChar) $sepChar='__';
  $wbsLevelArray=array();
}
?>
<select id="dependencyRefIdDep" size="14" name="dependencyRefIdDep[]" multiple
onchange="enableWidget('dialogDependencySubmit');" ondblclick="saveDependency();" 
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $lstObj) {
 	 $sel="";
 	 if (in_array($lstObj->id,$selectedArray)) {
 	 	$sel=" selected='selected' ";
 	 	$found[$lstObj->id]=true;
 	 }
 	 $val=$lstObj->name;
   if ($refType=="Project" and $sepChar!='no') {
     $wbs=$wbsList[$lstObj->id];
     $wbsTest=$wbs;
     $level=1;
     while (strlen($wbsTest)>3) {
       $wbsTest=substr($wbsTest,0,strlen($wbsTest)-4);
       if (array_key_exists($wbsTest, $wbsLevelArray)) {
         $level=$wbsLevelArray[$wbsTest]+1;
         $wbsTest="";
       }
     }
     $wbsLevelArray[$wbs]=$level;
     $sep='';for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
     $val = $sep.$val;
   }
   echo "<option value='$lstObj->id'" . $sel . ">#".$lstObj->id." - ".htmlEncode($val)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $val=$lstObj->name;
	 	 $lstObj=new $refTypeDep($selected);
	 	 echo "<option value='$lstObj->id' selected='selected' >#".$lstObj->id." - ".htmlEncode($val)."</option>";
	 }
 }
 ?>
</select>