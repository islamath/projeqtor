<?php 
/* ============================================================================
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/portfolioPlanningMain.php');  
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Planning" />
<input type="hidden" name="portfolioPlanning" id="portfolioPlanning" value="true" />
<div id="mainDivContainer" class="container" dojoType="dijit.layout.BorderContainer">
  <div id="listDiv" dojoType="dijit.layout.ContentPane" region="top" splitter="true" style="height:60%;">
   <?php include 'portfolioPlanningList.php'?>
  </div>
  <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center">
   <?php $noselect=true; //include 'objectDetail.php'; ?>
  </div>
</div>  