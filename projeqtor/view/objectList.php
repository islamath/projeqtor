<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/objectList.php');

if (! isset($comboDetail)) {
  $comboDetail=false;
}
$objectClass=$_REQUEST['objectClass'];
$objectType='';
if (array_key_exists('objectType',$_REQUEST)) {
  $objectType=$_REQUEST['objectType'];
}
$obj=new $objectClass;

if (array_key_exists('Directory', $_REQUEST)) {
	$_SESSION['Directory']=$_REQUEST['Directory'];
} else {
	unset($_SESSION['Directory']);
}
$multipleSelect=false;
if (array_key_exists('multipleSelect', $_REQUEST)) {
	if ($_REQUEST['multipleSelect']) {
		$multipleSelect=true;
	}
}
$showIdle=(! $comboDetail and isset($_SESSION['projectSelectorShowIdle']) and $_SESSION['projectSelectorShowIdle']==1)?1:0;
?>
<div dojoType="dojo.data.ItemFileReadStore" id="objectStore" jsId="objectStore" clearOnClose="true"
  url="../tool/jsonQuery.php?objectClass=<?php echo $objectClass;?><?php echo ($comboDetail)?'&comboDetail=true':'';?><?php echo ($showIdle)?'&idle=true':'';?>" >
</div>
<div dojoType="dijit.layout.BorderContainer">
<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv">
  <form dojoType="dijit.form.Form" id="quickSearchListForm" action="" method="" >
  <script type="dojo/method" event="onSubmit" >
    quickSearchExecute();
    return false;        
  </script>
  <div class="listTitle" id="quickSearchDiv" 
     style="display:none; height:100%; width: 100%; position: absolute;">
    <table >
      <tr height="100%" style="vertical-align: middle;">
        <td width="50px" align="center">
          <img src="css/images/icon<?php echo $_REQUEST['objectClass'];?>32.png" width="32" height="32" />
        </td>
        <td><span class="title"><?php echo i18n("menu" . $objectClass);?></span></td>
        <td style="text-align:right;" width="200px">
                <NOBR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php echo i18n("quickSearch");?>
                &nbsp;</NOBR> 
        </td>
        <td style="vertical-align: middle;">
          <div title="<?php echo i18n('quickSearch')?>" type="text" class="filterField" dojoType="dijit.form.TextBox" 
             id="quickSearchValue" name="quickSearchValue"
             style="width:200px;">
          </div>
        </td>

	      <td>             
	        <button title="<?php echo i18n('quickSearch')?>"  
	          dojoType="dijit.form.Button" 
	          id="listQuickSearchExecute" name="listQuickSearchExecute"
	          iconClass="iconSearch" showLabel="false">
	          <script type="dojo/connect" event="onClick" args="evt">
              //dijit.byId('quickSearchListForm').submit();
              quickSearchExecute();
          </script>
	        </button>
	      </td>      
      
        <td>
          <button title="<?php echo i18n('comboCloseButton')?>"  
            dojoType="dijit.form.Button" 
            id="listQuickSearchClose" name="listQuickSearchClose"
            iconClass="dijitEditorIcon dijitEditorIconUndo" showLabel="false">
            <script type="dojo/connect" event="onClick" args="evt">
              quickSearchClose();
            </script>
          </button>
        </td>    
      </tr>
    </table>
  </div>
  </form>
<table width="100%" class="listTitle" >
  <tr >
    <td width="50px" align="center">
      <img src="css/images/icon<?php echo $_REQUEST['objectClass'];?>32.png" width="32" height="32" />
    </td>
    <td><span class="title"><?php echo i18n("menu" . $objectClass);?></span></td>
    <td>   
      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
        <script type="dojo/method" event="onSubmit" >
          return false;        
        </script>
        <table style="width: 100%; height: 27px;">
          <tr>
            <td style="text-align:right;" width="5px">
              <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
              <input type="hidden" id="objectId" name="objectId" value="<?php if (isset($_REQUEST['objectId']))  { echo $_REQUEST['objectId'];}?>" />
              <NOBR>&nbsp;&nbsp;&nbsp;&nbsp;
              <?php echo i18n("colId");?>
              &nbsp;</NOBR> 
            </td>
            <td width="5px">
              <div title="<?php echo i18n('filterOnId')?>" style="width:50px" class="filterField" dojoType="dijit.form.TextBox" 
               type="text" id="listIdFilter" name="listIdFilter">
                <script type="dojo/method" event="onKeyUp" >
				          setTimeout("filterJsonList()",10);
                </script>
              </div>
            </td>
              <?php if ( property_exists($obj,'name')) { ?>
              <td style="text-align:right;" width="5px">
                <NOBR>&nbsp;&nbsp;&nbsp;
                <?php echo i18n("colName");?>
                &nbsp;</NOBR> 
              </td>
              <td width="5px">
                <div title="<?php echo i18n('filterOnName')?>" type="text" class="filterField" dojoType="dijit.form.TextBox" 
                id="listNameFilter" name="listNameFilter">
                  <script type="dojo/method" event="onKeyUp" >
                  setTimeout("filterJsonList()",10);
                </script>
                </div>
              </td>
              <?php }?>              
              <?php if ( property_exists($obj,'id' . $objectClass . 'Type') ) { ?>
              <td style="vertical-align: middle; text-align:right;" width="5px">
                 <NOBR>&nbsp;&nbsp;&nbsp;
                <?php echo i18n("colType");?>
                &nbsp;</NOBR>
              </td>
              <td width="5px">
                <select title="<?php echo i18n('filterOnType')?>" type="text" class="filterField" dojoType="dijit.form.FilteringSelect" 
                id="listTypeFilter" name="listTypeFilter" style="height: 14px;">
                  <?php htmlDrawOptionForReference('id' . $objectClass . 'Type', $objectType, $obj, false); ?>
                  <script type="dojo/method" event="onChange" >
                    refreshJsonList('<?php echo $objectClass;?>');
                  </script>
                </select>
              </td>
              <?php }?>              
              <?php $activeFilter=false;
                 if (! $comboDetail and is_array($_SESSION['user']->_arrayFilters)) {
                   if (array_key_exists($objectClass, $_SESSION['user']->_arrayFilters)) {
                     if (count($_SESSION['user']->_arrayFilters[$objectClass])>0) {
                       $activeFilter=true;
                     }
                   }
                 } else if ($comboDetail and is_array($_SESSION['user']->_arrayFiltersDetail)) {
                   if (array_key_exists($objectClass, $_SESSION['user']->_arrayFiltersDetail)) {
                     if (count($_SESSION['user']->_arrayFiltersDetail[$objectClass])>0) {
                       $activeFilter=true;
                     }
                   }
                 }
                 ?>
            <td >&nbsp;</td>
            <td width="5px"><NOBR>&nbsp;</NOBR></td>
<?php if (! $comboDetail or 1) {?>            
            <td width="32px">
              <button title="<?php echo i18n('quickSearch')?>"  
               dojoType="dijit.form.Button" 
               id="iconSearchOpenButton" name="iconSearchOpenButton"
               iconClass="iconSearch" showLabel="false">
                <script type="dojo/connect" event="onClick" args="evt">
                  quickSearchOpen();
                </script>
              </button>
              <span id="gridRowCountShadow1" class="gridRowCountShadow1"></span>
              <span id="gridRowCountShadow2" class="gridRowCountShadow2"></span>              
              <span id="gridRowCount" class="gridRowCount"></span>             
              <input type="hidden" id="listFilterClause" name="listFilterClause" value="" style="width: 50px;" />
            </td>
<?php }
      if (! $comboDetail or 1) {?>            
            <td width="32px">
              <button 
              title="<?php echo i18n('advancedFilter')?>"  
              class="filterField"
               dojoType="dijit.form.DropDownButton" 
               id="listFilterFilter" name="listFilterFilter"
               iconClass="icon<?php echo($activeFilter)?'Active':'';?>Filter16" showLabel="false">
                <script type="dojo/connect" event="onClick" args="evt">
                  showFilterDialog();
                </script>
                <script type="dojo/method" event="onMouseEnter" args="evt">
                  this.openDropDown();
                </script>
                <script type="dojo/method" event="onMouseLeave" args="evt">
                  closeFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').closeDropDown();",2000);
                </script>
                <div dojoType="dijit.TooltipDialog" id="directFilterList" style="z-index: 999999;display:none; position: absolute;">
                  <?php 
                     //$_REQUEST['filterObjectClass']=$objectClass;
                     //$_REQUEST['context']="directFilterList";
                     if ($comboDetail) $_REQUEST['comboDetail']=true;
                     include "../tool/displayFilterList.php";?>
                 <script type="dojo/method" event="onMouseEnter" args="evt">
                    clearTimeout(closeFilterListTimeout);
                </script>
                <script type="dojo/method" event="onMouseLeave" args="evt">
                  dijit.byId('listFilterFilter').closeDropDown();
                </script>
                </div> 
              </button>
            </td>
<?php }?>   
<?php if (! $comboDetail) {?>  
            <td  width="32px">           
							<div dojoType="dijit.form.DropDownButton"
							  style="height: 20px; color:#202020;"  
							  id="listColumnSelector" jsId="listColumnSelector" name="listColumnSelector" 
							  showlabel="false" class="" iconClass="iconColumnSelector"
							  title="<?php echo i18n('columnSelector');?>">
                <span>title</span>
							  <div dojoType="dijit.TooltipDialog" class="white" id="listColumnSelectorDialog"
							    style="position: absolute; top: 50px; right: 40%">   
                  <script type="dojo/connect" event="onHide" args="evt">
                    if (dndMoveInProgress) { this.show(); }
                  </script>
                  <script type="dojo/connect" event="onShow" args="evt">
                    recalculateColumnSelectorName();
                  </script>                 
                  <div style="text-align: center;position: relative;"> 
                    <button dojoType="dijit.form.Button" title="<?php echo i18n('titleResetList');?>"
                        id="" name="" showLabel="true"><?php echo i18n('buttonReset');?>
                        <script type="dojo/connect" event="onClick" args="evt">
                        resetListColumn();
                      </script>
                      </button>
                    <button title="" dojoType="dijit.form.Button" 
                      id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                      <script type="dojo/connect" event="onClick" args="evt">
                        validateListColumn();
                      </script>
                    </button>
                    <div style="position: absolute;top: 6px; right:3px;" id="columnSelectorTotWidthTop"></div>
                  </div>   
                  <div style="height:5px;border-bottom:1px solid #AAAAAA"></div>    
							    <div id="dndListColumnSelector" jsId="dndListColumnSelector" dojotype="dojo.dnd.Source"  
							      dndType="column"
							      withhandles="true" class="container">                       
							      <?php include('../tool/listColumnSelector.php')?>
							    </div>
                  <div style="height:5px;border-top:1px solid #AAAAAA"></div>    
                  <div style="text-align: center;position: relative;"> 
	                  <button dojoType="dijit.form.Button" title="<?php echo i18n('titleResetList');?>"
	                      id="" name="" showLabel="true"><?php echo i18n('buttonReset');?>
	                      <script type="dojo/connect" event="onClick" args="evt">
                        resetListColumn();
                      </script>
	                    </button>
                    <button title="" dojoType="dijit.form.Button" 
                      id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                      <script type="dojo/connect" event="onClick" args="evt">
                        validateListColumn();
                      </script>
                    </button>
                    <div style="position: absolute;bottom: 5px; right:3px;" id="columnSelectorTotWidthBottom"></div>
                  </div>   
							  </div>
							</div>   
             </td>
<?php }?>                 
<?php if (! $comboDetail) {?>                
             <td width="32px">
              <button title="<?php echo i18n('printList')?>"  
               dojoType="dijit.form.Button" 
               id="listPrint" name="listPrint"
               iconClass="dijitEditorIcon dijitEditorIconPrint" showLabel="false">
                <script type="dojo/connect" event="onClick" args="evt">
                  showPrint("../tool/jsonQuery.php", 'list');
                </script>
              </button>
              </td>
<?php }?>            
<?php if (! $comboDetail) {?>        
             <td width="32px">
              <button title="<?php echo i18n('reportPrintPdf')?>"  
               dojoType="dijit.form.Button" 
               id="listPrintPdf" name="listPrintPdf"
               iconClass="iconPdf" showLabel="false">
                <script type="dojo/connect" event="onClick" args="evt">
                  showPrint("../tool/jsonQuery.php", 'list', null, 'pdf');
                </script>
              </button>              
            </td>
             <td width="32px">
              <button title="<?php echo i18n('reportPrintCsv')?>"  
               dojoType="dijit.form.Button" 
               id="listPrintCsv" name="listPrintCsv"
               iconClass="iconCsv" showLabel="false">
                <script type="dojo/connect" event="onClick" args="evt">
                  openExportDialog('csv');
                  //showPrint("../tool/jsonQuery.php", 'list', null, 'csv');
                </script>
              </button>              
            </td>
<?php }?>       
<?php if (! $comboDetail) {?> 
            <td style="text-align: right; " width="5px">
              <NOBR>&nbsp;&nbsp;&nbsp;
              <?php echo i18n("labelShowIdle");?>
              </NOBR>
            </td>
            <td style="text-align: right; vertical-align: middle;" width="30px">
              <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" 
                <?php if ($showIdle) echo " checked ";?>
                type="checkbox" id="listShowIdle" name="listShowIdle">
                <script type="dojo/method" event="onChange" >
                  refreshJsonList('<?php echo $objectClass;?>');
                </script>
              </div>&nbsp;
            </td>
<?php }?>           
          </tr>
        </table>    
      </form>
    </td>
  </tr>
</table>
<div id="listBarShow" onMouseover="showList('mouse')" onClick="showList('click');">
  <div id="listBarIcon" align="center"></div>
</div>
</div>
<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv">
<table id="objectGrid" jsId="objectGrid" dojoType="dojox.grid.DataGrid"
  query="{ id: '*' }" store="objectStore"
  queryOptions="{ignoreCase:true}" 
  rowPerPage="<?php echo Parameter::getGlobalParameter('paramRowPerPage');?>"
  columnReordering="false"
  rowSelector="false"
  onHeaderCellContextMenu="dijit.byId('listColumnSelector').toggleDropDown();"
  selectionMode="<?php echo ($multipleSelect)?'extended':'single';?>" >
  <thead>
    <tr>
      <?php echo $obj->getLayout();?>
    </tr>
  </thead>
  <script type="dojo/connect" event="onSelected" args="evt">
    if (gridReposition) {return;}
    if (multiSelection) {updateSelectedCountMultiple();return;}
	  if ( dojo.byId('comboDetail') ) {
      rows=objectGrid.selection.getSelected();
      row=rows[0]; 
      dojo.byId('comboDetailId').value=row.id;
      dojo.byId('comboDetailId').value=dojo.byId('comboDetailId').value.replace(/^[0]+/g,"");
      dojo.byId('comboDetailName').value=row.name;
      return true;
    }
    actionYes = function () {
      rows=objectGrid.selection.getSelected();
      row=rows[0]; 
      var id = row.id;
	  dojo.byId('objectId').value=id;
	  //cleanContent("detailDiv");
      formChangeInProgress=false; 
      listClick();
      loadContent("objectDetail.php", "detailDiv", 'listForm');
   	}
    actionNo = function () {
	    //unselectAllRows("objectGrid");
      selectRowById('objectGrid', parseInt(dojo.byId('objectId').value));
    }
    if (checkFormChangeInProgress(actionYes, actionNo)) {
      return true;
    }
  </script>
  <script type="dojo/connect" event="onDeselected" args="evt">
    if (multiSelection) {updateSelectedCountMultiple();return;}
  </script>
  <script type="dojo/method" event="onRowDblClick" args="row">
    if ( dojo.byId('comboDetail') ) {
      rows=objectGrid.selection.getSelected();
      row=rows[0]; 
      dojo.byId('comboDetailId').value=row.id;
      dojo.byId('comboDetailId').value=dojo.byId('comboDetailId').value.replace(/^[0]+/g,"");
      dojo.byId('comboDetailName').value=row.name;
      top.selectDetailItem();
      return;
    }
  </script>
  <script type="dojo/connect" event="_onFetchComplete" args="items, req">
     refreshGridCount();
  </script>
</table>
</div>
</div>