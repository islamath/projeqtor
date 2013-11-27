<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/planningPrint.php');
?>
  <div style="border-right: 2px solid grey; z-index:30; position:relative; overflow:hidden;" class="ganttDiv" 
    id="leftGanttChartDIV_print" name="leftGanttChartDIV_print">
  </div>
  <div style="xborder: 2px solid green; overflow:hidden; position: absolute; top: 0px;" xclass="ganttDiv" 
    id="GanttChartDIV_print" name="GanttChartDIV_print" >
    <div style="overflow:hidden;" class="ganttDiv"
      id="topGanttChartDIV_print" name="topGanttChartDIV_print">
    </div>
    <div style="xborder: 2px solid red; z-index:30; position: relative; top: 43px;" class="ganttDiv"
      id="rightGanttChartDIV_print" name="rightGanttChartDIV_print">
    </div>
  </div>
  <div id="ganttDiv"></div>