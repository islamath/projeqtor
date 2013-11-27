<?php 
/* ============================================================================
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/importData.php');  
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Import" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="importDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top" splitter="false">
    <form dojoType="dijit.form.Form" id="importDataForm" 
      ENCTYPE="multipart/form-data" method=POST
      action="../tool/import.php"
      target="resultImportData"
      onSubmit="return importData();" >
    <table width="100%">
      <tr>
        <td width="50px" align="center">
          <img src="css/images/iconImportData32.png" width="32" height="32" />
        </td>
        <td NOWRAP width="30%" class="title" >
          <?php echo i18n('menuImportData')?>&nbsp;&nbsp;&nbsp;
        </td>
        <td width="10px" >&nbsp;
        </td>
        <td class="white" width="10%" nowrap align="right" >
          <?php echo i18n("colImportElementType") ?>&nbsp;&nbsp;
        </td>
        <td width="10%" >
          <select dojoType="dijit.form.FilteringSelect" 
            id="elementType" name="elementType" 
            class="input" value="" style="width: 200px;">
            <?php htmlDrawOptionForReference('idImportable', null, null, true);?>
           </select> 
        </td>
        <td  align="left"> 
          <button id="helpImportData" iconClass="iconHelp" dojoType="dijit.form.Button" type="button" showlabel="false"
          title="<?php echo i18n('helpImport');?>">
             <script type="dojo/connect" event="onClick" args="evt">
               showHelpImportData();
               return false;
             </script>
          </button>        
        </td>
      </tr>
      <tr>
        <td colspan="3">
        </td>
        <td class="white" nowrap align="right">
          <?php echo i18n("colImportFileType") ?>&nbsp;&nbsp;
        </td>
        <td width="10px" >
          <select dojoType="dijit.form.FilteringSelect" 
            id="fileType" name="fileType" 
            class="input" value="csv" style="width: 200px;">
              <option value="csv"><?php echo i18n('csvFile')?></option>
              <option value="xlsx"><?php echo i18n('xlsxFile')?></option>
           </select> 
        </td>
        <td></td>
      </tr>
      <tr height="30px">
        <td colspan="3">
        </td>
        <td class="white" nowrap align="right">
         <?php echo i18n("colFile");?>&nbsp;&nbsp;
        </td>
        <td>
         <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>" />     
         <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
          dojoType="dojox.form.FileInput" type="file"
          style="color: #000000;" 
          name="importFile" id="importFile" 
          cancelText="<?php echo i18n("buttonReset");?>"
          label="<?php echo i18n("buttonBrowse");?>"
          title="<?php echo i18n("helpSelectFile");?>" />
        </td>
      </tr>
      <tr>
        <td colspan="4"></td>
        <td>
          <button id="runImportData" dojoType="dijit.form.Button" style="color: #000000;" type="submit">
            <?php echo i18n("buttonImportData");?>
          </button>
         </td>
         <td></td>
      </tr>
    </table>
    </form>
  </div>
  <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center">
   <iframe width="100%" height="100%" name="resultImportData" id="resultImportData"></iframe>
  </div>
</div>  