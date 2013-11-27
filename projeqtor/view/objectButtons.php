<?php
/* ============================================================================
 * Presents the action buttons of an object.
 * 
 */ 
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/objectButton.php'); 
  if (! isset($comboDetail)) {
    $comboDetail=false;
  }
  $obj=new $_REQUEST['objectClass']();
?>
<table>
  <tr>
    <td width="50px" align="center">
      <img src="css/images/icon<?php echo $_REQUEST['objectClass'];?>32.png" width="32" height="32" />
    </td>
    <td><span class="title"><?php echo i18n($_REQUEST['objectClass']);?></span></td>
    <td width="15px">&nbsp;</td>
    <td><nobr>
    <?php if (! $comboDetail ) {?>
      <button id="newButton" dojoType="dijit.form.Button" showlabel="false" 
       title="<?php echo i18n('buttonNew', array(i18n($_REQUEST['objectClass'])));?>"
       iconClass="dijitEditorIcon dijitEditorIconNew" >
        <script type="dojo/connect" event="onClick" args="evt">
		  dojo.byId("newButton").blur();
          id=dojo.byId('objectId');
	      if (id) { 	
		    id.value="";
		    unselectAllRows("objectGrid");
            loadContent("objectDetail.php", "detailDiv", dojo.byId('listForm'));
          } else { 
            showError(i18n("errorObjectId"));
	      }
        </script>
      </button>
      <button id="saveButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonSave', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?>
       iconClass="dijitEditorIcon dijitEditorIconSave" >
        <script type="dojo/connect" event="onClick" args="evt">
		    dojo.byId("saveButton").blur();
          //unselectAllRows("objectGrid");
	        submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);  
        </script>
      </button>
      <button id="printButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonPrint', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitEditorIcon dijitEditorIconPrint" >
        <script type="dojo/connect" event="onClick" args="evt">
		    dojo.byId("printButton").blur();
        if (dojo.byId("printPdfButton")) {dojo.byId("printPdfButton").blur();}
		    showPrint("objectDetail.php");
        </script>
      </button>  
<?php if ($_REQUEST['objectClass']!='Workflow' and $_REQUEST['objectClass']!='Mail') {?>    
     <button id="printButtonPdf" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('reportPrintPdf');?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="iconPdf" >
        <script type="dojo/connect" event="onClick" args="evt">
        dojo.byId("printButton").blur();
        if (dojo.byId("printPdfButton")) {dojo.byId("printPdfButton").blur();}
        showPrint("objectDetail.php", null, null, 'pdf');
        </script>
      </button>   
<?php } 
      if (! (property_exists($_REQUEST['objectClass'], '_noCopy')) ) { ?>
      <button id="copyButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonCopy', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?>
       iconClass="dijitEditorIcon dijitEditorIconCopy" >
        <script type="dojo/connect" event="onClick" args="evt">
          <?php 
          $crit=array('name'=> $_REQUEST['objectClass']);
          if ( $_REQUEST['objectClass'] == "Project") {
            echo "copyProject();";
          } else {
            $copyable=SqlElement::getSingleSqlElementFromCriteria('Copyable', $crit);
	          if ($copyable->id) {
	            echo "copyObjectTo('" . $_REQUEST['objectClass'] . "');";
	          } else {
	            echo "copyObject('" .$_REQUEST['objectClass'] . "');";
	          }
          }
          ?>
        </script>
      </button>    
<?php }?>
      <button id="undoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonUndo', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect or 1) {echo "disabled";} ?>
       iconClass="dijitEditorIcon dijitEditorIconUndo" >
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("undoButton").blur();
          loadContent("objectDetail.php", "detailDiv", 'listForm');
          formChangeInProgress=false;
        </script>
      </button>    
      <button id="deleteButton" dojoType="dijit.form.Button" showlabel="false" 
       title="<?php echo i18n('buttonDelete', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitEditorIcon dijitEditorIconDelete" >
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("deleteButton").blur();
		      action=function(){
            //unselectAllRows('objectGrid');
		        loadContent("../tool/deleteObject.php", "resultDiv", 'objectForm', true);
          };
          var alsoDelete="";
		      if (dojo.byId('nbAttachements')) {
            if (dojo.byId('nbAttachements').value>0) {
              alsoDelete+="<br/><br/>" + i18n('alsoDeleteAttachement', new Array(dojo.byId('nbAttachements').value) );
            }
          }
          showConfirm(i18n("confirmDelete", new Array("<?php echo i18n($_REQUEST['objectClass']);?>",dojo.byId('id').value))+alsoDelete ,action);
        </script>
      </button>    
     <button id="refreshButton" dojoType="dijit.form.Button" showlabel="false" 
       title="<?php echo i18n('buttonRefresh', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitEditorIcon dijitEditorIconRefresh" >
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("refreshButton").blur();
          loadContent("objectDetail.php", "detailDiv", 'listForm');
        </script>
      </button>    
    <?php } 
    $clsObj=get_class($obj);
    if ($clsObj=='TicketSimple') {$clsObj='Ticket';}
    $mailable=SqlElement::getSingleSqlElementFromCriteria('Mailable', array('name'=>$clsObj));
    if ($mailable and $mailable->id) {
    ?>
     <button id="mailButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonMail', array(i18n($clsObj)));?>"
       <?php if ($noselect) {echo "disabled";} ?>
       iconClass="dijitEditorIcon dijitEditorIconMail" >
        <script type="dojo/connect" event="onClick" args="evt">
          showMailOptions();  
        </script>
      </button>
    <?php 
    if (! array_key_exists('planning',$_REQUEST)) {?> 
    <span id="multiUpdateButtonDiv">
    <button id="multiUpdateButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonMultiUpdate');?>"
       iconClass="dijitEditorIcon dijitEditorIconMultipleUpdate" >
        <script type="dojo/connect" event="onClick" args="evt">
          startMultipleUpdateMode('<?php echo get_class($obj);?>');  
        </script>
    </button>
    </span>  
      <?php
        }
      } 
        $id=null;
        $class=$_REQUEST['objectClass'];
        if (array_key_exists('objectId',$_REQUEST)) {
          $id=$_REQUEST['objectId'];
          $obj=new $class($id);
        }
        $createRight=securityGetAccessRightYesNo('menu' . $class, 'create');
        $updateRight=securityGetAccessRightYesNo('menu' . $class, 'update', $obj);
        $deleteRight=securityGetAccessRightYesNo('menu' . $class, 'delete', $obj);
      ?>
      <input type="hidden" id="createRight" name="createRight" value="<?php echo $createRight;?>" />
      <input type="hidden" id="updateRight" name="updateRight" value="<?php echo $updateRight;?>" />
      <input type="hidden" id="deleteRight" name="deleteRight" value="<?php echo $deleteRight;?>" />
    </nobr></td>
  </tr>
</table>
