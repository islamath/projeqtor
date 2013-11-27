<?php
scriptLog('dynamicDialogDocumentVersion.php');
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
	$isIE=$_REQUEST['isIE'];
} 
?>
  <form id='documentVersionForm' name='documentVersionForm' jsId='documentVersionForm' 
  ENCTYPE="multipart/form-data" method=POST
  <?php if ($isIE and $isIE<=9) {?>
    action="../tool/saveDocumentVersion.php?isIE=<?php echo $isIE;?>"
    target="documentVersionPost"
    onSubmit="return saveDocumentVersion();"
  <?php }?> 
  >
    <input id="documentVersionId" name="documentVersionId" type="hidden" value="" />
    <input id="documentVersionVersion" name="documentVersionVersion" type="hidden" value="" />
    <input id="documentVersionRevision" name="documentVersionRevision" type="hidden" value="" />
    <input id="documentVersionDraft" name="documentVersionDraft" type="hidden" value="" />
    <input id="documentVersionNewVersion" name="documentVersionNewVersion" type="hidden" value="" />
    <input id="documentVersionNewRevision" name="documentVersionNewRevision" type="hidden" value="" />
    <input id="documentVersionNewDraft" name="documentVersionNewDraft" type="hidden" value="" />
    <input id="documentId" name="documentId" type="hidden" value="" />
    <input id="documentVersionMode" name="documentVersionMode" type="hidden" value="" />
    <input id="typeEvo" name="typeEvo" type="hidden" value="" />
<div id="inputFileDocumentVersion" name="inputFileDocumentVersion">
    <table>
      <tr height="30px"> 
        <td class="dialogLabel" >
         <label for="documentVersionFile" ><?php echo i18n("colFile");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
         <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>" />     
         <?php  if ($isIE and $isIE<=9) {?>
         <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
          dojoType="dojox.form.FileInput" type="file" 
          name="documentVersionFile" id="documentVersionFile" 
          cancelText="<?php echo i18n("buttonReset");?>"
          label="<?php echo i18n("buttonBrowse");?>"
          title="<?php echo i18n("helpSelectFile");?>" />
         <?php } else {?>  
         <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
          dojoType="dojox.form.Uploader" type="file" 
          url="../tool/saveDocumentVersion.php"
          name="documentVersionFile" id="documentVersionFile" 
          cancelText="<?php echo i18n("buttonReset");?>"
          multiple="false" 
          onBegin="saveDocumentVersion();"
          onChange="changeDocumentVersion(this.getFileList());"
          onError="dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
          label="<?php echo i18n("buttonBrowse");?>"
          title="<?php echo i18n("helpSelectFile");?>"  />
         <?php }?>
         <i><span name="documentVersionFileName" id="documentVersionFileName"></span></i> 
        </td>
      </tr>
      <tr><td colspan="2"><div style="display:none"><table>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionLink" ><?php echo i18n("colOrLink");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionLink" name="documentVersionLink"
          style="width: 450px;"
          maxlength="400"
          class="input">  
         </div>  
        </td>
      </tr>
      </table></div></td></tr>
    </table>
</div>
    <table>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionVersionDisplay" ><?php echo i18n("colCurrentDocumentVersion");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionVersionDisplay" name="documentVersionVersionDisplay"
          style="width: 450px;" readonly
          maxlength="100"
          class="input">  
         </div>  
        </td>
      </tr>            
      <tr style="height:21px">
        <td class="dialogLabel" >
         <label for="documentVersionUpdateMajor" ><?php echo i18n("documentVersionUpdate");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <table><tr>
            <td style="text-align:right; width:5%;">
              <label class="smallRadioLabel" for="documentVersionUpdateMajor"><?php echo i18n('versionMajorUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;"> 
              <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
               name="documentVersionUpdate" id="documentVersionUpdateMajor"
               checked value="major" style="background-color:white;"/>
            </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateMinor"><?php echo i18n('versionMinorUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;">    
              <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
               name="documentVersionUpdate" id="documentVersionUpdateMinor"
               value="minor" style="background-color:white;"/>
            </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateNo"><?php echo i18n('versionNoUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;">    
              <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
               name="documentVersionUpdate" id="documentVersionUpdateNo"
               value="no" style="background-color:white;"/>
            </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateDraft"><?php echo i18n('versionDraftUpdate');?>&nbsp;</label>
            </td>
            <td style="text-align:right; width:5%">
            <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.CheckBox" 
             name="documentVersionUpdateDraft" id="documentVersionUpdateDraft"
             value="draft" style="background-color:white;"/>
            </td>
          </tr></table>
         </td> 
      </tr>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionNewVersionDisplay" ><?php echo i18n("colNextDocumentVersion");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionNewVersionDisplay" name="documentVersionNewVersionDisplay"
          style="width: 450px;" readonly
          maxlength="100"
          class="input">  
         </div>  
        </td>
      </tr>   
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionDate" ><?php echo i18n("colDate");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <div id="documentVersionDate" name="documentVersionDate"
           dojoType="dijit.form.DateTextBox" 
           invalidMessage="<?php echo i18n('messageInvalidDate');?> " 
           type="text" maxlength="10" 
           style="width:100px; text-align: center;" class="input"
           required="true"
           hasDownArrow="true" 
           onchange="calculateNewVersion(false);"
           missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
           invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
          >
          </div>
        </td>
      </tr>
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionIdStatus" ><?php echo i18n("colIdStatus");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <select dojoType="dijit.form.FilteringSelect" 
                id="documentVersionIdStatus" name="documentVersionIdStatus"
                class="input" value="" 
                onChange=""
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdStatus')));?>" >
                 <?php //htmlDrawOptionForReference('idStatus', null, null, true);
                       // no need will be updated on dialog opening?>
          </select>  
        </td>
      </tr>
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionIsRef" ><?php echo i18n("colIsRef");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <table><tr><td>
          <input dojoType="dijit.form.CheckBox" 
           name="documentVersionIsRef" id="documentVersionIsRef"
           style="background-color:white;"
           onclick="setDisplayIsRefDocumentVersion();"
           />
           </td><td>
          <span id="documentVersionIsRefDisplay" style="font-size:80%"><i><?php echo i18n('documentVersionIsRef');?>&nbsp;</i></span>
          </tr></table>
        </td>     
      </tr>          
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <textarea dojoType="dijit.form.Textarea" 
          id="documentVersionDescription" name="documentVersionDescription"
          style="width: 450px;"
          maxlength="4000"
          class="input"></textarea>
          <textarea style="display:none" id="documentVersionAck" name="documentVersionAck"></textarea>      
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
          <input type="hidden" id="dialogDocumentVersionAction">
          <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogDocumentVersion').hide();">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button id="submitDocumentVersionUpload" dojoType="dijit.form.Button" type="submit" 
           <?php if ($isIE and $isIE<=9) {?>onclick="saveDocumentVersion();"<?php }?> >
            <?php echo i18n("buttonOK");?>
          </button>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
         <div style="display:none">
           <iframe name="documentVersionPost" id="documentVersionPost"></iframe>
         </div>
        </td>
      </tr>
    </table>
  </form>
  <form id='documentVersionAckForm' name='documentVersionAckForm'> 
    <input type='hidden' id="resultAckDocumentVersion" name="resultAckDocumentVersion" />
  </form>  