<?php
scriptLog('dynamicDialogAttachement.php');
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
	$isIE=$_REQUEST['isIE'];
} 
?>
  <form id='attachementForm' name='attachementForm' 
  ENCTYPE="multipart/form-data" method="POST"
<?php if ($isIE and $isIE<=9) {?>
  action="../tool/saveAttachement.php?isIE=<?php echo $isIE;?>"
  target="resultPost"
  onSubmit="return saveAttachement();"
<?php }?> 
  >
    <input id="attachementId" name="attachementId" type="hidden" value="" />
    <input id="attachementRefType" name="attachementRefType" type="hidden" value="" />
    <input id="attachementRefId" name="attachementRefId" type="hidden" value="" />
    <input id="attachementType" name="attachementType" type="hidden" value="" />
    <div id="dialogAttachementFileDiv">
      <table>
        <tr height="30px">
          <td class="dialogLabel" >
           <label for="attachementFile" ><?php echo i18n("colFile");?>&nbsp;:&nbsp;</label>
          </td>
          <td style="position:relative">
           <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>" />
          <?php  if ($isIE and $isIE<=9) {?>
           <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
            dojoType="dojox.form.FileInput" type="file"
            name="attachementFile" id="attachementFile"
            cancelText="<?php echo i18n("buttonReset");?>"
            label="<?php echo i18n("buttonBrowse");?>"
            title="<?php echo i18n("helpSelectFile");?>" />
          <?php } else {?>  
           <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
            dojoType="dojox.form.Uploader" type="file" 
            url="../tool/saveAttachement.php"
            <?php if (! $isIE) {?>
            style="overflow: hidden; z-index: 50;width:340px; border: 3px dotted #EEEEEE;"
            <?php } else {?>
            style="overflow: hidden; border: 0px"
            <?php }?>
            name="attachementFile" id="attachementFile" 
            cancelText="<?php echo i18n("buttonReset");?>"
            multiple="true" 
            uploadOnSelect="false"
            onBegin="saveAttachement();"
            onChange="changeAttachment(this.getFileList());"
            onError="dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
            label="<?php echo i18n("buttonBrowse");?>"
            title="<?php echo i18n("helpSelectFile");?>"  />
          <?php }?>
          <i>
          <?php if (! $isIE) {?>
          <span style="position: absolute; z-index: 49; top: 8px; left: 100px; color: #AAAAAA; width:250px"><?php echo i18n("dragAndDrop");?></span></i>
          <?php }?>
          <div style="position: relative; left:10px; border-left: 2px solid #EEEEEE; padding-left:5px;" name="attachementFileName" id="attachementFileName"></div></i>     
          </td>
        </tr>
      </table>
    </div>
    <div id="dialogAttachementLinkDiv">
      <table>
        <tr height="30px">
          <td class="dialogLabel" >
            <label for="attachementLink" ><?php echo i18n("colHyperlink");?>&nbsp;:&nbsp;</label>
          </td>
          <td>
            <div id="attachementLink" name="attachementLink" dojoType="dijit.form.ValidationTextBox"
               style="width: 350px;"
               trim="true" maxlength="400" class="input"
               value="">
            </div>
          </td>
        </tr>
      </table>
    </div>
    <table>
      <tr>
        <td class="dialogLabel" >
         <label for="attachementDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <textarea dojoType="dijit.form.Textarea" 
          id="attachementDescription" name="attachementDescription"
          style="width: 350px;"
          maxlength="4000"
          class="input"></textarea>   
        </td>
      </tr>
      <tr><td colspan="2">
       <table width="100%"><tr height="25px">
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="attachmentPrivacyPublic"><?php echo i18n('public');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyPublic" value="1" />
            </td>
            <td width="34%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="attachmentPrivacyTeam"><?php echo i18n('team');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyTeam" value="2" />
            </td>
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="attachmentPrivacyPrivate"><?php echo i18n('private');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyPrivate" value="3" />
            </td>
          </tr></table>
      </td></tr>
      <tr>
        <td colspan="2" align="center">
          <input type="hidden" id="dialogAttachementAction">
          <button dojoType="dijit.form.Button" type="button" id="dialogAttachementCancel" onclick="dijit.byId('dialogAttachement').hide();">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button id="dialogAttachementSubmit" dojoType="dijit.form.Button" type="submit"
          <?php if ($isIE and $isIE<=9) {?>onclick="saveAttachement();"<?php }?> >
            <?php echo i18n("buttonOK");?>
          </button>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">  
         <div style="display:none">
           <iframe name="resultPost" id="resultPost"></iframe>
         </div>
        </td>
      </tr>
    </table>
    </form>
    