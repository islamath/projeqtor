<?php 
$showIdle=(isset($_SESSION['projectSelectorShowIdle']) and $_SESSION['projectSelectorShowIdle']==1)?1:0;
$displayMode="standard";
if (isset($_SESSION['projectSelectorDisplayMode'])) {
  $displayMode=$_SESSION['projectSelectorDisplayMode'];
}
?>
<table style="width:100%">
  <tr>
    <td style="text-align: right;width:250px">
	    <?php echo i18n("labelShowIdle");?>&nbsp;:&nbsp;
	  </td>
	  <td style="text-align: left; vertical-align: middle;width:250px">
	     <div title="<?php echo i18n('showIdleElements');?>" dojoType="dijit.form.CheckBox" type="checkbox"
         <?php if ($showIdle) echo ' checked ';?>">
	       <script type="dojo/method" event="onChange" >
           dojo.xhrPost({
             url: "../tool/saveDataToSession.php?id=projectSelectorShowIdle&value="+((this.checked)?1:0),
             load: function() {loadContent("../view/menuProjectSelector.php", 'projectSelectorDiv');}
           });
           dijit.byId('dialogProjectSelectorParameters').hide();
         </script>
	     </div>
	  </td>
  </tr>
  <tr><td></td><td>&nbsp;</td></tr>
  <tr>
    <td style="text-align: right;width:250px">
      <?php echo i18n("projectListDisplayMode");?>&nbsp;:&nbsp;
    </td>
    <td style="text-align: left; vertical-align: middle;width:250px; word-wrap: none">
      <table><tr><td>
	    <input type="radio" data-dojo-type="dijit/form/RadioButton" name="displayModeCkeckbox"
	     <?php echo ($displayMode=='standard')?'checked':'';?> 
        id="displayModeCkeckboxStandard" value="standard" onClick="changeProjectSelectorType('standard');" />
        </td><td>
        <label class="display" style="background-color: white" for="displayModeCkeckboxStandard"><?php echo i18n("displayModeStandard")?></label>
        </td></tr><tr><td>
	    <input type="radio" data-dojo-type="dijit/form/RadioButton" name="displayModeCkeckbox" 
	     <?php echo ($displayMode=='select')?'checked':'';?> 
        id="displayModeCkeckboxSelect" value="select" onClick="changeProjectSelectorType('select');" />
        </td><td>
        <label class="display" style="background-color: white" for="displayModeCkeckboxSelect"><?php echo i18n("displayModeSelect")?></label>
        </td></tr></table>
    </td>
  </tr>
</table>  
<table style="width:100%">
  <tr style="border-bottom:2px solid #F0F0F0;"><td></td><td>&nbsp;</td></tr>
  <tr style="height:10px;"><td></td><td>&nbsp;</td></tr>
</table>
<table style="width:100%">
	<tr style="height:10px;">
	  <td align="center">
	   <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogProjectSelectorParameters').hide();">
	     <?php echo i18n("buttonCancel");?>
	   </button>&nbsp;
     <button dojoType="dijit.form.Button"
     onclick="refreshProjectSelectorList();">
       <?php echo i18n("buttonRefreshList");?>
     </button>
	  </td>
    <td align="center">
     
    </td>
	</tr>
</table>