<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/refrehImputationList.php'); 
$rangeType=$_REQUEST['rangeType'];
$rangeValue=$_REQUEST['rangeValue'];
$userId=$_REQUEST['userId'];
$idle=false;
if (array_key_exists('idle',$_REQUEST)) {
    $idle=$_REQUEST['idle'];
}
$showPlannedWork=false;
if (array_key_exists('showPlannedWork',$_REQUEST)) {
    $showPlannedWork=$_REQUEST['showPlannedWork'];
}
?>
<form dojoType="dijit.form.Form" id="listForm" action="" method="post" >
  <input type="hidden" name="userId" id="userId" value="<?php echo $userId;?>"/>
  <input type="hidden" name="rangeType" id="rangeType" value="<?php echo $rangeType;?>"/>
  <input type="hidden" name="rangeValue" id="rangeValue" value="<?php echo $rangeValue;?>"/>
  <input type="checkbox" name="idle" id="idle" style="display: none;"/>
  <input type="checkbox" name="showPlannedWork" id="showPlannedWork" style="display: none;">
  <input type="hidden" id="page" name="page" value="../report/imputation.php"/>
  <input type="hidden" id="outMode" name="outMode" value="" />
<?php 
ImputationLine::drawLines($userId, $rangeType, $rangeValue, $idle, $showPlannedWork);
?>
</form>