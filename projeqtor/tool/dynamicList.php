<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicList.php');
$ref1Type=$_REQUEST['linkRef1Type'];
$ref1Id=$_REQUEST['linkRef1Id'];
$ref2Type=SqlList::getNameFromId('Linkable', $_REQUEST['linkRef2Type']);
//$id=$_REQUEST['id'];

$obj=new $ref1Type($ref1Id);

$crit = array ( 'idle'=>'0', 'idProject'=>$obj->idProject);

$objList=new $ref2Type();
$list=$objList->getSqlElementsFromCriteria($crit,false,null);
if ($ref2Type=="Project") {
	$wbsList=SqlList::getList('Project','sortOrder');
  $sepChar=Parameter::getUserParameter('projectIndentChar');
  if (!$sepChar) $sepChar='__';
}
?>
<select id="linkRef2Id" multiple="false" name="linkRef2Id"
onchange="enableWidget('dialogLinkSubmit');"  
class="selectList" >
 <?php
 foreach ($list as $lstObj) {
 	 $val=$lstObj->name;
   if ($ref2Type=="Project" and $sepChar!='no') {
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
   echo "<option value='$lstObj->id'>#".$lstObj->id." - ".htmlEncode($val)."</option>";
 }
 ?>
</select>