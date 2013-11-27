<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/portfolioPlanningList.php');

$canPlan=false;
$right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'planning'));
if ($right) {
  $list=new ListYesNo($right->rightAccess);
  if ($list->code=='YES') {
    $canPlan=true;
  }
}
$startDate=date('Y-m-d');
$endDate=null;
$user=$_SESSION['user'];
$saveDates=false;
$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
if ($paramStart->id) {
  $startDate=$paramStart->parameterValue;
  $saveDates=true;
}
$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
if ($paramEnd->id) {
  $endDate=$paramEnd->parameterValue;
  $saveDates=true;
}
$saveShowWbsObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWbs'));
$saveShowWbs=$saveShowWbsObj->parameterValue;
$saveShowResourceObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowResource'));
$saveShowResource=$saveShowResourceObj->parameterValue;
$saveShowWorkObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWork'));
$saveShowWork=$saveShowWorkObj->parameterValue;
$saveShowClosedObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowClosed'));
$saveShowClosed=$saveShowClosedObj->parameterValue;
$saveShowMilestoneObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowMilestone'));
$saveShowMilestone=$saveShowMilestoneObj->parameterValue;

if ($saveShowClosed) {
	$_REQUEST['idle']=true;
}
//$objectClass='Task';
//$obj=new $objectClass;
?>
  
<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px">
		<table width="100%" height="27px" class="listTitle" >
		  <tr height="27px">
		    <td width="50px" align="center">
		      <span style="position:absolute; left:10px; top:7px">
            <img src="css/images/iconPortfolioPlanning32.png" width="32" height="32" />
          </span>
		    </td>
		    <td><span class="title"><?php echo i18n('menuPortfolioPlanning');?></span></td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
		        <table style="width: 100%;">
		          <tr>
		            <td>
		              <input type="hidden" id="objectClass" name="objectClass" value="" /> 
		              <input type="hidden" id="objectId" name="objectId" value="" />
                  <input type="hidden" id="portfolio" name="portfolio" value="true" />
		              &nbsp;&nbsp;&nbsp;
<?php if ($canPlan) { ?>
		              <button id="planButton" dojoType="dijit.form.Button" showlabel="false"
		                title="<?php echo i18n('buttonPlan');?>"
		                iconClass="iconPlan" style="display:none">
		                <script type="dojo/connect" event="onClick" args="evt">
                     showPlanParam();
                     return false;
                    </script>
		              </button>
<?php }?>             
		            </td>
		            <td style="white-space:nowrap;">
		              <table>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayStartDate");?>&nbsp;&nbsp;</td><td>
                        <div dojoType="dijit.form.DateTextBox"
                           id="startDatePlanView" name="startDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input"
                           hasDownArrow="true"
                           value="<?php echo $startDate;?>" >
                           <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                           </script>
                         </div>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayEndDate");?>&nbsp;&nbsp;</td>
                      <td>
                        <div dojoType="dijit.form.DateTextBox"
                           id="endDatePlanView" name="endDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input"
                           hasDownArrow="true"
                           value="<?php echo $endDate;?>" >
                           <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                           </script>
                        </div>
                      </td>
                    </tr>
                  </table>
		            </td>
                <td>
                  <table >
                    <tr>
                      <td width="32px">
                        <button title="<?php echo i18n('printPlanning')?>"
                         dojoType="dijit.form.Button"
                         id="listPrint" name="listPrint"
                         iconClass="dijitEditorIcon dijitEditorIconPrint" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
<?php $ganttPlanningPrintOldStyle=Parameter::getGlobalParameter('ganttPlanningPrintOldStyle');
      if (!$ganttPlanningPrintOldStyle) {$ganttPlanningPrintOldStyle="NO";}
      if ($ganttPlanningPrintOldStyle=='YES') {?>
                          showPrint("../tool/jsonPlanning.php?portfolio=true", 'planning');
<?php } else { ?>
                          showPrint("planningPrint.php", 'planning');
<?php }?>   
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <button title="<?php echo i18n('reportPrintPdf')?>"
                         dojoType="dijit.form.Button"
                         id="listPrintPdf" name="listPrintPdf"
                         iconClass="iconPdf" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                          //showPrint("../tool/jsonPlanning.php", 'planning', null, 'pdf');
                           showPrint("../tool/jsonPlanning_pdf.php?portfolio=true", 'planning', null, 'pdf');
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <button title="<?php echo i18n('reportExportMSProject')?>"
                         dojoType="dijit.form.Button"
                         id="listPrintMpp" name="listPrintMpp"
                         iconClass="iconMpp" showLabel="false" style="display:none">
                          <script type="dojo/connect" event="onClick" args="evt">
                          showPrint("../tool/jsonPlanning.php", 'planning', null, 'mpp');
                          </script>
                        </button>
                        <input type="hidden" id="outMode" name="outMode" value="" />
                      </td>
                      <td>
                       <div dojoType="dijit.form.DropDownButton"
                             style="height: 20px; color:#202020;"  
                             id="planningColumnSelector" jsId="planningColumnSelector" name="planningColumnSelector" 
                             showlabel="false" class="" iconClass="iconColumnSelector"
                             title="<?php echo i18n('columnSelector');?>">
                          <span>title</span>
                          <div dojoType="dijit.TooltipDialog" class="white" style="width:200px;">
                            <script type="dojo/connect" event="onHide" args="evt">
                              if (dndMoveInProgress) { this.show(); }
                            </script>   
                            <div id="dndPlanningColumnSelector" jsId="dndPlanningColumnSelector" dojotype="dojo.dnd.Source"  
                             dndType="column"
                             withhandles="true" class="container">    
                               <?php 
                                 $portfolioPlanning=true; 
                                 include('../tool/planningColumnSelector.php')?>
                            </div> 
                            <div style="height:5px;"></div>    
                            <div style="text-align: center;"> 
                              <button title="" dojoType="dijit.form.Button" 
                                id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                                <script type="dojo/connect" event="onClick" args="evt">
                                  validatePlanningColumn();
                                </script>
                              </button>
                            </div>                
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" style="white-space:nowrap;">
                        <span title="<?php echo i18n('saveDates')?>" dojoType="dijit.form.CheckBox"
                           type="checkbox" id="listSaveDates" name="listSaveDates" class=""
                           <?php if ( $saveDates) {echo 'checked="checked"'; } ?>  >

                          <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                          </script>
                        </span>
                        <span for="listSaveDates"><?php echo i18n("saveDates");?></span>
                      </td>
                    </tr>
                  </table>
                </td>
		            <td>
                  <div id="planResultDiv" style=" width: 200px;height: 10px;" 
                    dojoType="dijit.layout.ContentPane" region="center" >
                  </div>
                </td>
		            <td style="text-align: right; align: right;">
		              <table width="100%">
                    <tr style="height:10px">
                      <td><?php echo i18n("labelShowWbs");?></td>
                      <td style="width:25px">
					              <div title="<?php echo i18n('showWbs')?>" dojoType="dijit.form.CheckBox" 
			                    type="checkbox" id="showWBS" name="showWBS"
			                    <?php if ($saveShowWbs=='1') { echo ' checked="checked" '; }?> >
					                <script type="dojo/method" event="onChange" >
                            saveUserParameter('planningShowWbs',((this.checked)?'1':'0'));
                            refreshJsonPlanning();
                          </script>
					              </div>&nbsp;
		                  </td>
                    </tr>
                    <tr>
                      <td><?php echo i18n("labelShowIdle");?></td>
                      <td>
					              <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" 
			                    type="checkbox" id="listShowIdle" name="listShowIdle"
			                    <?php if ($saveShowClosed=='1') { echo ' checked="checked" '; }?> >
					                <script type="dojo/method" event="onChange" >
                            saveUserParameter('planningShowClosed',((this.checked)?'1':'0'));
                            refreshJsonPlanning();
                          </script>
					              </div>&nbsp;
                      </td>
                    </tr>                 
                    <tr>
                    <td colspan="2">
                      <?php echo i18n("colListShowMilestone");?>                  
				                <select dojoType="dijit.form.FilteringSelect" class="input" 
				                  style="width: 150px;"
				                  name="listShowMilestone" id="listShowMilestone">
				                  <script type="dojo/method" event="onChange" >
                            saveUserParameter('planningShowMilestone',this.value);
                            refreshJsonPlanning();
                          </script>
                            <OPTION value=" " <?php echo (! $saveShowMilestone)?'SELECTED':'';?>><?php echo i18n("paramNone");?></OPTION>                            
                            <?php htmlDrawOptionForReference('idMilestoneType', $saveShowMilestone,null, true);?>
                            <OPTION value="all" <?php echo ($saveShowMilestone=='all')?'SELECTED':'';?>><?php echo i18n("all");?></OPTION>
			                  </select>
                      </td>
                    </tr>
                  </table>
		            </td>
		          </tr>
		        </table>    
		      </form>
		    </td>
		  </tr>
		</table>
		<div id="listBarShow" onMouseover="showList('mouse')" onClick="showList('click');">
		  <div id="listBarIcon" align="center"></div>
		</div>
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
		        $portfolio=true;
            include '../tool/jsonPlanning.php';
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv">
   <div id="submainPlanningDivContainer" dojoType="dijit.layout.BorderContainer"
    style="border-top:1px solid #ffffff;">
        <?php $leftPartSize=Parameter::getUserParameter('planningLeftSize');
          if (! $leftPartSize) {$leftPartSize='325px';} ?>
	   <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" 
      style="width:<?php echo $leftPartSize;?>; height:100%; overflow-x:scroll; overflow-y:hidden;" class="ganttDiv" 
      id="leftGanttChartDIV" name="leftGanttChartDIV"
      onScroll="dojo.byId('ganttScale').style.left=(this.scrollLeft)+'px'; this.scrollTop=0;" 
      onmousewheel="leftMouseWheel(event);">
      <script type="dojo/method" event="onUnload" >
         var width=this.domNode.style.width;
         setTimeout("saveUserParameter('planningLeftSize','"+width+"');",1);
         return true;
      </script>
     </div>
     <div dojoType="dijit.layout.ContentPane" region="center" 
      style="height:100%; overflow:hidden;" class="ganttDiv" 
      id="GanttChartDIV" name="GanttChartDIV" >
       <div id="mainRightPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:43px; overflow:hidden;" class="ganttDiv"
          id="topGanttChartDIV" name="topGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
          id="rightGanttChartDIV" name="rightGanttChartDIV"
          onScroll="dojo.byId('rightside').style.left='-'+(this.scrollLeft+1)+'px';
                    dojo.byId('leftside').style.top='-'+(this.scrollTop)+'px';"
         >
         </div>
       </div>
     </div>
   </div>
	</div>
</div>
