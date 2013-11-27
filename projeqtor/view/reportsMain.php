<?php 
/* ============================================================================
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/reportsMain.php');  
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Report" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="listReportDiv" dojoType="dijit.layout.ContentPane" region="top" splitter="true" style="height:220px;">
   <?php include 'reportsList.php'?>
  </div>
  <div id="detailReportDiv" dojoType="dijit.layout.ContentPane" region="center">
   <?php $noselect=true; //include 'objectDetail.php'; ?>
  </div>
</div>  