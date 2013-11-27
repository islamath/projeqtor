// ============================================================================
// All specific ProjeQtOr functions and variables for Dialog Purpose
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

//=============================================================================
//= Variables (global)
//=============================================================================

var filterType="";
var closeFilterListTimeout;
//=============================================================================
//= Wait spinner
//=============================================================================

var waitingForReply=false;
/** ============================================================================
 * Shows a wait spinner
 * @return void
 */
function showWait() {
	if (dojo.byId("wait")) {
		showField("wait");
    waitingForReply=true;
	} else {
		showField("waitLogin");
	}
}

/** ============================================================================
 * Hides a wait spinner
 * @return void
 */
function hideWait() {
  waitingForReply=false;
  hideField("wait");
  hideField("waitLogin");
  if (top.dijit.byId("dialogInfo")) {
    top.dijit.byId("dialogInfo").hide();
  }
}

//=============================================================================
//= Generic field visibility properties
//=============================================================================

/** ============================================================================
 * Setup the style properties of a field to set it visible (show it)
 * @param field the name of the field to be set 
 * @return void
 */
function showField(field) {
	var dest = dojo.byId(field);
  if (dijit.byId(field)) {
  	dest=dijit.byId(field).domNode;
  }
  if (dest) {
  	dojo.style(dest, {visibility:'visible'});
  	dojo.style(dest, {display:'inline'});
  	//dest.style.visibility = 'visible';
  	//dest.style.display = 'inline';
  }
}

/** ============================================================================
 * Setup the style properties of a field to set it invisible (hide it) 
 * @param field the name of the field to be set
 * @return void 
 */
function hideField(field) {
  var dest = dojo.byId(field);
  if (dijit.byId(field)) {
  	dest=dijit.byId(field).domNode;
  }
  if (dest) {
  	dojo.style(dest, {visibility:'hidden'});
  	dojo.style(dest, {display:'none'});
  	//dest.style.visibility = 'hidden';
  	//dest.style.display = 'none';
  }
}

//=============================================================================
//= Message boxes
//=============================================================================

/** ============================================================================
 * Display a Dialog Error Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showError (msg) {
	top.hideWait();
	top.dojo.byId("dialogErrorMessage").innerHTML=msg ;
	top.dijit.byId("dialogError").show();
}

/** ============================================================================
 * Display a Dialog Information Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showInfo (msg) {
	top.dojo.byId("dialogInfoMessage").innerHTML=msg ;
	top.dijit.byId("dialogInfo").show();
}

/** ============================================================================
 * Display a Dialog Alert Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showAlert (msg) {
	top.hideWait();
	top.dojo.byId("dialogAlertMessage").innerHTML=msg ;
	top.dijit.byId("dialogAlert").show();
}

/** ============================================================================
 * Display a Dialog Question Message Box, with Yes/No buttons
 * @param msg the message to display in the box
 * @param actionYes the function to be executed if click on Yes button
 * @param actionNo the function to be executed if click on No button
 * @return void 
 */
function showQuestion (msg,actionYes, ActionNo) {
	dojo.byId("dialogQuestionMessage").innerHTML=msg ;
	dijit.byId("dialogQuestion").acceptCallbackYes=actionYes;
	dijit.byId("dialogQuestion").acceptCallbackNo=actionNo;
	dijit.byId("dialogQuestion").show();
}

/** ============================================================================
 * Display a Dialog Confirmation Message Box, with OK/Cancel buttons
 * NB : no action on Cancel click
 * @param msg the message to display in the box
 * @param actionOK the function to be executed if click on OK button
 * @return void 
 */
function showConfirm (msg, actionOK) {
	dojo.byId("dialogConfirmMessage").innerHTML=msg ;
	dijit.byId("dialogConfirm").acceptCallback=actionOK;
	dijit.byId("dialogConfirm").show();
}

/** ============================================================================
 * Display a About Box
 * @param msg the message of the about box (must be passed here because built in php)
 * @return void 
 */
function showAbout (msg) {
	showInfo(msg);
}

//=============================================================================
//= Print
//=============================================================================

/** ============================================================================
 * Display a Dialog Print Preview Box
 * @param page the page to display
 * @param forms the form containing the data to send to the page
 * @return void 
 */
function showPrint (page, context, comboName, outMode) {
	//dojo.byId('printFrame').style.width= 1000 + 'px';
	showWait();
	quitConfirmed=true;
	noDisconnect=true;
	if (! outMode) outMode='html';
	var printInNewWin=printInNewWindow;
	if (outMode=="pdf") {
	  printInNewWin=pdfInNewWindow;
	}
	if (outMode=="csv") {
		printInNewWin=true;
	}
	if (outMode=="mpp") {
		printInNewWin=true;
	}
	if ( ! printInNewWin) {
	  //window.frames['printFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		//frames['printFrame'].location.href='../view/preparePreview.php';
		dijit.byId("dialogPrint").show();
	}
	cl='';
	if (dojo.byId('objectClass')) {
		cl=dojo.byId('objectClass').value;
	} 
	id='';
	if (dojo.byId('objectId')) {
		id=dojo.byId('objectId').value;
	}
	var params="";
	dojo.byId("sentToPrinterDiv").style.display='block';
	if (outMode) {
		params+="&outMode=" + outMode;
		if (outMode=='pdf') {
			dojo.byId("sentToPrinterDiv").style.display='none';
		}
	}
	if (context=='list') {
		if (dijit.byId("listShowIdle")) {
			if (dijit.byId("listShowIdle").get('checked')) {
			  params+="&idle=true";
			}
		}
		if (dijit.byId("listIdFilter")) {		
			if (dijit.byId("listIdFilter").get('value')) {
			  params+="&listIdFilter="+encodeURIComponent(dijit.byId("listIdFilter").get('value'));
			}
		}
		if (dijit.byId("listNameFilter")) {		
			if (dijit.byId("listNameFilter").get('value')) {
			  params+="&listNameFilter="+encodeURIComponent(dijit.byId("listNameFilter").get('value'));
			}
		}
		if (dijit.byId("listTypeFilter")) {		
			if (trim(dijit.byId("listTypeFilter").get('value'))) {
				params+="&objectType="+encodeURIComponent(dijit.byId("listTypeFilter").get('value'));
			}
		}
	} else if (context=='planning'){
		if (dijit.byId("startDatePlanView").get('value')) {
		  params+="&startDate="+encodeURIComponent(formatDate(dijit.byId("startDatePlanView").get("value")));
		  params+="&endDate="+encodeURIComponent(formatDate(dijit.byId("endDatePlanView").get("value")));
		  params+="&format="+g.getFormat();
		  if (dijit.byId('listShowIdle').get('checked')) {
		  	params+="&idle=true";
		  }
		  if (dijit.byId('showWBS').checked) { 
				params+="&showWBS=true";
		  }
		  if ( dijit.byId('listShowResource') ) {
		    if (dijit.byId('listShowResource').checked) { 
		      params+="&showResource=true";
		    }
		  }
		  if ( dijit.byId('listShowLeftWork') ) {
		    if (dijit.byId('listShowLeftWork').checked) { 
		      params+="&showWork=true";
		    }
		  }
		  if ( dijit.byId('listShowProject') ) {
		    if (dijit.byId('listShowProject').checked) { 
		      params+="&showProject=true";
		    }
		  }
		}
	} else if (context=='report'){
		var frm=dojo.byId('reportForm');
		frm.action="../view/print.php";
		if (outMode) {
			frm.page.value = page;
			dojo.byId('outMode').value=outMode;
		} else {
			dojo.byId('outMode').value='';
		}
		if (printInNewWin) {
			frm.target='#';
		} else {
		  frm.target='printFrame';
		}
		frm.submit();
		hideWait();
		quitConfirmed=false;
		noDisconnect=false;
		return;
	} else if (context=='imputation'){
		var frm=dojo.byId('listForm');
		frm.action="../view/print.php";
		if (printInNewWin) {
			frm.target='#';
		} else {
		  frm.target='printFrame';
		}
		if (outMode) {
			dojo.byId('outMode').value=outMode;
		} else {
			dojo.byId('outMode').value='';
		}
		frm.submit();
		hideWait();
		quitConfirmed=false;
		noDisconnect=false;
		return;
	}
	var grid=dijit.byId('objectGrid');
	if (grid) {
		var sortWay=(grid.getSortAsc())?'asc':'desc';
		var sortIndex=grid.getSortIndex();
		if (sortIndex>=0) {
			params+="&sortIndex="+sortIndex;
			params+="&sortWay="+sortWay;
		}
	}
	if (outMode=="csv") {
	  dojo.byId("printFrame").src = "print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params;
	  hideWait();
	} else if (printInNewWin) {
		var newWin=window.open("print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params);
		hideWait();
	} else {
	  // Fixing IE9 bug
	  //window.frames['printFrame'].location.href="print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params;
	  dojo.byId("printFrame").src = "print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params;
	  if (outMode=='pdf') {
		  hideWait();
	  }
	}
	quitConfirmed=false;
	noDisconnect=false;
	//document.getElementsByTagName('printFrame')[0].contentWindow.print();
}

function sendFrameToPrinter() {
	dojo.byId("sendToPrinter").blur();
  //printFrame.focus();
  //printFrame.print();
  window.frames['printFrame'].focus();
  window.frames['printFrame'].print();
  //var myRef = window.open(window.frames['printFrame'].location +"&directPrint=true",'mywin', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0');
  dijit.byId('dialogPrint').hide();      
  return true;
}
//=============================================================================
//= Detail (from combo)
//=============================================================================

function showDetailDependency() {
	var depType=dijit.byId('dependencyRefTypeDep').get("value");
	if (depType) {
		var dependable=dependableArray[depType];
		var canCreate=0;
		if (canCreateArray[dependable]=="YES") {
			canCreate=1;
	    }
		showDetail('dependencyRefIdDep',canCreate, dependable,true);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
	}
}

function showDetailLink() {
	var linkType=dijit.byId('linkRef2Type').get("value");
	if (linkType) {
		var linkable=linkableArray[linkType];
		var canCreate=0;
		if (canCreateArray[linkable]=="YES") {
			canCreate=1;
	    }
		showDetail('linkRef2Id',canCreate, linkable, true);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
	}
}

function showDetailApprover() {
  var canCreate=0;
  if (canCreateArray['Resource']=="YES") {
    canCreate=1;
  }
  showDetail('approverId',canCreate, 'Resource', true);
}

function showDetailOrigin() {
	var originType=dijit.byId('originOriginType').get("value");
	if (originType) {
		var originable=originableArray[originType];
		var canCreate=0;
		if (canCreateArray[originable]=="YES") {
			canCreate=1;
	    }
		showDetail('originOriginId',canCreate, originable);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('originType'))));
	}
}

function showDetail (comboName, canCreate, objectClass, multiSelect) {
	var contentWidget = dijit.byId("comboDetailResult");
	dojo.byId("canCreateDetail").value=canCreate;
    if (contentWidget) {
      contentWidget.set('content','');
    }
    if (! objectClass) {
    	objectClass=comboName.substring(2);
    }
	dojo.byId('comboName').value=comboName;
	dojo.byId('comboClass').value=objectClass;
	dojo.byId('comboMultipleSelect').value=(multiSelect)?'true':'false';
	var val=null;
	if (dijit.byId(comboName)) {
	  val=dijit.byId(comboName).get('value');
	} else {
	  val=dojo.byId(comboName).value;	
	}
	if (! val || val=="" || val==" ") {
		cl=objectClass;
		window.frames['comboDetailFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		dijit.byId("dialogDetail").show();
		displaySearch(cl);
  } else {
		cl=objectClass;
	    id=val;
		window.frames['comboDetailFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		dijit.byId("dialogDetail").show();
		displayDetail(cl,id);
  }
}

function displayDetail(objClass, objId) {
	showWait();
	showField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	hideField('comboSaveButton');
	showField('comboCloseButton');	
    frames['comboDetailFrame'].location.href="print.php?print=true&page=objectDetail.php&objectClass="+objClass+"&objectId="+objId+"&detail=true";
}

function selectDetailItem(selectedValue) {
    var idFldVal="";
	if (selectedValue) {
		idFldVal=selectedValue;
	} else {
		var idFld=frames['comboDetailFrame'].dojo.byId('comboDetailId');
		var comboGrid=frames['comboDetailFrame'].dijit.byId('objectGrid');
		if (comboGrid) {
			idFldVal="";
			var items=comboGrid.selection.getSelected();
			dojo.forEach(items, function(selectedItem){
			  if(selectedItem !== null){
				idFldVal+=(idFldVal!="")?'_':'';
				idFldVal+=parseInt(selectedItem.id,10)+'';
			  }
			});
		} else {	
			if (! idFld) {
				showError('error : comboDetailId not defined');
				return;
			}
			idFldVal=idFld.value;
		}
		if (! idFldVal) {
			showAlert(i18n('noItemSelected'));
			return;
		}
	}
	var comboName=dojo.byId('comboName').value;
	var combo=dijit.byId(comboName);
	var comboClass=dojo.byId('comboClass').value;
	crit=null;
	critVal=null;
	if (comboClass=='Activity' || comboClass=='Resource' || comboClass=='Ticket') {
		prj=dijit.byId('idProject');
		if (prj) {
		  crit='idProject';
		  critVal=prj.get("value");
		}		
	}
	if (comboName!='idStatus' && comboName!='idProject') {
	  // TODO : study if such restriction should be applied to idActivity
		if (combo) {
		  refreshList('id'+comboClass, crit, critVal, idFldVal,comboName);
		} else {
			if (comboName=='dependencyRefIdDep') {
				refreshDependencyList(idFldVal);
				setTimeout("dojo.byId('dependencyRefIdDep').focus()",1000);
				enableWidget('dialogDependencySubmit');
			} else if (comboName=='linkRef2Id') {
				refreshLinkList(idFldVal);
				setTimeout("dojo.byId('linkRef2Id').focus()",1000);
				enableWidget('dialogLinkSubmit');
			} else if (comboName=='otherVersionIdVersion') {
				refreshOtherVersionList(idFldVal);
				setTimeout("dojo.byId('otherVersionIdVersion').focus()",1000);
				enableWidget('dialogOtherVersionSubmit');
		    } else if (comboName=='approverId') {
		        refreshApproverList(idFldVal);
		        setTimeout("dojo.byId('approverId').focus()",1000);
		        enableWidget('dialogApproverSubmit');
			} else if (comboName=='originOriginId') {
				refreshOriginList(idFldVal);
				setTimeout("dojo.byId('originOriginId').focus()",1000);
				enableWidget('dialogOriginSubmit');
			} else if (comboName=='testCaseRunTestCaseList') {
				refreshTestCaseRunList(idFldVal);
				setTimeout("dojo.byId('testCaseRunTestCaseList').focus()",1000);
				enableWidget('dialogTestCaseRunSubmit');
			} 
		}			
	}
	if (combo) {
	  combo.set("value", idFldVal);
	}
    hideDetail();
}

function displaySearch(objClass) {
	if (! objClass) {
		// comboName=dojo.byId('comboName').value;
		objClass=dojo.byId('comboClass').value;
	}
	showWait();
	hideField('comboSearchButton');
	showField('comboSelectButton');
	if (dojo.byId("canCreateDetail").value=="1") { 
	  showField('comboNewButton');
	} else {
      hideField('comboNewButton');	
	}
	hideField('comboSaveButton');
	showField('comboCloseButton');
	var multipleSelect=(dojo.byId('comboMultipleSelect').value=='true')?'&multipleSelect=true':'';
    top.frames['comboDetailFrame'].location.href="comboSearch.php?objectClass="+objClass+"&mode=search"+multipleSelect;
    setTimeout('dijit.byId("dialogDetail").show()',10);
}

function newDetailItem() {
	//comboName=dojo.byId('comboName').value;
	var objClass=dojo.byId('comboClass').value;
	showWait();
	showField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	if (dojo.byId("canCreateDetail").value=="1") { 
	  showField('comboSaveButton');
	} else {
      hideField('comboSaveButton');	
	}
	showField('comboCloseButton');
	contentNode=frames['comboDetailFrame'].dojo.byId('body');
	destinationWidth=dojo.style(contentNode, "width");
	page="comboSearch.php";
	page+="?objectClass="+objClass;
	page+="&objectId=0";
	page+="&mode=new";
    page+="&destinationWidth="+destinationWidth;
	top.frames['comboDetailFrame'].location.href=page;
	setTimeout('dijit.byId("dialogDetail").show()',10);
}

function saveDetailItem() {
	var comboName=dojo.byId('comboName').value;
	var formVar = frames['comboDetailFrame'].dijit.byId("objectForm");
	if ( ! formVar) {
		showError(i18n("errorSubmitForm", new Array(page, destination, formName)));
		return;
	}
	// validate form Data
	if(formVar.validate()){
		showWait();
		frames['comboDetailFrame'].dojo.xhrPost({
		      url: "../tool/saveObject.php?comboDetail=true",
		      form: "objectForm",
		      handleAs: "text",
		      load: function(data,args){
				        var contentWidget = dijit.byId("comboDetailResult");
				        if (! contentWidget) {return;}
				        contentWidget.set('content',data);
				        checkDestination("comboDetailResult");
				        var lastOperationStatus = top.dojo.byId('lastOperationStatusComboDetail');
				        var lastOperation = top.dojo.byId('lastOperationComboDetail');
				        var lastSaveId=top.dojo.byId('lastSaveIdComboDetail');
				        if (lastOperationStatus.value=="OK") {
				        	selectDetailItem(lastSaveId.value);
				        }
				        hideWait();
                    },
               error: function(){hideWait();}
		});

  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function hideDetail() {
	hideField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	hideField('comboSaveButton');
	hideField('comboCloseButton');
	frames['comboDetailFrame'].location.href="preparePreview.php";
	dijit.byId("dialogDetail").hide();
}

//=============================================================================
//= Notes
//=============================================================================

/**
 * Display a add note Box
 * 
 */
function addNote () {
	if (dijit.byId("noteToolTip")) {
		dijit.byId("noteToolTip").destroy();
		dijit.byId("noteNote").set("class","");
	}
	dojo.byId("noteId").value="";
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	dijit.byId("noteNote").set("value",null);
	dijit.byId("dialogNote").set('title',i18n("dialogNote"));
	dojo.xhrGet({
		url: '../tool/dynamicListPredefinedText.php?objectClass='+dojo.byId("noteRefType").value
		      + '&objectType='+dijit.byId('id'+dojo.byId("noteRefType").value+'Type'),
		handleAs: "text",
		load: function (data) {
			var contentWidget = dijit.byId('dialogNotePredefinedDiv');
	        if (! contentWidget) {return;}
	        contentWidget.set('content',data);
		}
	});
	dijit.byId('notePrivacyPublic').set('checked','true');	
	dijit.byId("dialogNote").show();
}

function noteSelectPredefinedText(idPrefefinedText) {
	dojo.xhrGet({
		url: '../tool/getPredefinedText.php?id='+idPrefefinedText,
		handleAs: "text",
		load: function (data) {
			dijit.byId('noteNote').set('value',data);
		  }
	});
}
/**
 * Display a edit note Box
 * 
 */
function editNote (noteId, privacy) {
	if (dijit.byId("noteToolTip")) {
		dijit.byId("noteToolTip").destroy();
		dijit.byId("noteNote").set("class","");
	}
	dojo.byId("noteId").value=noteId;
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	dijit.byId("noteNote").set("value",dojo.byId("note_"+noteId).value);
	dijit.byId("dialogNote").set('title',i18n("dialogNote") + " #" + noteId);
	if (privacy==1) {
		dijit.byId('notePrivacyPublic').set('checked','true');	
	} else if (privacy==2) {
		dijit.byId('notePrivacyTeam').set('checked','true');
	} else if (privacy==3) {
		dijit.byId('notePrivacyPrivate').set('checked','true');
	}
	dijit.byId("dialogNote").show();
}

/**
 * save a note (after addNote or editNote)
 * 
 */
function saveNote() {
	if (dijit.byId("noteNote").getValue()=='') {
		dijit.byId("noteNote").set("class","dijitError");
		//dijit.byId("noteNote").blur();
		var msg=i18n('messageMandatory', new Array(i18n('Note')));
		new dijit.Tooltip({
			id : "noteToolTip",
      connectId: ["noteNote"],
      label: msg,
      showDelay: 0
    });
		dijit.byId("noteNote").focus();
	} else {
		loadContent("../tool/saveNote.php", "resultDiv", "noteForm", true, 'note');
		dijit.byId('dialogNote').hide();
	}
}

/**
 * Display a delete note Box
 * 
 */
function removeNote (noteId) {
	dojo.byId("noteId").value=noteId;
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeNote.php", "resultDiv", "noteForm", true, 'note');};
	msg=i18n('confirmDelete',new Array(i18n('Note'), noteId));
	showConfirm (msg, actionOK);
}


//=============================================================================
//= Attachments
//=============================================================================

/**
 * Display an add attachement Box
 * 
 */
function addAttachement (attachmentType) {
	content=dijit.byId('dialogAttachement').get('content');
	if (content=="") {
	  callBack=function() {
		  dojo.connect(dijit.byId("attachementFile"), "onComplete", function(dataArray){saveAttachementAck(dataArray);});
	      dojo.connect(dijit.byId("attachementFile"), "onProgress", function(data){saveAttachementProgress(data);});
		  addAttachement (attachmentType);};	
	  loadDialog('dialogAttachement',callBack);
	  return;
	}
	dojo.byId("attachementId").value="";
	dojo.byId("attachementRefType").value=dojo.byId("objectClass").value;
	dojo.byId("attachementRefId").value=dojo.byId("objectId").value;
    dojo.byId("attachementType").value=attachmentType;
    dojo.byId('attachementFileName').innerHTML="";   
    dojo.style(dojo.byId('downloadProgress'), {display:'none'});
    if (attachmentType=='file') {
      if (dijit.byId("attachementFile")) {
        dijit.byId("attachementFile").reset();
        if (! isHtml5()) {
          enableWidget('dialogAttachementSubmit');
        } else {
          disableWidget('dialogAttachementSubmit');
        }
      }
      dojo.style(dojo.byId('dialogAttachementFileDiv'), {display:'block'});
      dojo.style(dojo.byId('dialogAttachementLinkDiv'), {display:'none'});
    } else {
      dijit.byId("attachementLink").set('value', null);
      dojo.style(dojo.byId('dialogAttachementFileDiv'), {display:'none'});
      dojo.style(dojo.byId('dialogAttachementLinkDiv'), {display:'block'});
      enableWidget('dialogAttachementSubmit');
    }
	dijit.byId("attachementDescription").set('value',null);
	dijit.byId("dialogAttachement").set('title',i18n("dialogAttachement"));
	dijit.byId('attachmentPrivacyPublic').set('checked','true');
	dijit.byId("dialogAttachement").show();
}

function changeAttachment(list) {
  if (list.length>0) {
	htmlList="";
	for (i=0;i<list.length;i++) {
	  htmlList+=list[i]['name']+'<br/>';
    }
	dojo.byId('attachementFileName').innerHTML=htmlList;
    enableWidget('dialogAttachementSubmit');
    dojo.byId('attachementFile').height="200px";
  } else {
	dojo.byId('attachementFileName').innerHTML="";
	disableWidget('dialogAttachementSubmit');
	dojo.byId('attachementFile').height="20px";
  }
}

/**
 * save an Attachement
 * 
 */
function saveAttachement() {
	//disableWidget('dialogAttachementSubmit');
	if (! isHtml5()) {
	  //dojo.byId('attachementForm').submit();
	  showWait();
	  dijit.byId('dialogAttachement').hide();
	  return true;
	}
	if (dojo.byId("attachementType") && dojo.byId("attachementType").value=='file' 
     && dojo.byId('attachementFileName') && dojo.byId('attachementFileName').innerHTML=="") {
	  return false;
	}
	dojo.style(dojo.byId('downloadProgress'), {display:'block'});
	showWait();
	dijit.byId('dialogAttachement').hide();
	return true;
}

/**
 * Acknowledge the attachment save
 * @return void
 */
function saveAttachementAck(dataArray) {
	if (! isHtml5()) {
		resultFrame=document.getElementById("resultPost");
		resultText=resultPost.document.body.innerHTML;
		dojo.byId('resultAck').value=resultText;
		loadContent("../tool/ack.php", "resultDiv", "attachementAckForm", true, 'attachement');
		return;
	}
	dijit.byId('dialogAttachement').hide();
    if (dojo.isArray(dataArray)) {
      result=dataArray[0];
    } else {
      result=dataArray;
    }
    dojo.style(dojo.byId('downloadProgress'), {display:'none'});
  	dojo.byId('resultAck').value=result.message;
	loadContent("../tool/ack.php", "resultDiv", "attachementAckForm", true, 'attachement');
}

function saveAttachementProgress(data) {
	done=data.bytesLoaded;
	total=data.bytesTotal;
	if (total) {
		progress=done/total;
	}
	//dojo.style(dojo.byId('downloadProgress'), {display:'block'});
	dijit.byId('downloadProgress').set('value',progress);
}
/**
 * Display a delete Attachement Box
 * 
 */
function removeAttachement (attachementId) {
	content=dijit.byId('dialogAttachement').get('content');
	if (content=="") {
	  callBack=function() {
		  dojo.connect(dijit.byId("attachementFile"), "onComplete", function(dataArray){saveAttachementAck(dataArray);});
	      dojo.connect(dijit.byId("attachementFile"), "onProgress", function(data){saveAttachementProgress(data);});
		  dijit.byId('dialogAttachement').hide();
		  removeAttachement (attachementId);
	  };	
	  loadDialog('dialogAttachement',callBack);
	  return;
	}
	dojo.byId("attachementId").value=attachementId;
	dojo.byId("attachementRefType").value=dojo.byId("objectClass").value;
	dojo.byId("attachementRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeAttachement.php", "resultDiv", "attachementForm", true, 'attachement');};
	msg=i18n('confirmDelete',new Array(i18n('Attachement'), attachementId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= Links
//=============================================================================

/**
 * Display a add link Box
 * 
 */
var noRefreshLink=false;
function addLink (classLink, defaultLink) {
	noRefreshLink=true;
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
    var message=i18n("dialogLink");
	dojo.byId("linkId").value="";
	dojo.byId("linkRef1Type").value=objectClass;
	dojo.byId("linkRef1Id").value=objectId;
	dojo.style(dojo.byId('linkDocumentVersionDiv'), {display:'none'});
	dijit.byId("linkDocumentVersion").reset();
	if (classLink) {
	    dojo.byId("linkFixedClass").value=classLink;
	  	message = i18n("dialogLinkRestricted", new Array(i18n(objectClass), objectId, i18n(classLink)));
	  	dijit.byId("linkRef2Type").setDisplayedValue(i18n(classLink));
	  	lockWidget("linkRef2Type");
	  	//var url="../tool/dynamicListLink.php" 
	  	//	+ "?linkRef2Type="+dojo.byId("linkRef2Type").value
	  	//  + "&linkRef1Type="+objectClass
	  	//	+ "&linkRef1Id="+objectId;
	  	//loadContent(url, "dialogLinkList", null, false);
	  	noRefreshLink=false;
	  	refreshLinkList();
	} else {
	  	dojo.byId("linkFixedClass").value="";
	  	if (defaultLink) {
	  	  dijit.byId("linkRef2Type").set('value',defaultLink);
	  	} else {
          dijit.byId("linkRef2Type").reset();
	  	}
	  	message = i18n("dialogLinkExtended", new Array(i18n(objectClass), objectId));
	  	unlockWidget("linkRef2Type");
	  	noRefreshLink=false;
	  	refreshLinkList();
    }

	//dojo.byId("linkRef2Id").value='';
	dijit.byId("dialogLink").set('title', message);
	dijit.byId("linkComment").set('value', '');
	dijit.byId("dialogLink").show();
	disableWidget('dialogLinkSubmit');
}

function selectLinkItem() {
  var nbSelected=0;
  list=dojo.byId('linkRef2Id');
  if (dojo.byId("linkRef2Type").value=="Document") {
	if (list.options) {
	  selected = new Array(); 
	  for (var i = 0; i < list.options.length; i++) {
		  if (list.options[i].selected) {
			  selected.push(list.options[ i ].value);
        nbSelected++;
		  }
	  }
	  if (selected.length==1) {
		  dijit.byId("linkDocumentVersion").reset();
		  refreshList('idDocumentVersion', 'idDocument', selected[0], null, 'linkDocumentVersion', false);
		  dojo.style(dojo.byId('linkDocumentVersionDiv'), {display:'block'});
	  } else {
		  dojo.style(dojo.byId('linkDocumentVersionDiv'), {display:'none'});
		  dijit.byId("linkDocumentVersion").reset();
	  }
    }  
  } else {
    if (list.options) {
      for (var i = 0; i < list.options.length; i++) {
        if (list.options[i].selected) {
          nbSelected++;
        }
      }
    }
	  dojo.style(dojo.byId('linkDocumentVersionDiv'), {display:'none'});
	  dijit.byId("linkDocumentVersion").reset();
  }
  if (nbSelected>0) {
    enableWidget('dialogLinkSubmit');
  } else {
    disableWidget('dialogLinkSubmit');
  }
}

/**
 * Refresh the link list (after update)
 */
function refreshLinkList(selected) {
	if (noRefreshLink) return;
	disableWidget('dialogLinkSubmit');
	var url='../tool/dynamicListLink.php';
	if (selected) {
	  url+='?selected='+selected;	
	}
	if (! selected) {
	  selectLinkItem();
	}
	loadContent(url, 'dialogLinkList', 'linkForm', false);
}

/**
 * save a link (after addLink)
 * 
 */
function saveLink() {
  if (dojo.byId("linkRef2Id").value=="") return;
  loadContent("../tool/saveLink.php", "resultDiv", "linkForm", true,'link');
	dijit.byId('dialogLink').hide();
}

/**
 * Display a delete Link Box
 * 
 */
function removeLink (linkId, refType, refId, refTypeName) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("linkId").value=linkId;
	dojo.byId("linkRef1Type").value=dojo.byId("objectClass").value;
	dojo.byId("linkRef1Id").value=dojo.byId("objectId").value;
	dijit.byId("linkRef2Type").set('value',refType);
	dojo.byId("linkRef2Id").value=refId;
	actionOK=function() {loadContent("../tool/removeLink.php", "resultDiv", "linkForm", true,'link');};
	if (! refTypeName) {
		refTypeName=i18n(refType);
	}
	msg=i18n('confirmDeleteLink',new Array(refTypeName,refId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= OtherVersions
//=============================================================================
function addOtherVersion(versionType) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
	dojo.byId("otherVersionRefType").value=objectClass;
	dojo.byId("otherVersionRefId").value=objectId;
	dojo.byId("otherVersionType").value=versionType;
  	refreshOtherVersionList();
	//dojo.byId("linkRef2Id").value='';
	dijit.byId("dialogOtherVersion").show();
	disableWidget('dialogOtherVersionSubmit');
}

/**
 * Refresh the link list (after update)
 */
function refreshOtherVersionList(selected) {
	disableWidget('dialogOtherVersionSubmit');
	var url='../tool/dynamicListOtherVersion.php';
	if (selected) {
	  url+='?selected='+selected;	
	}
	if (! selected) {
	  selectOtherVersionItem();
	}
	loadContent(url, 'dialogOtherVersionList', 'otherVersionForm', false);
}

function selectOtherVersionItem() {
  var nbSelected=0;
  list=dojo.byId('otherVersionIdVersion');
  if (list.options) {
    for (var i = 0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected++;
      }
    }
  }
  if (nbSelected>0) {
    enableWidget('dialogOtherVersionSubmit');
  } else {
    disableWidget('dialogOtherVersionSubmit');
  }
}

function saveOtherVersion() {
  if (dojo.byId("otherVersionIdVersion").value=="") return;
  loadContent("../tool/saveOtherVersion.php", "resultDiv", "otherVersionForm", true,'otherVersion');
  dijit.byId('dialogOtherVersion').hide();
}

function removeOtherVersion(id, name, type) {
  if (checkFormChangeInProgress()) {
	showAlert(i18n('alertOngoingChange'));
	return;
  }	
  dojo.byId("otherVersionId").value=id;
  actionOK=function() {loadContent("../tool/removeOtherVersion.php", "resultDiv", "otherVersionForm", true,'otherVersion');};
  msg=i18n('confirmDeleteOtherVersion',new Array(name, i18n('col'+type)));
  showConfirm (msg, actionOK);	
}

function swicthOtherVersionToMain(id, name, type) {
  if (checkFormChangeInProgress()) {
	showAlert(i18n('alertOngoingChange'));
	return;
  }		
  dojo.byId("otherVersionId").value=id;
  //actionOK=function() {loadContent("../tool/switchOtherVersion.php", "resultDiv", "otherVersionForm", true,'otherVersion');};
  //msg=i18n('confirmSwitchOtherVersion',new Array(name, i18n('col'+type)));
  //showConfirm (msg, actionOK);
  loadContent("../tool/switchOtherVersion.php", "resultDiv", "otherVersionForm", true,'otherVersion');
}

function showDetailOtherVersion() {
  var canCreate=0;
  if (canCreateArray['Version']=="YES") {
	canCreate=1;
  }
  showDetail('otherVersionIdVersion',canCreate, 'Version', true);
}
//=============================================================================
//= Approvers
//=============================================================================

/**
 * Display a add link Box
 *
 */
function addApprover () {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objectClass=dojo.byId("objectClass").value;
  var objectId=dojo.byId("objectId").value;
  dojo.byId("approverRefType").value=objectClass;
  dojo.byId("approverRefId").value=objectId;
  refreshApproverList();
  dijit.byId("dialogApprover").show();
  disableWidget('dialogApproverSubmit');
}

function selectApproverItem() {
  var nbSelected=0;
  list=dojo.byId('approverId');
  if (list.options) {
    for (var i = 0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected++;
      }
    }
  }
  if (nbSelected>0) {
    enableWidget('dialogApproverSubmit');
  } else {
    disableWidget('dialogApproverSubmit');
  }
}

/**
 * Refresh the Approver list (after update)
 */
function refreshApproverList(selected) {
  disableWidget('dialogApproverSubmit');
  var url='../tool/dynamicListApprover.php';
  if (selected) {
    url+='?selected='+selected;
  }
  selectApproverItem();
  loadContent(url, 'dialogApproverList', 'approverForm', false);
}

/**
 * save a link (after addLink)
 *
 */
function saveApprover() {
  if (dojo.byId("approverId").value=="") return;
  loadContent("../tool/saveApprover.php", "resultDiv", "approverForm", true,'approver');
  dijit.byId('dialogApprover').hide();
}

/**
 * Display a delete Link Box
 *
 */
function removeApprover (approverId, approverName) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("approverItemId").value=approverId;
  dojo.byId("approverRefType").value=dojo.byId("objectClass").value;
  dojo.byId("approverRefId").value=dojo.byId("objectId").value;
  actionOK=function() {loadContent("../tool/removeApprover.php", "resultDiv", "approverForm", true,'approver');};
  msg=i18n('confirmDeleteApprover',new Array(approverName));
  showConfirm (msg, actionOK);
}

function approveItem(approverId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  loadContent("../tool/approveItem.php?approverId="+approverId, "resultDiv", null, true,'approver');
}
//=============================================================================
//= Origin
//=============================================================================

/**
* Display a add origin Box
* 
*/
function addOrigin () {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
	dijit.byId("originOriginType").reset();
	refreshOriginList();
	dojo.byId("originId").value="";
	dojo.byId("originRefType").value=objectClass;
	dojo.byId("originRefId").value=objectId;
	dijit.byId("dialogOrigin").show();
	disableWidget('dialogOriginSubmit');
}

/**
* Refresh the origin list (after update)
*/
function refreshOriginList(selected) {
	disableWidget('dialogOriginSubmit');
	var url='../tool/dynamicListOrigin.php';
	if (selected) {
	  url+='?selected='+selected;	
    }
	loadContent(url, 'dialogOriginList', 'originForm', false);
}

/**
* save a link (after addLink)
* 
*/
function saveOrigin() {
	if (dojo.byId("originOriginId").value=="") return;
	loadContent("../tool/saveOrigin.php", "resultDiv", "originForm", true,'origin');
	dijit.byId('dialogOrigin').hide();
}

/**
* Display a delete Link Box
* 
*/
function removeOrigin (id, origType, origId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("originId").value=id;
	dojo.byId("originRefType").value=dojo.byId("objectClass").value;
	dojo.byId("originRefId").value=dojo.byId("objectId").value;
	dijit.byId("originOriginType").set('value',origType);
	dojo.byId("originOriginId").value=origId;
	actionOK=function() {loadContent("../tool/removeOrigin.php", "resultDiv", "originForm", true,'origin');};
	msg=i18n('confirmDeleteOrigin',new Array(i18n(origType),origId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= Assignments
//=============================================================================

/**
 * Display a add Assignment Box
 * 
 */
function addAssignment (unit, rawUnit, hoursPerDay) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var prj=dijit.byId('idProject').get('value');

	/*var datastore =new dojo.data.ItemFileReadStore({
	       query: {id:'*'},
	       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj,
           clearOnClose: true });
	var store = new dojo.store.DataStore({store: datastore});
	//store.query({id:"*"});
	dijit.byId('assignmentIdResource').set('store',store);*/
	refreshListSpecific('listResourceProject', 'assignmentIdResource', 'idProject', prj);
	dijit.byId("assignmentIdResource").reset();

	dojo.byId("assignmentId").value="";
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("objectId").value;
	dijit.byId("assignmentIdRole").reset();
	dijit.byId("assignmentDailyCost").reset();
	dijit.byId("assignmentRate").set('value','100');
	dijit.byId("assignmentAssignedWork").set('value','0');
	dojo.byId("assignmentAssignedWorkInit").value='0';
	dijit.byId("assignmentRealWork").set('value','0');
	dijit.byId("assignmentLeftWork").set('value','0');
	dojo.byId("assignmentLeftWorkInit").value='0';
	dijit.byId("assignmentPlannedWork").set('value','0');
	dijit.byId("assignmentComment").set('value','');
	dijit.byId("dialogAssignment").set('title',i18n("dialogAssignment"));
	dijit.byId("assignmentIdResource").set('readOnly',false);
	dijit.byId("assignmentIdRole").set('readOnly',false);
	dojo.byId("assignmentPlannedUnit").value=unit;
	dojo.byId("assignmentLeftUnit").value=unit;
	dojo.byId("assignmentRealUnit").value=unit;
	dojo.byId("assignmentAssignedUnit").value=unit;
	if (dojo.byId('objectClass').value=='Meeting' || dojo.byId('objectClass').value=='PeriodicMeeting') {
	  if (dijit.byId('meetingEndTime') && dijit.byId('meetingEndTime').get('value') 
	   && dijit.byId('meetingStartTime') && dijit.byId('meetingStartTime').get('value') ) {
		delay=(dijit.byId('meetingEndTime').get('value')-dijit.byId('meetingStartTime').get('value'))/1000/60/60;
		if (rawUnit=='hours') {
		  // OK
		} else { 
		  delay=Math.round(delay/hoursPerDay*100)/100;
		}
		dijit.byId("assignmentAssignedWork").set('value',delay);
		dijit.byId("assignmentPlannedWork").set('value',delay);
		dijit.byId("assignmentLeftWork").set('value',delay);
	  }
	  
	}
	dijit.byId("dialogAssignment").show();
}

/**
 * Display a edit Assignment Box
 * 
 */

var editAssignmentLoading=false;
function editAssignment (assignmentId, idResource, idRole, cost, rate, assignedWork, realWork, leftWork, unit) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	editAssignmentLoading=true;
	var prj=dijit.byId('idProject').get('value');
	/*var datastore =new dojo.data.ItemFileReadStore({
	       query: {id:'*'},
	       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj
	           +'&selected=' + idResource,
	           clearOnClose: true });
	var store = new dojo.store.DataStore({store: datastore});
	//store.query({id:"*"});
	dijit.byId('assignmentIdResource').set('store',store);*/
	refreshListSpecific('listResourceProject', 'assignmentIdResource', 'idProject', prj, idResource);
	dijit.byId("assignmentIdResource").reset();
	dijit.byId("assignmentIdResource").set("value",idResource);
	dijit.byId("assignmentIdRole").set("value",idRole);
	dojo.byId("assignmentId").value=assignmentId;
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("id").value;
	dijit.byId("assignmentDailyCost").set('value',dojo.number.format(cost/100));
	dojo.byId("assignmentRate").value=rate;
	dijit.byId("assignmentAssignedWork").set('value',dojo.number.format(assignedWork/100));
	dojo.byId("assignmentAssignedWorkInit").value=assignedWork/100;
	dijit.byId("assignmentRealWork").set('value',dojo.number.format(realWork/100));
	dijit.byId("assignmentLeftWork").set('value',dojo.number.format(leftWork/100));
	var comment=dojo.byId('comment_assignment_'+assignmentId);
	if (comment) {
	  dijit.byId("assignmentComment").set('value',comment.value);
	} else {
		dijit.byId("assignmentComment").set('value','');	
	} 
	dojo.byId("assignmentPlannedUnit").value=unit;
	dojo.byId("assignmentLeftUnit").value=unit;
	dojo.byId("assignmentRealUnit").value=unit;
	dojo.byId("assignmentAssignedUnit").value=unit;
	dojo.byId("assignmentLeftWorkInit").value=leftWork/100;
	assignmentUpdatePlannedWork('assignment');
	dijit.byId("dialogAssignment").set('title',i18n("dialogAssignment") + " #" + assignmentId);
	dijit.byId("dialogAssignment").show();
	if (dojo.number.parse(realWork)==0) {
		dijit.byId("assignmentIdResource").set('readOnly',false);
		dijit.byId("assignmentIdRole").set('readOnly',false);
	} else {
		dijit.byId("assignmentIdResource").set('readOnly',true);
		dijit.byId("assignmentIdRole").set('readOnly',true);
	}
	setTimeout("editAssignmentLoading=false",1000);
}

/**
 * Update the left work on assignment update
 * @param prefix
 * @return
 */
function assignmentUpdateLeftWork(prefix) {
	var initAssigned = dojo.byId(prefix + "AssignedWorkInit"); 
  var initLeft =  dojo.byId(prefix + "LeftWorkInit");
  var assigned =  dojo.byId(prefix + "AssignedWork"); 
  var newAssigned=dojo.number.parse(assigned.value);
  if (newAssigned==null || isNaN(newAssigned)) {
  	newAssigned=0;
  	assigned.value=dojo.number.format(newAssigned);
	}  
  var left = dojo.byId(prefix + "LeftWork");
  var real = dojo.byId(prefix + "RealWork"); 
  var planned = dojo.byId(prefix + "PlannedWork");
	diff=dojo.number.parse(assigned.value)-initAssigned.value;
	newLeft=parseFloat(initLeft.value) + diff;
	if (newLeft<0 || isNaN(newLeft)) { newLeft=0;}
	left.value=dojo.number.format(newLeft);
	assignmentUpdatePlannedWork(prefix);
}

/**
 * Update the planned work on assignment update
 * @param prefix
 * @return
 */
function assignmentUpdatePlannedWork(prefix) {
  var left = dojo.byId(prefix + "LeftWork");
  var newLeft=dojo.number.parse(left.value);
  if (newLeft==null || isNaN(newLeft)) {
  	newLeft=0;
  	left.value=dojo.number.format(newLeft);
	}
  var real = dojo.byId(prefix + "RealWork"); 
  var planned = dojo.byId(prefix + "PlannedWork");
	newPlanned=dojo.number.parse(real.value)+dojo.number.parse(left.value);
	planned.value=dojo.number.format(newPlanned);
}

/**
 * save an Assignment (after addAssignment or editAssignment)
 * 
 */
function saveAssignment() {
	/*if (! dijit.byId('assignmentIdResource').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
		return;
	}
	if (! dijit.byId('assignmentIdResource').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
		return;
	}	*/
	var formVar = dijit.byId('assignmentForm');
  if(formVar.validate()){		
	  dijit.byId("assignmentPlannedWork").focus();
	  dijit.byId("assignmentLeftWork").focus();
	  loadContent("../tool/saveAssignment.php", "resultDiv", "assignmentForm", true, 'assignment');
	  dijit.byId('dialogAssignment').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
 
/**
 * Display a delete Assignment Box
 * 
 */
function removeAssignment (assignmentId, realWork, resource) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	if (parseFloat(realWork)) {
		msg=i18n('msgUnableToDeleteRealWork');
		showAlert (msg);
		return;
	}
	dojo.byId("assignmentId").value=assignmentId;
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeAssignment.php", "resultDiv", "assignmentForm", true, 'assignment');};
	msg=i18n('confirmDeleteAssignment',new Array(resource));
	showConfirm (msg, actionOK);
}

function assignmentChangeResource() {
	if (editAssignmentLoading) return;
	var idResource=dijit.byId("assignmentIdResource").get("value");
	if (! idResource) return;
	dijit.byId('assignmentDailyCost').reset();
	dojo.xhrGet({
		url: '../tool/getSingleData.php?dataType=resourceRole&idResource=' + idResource,
		handleAs: "text",
		load: function (data) {dijit.byId('assignmentIdRole').set('value',data);}
	});
}

function assignmentChangeRole() {
	if (editAssignmentLoading) return;
	var idResource=dijit.byId("assignmentIdResource").get("value");
	var idRole=dijit.byId("assignmentIdRole").get("value");
	if (! idResource || ! idRole ) return;
	dojo.xhrGet({
		url: '../tool/getSingleData.php?dataType=resourceCost&idResource=' + idResource + '&idRole=' + idRole,
		handleAs: "text",
		load: function (data) {
		  // #303
		  //dijit.byId('assignmentDailyCost').set('value',data);
		  dijit.byId('assignmentDailyCost').set('value',dojo.number.format(data));
		}
	});
}

//=============================================================================
//= ExpenseDetail
//=============================================================================

/**
* Display a add Assignment Box
* 
*/
function addExpenseDetail () {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("expenseDetailId").value="";
	dojo.byId("idExpense").value=dojo.byId("objectId").value;
	dijit.byId("expenseDetailName").reset();
	dijit.byId("expenseDetailDate").set('value',null);
	dijit.byId("expenseDetailType").reset();
	dojo.byId("expenseDetailDiv").innerHtml="";
	dijit.byId("expenseDetailAmount").reset();
	//dijit.byId("dialogExpenseDetail").set('title',i18n("dialogExpenseDetail"));
	dijit.byId("dialogExpenseDetail").show();
}

/**
* Display a edit Assignment Box
* 
*/
var expenseDetailLoad=false;
function editExpenseDetail (id, idExpense, type, expenseDate, amount) {
	expenseDetailLoad=true;
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("expenseDetailId").value=id;
	dojo.byId("idExpense").value=idExpense;	
	dijit.byId("expenseDetailName").set("value",dojo.byId('expenseDetail_'+id).value);
  	dijit.byId("expenseDetailDate").set("value",getDate(expenseDate));
	dijit.byId("expenseDetailAmount").set("value",dojo.number.parse(amount));
	dijit.byId("dialogExpenseDetail").set('title',i18n("dialogExpenseDetail") + " #" + id);
	dijit.byId("expenseDetailType").set("value",type);
	expenseDetailLoad=false;
	expenseDetailTypeChange(id);
	expenseDetailLoad=true;
	setTimeout('expenseDetailLoad=false;',500);
	dijit.byId("dialogExpenseDetail").show();
}

/**
* save an Assignment (after addAssignment or editAssignment)
* 
*/
function saveExpenseDetail() {
	expenseDetailRecalculate();
	if (! dijit.byId('expenseDetailName').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colName'))));
		return;
	}
	if (! dijit.byId('expenseDetailDate').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colDate'))));
		return;
	}
	if (! dijit.byId('expenseDetailType').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colType'))));
		return;
	}
	if (! dijit.byId('expenseDetailAmount').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colAmount'))));
		return;
	}
	var formVar = dijit.byId('expenseDetailForm');
    if(formVar.validate()){		
	  dijit.byId("expenseDetailName").focus();
	  dijit.byId("expenseDetailAmount").focus();
	  loadContent("../tool/saveExpenseDetail.php", "resultDiv", "expenseDetailForm", true, 'expenseDetail');
	  dijit.byId('dialogExpenseDetail').hide();
    } else {
    	showAlert(i18n("alertInvalidForm"));
    }
}

/**
* Display a delete Assignment Box
* 
*/
function removeExpenseDetail (expenseDetailId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("expenseDetailId").value=expenseDetailId;
	actionOK=function() {loadContent("../tool/removeExpenseDetail.php", "resultDiv", "expenseDetailForm", true, 'expenseDetail');};
	msg=i18n('confirmDeleteExpenseDetail',new Array(dojo.byId('expenseDetail_'+expenseDetailId).value));
	showConfirm (msg, actionOK);
}

function expenseDetailTypeChange(expenseDetailId) {
	if (expenseDetailLoad) return;
	var idType=dijit.byId("expenseDetailType").get("value");
	var url='../tool/expenseDetailDiv.php?idType='+idType;
	if (expenseDetailId) {
	  url+='&expenseDetailId='+expenseDetailId;
	}
	loadContent(url, 'expenseDetailDiv', null, false);
}

function expenseDetailRecalculate() {
	val=false;
	if (dijit.byId('expenseDetailValue01')) {
		val01=dijit.byId('expenseDetailValue01').get("value");
	} else {
		val01=dojo.byId('expenseDetailValue01').value;
	}
	if (dijit.byId('expenseDetailValue02')) {
		val02=dijit.byId('expenseDetailValue02').get("value");
	} else {
		val02=dojo.byId('expenseDetailValue02').value;
	}
	if (dijit.byId('expenseDetailValue03')) {
		val03=dijit.byId('expenseDetailValue03').get("value");
	} else {
		val03=dojo.byId('expenseDetailValue03').value;
	}	
	total=1;
	if (dojo.byId('expenseDetailUnit01').value) {
		total=total*val01;
		val=true;
	}
	if (dojo.byId('expenseDetailUnit02').value) {
		total=total*val02;
		val=true;
	}
	if (dojo.byId('expenseDetailUnit03').value) {
		total=total*val03;
		val=true;
	}
	if (val) {
	  dijit.byId("expenseDetailAmount").set('value',total);
	  lockWidget("expenseDetailAmount");
	} else {
      unlockWidget("expenseDetailAmount");
	}
}

//=============================================================================
//= DocumentVersion
//=============================================================================

/**
* Display a add Document Version Box
* 
*/
function addDocumentVersion (defaultStatus, typeEvo, numVers, dateVers, nameVers) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	content=dijit.byId('dialogDocumentVersion').get('content');
	if (content=="") {
	  callBack=function() {
		  dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(dataArray){saveDocumentVersionAck(dataArray);});
	      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(data){saveDocumentVersionProgress(data);});
	      addDocumentVersion(defaultStatus, typeEvo, numVers, dateVers, nameVers);};	
	  loadDialog('dialogDocumentVersion',callBack);
	  return;
	}
	dojo.style(dojo.byId('downloadProgress'), {display:'none'});
      if (dijit.byId("documentVersionFile")) {
        dijit.byId("documentVersionFile").reset();
        if (! isHtml5()) {
          enableWidget('dialogDocumentVersionSubmit');
        } else {
          disableWidget('dialogDocumentVersionSubmit');
        }
      }
	dojo.byId("documentVersionId").value="";
	dojo.byId('documentVersionFileName').innerHTML=""; 
	refreshListSpecific('listStatusDocumentVersion', 'documentVersionIdStatus','idDocumentVersion', '');
	dijit.byId('documentVersionIdStatus').set('value',defaultStatus);
	dojo.style(dojo.byId('inputFileDocumentVersion'), {display:'block'});
	dojo.byId("documentId").value=dojo.byId("objectId").value;
	dojo.byId("documentVersionVersion").value=dojo.byId('version').value;
	dojo.byId("documentVersionRevision").value=dojo.byId('revision').value;
	dojo.byId("documentVersionDraft").value=dojo.byId('draft').value;
	dojo.byId("typeEvo").value=typeEvo;
	dijit.byId("documentVersionLink").set('value','');
	dijit.byId("documentVersionFile").reset();
	dijit.byId("documentVersionDescription").set('value','');
	dijit.byId("documentVersionUpdateMajor").set('checked','true');
	dijit.byId("documentVersionUpdateDraft").set('checked',false);
	dijit.byId("documentVersionDate").set('value',new Date());
	dijit.byId("documentVersionUpdateMajor").set('readOnly',false);
	dijit.byId("documentVersionUpdateMinor").set('readOnly',false);
	dijit.byId("documentVersionUpdateNo").set('readonly',false);
	dijit.byId("documentVersionUpdateDraft").set('readonly',false);
	dijit.byId("documentVersionIsRef").set('checked',false);
	dijit.byId('documentVersionVersionDisplay').set('value',
			getDisplayVersion(typeEvo,
					dojo.byId('documentVersionVersion').value,
					dojo.byId('documentVersionRevision').value,
					dojo.byId('documentVersionDraft').value),
					numVers, 
					dateVers,
					nameVers);
	dojo.byId('documentVersionMode').value="add";
	calculateNewVersion();
	setDisplayIsRefDocumentVersion();
	dijit.byId("dialogDocumentVersion").show();
}

/**
* Display a edit Document Version Box
* 
*/
//var documentVersionLoad=false;
function editDocumentVersion (id,version,revision,draft,versionDate, status, isRef, typeEvo, numVers, dateVers, nameVers) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	content=dijit.byId('dialogDocumentVersion').get('content');
	if (content=="") {
	  callBack=function() {
		  dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(dataArray){saveDocumentVersionAck(dataArray);});
	      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(data){saveDocumentVersionProgress(data);});
	      editDocumentVersion (id,version,revision,draft,versionDate, status, isRef, typeEvo, numVers, dateVers, nameVers);};	
	  loadDialog('dialogDocumentVersion',callBack);
	  return;
	}
	dijit.byId('documentVersionIdStatus').store;
	refreshListSpecific('listStatusDocumentVersion', 'documentVersionIdStatus','idDocumentVersion', id);
    dijit.byId('documentVersionIdStatus').set('value',status);
	dojo.style(dojo.byId('inputFileDocumentVersion'), {display:'none'});
	dojo.byId("documentVersionId").value=id;
	dojo.byId("documentId").value=dojo.byId("objectId").value;
	dojo.byId("documentVersionVersion").value=version;
	dojo.byId("documentVersionRevision").value=revision;
	dojo.byId("documentVersionDraft").value=draft;
	dojo.byId("typeEvo").value=typeEvo;
	if (draft) {
		dijit.byId('documentVersionUpdateDraft').set('checked',true);
	} else {
		dijit.byId('documentVersionUpdateDraft').set('checked',false);
	}
	if (isRef=='1') {
		dijit.byId('documentVersionIsRef').set('checked',true);
	} else {
		dijit.byId('documentVersionIsRef').set('checked',false);
	}
	dijit.byId("documentVersionLink").set('value','');
	dijit.byId("documentVersionFile").reset();
	dijit.byId("documentVersionDescription").set("value",dojo.byId("documentVersion_"+id).value);
	dijit.byId("documentVersionUpdateMajor").set('readOnly','readOnly');
	dijit.byId("documentVersionUpdateMinor").set('readOnly','readOnly');
	dijit.byId("documentVersionUpdateNo").set('readonly','readonly');
	dijit.byId("documentVersionUpdateNo").set('checked',true);
	dijit.byId("documentVersionUpdateDraft").set('readonly','readonly');
	dijit.byId("documentVersionDate").set('value',versionDate);
	dojo.byId('documentVersionMode').value="edit";
	dijit.byId('documentVersionVersionDisplay').set('value',nameVers);
	calculateNewVersion(false);
	setDisplayIsRefDocumentVersion();
	dijit.byId("dialogDocumentVersion").show();
}

function changeDocumentVersion(list) {
  if (list.length>0) {
    dojo.byId('documentVersionFileName').innerHTML=list[0]['name'];
    enableWidget('dialogDocumentVersionSubmit');
  } else {
	dojo.byId('documentVersionFileName').innerHTML="";
	disableWidget('dialogDocumentVersionSubmit');
  }
}

/**
* save an Assignment (after addAssignment or editAssignment)
* 
*/
function saveDocumentVersion() {
	//dojo.byId('documentVersionForm').submit();
	if (! isHtml5()) {
	  //dojo.byId('documentVersionForm').submit();
	  showWait();
	  dijit.byId('dialogDocumentVersion').hide();
	  return true;
	}
	if (dojo.byId('documentVersionFileName').innerHTML=="") {
	  return false;
	}
	dojo.style(dojo.byId('downloadProgress'), {display:'block'});
    showWait();
	dijit.byId('dialogDocumentVersion').hide();
	return true;
}

/**
 * Acknoledge the attachment save
 * @return void
 */
function saveDocumentVersionAck(dataArray) {
	if (! isHtml5()) {
	  resultFrame=document.getElementById("documentVersionPost");
	  resultText=documentVersionPost.document.body.innerHTML;
	  dojo.byId('resultAckDocumentVersion').value=resultText;
	  loadContent("../tool/ack.php", "resultDiv", "documentVersionAckForm", true, 'documentVersion');
	  return;
	}
	dijit.byId('dialogDocumentVersion').hide();
    if (dojo.isArray(dataArray)) {
      result=dataArray[0];
    } else {
      result=dataArray;
    }
    dojo.style(dojo.byId('downloadProgress'), {display:'none'});
  	dojo.byId('resultAckDocumentVersion').value=result.message;
	loadContent("../tool/ack.php", "resultDiv", "documentVersionAckForm", true, 'documentVersion');	
}

function saveDocumentVersionProgress(data) {
	done=data.bytesLoaded;
	total=data.bytesTotal;
	if (total) {
		progress=done/total;
	}
	dijit.byId('downloadProgress').set('value',progress);
}
/**
* Display a delete Assignment Box
* 
*/
function removeDocumentVersion (documentVersionId, documentVersionName) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	content=dijit.byId('dialogDocumentVersion').get('content');
	if (content=="") {
	  callBack=function() {
		  dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(dataArray){saveDocumentVersionAck(dataArray);});
	      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(data){saveDocumentVersionProgress(data);});
	      removeDocumentVersion (documentVersionId, documentVersionName);};	
	  loadDialog('dialogDocumentVersion',callBack);
	  return;
	}
	dojo.byId("documentVersionId").value=documentVersionId;
	actionOK=function() {loadContent("../tool/removeDocumentVersion.php", "resultDiv", "documentVersionForm", true, 'documentVersion');};
	msg=i18n('confirmDeleteDocumentVersion',new Array(documentVersionName));
	showConfirm (msg, actionOK);
}

function getDisplayVersion(typeEvo, version, revision, draft, numVers, dateVers, nameVers) {
  var res="";
  if (typeEvo=="EVO") {
	if (version!="" && revision !="") {
	  res="V"+version+"."+revision;
    }
  } else if (typeEvo=="EVT") {
	res=dateVers;
  } else if (typeEvo=="SEQ") {
	res=numVers;
  } else if (typeEvo=="EXT") {
	res=nameVers;
  }
  if (typeEvo=="EVO" || typeEvo=="EVT" || typeEvo=="SEQ") {
	if (draft) {
	  res+=draftSeparator+draft;
	}
  }
  return res;
}

function calculateNewVersion(update) {
  var typeEvo=dojo.byId("typeEvo").value;
  var numVers="";
  var dateVers="";
  var nameVers="";
  if (dijit.byId('documentVersionUpdateMajor').get('checked')) {
	  type="major";
  } else if (dijit.byId('documentVersionUpdateMinor').get('checked')) {
	  type="minor";
  } else if (dijit.byId('documentVersionUpdateNo').get('checked')) {
	  type="none";
  }
  version=dojo.byId('documentVersionVersion').value;
  revision=dojo.byId('documentVersionRevision').value;
  draft=dojo.byId('documentVersionDraft').value;
  isDraft=dijit.byId('documentVersionUpdateDraft').get('checked');
  version=(version=='')?0:parseInt(version,10);
  revision=(revision=='')?0:parseInt(revision,10);
  draft=(draft=='')?0:parseInt(draft,10);
  if (type=="major") {
	dojo.byId('documentVersionNewVersion').value=version+1;
	dojo.byId('documentVersionNewRevision').value=0;
	dojo.byId('documentVersionNewDraft').value=(isDraft)?'1':'';
  } else if (type=="minor") {
	dojo.byId('documentVersionNewVersion').value=version;
	dojo.byId('documentVersionNewRevision').value=revision+1;
	dojo.byId('documentVersionNewDraft').value=(isDraft)?'1':'';
  } else { // 'none'
	dojo.byId('documentVersionNewVersion').value=version;
	dojo.byId('documentVersionNewRevision').value=revision;
	if (dojo.byId('documentVersionId').value) {
	  dojo.byId('documentVersionNewDraft').value=(isDraft)?((draft)?draft:1):'';	
	} else {
	  dojo.byId('documentVersionNewDraft').value=(isDraft)?draft+1:'';
	}
  }
  dateVers=dojo.date.locale.format(dijit.byId("documentVersionDate").get('value'), {datePattern: "yyyyMMdd", selector: "date"});
  nameVers=dijit.byId("documentVersionVersionDisplay").get('value');
  numVers=nameVers;
  if (typeEvo=="SEQ" && dojo.byId('documentVersionMode').value=="add") {
	  if (! nameVers) {nameVers=0;}
	  numVers=parseInt(nameVers,10)+1;
  }
  dijit.byId("documentVersionNewVersionDisplay").set('readOnly','readOnly');
  if (typeEvo=="EXT" ) {
	  dijit.byId("documentVersionNewVersionDisplay").set('readOnly', false);
  }
  var newVers=getDisplayVersion(typeEvo,
		  dojo.byId('documentVersionNewVersion').value,
		  dojo.byId('documentVersionNewRevision').value,
		  dojo.byId('documentVersionNewDraft').value,
		  numVers, 
		  dateVers, 
		  nameVers);
  dijit.byId('documentVersionNewVersionDisplay').set('value',newVers);
  if (isDraft) {
	dijit.byId('documentVersionIsRef').set('checked',false);
	setDisplayIsRefDocumentVersion();
  }
}

function setDisplayIsRefDocumentVersion() {
	if (dijit.byId('documentVersionIsRef').get('checked')) {
		dojo.style(dojo.byId('documentVersionIsRefDisplay'), {display:'block'});
		dijit.byId('documentVersionUpdateDraft').set('checked',false);
		calculateNewVersion();
	} else {
		dojo.style(dojo.byId('documentVersionIsRefDisplay'), {display:'none'});
	}
}
//=============================================================================
//= Dependency
//=============================================================================

/**
* Display a add Dependency Box
* 
*/
function addDependency (depType) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	noRefreshDependencyList=false;
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
	var message=i18n("dialogDependency");
	if (depType) {
		dojo.byId("dependencyType").value=depType;
		message = i18n("dialogDependencyRestricted", new Array(i18n(objectClass), objectId, i18n(depType)));
	} else {
		dojo.byId("dependencyType").value=null;
		message = i18n("dialogDependencyExtended", new Array(i18n(objectClass), objectId.value));
	}
	if (objectClass=='Requirement') {
	  refreshList('idDependable', 'scope', 'R',null,'dependencyRefTypeDep',true);
	  dijit.byId("dependencyRefTypeDep").set('value','4');
	  dijit.byId("dependencyDelay").set('value','0');
	  dojo.byId("dependencyDelayDiv").style.display="none";
	} else if (objectClass=='TestCase') {
	  refreshList('idDependable', 'scope', 'TC',null,'dependencyRefTypeDep',true);
	  dijit.byId("dependencyRefTypeDep").set('value','5');
	  dijit.byId("dependencyDelay").set('value','0');
	  dojo.byId("dependencyDelayDiv").style.display="none";
	} else{
	  refreshList('idDependable', 'scope', 'PE',null,'dependencyRefTypeDep',true);
	  if (objectClass=='Project') {
		dijit.byId("dependencyRefTypeDep").set('value','3');  
	  } else {
	    dijit.byId("dependencyRefTypeDep").set('value','1');
	  }
	  if (objectClass=='Term') {
		dojo.byId("dependencyDelayDiv").style.display="none";
	  } else {
	    dojo.byId("dependencyDelayDiv").style.display="block";
	  }
	}
	refreshDependencyList();
	refreshList('idActivity', 'idProject', '0', null, 'dependencyRefIdDepEdit', false);
	dijit.byId('dependencyRefIdDepEdit').reset();
	dojo.byId("dependencyId").value="";
	dojo.byId("dependencyRefType").value=objectClass;
	dojo.byId("dependencyRefId").value=objectId;
	dijit.byId("dialogDependency").set('title', message);
	dijit.byId("dialogDependency").show();
	dojo.byId('dependencyAddDiv').style.display='block';
	dojo.byId('dependencyEditDiv').style.display='none';
	dijit.byId("dependencyRefTypeDep").set('readOnly',false);
	disableWidget('dialogDependencySubmit');
}

function editDependency (depType, id, refType, refTypeName, refId, delay) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	noRefreshDependencyList=true;
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
	var message=i18n("dialogDependencyEdit");
	if (objectClass=='Requirement') {
	  refreshList('idDependable', 'scope', 'R',null,'dependencyRefTypeDep',true);
	  dijit.byId("dependencyRefTypeDep").set('value',refType);
	  dijit.byId("dependencyDelay").set('value','0');
	  dojo.byId("dependencyDelayDiv").style.display="none";
	} else if (objectClass=='TestCase') {
	  refreshList('idDependable', 'scope', 'TC',null,'dependencyRefTypeDep',true);
	  dijit.byId("dependencyRefTypeDep").set('value',refType);
	  dijit.byId("dependencyDelay").set('value','0');
	  dojo.byId("dependencyDelayDiv").style.display="none";
	} else{
	  refreshList('idDependable', 'scope', 'PE',null,'dependencyRefTypeDep',true);
	  dijit.byId("dependencyRefTypeDep").set('value',refType);	
	  dijit.byId("dependencyDelay").set('value',delay);
	  dojo.byId("dependencyDelayDiv").style.display="block";
	}
	//refreshDependencyList();
	refreshList('id'+refTypeName, 'idProject', '0', refId, 'dependencyRefIdDepEdit', true);
	dijit.byId('dependencyRefIdDepEdit').set('value',refId);
	dojo.byId("dependencyId").value=id;
	dojo.byId("dependencyRefType").value=objectClass;
	dojo.byId("dependencyRefId").value=objectId;
	dijit.byId("dialogDependency").set('title', message);
	dijit.byId("dialogDependency").show();
	dojo.byId('dependencyAddDiv').style.display='none';
	dojo.byId('dependencyEditDiv').style.display='block';
	dijit.byId("dependencyRefTypeDep").set('readOnly',true);
	dijit.byId("dependencyRefIdDepEdit").set('readOnly',true);
	enableWidget('dialogDependencySubmit');
}

/**
* Refresh the Dependency list (after update)
*/
var noRefreshDependencyList=false;
function refreshDependencyList(selected) {
	if (noRefreshDependencyList) return;
	disableWidget('dialogDependencySubmit');
	var url='../tool/dynamicListDependency.php';
	if (selected) {
		url+='?selected='+selected;
	}
	loadContent(url, 'dialogDependencyList', 'dependencyForm', false);
}

/**
* save a Dependency (after addLink)
* 
*/
function saveDependency() {
	var formVar = dijit.byId('dependencyForm');
	if(! formVar.validate()){		
	  showAlert(i18n("alertInvalidForm"));
	  return;
	}
	if (dojo.byId("dependencyRefIdDep").value=="" && ! dojo.byId('dependencyId').value) return;
	loadContent("../tool/saveDependency.php", "resultDiv", "dependencyForm", true,'dependency');
	dijit.byId('dialogDependency').hide();
}

function saveDependencyFromDndLink(ref1Type,ref1Id, ref2Type, ref2Id) {
//alert("saveDependencyFromDndLink("+ref1Type+","+ref1Id+","+ref2Type+","+ref2Id+")");
	if (ref1Type==ref2Type && ref1Id==ref2Id) return;
	param="ref1Type="+ref1Type;
	param+="&ref1Id="+ref1Id;
	param+="&ref2Type="+ref2Type;
	param+="&ref2Id="+ref2Id;
	loadContent("../tool/saveDependencyDnd.php?"+param, "planResultDiv", null, true,'dependency');
}
/**
* Display a delete Dependency Box
* 
*/
function removeDependency (dependencyId, refType, refId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("dependencyId").value=dependencyId;
	actionOK=function() {loadContent("../tool/removeDependency.php", "resultDiv", "dependencyForm", true,'dependency');};
	msg=i18n('confirmDeleteLink',new Array(i18n(refType),refId));
	showConfirm (msg, actionOK);
}


//=============================================================================
//= BillLines
//=============================================================================

/**
* Display a add line Box
* 
*/
function addBillLine (line) {
	dojo.byId("billLineId").value="";
	dojo.byId("billLineRefType").value=dojo.byId("objectClass").value;
	dojo.byId("billLineRefId").value=dojo.byId("objectId").value;	
	dijit.byId("billLineLine").set("value",line);
	dijit.byId("billLineQuantity").set("value",null);
	var prj=dijit.byId('idProject').get('value');
	/*var datastoreTerm = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=listTermProject&idProject='+prj,
           clearOnClose: true });
	var storeTerm = new dojo.store.DataStore({store: datastoreTerm});
	storeTerm.query({id:"*"});
	dijit.byId('billLineIdTerm').set('store',storeTerm);*/
	refreshListSpecific('listTermProject', 'billLineIdTerm','idProject', prj);
	dijit.byId("billLineIdTerm").reset();
	/*var datastoreResource = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj,
           clearOnClose: true });
	var storeResource = new dojo.store.DataStore({store: datastoreResource});
	storeResource.query({id:"*"});
	dijit.byId('billLineIdResource').set('store',storeResource);*/
	refreshListSpecific('listResourceProject', 'billLineIdResource','idProject', prj);
	dijit.byId("billLineIdResource").reset();
	/*var datastoreActivityPrice = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=list&dataType=idActivityPrice&critField=idProject&critValue='+prj,
           clearOnClose: true });
	var storeActivityPrice = new dojo.store.DataStore({store: datastoreActivityPrice});
	storeActivityPrice.query({id:"*"});
	dijit.byId('billLineIdActivityPrice').set('store',storeActivityPrice);*/
	refreshList('idActivityPrice', 'idProject', prj,null,'billLineIdActivityPrice');
	dijit.byId("billLineIdActivityPrice").reset("value");
	dijit.byId("billLineStartDate").reset("value");
	dijit.byId("billLineEndDate").reset("value");
	dijit.byId("billLineDescription").set("value","");
	dijit.byId("billLineDetail").set("value","");
	dijit.byId("billLinePrice").set("value",null);
	dijit.byId("dialogBillLine").set('title',i18n("dialogBillLine"));
	manageBillingType();
	dijit.byId("dialogBillLine").show();
}


/**
* Display a edit line Box
* 
*/
function editBillLine (id,line,quantity,idTerm,idResource, idActivityPrice, startDate, endDate,price) {
	dojo.byId("billLineId").value=id;
	dojo.byId("billLineRefType").value=dojo.byId("objectClass").value;
	dojo.byId("billLineRefId").value=dojo.byId("objectId").value;
	dijit.byId("billLineLine").set("value",line);
	dijit.byId("billLineQuantity").set('value',quantity);
	var prj=dijit.byId('idProject').get('value');
	/*var datastoreTerm = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=listTermProject&idProject='+prj+'&selected='+idTerm,
           clearOnClose: true });
	var storeTerm = new dojo.store.DataStore({store: datastoreTerm});
	storeTerm.query({id:"*"});
	dijit.byId('billLineIdTerm').set('store',storeTerm);*/
	refreshListSpecific('listTermProject', 'billLineIdTerm','idProject', prj,idTerm);
	dijit.byId("billLineIdTerm").set("value",idTerm);
	/*var datastoreResource = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj+'&selected='+idResource,
           clearOnClose: true });
	var storeResource = new dojo.store.DataStore({store: datastoreResource});
	storeResource.query({id:"*"});
	dijit.byId('billLineIdResource').set('store',storeResource);*/
	refreshListSpecific('listResourceProject', 'billLineIdResource','idProject', prj,idResource);
	dijit.byId("billLineIdResource").set("value",idResource);
	/*var datastoreActivityPrice = new dojo.data.ItemFileReadStore({
	       url: '../tool/jsonList.php?listType=list&dataType=idActivityPrice&critField=idProject&critValue='+prj,
           clearOnClose: true });
	var storeActivityPrice = new dojo.store.DataStore({store: datastoreActivityPrice});
	storeActivityPrice.query({id:"*"});
	dijit.byId('billLineIdActivityPrice').set('store',storeActivityPrice);*/
	refreshList('idActivityPrice', 'idProject', prj,null,'billLineIdActivityPrice');
	dijit.byId("billLineIdActivityPrice").set("value",idActivityPrice);
	dijit.byId("billLineStartDate").set("value",startDate);
	dijit.byId("billLineEndDate").set("value",endDate);
	dijit.byId("billLineDescription").set('value',dojo.byId('billLineDescription_'+id).value);
	dijit.byId("billLineDetail").set("value",dojo.byId('billLineDetail_'+id).value);
	dijit.byId("billLinePrice").set("value",price);
	dijit.byId("dialogBillLine").set('title',i18n("dialogBillLine") + " #" + id);
	manageBillingType();
	dijit.byId("dialogBillLine").show();
}

function manageBillingType() {
	type=dijit.byId('billingType').get('value');
	if (type=='E') {
	  if (! dijit.byId("billLineQuantity").get("value")) {
		  dijit.byId("billLineQuantity").set("value",'1');
	  }
	  dojo.style(dojo.byId('billLineFrameTerm'), {display:'block'});
	  dojo.style(dojo.byId('billLineFrameResource'), {display:'none'});
	  if (! dojo.byId("billLineId").value) { // add
		dijit.byId("billLineIdTerm").set('readOnly',false);
		dojo.style(dojo.byId('billLineFrameDescription'), {display:'none'});
	  } else { // edit
		dijit.byId("billLineIdTerm").set('readOnly',true);
		dojo.style(dojo.byId('billLineFrameDescription'), {display:'block'});
	  }
	  dijit.byId("billLineQuantity").set('readOnly',false);
	  dijit.byId("billLinePrice").set('readOnly',true);
	} else if (type=='R' || type=='P') {
	  dojo.style(dojo.byId('billLineFrameTerm'), {display:'none'});
	  dojo.style(dojo.byId('billLineFrameResource'), {display:'block'});
	  dijit.byId("billLineQuantity").set('readOnly',true);
	  dijit.byId("billLinePrice").set('readOnly',true);
	  if (! dojo.byId("billLineId").value) { // add
		dojo.style(dojo.byId('billLineFrameDescription'), {display:'none'});  
		dijit.byId("billLineIdResource").set('readOnly',false);
		dijit.byId("billLineStartDate").set('readOnly',false);
		dijit.byId("billLineEndDate").set('readOnly',false);
	  } else { // edit
		dojo.style(dojo.byId('billLineFrameDescription'), {display:'block'});
		dijit.byId("billLineIdResource").set('readOnly',true);
		dijit.byId("billLineStartDate").set('readOnly',true);
		dijit.byId("billLineEndDate").set('readOnly',true);
	  }
	} else if (type=='M') {
	  if (! dijit.byId("billLineQuantity").get("value")) {
		dijit.byId("billLineQuantity").set("value",'1');
	  }
	  dojo.style(dojo.byId('billLineFrameDescription'), {display:'block'});
	  dojo.style(dojo.byId('billLineFrameTerm'), {display:'none'});
	  dojo.style(dojo.byId('billLineFrameResource'), {display:'none'});
	  dijit.byId("billLineQuantity").set('readOnly',false);
	  dijit.byId("billLinePrice").set('readOnly',false);
	  dijit.byId("billLineDescription").set('readOnly',false);
	  dijit.byId("billLineDetail").set('readOnly',false);
    } else if (type=='N') {
      showAlert(i18n('billingTypeN'));
    } else {
      showAlert('error : unknown billing type');
    }
}
/**
* save a line (after addDetail or editDetail)
* 
*/
function saveBillLine() {
	if (isNaN(dijit.byId("billLineLine").getValue())) {
		dijit.byId("billLineLine").set("class","dijitError");
		//dijit.byId("noteNote").blur();
		var msg=i18n('messageMandatory', new Array(i18n('BillLine')));
		new dijit.Tooltip({
			id : "billLineToolTip",
    connectId: ["billLineLine"],
    label: msg,
    showDelay: 0
  });
		dijit.byId("billLineLine").focus();
	} else {
		loadContent("../tool/saveBillLine.php", "resultDiv", "billLineForm", true, 'billLine');
		dijit.byId('dialogBillLine').hide();
	}
}


/**
* Display a delete line Box
* 
*/
function removeBillLine (lineId) {
	dojo.byId("billLineId").value=lineId;
	actionOK=function() {loadContent("../tool/removeBillLine.php", "resultDiv", "billLineForm", true, 'billLine');};
	msg=i18n('confirmDelete',new Array(i18n('BillLine'), lineId));
	showConfirm (msg, actionOK);
}



//=============================================================================
//= Import
//=============================================================================

/**
 * Display an import Data Box
 * (Not used, for an eventual improvement)
 * 
 */
function importData() {
	var controls=controlImportData();
	if (controls) {	showWait();}
	return controls;
}

function showHelpImportData() {
	var controls=controlImportData();
	if (controls) {	
		showWait();
		var url='../tool/importHelp.php?elementType='+dijit.byId('elementType').get('value');
		url+='&fileType='+dijit.byId('fileType').get('value');
		frames['resultImportData'].location.href=url;
  }
}

function controlImportData() {
	var elementType=dijit.byId('elementType').get('value');
	if (! elementType ) {
		showAlert(i18n('messageMandatory',new Array(i18n('colImportElementType'))));
		return false;
	}
	var fileType=dijit.byId('fileType').get('value');
	if (! fileType ) {
		showAlert(i18n('messageMandatory',new Array(i18n('colImportFileType'))));
		return false;
	}
	return true;
}
//=============================================================================
//= Plan
//=============================================================================

/**
 * Display a planning Box
 * 
 */
function showPlanParam (selectedProject) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dijit.byId("dialogPlan").show();
}

/**
 * Run planning
 * 
 */
function plan() {
	loadContent("../tool/plan.php", "planResultDiv", "dialogPlanForm", true,null);
	dijit.byId("dialogPlan").hide();
}

//=============================================================================
//= Filter
//=============================================================================

/**
 * Display a Filter Box
 * 
 */
var filterStartInput=false;
var filterFromDetail=false;
function showFilterDialog () {
	/*if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}*/
	filterStartInput=false;
	top.filterFromDetail=false;
	if (top.dijit.byId('dialogDetail').open) {
	  top.filterFromDetail=true;
	  top.dojo.byId('filterDefaultButtonDiv').style.display='none';
	} else {
	  top.dojo.byId('filterDefaultButtonDiv').style.display='block';
	}
	dojo.style(top.dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
	dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
	dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
	dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	dojo.style(top.dijit.byId('filterSortValueList').domNode, {display:'none'});
	top.dijit.byId('idFilterAttribute').reset();
	top.dojo.byId('filterObjectClass').value= dojo.byId('objectClass').value;
	filterType="";
	var compUrl=(top.dijit.byId("dialogDetail").open)?'&comboDetail=true':'';
	top.dojo.xhrPost({
		url: "../tool/backupFilter.php?filterObjectClass=" + top.dojo.byId('filterObjectClass').value + compUrl,
		handleAs: "text",
		load: function(data,args) { }
		});
	compUrl=(top.dijit.byId("dialogDetail").open)?'?comboDetail=true':'';
	top.loadContent("../tool/displayFilterClause.php"+compUrl, "listFilterClauses", "dialogFilterForm", false);
	top.loadContent("../tool/displayFilterList.php"+compUrl, "listStoredFilters", "dialogFilterForm", false);
	/*var datastore = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?listType=object&objectClass=' + dojo.byId("objectClass").value});
	var store = new dojo.store.DataStore({store: datastore});
	store.query({id:"*"});
	dijit.byId('idFilterAttribute').set('store',store);*/
	top.refreshListSpecific('object', 'idFilterAttribute', 'objectClass', dojo.byId("objectClass").value);
	top.dijit.byId("dialogFilter").show();
}

/**
 * Select attribute : refresh dependant lists box
 * 
 */
function filterSelectAtribute(value) {
	if (value) {
	  filterStartInput=true;	
	  top.dijit.byId('idFilterAttribute').store.store.fetchItemByIdentity({
	    identity : value, 
	    onItem : function(item) { 
	  	  var dataType = top.dijit.byId('idFilterAttribute').store.store.getValue(item, "dataType", "inconnu");
	  	  var datastoreOperator = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?listType=operator&dataType=' + dataType});	  		
	  	  var storeOperator = new dojo.store.DataStore({store: datastoreOperator});
	  	  storeOperator.query({id:"*"});
	  	  top.dijit.byId('idFilterOperator').set('store',storeOperator);
	  	  datastoreOperator.fetch({
	  	  	query : {id : "*"},
	  	  	count : 1,
	  	  	onItem : function(item) { 
	  	  		top.dijit.byId('idFilterOperator').set("value",item.id);
	  	  	},  
            onError : function(err) { 
              console.info(err.message) ;  
            }
	  	  });	      
	  	  dojo.style(top.dijit.byId('idFilterOperator').domNode, {visibility:'visible'});
	  		top.dojo.byId('filterDataType').value=dataType;
	  		if (dataType=="bool") {
	  			filterType="bool";
	  			dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'block'});
	  			top.dijit.byId('filterValueCheckbox').set('checked','');
	  			dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	  		} else if (dataType=="list") {
	  			filterType="list";
	  			if (value=='idTargetVersion' || value=='idOriginalValue') {value='idVersion';}
	  			var tmpStore = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?required=true&listType=list&dataType=' + value});
	  			var mySelect=top.dojo.byId("filterValueList");
	  			mySelect.options.length=0;
	  			var nbVal=0;
	  			tmpStore.fetch({
		  	  	  query : {id : "*"},
		  	  	  onItem : function(item) {
		  	  		mySelect.options[mySelect.length] = new Option(tmpStore.getValue(item,"name",""),tmpStore.getValue(item,"id",""));
		  	  		nbVal++;
		  	  	  },  
	              onError : function(err) { 
	                console.info(err.message) ;  
	              }
		  	    });
	  			mySelect.size=(nbVal>10)?10:nbVal;
	  			dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueList').domNode, {display:'block'});
	  			top.dijit.byId('filterValueList').reset();
	  			dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	  		} else if (dataType=="date") {
	  			filterType="date";
	  			dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  			dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'block'});
	  			top.dijit.byId('filterValueDate').reset();
	  		} else {
	  	  	    filterType="text";
	  	  	    dojo.style(top.dijit.byId('filterValue').domNode, {display:'block'});
	  	  	    top.dijit.byId('filterValue').reset();
	  	        dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
	  	        dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  	        dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	  		}
	  	},
	    onError : function(err) {
	    	dojo.style(top.dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
	    	dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
	    	dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
	    	dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	    	dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	  		//hideWait();
	    }
    }) ;
    top.dijit.byId('filterValue').reset();
    top.dijit.byId('filterValueList').reset();
    top.dijit.byId('filterValueCheckbox').reset();
    top.dijit.byId('filterValueDate').reset();
	} else {
		dojo.style(top.dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
		dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
	}
}

function filterSelectOperator(operator) {
	filterStartInput=true;
	if (operator=="SORT") {
		filterType="SORT";
		dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterSortValueList').domNode, {display:'block'});
	} else if (operator=="<=now+" || operator==">=now+") {
		filterType="text";
		dojo.style(top.dijit.byId('filterValue').domNode, {display:'block'});
		dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
		dojo.style(top.dijit.byId('filterSortValueList').domNode, {display:'none'});
    } else if (operator=="isEmpty" || operator=="isNotEmpty") {
        filterType="null";
        dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
        dojo.style(top.dijit.byId('filterValueList').domNode, {display:'none'});
        dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'none'});
        dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'none'});
        dojo.style(top.dijit.byId('filterSortValueList').domNode, {display:'none'});
	} else {
		dojo.style(top.dijit.byId('filterValue').domNode, {display:'none'});
		dataType=top.dojo.byId('filterDataType').value;
		dojo.style(top.dijit.byId('filterSortValueList').domNode, {display:'none'});
		if (dataType=="bool") {
			filterType="bool";
			dojo.style(top.dijit.byId('filterValueCheckbox').domNode, {display:'block'});
		} else if (dataType=="list") {
			filterType="list";
			dojo.style(top.dijit.byId('filterValueList').domNode, {display:'block'});
		} else if (dataType=="date") {
			filterType="date";
			dojo.style(top.dijit.byId('filterValueDate').domNode, {display:'block'});
		} else {
	  	    filterType="text";
			dojo.style(top.dijit.byId('filterValue').domNode, {display:'block'});
		}
	}
}

/**
 * Save filter clause
 * 
 */
function addfilterClause(silent) {
	filterStartInput=false;
	if (top.dijit.byId('filterNameDisplay')) {
		top.dojo.byId('filterName').value=top.dijit.byId('filterNameDisplay').get('value');
	}
	if (filterType=="") { 
		if (!silent) showAlert(i18n('attributeNotSelected')); 
		return;
	}
	if (trim(top.dijit.byId('idFilterOperator').get('value'))=='') { 
		if (!silent) showAlert(i18n('operatorNotSelected')); 
		return;
	}
    if (filterType=="list" && trim(top.dijit.byId('filterValueList').get('value'))=='') {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
    }
    if (filterType=="date" && ! top.dijit.byId('filterValueDate').get('value')) {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
    }
    if (filterType=="text" && ! top.dijit.byId('filterValue').get('value')) {
        if (!silent) showAlert(i18n('valueNotSelected'));
        return;
    }
	// Add controls on operator and value
    var compUrl=(top.dijit.byId("dialogDetail").open)?'?comboDetail=true':'';
    top.loadContent("../tool/addFilterClause.php"+compUrl, "listFilterClauses", "dialogFilterForm", false);
	//dijit.byId('filterNameDisplay').set('value',null);
	//dojo.byId('filterName').value=null;
}

/**
 * Remove a filter clause
 * 
 */
function removefilterClause(id) {
	if (top.dijit.byId('filterNameDisplay')) {
		top.dojo.byId('filterName').value=top.dijit.byId('filterNameDisplay').get('value');
	}
	// Add controls on operator and value
	top.dojo.byId("filterClauseId").value=id;
	var compUrl=(top.dijit.byId("dialogDetail").open)?'?comboDetail=true':'';
	top.loadContent("../tool/removeFilterClause.php"+compUrl, "listFilterClauses", "dialogFilterForm", false);
	//dijit.byId('filterNameDisplay').set('value',null);
	//dojo.byId('filterName').value=null;
}

/**
 * Action on OK for filter
 * 
 */
function selectFilter() {
	if (filterStartInput) {
		addfilterClause(true);
		setTimeout("selectFilterContinue();",1000);
	} else {
		selectFilterContinue();
	}
}
function selectFilterContinue() {
	if (top.dijit.byId('dialogDetail').open) { 
		var doc=window.frames['comboDetailFrame'];
    } else { 
    	var doc=top;
    }
	if (top.dijit.byId('filterNameDisplay')) {
		top.dojo.byId('filterName').value=top.dijit.byId('filterNameDisplay').get('value');
	}
	var compUrl=(top.dijit.byId("dialogDetail").open)?'&comboDetail=true':'';
	dojo.xhrPost({
		url: "../tool/backupFilter.php?valid=true"+compUrl,
		form: top.dojo.byId('dialogFilterForm'),
		handleAs: "text",
		load: function(data,args) { }
	});
	if (top.dojo.byId("nbFilterCriteria").value>0) {
		doc.dijit.byId("listFilterFilter").set("iconClass","iconActiveFilter16");
	} else {
		doc.dijit.byId("listFilterFilter").set("iconClass","iconFilter16");
	}
	doc.loadContent("../tool/displayFilterList.php?context=directFilterList&filterObjectClass="+dojo.byId('objectClass').value+compUrl, "directFilterList", null, false,'returnFromFilter', false);
	doc.refreshJsonList(dojo.byId('objectClass').value);
	top.dijit.byId("dialogFilter").hide();
	filterStartInput=false;
}

/**
 * Action on Cancel for filter
 * 
 */
function cancelFilter() {
	filterStartInput=true;
	var compUrl=(top.dijit.byId("dialogDetail").open)?'&comboDetail=true':'';
	top.dojo.xhrPost({url: "../tool/backupFilter.php?cancel=true"+compUrl,
		form: dojo.byId('dialogFilterForm'),
		handleAs: "text",
		load: function(data,args) { }
	});
		top.dijit.byId('dialogFilter').hide();
}

/**
 * Action on Clear for filter
 * 
 */
function clearFilter() {
	if (top.dijit.byId('filterNameDisplay')) {
		top.dijit.byId('filterNameDisplay').reset();
	}
	top.dojo.byId('filterName').value="";
	top.removefilterClause('all');	
	//setTimeout("selectFilter();dijit.byId('listFilterFilter').set('iconClass','iconFilter16');",100);
	dijit.byId('listFilterFilter').set('iconClass','iconFilter16');
	top.dijit.byId('filterNameDisplay').set('value',null);
	top.dojo.byId('filterName').value=null;
}

/**
 * Action on Default for filter
 * 
 */
function defaultFilter() {
	if (top.dijit.byId('filterNameDisplay')) {
		//if (dijit.byId('filterNameDisplay').get('value')=="") {
		//	showAlert(i18n("messageMandatory", new Array(i18n("filterName")) ));
		//	exit;
		//}
		top.dojo.byId('filterName').value=top.dijit.byId('filterNameDisplay').get('value');
	}
	var compUrl=(top.dijit.byId("dialogDetail").open)?'?comboDetail=true':'';
	top.loadContent("../tool/defaultFilter.php"+compUrl, "listStoredFilters", "dialogFilterForm", false);
}

/**
 * Save a filter as a stored filter
 * 
 */
function saveFilter() {
	if (top.dijit.byId('filterNameDisplay')) {
		if (top.dijit.byId('filterNameDisplay').get('value')=="") {
			showAlert(i18n("messageMandatory", new Array(i18n("filterName")) ));
			return;
		}
		top.dojo.byId('filterName').value=top.dijit.byId('filterNameDisplay').get('value');
	}
	var compUrl=(top.dijit.byId("dialogDetail").open)?'?comboDetail=true':'';
	top.loadContent("../tool/saveFilter.php"+compUrl, "listStoredFilters", "dialogFilterForm", false);
}

/**
 * Select a stored filter in the list and fetch criteria
 * 
 */
function selectStoredFilter(idFilter,context) {
  var compUrl=(top.dijit.byId("dialogDetail").open)?'&comboDetail=true':'';
  if (context=='directFilterList') {
	  if (idFilter=='0') {
			top.dojo.byId('noFilterSelected').value='true';
	  } else {
			top.dojo.byId('noFilterSelected').value='false';
	  }
	  loadContent("../tool/selectStoredFilter.php?idFilter="+idFilter+"&context=" + context 
			+ "&filterObjectClass="+dojo.byId('objectClass').value+compUrl, "directFilterList", null, false);	
  } else {
	loadContent("../tool/selectStoredFilter.php?idFilter="+idFilter+compUrl, "listFilterClauses", "dialogFilterForm", false);
  }
}

/**
 * Removes a stored filter from the list
 * 
 */
function removeStoredFilter(idFilter, nameFilter) {
  var compUrl=(top.dijit.byId("dialogDetail").open)?'&comboDetail=true':'';
  var action=function() {
  	top.loadContent("../tool/removeFilter.php?idFilter="+idFilter+compUrl, "listStoredFilters", "dialogFilterForm", false);
  };
  showConfirm(i18n("confirmRemoveFilter",new Array(nameFilter)),action);
}

//=============================================================================
//= Reports
//=============================================================================

function reportSelectCategory(idCateg) {
	loadContent("../view/reportsParameters.php?idReport=", "reportParametersDiv", null, false);
	var tmpStore = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?required=true&listType=list&dataType=idReport&critField=idReportCategory&critValue='+idCateg});
	var mySelectWidget=dijit.byId("reportsList");
	mySelectWidget.reset();
	var mySelect=dojo.byId("reportsList");
	mySelect.options.length=0;
	var nbVal=0;
	tmpStore.fetch({
  	query : {id : "*"},
  	onItem : function(item) {
  		mySelect.options[mySelect.length] = new Option(tmpStore.getValue(item,"name",""),tmpStore.getValue(item,"id",""));
  		nbVal++;
  	},  
    onError : function(err) { 
      console.info(err.message) ;  
    }
  });
}

function reportSelectReport(idReport) {
	loadContent("../view/reportsParameters.php?idReport="+idReport, "reportParametersDiv", null, false);
}

//=============================================================================
//= Resource Cost
//=============================================================================

function addResourceCost(idResource, idRole, funcList) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("resourceCostId").value="";
	dojo.byId("resourceCostIdResource").value=idResource;
	dojo.byId("resourceCostFunctionList").value=funcList;
	dijit.byId("resourceCostIdRole").set('readOnly',false);
	if (idRole) {
	  dijit.byId("resourceCostIdRole").set('value',idRole);
	} else {
		dijit.byId("resourceCostIdRole").reset();
	}
	dijit.byId("resourceCostValue").reset('value');
	dijit.byId("resourceCostStartDate").set('value',null);
	resourceCostUpdateRole();
	dijit.byId("dialogResourceCost").show();
}

function removeResourceCost(id, idRole, nameRole, startDate) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("resourceCostId").value=id;
	actionOK=function() {loadContent("../tool/removeResourceCost.php", "resultDiv", "resourceCostForm", true, 'resourceCost');};
	msg=i18n('confirmDeleteResourceCost',new Array(nameRole, startDate));
	showConfirm (msg, actionOK);
} 

reourceCostLoad=false;
function editResourceCost(id, idResource,idRole,cost,startDate,endDate) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("resourceCostId").value=id;
	dojo.byId("resourceCostIdResource").value=idResource;
	dijit.byId("resourceCostIdRole").set('readOnly',true);
	dijit.byId("resourceCostValue").set('value',dojo.number.format(cost/100));
	var dateStartDate=getDate(startDate);
	dijit.byId("resourceCostStartDate").set('value',dateStartDate);
	dijit.byId("resourceCostStartDate").set('disabled',true);
	dijit.byId("resourceCostStartDate").set('required','false');
	reourceCostLoad=true;
	dijit.byId("resourceCostIdRole").set('value',idRole);
	setTimeout('reourceCostLoad=false;',300);
	dijit.byId("dialogResourceCost").show();  	
}

function saveResourceCost() {
	var formVar = dijit.byId('resourceCostForm');
  if(formVar.validate()){		
  	loadContent("../tool/saveResourceCost.php", "resultDiv", "resourceCostForm", true,'resourceCost');
  	dijit.byId('dialogResourceCost').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function resourceCostUpdateRole() {
	if (reourceCostLoad) {return;}
	var funcList=dojo.byId('resourceCostFunctionList').value;
	$key='#' + dijit.byId("resourceCostIdRole").get('value') + '#';
	if (funcList.indexOf($key)>=0) {
		dijit.byId("resourceCostStartDate").set('disabled',false);
		dijit.byId("resourceCostStartDate").set('required','true');
	} else {
		dijit.byId("resourceCostStartDate").set('disabled',true);
		dijit.byId("resourceCostStartDate").set('value',null);
		dijit.byId("resourceCostStartDate").set('required','false');
	}
}

//=============================================================================
//= Version Project
//=============================================================================

function addVersionProject(idVersion, idProject) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	refreshList('idProject', null, null, null, 'versionProjectProject', true);
	refreshList('idVersion', null, null, null, 'versionProjectVersion', true);
	dojo.byId("versionProjectId").value="";
	if (idVersion) {
		dijit.byId("versionProjectVersion").set('readOnly',true);
		dijit.byId("versionProjectVersion").set('value',idVersion);
	} else {
	    dijit.byId("versionProjectVersion").set('readOnly',false);
		dijit.byId("versionProjectVersion").reset();
	}
	if (idProject) {
		dijit.byId("versionProjectProject").set('readOnly',true);
		dijit.byId("versionProjectProject").set('value',idProject);
	} else {
		dijit.byId("versionProjectProject").set('readOnly',false);
		dijit.byId("versionProjectProject").reset();
	}
	
	dijit.byId("versionProjectIdle").reset();
	dijit.byId("versionProjectStartDate").reset();
	dijit.byId("versionProjectEndDate").reset();
	dijit.byId("dialogVersionProject").show();
}

function removeVersionProject(id) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("versionProjectId").value=id;
	actionOK=function() {loadContent("../tool/removeVersionProject.php", "resultDiv", "versionProjectForm", true, 'versionProject');};
	msg=i18n('confirmDeleteVersionProject');
	showConfirm (msg, actionOK);
} 

versionProjectLoad=false;
function editVersionProject(id, idVersion,idProject,startDate,endDate,idle) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("versionProjectId").value=id;
	refreshList('idProject', null, null, null, 'versionProjectProject', true);
	refreshList('idVersion', null, null, null, 'versionProjectVersion', true);
	if (idVersion) {
		dijit.byId("versionProjectVersion").set('readOnly',true);
		dijit.byId("versionProjectVersion").set('value',idVersion);
	} else {
	    dijit.byId("versionProjectVersion").set('readOnly',false);
		dijit.byId("versionProjectVersion").reset();
	}
	if (idProject) {
		dijit.byId("versionProjectProject").set('readOnly',true);
		dijit.byId("versionProjectProject").set('value',idProject);
	} else {
		dijit.byId("versionProjectProject").set('readOnly',false);
		dijit.byId("versionProjectProject").reset();
	}
	if (startDate) {
	  dijit.byId("versionProjectStartDate").set('value',startDate);
	} else {
		dijit.byId("versionProjectStartDate").reset();
	}
	if (endDate) {
	  dijit.byId("versionProjectEndDate").set('value',endDate);
	} else {
		dijit.byId("versionProjectEndDate").reset();
	}
	if (idle==1) {
		dijit.byId("versionProjectIdle").set('value',idle);
	} else {
		dijit.byId("versionProjectIdle").reset();
	}
	dijit.byId("dialogVersionProject").show();  	
}

function saveVersionProject() {
	var formVar = dijit.byId('versionProjectForm');
	if(formVar.validate()){		
		loadContent("../tool/saveVersionProject.php", "resultDiv", "versionProjectForm", true,'versionProject');
		dijit.byId('dialogVersionProject').hide();
	} else {
		showAlert(i18n("alertInvalidForm"));
	}
}

//=============================================================================
//= Test Case Run
//=============================================================================

function addTestCaseRun() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	refreshTestCaseRunList();
	dojo.byId("testCaseRunId").value="";
	dojo.byId("testCaseRunMode").value="add";
	dojo.byId("testCaseRunTestSession").value=dijit.byId('id').get('value');
	dijit.byId('testCaseRunComment').reset();
	dijit.byId('testCaseRunStatus').set('value',1);
	dojo.byId('testCaseRunAddDiv').style.display="block";
	dojo.byId('testCaseRunEditDiv').style.display="none";
	dijit.byId('testCaseRunTicket').reset();
	disableWidget('dialogTestCaseRunSubmit');
	dijit.byId("dialogTestCaseRun").show();
}
function refreshTestCaseRunList(selected) {
	disableWidget('dialogTestCaseRunSubmit');
	var url='../tool/dynamicListTestCase.php'
		+'?idProject='+dijit.byId('idProject').get('value')
		+'&idProduct='+dijit.byId('idProduct').get('value');
	if (selected) {
		url+='&selected='+selected;
	}
	loadContent(url, 'testCaseRunListDiv', 'testCaseRunForm', false);
}

function editTestCaseRun(idTestCaseRun, idTestCase, idRunStatus, idTicket, hide) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	idProject=dijit.byId('idProject').get('value');
	refreshList('idTestCase', 'idProject', '0', idTestCase, 'testCaseRunTestCase', true);
	refreshList('idTicket', 'idProject', idProject, idTicket, 'testCaseRunTicket', false);
	dijit.byId("testCaseRunTestCase").set('readOnly',true);
	dijit.byId('testCaseRunTestCase').set('value',idTestCase);
	if (idTicket) {
	  dijit.byId('testCaseRunTicket').set('value',idTicket);
	} else {
		dijit.byId('testCaseRunTicket').reset();
	}
	dojo.byId("testCaseRunId").value=idTestCaseRun;
	dojo.byId("testCaseRunMode").value="edit";
	dojo.byId("testCaseRunTestSession").value=dijit.byId('id').get('value');
	dijit.byId('testCaseRunComment').set('value',dojo.byId("comment_"+idTestCaseRun).value);
	dijit.byId('testCaseRunStatus').set('value',idRunStatus);
	dojo.byId('testCaseRunAddDiv').style.display="none";
	dojo.byId('testCaseRunEditDiv').style.display="block";
	testCaseRunChangeStatus();
	enableWidget('dialogTestCaseRunSubmit');
	if (! hide) {
	  dijit.byId("dialogTestCaseRun").show();
	}
}

function passedTestCaseRun(idTestCaseRun, idTestCase, idRunStatus, idTicket) {
	showWait();
	editTestCaseRun(idTestCaseRun, idTestCase, '2', idTicket, true);
	setTimeout("saveTestCaseRun()",500);
}

function failedTestCaseRun(idTestCaseRun, idTestCase, idRunStatus, idTicket) {
	editTestCaseRun(idTestCaseRun, idTestCase, '3', idTicket, false);
}

function blockedTestCaseRun(idTestCaseRun, idTestCase, idRunStatus, idTicket) {
	showWait();
	editTestCaseRun(idTestCaseRun, idTestCase, '4', idTicket, true);
	setTimeout("saveTestCaseRun()",500);
}

function testCaseRunChangeStatus() {
	var status=dijit.byId('testCaseRunStatus').get('value');
	if (status=='3') {
		dojo.byId('testCaseRunTicketDiv').style.display="block";
	} else {
		if (! trim(dijit.byId('testCaseRunTicket').get('value'))) {
		  dojo.byId('testCaseRunTicketDiv').style.display="none";
		} else {
			dojo.byId('testCaseRunTicketDiv').style.display="block";
		}
	}
}

function removeTestCaseRun(id, idTestCase) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("testCaseRunId").value=id;
	actionOK=function() {loadContent("../tool/removeTestCaseRun.php", "resultDiv", "testCaseRunForm", true, 'testCaseRun');};
	msg=i18n('confirmDeleteTestCaseRun', new Array(idTestCase));
	showConfirm (msg, actionOK);
} 

function saveTestCaseRun() {
	var formVar = dijit.byId('testCaseRunForm');
	var mode = dojo.byId("testCaseRunMode").value;
	if (mode=='add' && dojo.byId("testCaseRunTestCaseList").value=="") return;
	if (mode=='edit') {
	  var status=dijit.byId('testCaseRunStatus').get('value');
	  if (status=='3') {
		  if (trim(dijit.byId('testCaseRunTicket').get('value'))=='') {
			  dijit.byId("dialogTestCaseRun").show();
			showAlert(i18n('messageMandatory', new Array(i18n('colTicket'))));
			return;
		}
	  }
	}
	if(mode=='add' || formVar.validate()){		
		loadContent("../tool/saveTestCaseRun.php", "resultDiv", "testCaseRunForm", true,'testCaseRun');
		dijit.byId('dialogTestCaseRun').hide();
	} else {
		dijit.byId("dialogTestCaseRun").show();
		showAlert(i18n("alertInvalidForm"));
	}
}


//=============================================================================
//= Affectation
//=============================================================================

function addAffectation(objectClass, type, idResource, idProject) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	refreshList('idProject', null, null, null, 'affectationProject', true);
	if (objectClass=='Project') {
	  refreshList('id'+type, null, null, null, 'affectationResource', true);
	} else { 
	  refreshList('id'+objectClass, null, null, null, 'affectationResource', true);
	}
	dojo.byId("affectationId").value="";
	dojo.byId("affectationIdTeam").value="";
	if (objectClass=='Project') {
		dijit.byId("affectationProject").set('readOnly',true);
		dijit.byId("affectationProject").set('value',idProject);
		dijit.byId("affectationResource").set('readOnly',false);
		dijit.byId("affectationResource").reset();
	} else {
		dijit.byId("affectationResource").set('readOnly',true);
		dijit.byId("affectationResource").set('value',idResource);
		dijit.byId("affectationProject").set('readOnly',false);
		dijit.byId("affectationProject").reset();
	}
	dijit.byId("affectationResource").set('required',true);
	dijit.byId("affectationRate").set('value','100');
	dijit.byId("affectationIdle").reset();
	dijit.byId("dialogAffectation").show();
}

function removeAffectation(id) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("affectationId").value=id;
	dojo.byId("affectationIdTeam").value="";
	actionOK=function() {loadContent("../tool/removeAffectation.php", "resultDiv", "affectationForm", true, 'affectation');};
	msg=i18n('confirmDeleteAffectation',new Array(id));
	showConfirm (msg, actionOK);
} 

affectationLoad=false;
function editAffectation(id, objectClass, type, idResource, idProject, rate,idle) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	refreshList('idProject', null, null, idProject, 'affectationProject', true);
	if (objectClass=='Project') {
	  refreshList('id'+type, null, null, null, 'affectationResource', true);
	} else { 
	  refreshList('id'+objectClass, null, null, null, 'affectationResource', true);
	}
	dijit.byId("affectationResource").set('required',true);
	dojo.byId("affectationId").value=id;
	dojo.byId("affectationIdTeam").value="";
	if (objectClass=='Project') {
		dijit.byId("affectationProject").set('readOnly',true);
		dijit.byId("affectationProject").set('value',idProject);
		dijit.byId("affectationResource").set('readOnly',false);
		dijit.byId("affectationResource").set('value',idResource);
	} else {
		dijit.byId("affectationResource").set('readOnly',true);
		dijit.byId("affectationResource").set('value',idResource);
		dijit.byId("affectationProject").set('readOnly',false);
		dijit.byId("affectationProject").set('value',idProject);
	}
	if (rate) {
	  dijit.byId("affectationRate").set('value',rate);
	} else {
      dijit.byId("affectationRate").reset();
	}
	if (idle==1) {
		dijit.byId("affectationIdle").set('value',idle);
	} else {
		dijit.byId("affectationIdle").reset();
	}
	dijit.byId("dialogAffectation").show();  	
}

function saveAffectation() {
	var formVar = dijit.byId('affectationForm');
	if(formVar.validate()){		
		loadContent("../tool/saveAffectation.php", "resultDiv", "affectationForm", true,'affectation');
		dijit.byId('dialogAffectation').hide();
	} else {
		showAlert(i18n("alertInvalidForm"));
	}
}

function affectTeamMembers(idTeam) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	refreshList('idProject', null, null, null, 'affectationProject', true);
	dojo.byId("affectationId").value="";
	dojo.byId("affectationIdTeam").value=idTeam;
    dijit.byId("affectationResource").set('readOnly',true);
    dijit.byId("affectationResource").set('required',false);
	dijit.byId("affectationResource").reset();
	dijit.byId("affectationProject").set('readOnly',false);
	dijit.byId("affectationProject").reset();
	dijit.byId("affectationRate").set('value','100');
	dijit.byId("affectationIdle").reset();
	dijit.byId("affectationIdle").set('readOnly',true);
	dijit.byId("dialogAffectation").show();
}
//=============================================================================
//= Misceallanous
//=============================================================================

//var manualWindow=null;
function showHelp() {
	var objectClass=dojo.byId('objectClass');
	var objectClassManual=dojo.byId('objectClassManual');
	var section='';
	if (objectClassManual) {
		section=objectClassManual.value;
	} else if (objectClass) {
		section=objectClass.value;
	}
	var url='../manual/manual.php?section=' + section;
	var name="Manual";
	var attributes='toolbar=no, titlebar=no, menubar=no, status=no, scrollbars=no, directories=no, location=no, resizable=no,'
		 +'height=650, width=1024, top=0, left=0';
	manualWindow=window.open(url, name , attributes);
	manualWindow.focus();
	//manualWindow.window.focus();
	
	return false;
} 


/**
 * Refresh a list (after update)
 */
function refreshList(field, param, paramVal, selected, destination, required) {
	var urlList='../tool/jsonList.php?listType=list&dataType=' + field;
	if (param) {
	  urlList+='&critField='+param;
	  urlList+='&critValue='+paramVal;
	}
	if (selected) {
		urlList+='&selected='+selected;
	}
	if (required) {
		urlList+='&required=true';
	}
	var datastore = new dojo.data.ItemFileReadStore({url: urlList});
	var store = new dojo.store.DataStore({store: datastore});
	store.query({id:"*"});
	if (destination) {
	  var mySelect=dijit.byId(destination);	
	} else {
	  var mySelect=dijit.byId(field);
	}
	mySelect.set('store',store);
}
function refreshListSpecific(listType, destination, param, paramVal, selected, required ) {
	var urlList='../tool/jsonList.php?listType='+listType;
	if (param) {
	  urlList+='&'+param+'='+paramVal;
    }
	if (selected) {
		urlList+='&selected='+selected;
	}
	if (required) {
		urlList+='&required=true';
	}
	var datastore = new dojo.data.ItemFileReadStore({url: urlList});
	var store = new dojo.store.DataStore({store: datastore});
	store.query({id:"*"});
	var mySelect=dijit.byId(destination);	
	mySelect.set('store',store);
}
function setProductValueFromVersion(field,versionId) {
  //alert("Call : "+field+"/"+versionId);
  dojo.xhrGet({
		url: "../tool/getProductValueFromVersion.php?idVersion="+versionId,
		handleAs: "text",
		load: function(data,args) { 
			prd=dijit.byId(field);
			if (prd) {
			   prd.set("value",data);	
			}
		},
	    error: function() {   }
	  });
}
var menuHidden=false;
var menuActualStatus='visible';
var menuDivSize=0; 
var menuShowMode='CLICK';
/**
 * Hide or show the Menu (left part of the screen
 */
function hideShowMenu() {
	if (! dijit.byId("leftDiv")) {
		return;
	}
	duration=300;
	if (menuActualStatus=='visible' || ! menuHidden) {		
		menuDivSize=dojo.byId("leftDiv").offsetWidth;
		fullWidth=dojo.byId("mainDiv").offsetWidth;
		if (menuDivSize<2) {
			menuDivSize=dojo.byId("mainDiv").offsetWidth*.2;
		}
		if (! isHtml5()) {
		  duration=0;
		  dijit.byId("leftDiv").resize({w: 20});
		  setTimeout("dojo.byId('menuBarShow').style.display='block'",10);
		  //dojo.byId('menuBarShow').style.display='block';
		  dojo.byId('leftDiv_splitter').style.display='none';		
		} else {
		  dojox.fx.combine([
			dojox.fx.animateProperty({  node:"leftDiv", properties: { width: 20 }, duration: duration }),
			dojox.fx.animateProperty({  node:"centerDiv", properties: { left: 20, width: fullWidth}, duration: duration }),
			dojox.fx.animateProperty({  node:"leftDiv_splitter", properties: { left: 20}, duration: duration })
		  ]).play();
		  setTimeout("dojo.byId('menuBarShow').style.display='block'",duration);
		  setTimeout("dojo.byId('leftDiv_splitter').style.display='none';",duration);
		}
	    dijit.byId("buttonHideMenu").set('label',i18n('buttonShowMenu'));
		menuHidden=true;
		menuActualStatus='hidden';		
	} else {
		dojo.byId('menuBarShow').style.display='none';
		dojo.byId('leftDiv_splitter').style.left='20px';
		dojo.byId('leftDiv_splitter').style.display='block';
		if (menuDivSize<20) {
			menuDivSize=dojo.byId("mainDiv").offsetWidth*.2;
		}
		if (! isHtml5()) {
		  duration=0;
		  dijit.byId("leftDiv").resize({w: menuDivSize});
		} else {
   	      dojox.fx.combine([
			dojox.fx.animateProperty({ node:"leftDiv",  properties: { width: menuDivSize }, duration: duration }),
			dojox.fx.animateProperty({  node:"centerDiv", properties: { left: menuDivSize+5}, duration: duration }),
			dojox.fx.animateProperty({  node:"leftDiv_splitter", properties: { left: menuDivSize}, duration: duration })
		  ]).play();
		}
		dijit.byId("buttonHideMenu").set('label',i18n('buttonHideMenu'));
		menuHidden=false;
		menuActualStatus='visible';
	}
	setTimeout('dijit.byId("globalContainer").resize();',duration+10);
	//dojo.byId('menuBarShow').style.top='50px';
}
function tempShowMenu(mode) {
	if (mode=='mouse' && menuShowMode=='CLICK') return;
	hideShowMenu();
	menuHidden=true;
}
function menuClick() {
	if (menuHidden) {
		menuHidden=false;
		hideShowMenu();
		menuHidden=true;
	}
}

var switchedMode=false;
var listDivSize=0;
var switchedVisible='';
var switchListMode='CLICK';
function switchMode(){
	if (! switchedMode) {
		switchedMode=true;
		dijit.byId("buttonSwitchMode").set('label',i18n('buttonStandardMode'));
		if (! dojo.byId("listDiv")) {
			if (listDivSize==0) {
			  listDivSize=dojo.byId("centerDiv").offsetHeight*.4;
			}
			return;
		} else {
		  listDivSize=dojo.byId("listDiv").offsetHeight;
		}
		if (dojo.byId('listDiv_splitter')) {
			dojo.byId('listDiv_splitter').style.display='none';
		}
		if (dijit.byId('id')) {
		  hideList();
		} else {
		  showList();
		}
	} else {
		switchedMode=false;
		dijit.byId("buttonSwitchMode").set('label',i18n('buttonSwitchedMode'));
		if (! dojo.byId("listDiv")) {
			return;
		}
		if (dojo.byId('listBarShow')) {
		  dojo.byId('listBarShow').style.display='none';
		}
		if (dojo.byId('detailBarShow')) {
		  dojo.byId('detailBarShow').style.display='none';
		}
		if (dojo.byId('listDiv_splitter')) {
			dojo.byId('listDiv_splitter').style.display='block';
		}
		if (listDivSize==0) {
		  listDivSize=dojo.byId("centerDiv").offsetHeight*.4;
		}
		dijit.byId("listDiv").resize({h: listDivSize});		
		dijit.byId("mainDivContainer").resize();
	}
}

function showList(mode) {
	duration=300;
	if (mode=='mouse' && switchListMode=='CLICK') return;
	if (! switchedMode) {
		return;
	}
	if (! dijit.byId("listDiv") || ! dijit.byId("mainDivContainer") ) {
		return;
	}
	if (dojo.byId('listDiv_splitter')) {
		dojo.byId('listDiv_splitter').style.display='none';
	}
	if (dojo.byId('listBarShow')) {
	  dojo.byId('listBarShow').style.display='none';
	}
    fullSize=dojo.byId("listDiv").offsetHeight+dojo.byId("detailDiv").offsetHeight-20;
	if (0 || ! isHtml5() ) {
	  dijit.byId("listDiv").resize({h: fullSize});
	  duration=0;
	} else {
 	  dojox.fx.animateProperty({ node:"listDiv",  properties: { height: fullSize }, duration: duration }).play();
	}
	if (dojo.byId('detailBarShow')) {
		setTimeout("dojo.byId('detailBarShow').style.display='block';",duration+10);
	}
	resizeContainer("mainDivContainer",duration);
	switchedVisible='list';
}

function hideList(mode) {
	duration=300;
	if (mode=='mouse' && switchListMode=='CLICK') return;
	if (! switchedMode) {
		return;
	}
	if (! dijit.byId("listDiv") || ! dijit.byId("mainDivContainer") ) {
		return;
	}
	if (dojo.byId('listDiv_splitter')) {
		dojo.byId('listDiv_splitter').style.display='none';
	}
	if (dojo.byId('listBarShow')) {
	  dojo.byId('listBarShow').style.display='block';
	}
	if (dojo.byId('detailBarShow')) {
	  dojo.byId('detailBarShow').style.display='none';
	}
	if (! isHtml5() ) {
	  dijit.byId("listDiv").resize({h: 20});
	  duration=0;
	} else {
	  dojox.fx.combine([
		dojox.fx.animateProperty({ node:"listDiv",  properties: { height: 29 }, duration: duration })
		]).play();
	}
	resizeContainer("mainDivContainer",duration);
	switchedVisible='detail';
}

function resizeContainer(container,duration) {
  sequ=10;
  for (i=0;i<sequ;i++) {
	setTimeout('dijit.byId("'+container+'").resize();', i*duration/sequ);  
  }
  setTimeout('dijit.byId("'+container+'").resize();', duration+10);
}

function listClick() {
	stockHistory(dojo.byId('objectClass').value, dojo.byId('objectId').value);
	if (! switchedMode) {
		return;
	}
	hideList();
}

function stockHistory(curClass,curId) {
	//var len=historyTable.length;
	/*var lastClass="";
	var lastId=0;
	if (len>0) { 
	  lastClass=historyTable[len-1][0];
	  lastId=historyTable[len-1][1];
	}*/
	/*if (len==0 || curClass!=lastClass || curId!=lastId) {
	  historyTable[len]=new Array(curClass, curId);
	  historyPosition=len;
	  if (historyPosition>=1) {
	    enableWidget('menuBarUndoButton');
	  }
	  disableWidget('menuBarRedoButton');
	}*/
	historyPosition+=1;
	historyTable[historyPosition]=new Array(curClass, curId);
	if (historyPosition>0) {
	  enableWidget('menuBarUndoButton');
	}
	if (historyPosition==historyTable.length-1) {
	  disableWidget('menuBarRedoButton');	
	}
	
}

function undoItemButton() {
	var len=historyTable.length;
	if (len==0) {return;}
	if (historyPosition==0) {return;}
	historyPosition-=1;
	gotoElement(historyTable[historyPosition][0],historyTable[historyPosition][1], true);
	enableWidget('menuBarRedoButton');
	if (historyPosition==0) {
	   disableWidget('menuBarUndoButton');
	}
}

function redoItemButton() {
	var len=historyTable.length;
	if (len==0) {return;}
	if (historyPosition==len-1) {return;}
	historyPosition+=1;
	gotoElement(historyTable[historyPosition][0],historyTable[historyPosition][1], true);
	enableWidget('menuBarUndoButton');
	if (historyPosition==(len-1)) {
	   disableWidget('menuBarRedoButton');
	}
}

// Stock id and name, to 
// => avoid filterJsonList to reduce visibility => clear this data on open
// => retrieve data before close to retrieve the previous visibility
var quickSearchStockId=null;
var quickSearchStockName=null;
var quickSearchIsOpen=false;

function quickSearchOpen() {
  dojo.style("quickSearchDiv","display","block");
  if (dijit.byId("listTypeFilter")) {
	  dojo.style("listTypeFilter","display","none");	  
  }
  quickSearchStockId=dijit.byId('listIdFilter').get("value");
  if (dijit.byId('listNameFilter')) {
    quickSearchStockName=dijit.byId('listNameFilter').get("value");
    dojo.style("listNameFilter","display","none");
    dijit.byId('listNameFilter').reset();
  }  
  dijit.byId('listIdFilter').reset(); 
  dojo.style("listIdFilter","display","none");	
  dijit.byId("quickSearchValue").reset();
  dijit.byId("quickSearchValue").focus();
  quickSearchIsOpen=true;
}

function quickSearchClose() {
  quickSearchIsOpen=false;
  dojo.style("quickSearchDiv","display","none");
  if (dijit.byId("listTypeFilter")) {
	  dojo.style("listTypeFilter","display","block");	  
  }
  dojo.style("listIdFilter","display","block");
  if (dijit.byId('listNameFilter')) {
    dojo.style("listNameFilter","display","block");
    dijit.byId('listNameFilter').set("value",quickSearchStockName);
  }
  dijit.byId("quickSearchValue").reset();
  dijit.byId('listIdFilter').set("value",quickSearchStockId);  
  var objClass=dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

function quickSearchExecute() {
  if (! quickSearchIsOpen){
	  return;
  }
  if (! dijit.byId("quickSearchValue").get("value")) {
	showInfo(i18n('messageMandatory', new Array(i18n('quickSearch'))));
    return;
  }	
  var objClass=dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

/* ==========================================
 * Copy functions
 */
function copyObject(objectClass) {
  dojo.byId("copyButton").blur();
  action=function(){
    unselectAllRows('objectGrid');
    loadContent("../tool/copyObject.php", "resultDiv", 'objectForm', true);
  };
  showConfirm(i18n("confirmCopy", new Array(i18n(objectClass),dojo.byId('id').value)) ,action);
}

function copyObjectTo(objectClass) {
  dojo.byId('copyClass').value=dojo.byId("objectClass").value;
  dojo.byId('copyId').value=dojo.byId("objectId").value;
  //dijit.byId('copyToClass').set('displayedValue',i18n(objectClass));
  for (var i in copyableArray) {
    if (copyableArray[i]==objectClass) {
      dijit.byId('copyToClass').set('value',i);	
    }
  }
  dijit.byId('copyToName').set('value',dijit.byId('name').get('value'));
  dijit.byId('copyToOrigin').set('checked','checked');
  copyObjectToShowStructure();
  dijit.byId('copyToType').reset();
  //if (dojo.byId('copyClass').value==class) {
    var runModif="dijit.byId('copyToType').set('value',dijit.byId('id"+objectClass+"Type').get('value'))";
    setTimeout(runModif,1);
  //}  
  
  dijit.byId('dialogCopy').show();	
}
function copyObjectToShowStructure() {
	if (dojo.byId('copyClass').value=='Activity' && copyableArray[dijit.byId('copyToClass').get('value')]=='Activity') {
	  dojo.byId('copyWithStructureDiv').style.display='block';
	} else {
	  dojo.byId('copyWithStructureDiv').style.display='none';
	}
}

function copyProject() {
  var objectClass="Project";
  dojo.byId('copyProjectId').value=dojo.byId("objectId").value;
  dijit.byId('copyProjectToName').set('value',dijit.byId('name').get('value'));
  //dijit.byId('copyToOrigin').set('checked','checked');
  dijit.byId('copyProjectToType').reset();
  if (dijit.byId('idProjectType') && dojo.byId('codeType') && dojo.byId('codeType').value!='TMP') {
    var runModif="dijit.byId('copyProjectToType').set('value',dijit.byId('idProjectType').get('value'))";
    setTimeout(runModif,1);
  }
      
  dijit.byId('dialogCopyProject').show();	
}

function copyObjectToSubmit(objectClass) {
  var formVar = dijit.byId('copyForm');
  if(! formVar.validate()){  
    showAlert(i18n("alertInvalidForm"));
	return;
  }
  unselectAllRows('objectGrid');
  loadContent("../tool/copyObjectTo.php", "resultDiv", 'copyForm', true, 'copyTo');
  dijit.byId('dialogCopy').hide();
}

function copyProjectToSubmit(objectClass) {
  var formVar = dijit.byId('copyProjectForm');
  if(! formVar.validate()){  
    showAlert(i18n("alertInvalidForm"));
	return;
  }
  unselectAllRows('objectGrid');
  loadContent("../tool/copyProjectTo.php", "resultDiv", 'copyProjectForm', true, 'copyProject');
  dijit.byId('dialogCopyProject').hide();
  //dojo.byId('objectClass').value='Project';
}

function loadMenuBarObject(menuClass, itemName, from) {
  	if (checkFormChangeInProgress()) {
  		return false;
  	}
  	if (from=='bar') { selectTreeNodeById(dijit.byId('menuTree'), menuClass); }
  	cleanContent("detailDiv");
    formChangeInProgress=false;
    loadContent("objectMain.php?objectClass="+menuClass,"centerDiv");
    return true;
}

function loadMenuBarItem(item,itemName, from) {
  if (checkFormChangeInProgress()) {
    return false;
  }
  if (from=='bar') { selectTreeNodeById(dijit.byId('menuTree'), item); }
  cleanContent("detailDiv");
  formChangeInProgress=false;
  if (item=='Today') {
    loadContent("today.php","centerDiv");
  } else if (item=='Planning') {
	vGanttCurrentLine=-1;
	cleanContent("centerDiv");
    loadContent("planningMain.php","centerDiv");
  } else if (item=='PortfolioPlanning') {
	vGanttCurrentLine=-1;
	cleanContent("centerDiv");
	loadContent("portfolioPlanningMain.php","centerDiv");
  } else if (item=='ResourcePlanning') {
	vGanttCurrentLine=-1;
	cleanContent("centerDiv");
	loadContent("resourcePlanningMain.php","centerDiv");
  } else if (item=='Imputation') {
    loadContent("imputationMain.php","centerDiv");
  } else if (item=='ImportData') {
    loadContent("importData.php","centerDiv");
  } else if (item=='Reports') {
    loadContent("reportsMain.php","centerDiv");
  } else if(item=='UserParameter') {
    loadContent("parameter.php?type=userParameter","centerDiv");
  } else if(item=='ProjectParameter') {
    loadContent("parameter.php?type=projectParameter","centerDiv");
  } else if(item=='GlobalParameter') {
    loadContent("parameter.php?type=globalParameter","centerDiv");
  } else if(item=='Habilitation') {
    loadContent("parameter.php?type=habilitation","centerDiv");
  } else if(item=='HabilitationReport') {
    loadContent("parameter.php?type=habilitationReport","centerDiv");
  } else if(item=='HabilitationOther') {
    loadContent("parameter.php?type=habilitationOther","centerDiv");
  } else if(item=='AccessRight') {
    loadContent("parameter.php?type=accessRight","centerDiv");
  } else if(item=='Admin') {
    loadContent("admin.php","centerDiv");

  } else if(item=='Calendar') {
    loadContent("calendar.php","centerDiv");
  } else {
	  showInfo(i18n("messageSelectedNotAvailable", new Array(itemName)));
  }
  return true;
}

// ====================================================================================
// ALERTS
// ====================================================================================
//
//var alertDisplayed=false;
function checkAlert() {
  //if (alertDisplayed) return;
  dojo.xhrGet({
	url: "../tool/checkAlertToDisplay.php",
	handleAs: "text",
	load: function(data,args) { checkAlertRetour(data); },
    error: function() { checkAlert(); }
  });
}
function checkAlertRetour(data) {
  if (data) {
	var reminderDiv=dojo.byId('reminderDiv');
	var dialogReminder=dojo.byId('dialogReminder');
	reminderDiv.innerHTML=data;
	if (dojo.byId("requestRefreshProject") && dojo.byId("requestRefreshProject").value=="true") {
	  refreshProjectSelectorList();
	  setTimeout('checkAlert();',alertCheckTime*1000);
	} else if (dojo.byId('alertType')) {
		dojo.style(dialogReminder, {visibility:'visible', display:'inline', bottom: '-200px'});
		var toColor='#FFCCCC';
	    if (dojo.byId('alertType') && dojo.byId('alertType').value=='WARNING') {
			toColor='#FFFFCC';
		}
		if (dojo.byId('alertType') && dojo.byId('alertType').value=='INFO') {
			toColor='#CCCCFF';
		}
		dojo.animateProperty({
	        node: dialogReminder,
	        properties: {
	            bottom: { start: -200, end: 0 },
	            right: 0,
	            backgroundColor: { start: '#FFFFFF', end: toColor }
	        },
	        duration: 2000
	    }).play();
     }
  } else {
	setTimeout('checkAlert();',alertCheckTime*1000);  
  }
}
function setAlertReadMessage() {
  //alertDisplayed=false;
  closeAlertBox();
  if (dojo.byId('idAlert') && dojo.byId('idAlert').value) {
    setAlertRead(dojo.byId('idAlert').value);
  }
}
function setAlertReadMessageInForm() {
  dijit.byId('readFlag').set('checked','checked');
  submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
}
function setAlertRemindMessage() {
  closeAlertBox();
  if (dojo.byId('idAlert') && dojo.byId('idAlert').value) {
    setAlertRead(dojo.byId('idAlert').value, dijit.byId('remindAlertTime').get('value'));
  }
}

function setAlertRead(id, remind) {
  var url="../tool/setAlertRead.php?idAlert="+id;
  if (remind) {
	url+='&remind='+remind;
  }
  dojo.xhrGet({
	url: url,
	handleAs: "text",
	load: function(data,args) { setTimeout('checkAlert();',1000); },
	error: function() {setTimeout('checkAlert();',1000);}
  });
}

function closeAlertBox() {
	var dialogReminder=dojo.byId('dialogReminder');
	dojo.animateProperty({
        node: dialogReminder,
        properties: {
            bottom: { start: 0, end: -200 }
        },
        duration: 900,
        onEnd: function () {dojo.style(dialogReminder, {visibility:'hidden', display:'none', bottom: '-200px'}); }
    }).play();
}

// ===========================================================================================
// ADMIN functionalities
// ===========================================================================================
//
var cronCheckIteration=5; // Number of cronCheckTimeout to way max
function adminLaunchScript(scriptName) {
  var url="../tool/" + scriptName + ".php";
  dojo.xhrGet({
	url: url,
	handleAs: "text",
	load: function(data,args) {  },
	error: function() { }
  });	
  if (scriptName=='cronRun') {
    setTimeout('loadContent("admin.php","centerDiv");',1000);
  } else if (scriptName=='cronStop') {
	i=120;
	cronCheckIteration=5;
	setTimeout('adminCronCheckStop();',1000*cronSleepTime); 
  }
} 

function adminCronCheckStop() {
  dojo.xhrGet({
	url: "../tool/cronCheck.php",
	handleAs: "text",
	load: function(data,args) {
	        if (data!='running') {
	          loadContent("admin.php","centerDiv");
	        } else {
	          cronCheckIteration--;
	          if (cronCheckNumber>0) {
	        	setTimeout('adminCronCheckStop();',1000*cronSleepTime);
	          } else {
	            loadContent("admin.php","centerDiv");
	          }
	        }
	      },
	error: function() {loadContent("admin.php","centerDiv");}
  });  
}

function adminCronRelaunch() {
  var url="../tool/cronRelaunch.php";
  dojo.xhrGet({
	url: url,
	handleAs: "text",
	load: function(data,args) {  },
	error: function() { }
  });	
} 

function adminSendAlert() {
  formVar=dijit.byId("adminForm");	
  if(formVar.validate()){
    loadContent("../tool/adminFunctionalities.php?adminFunctionality=sendAlert","resultDiv", "adminForm", true, 'admin');
  }
}

function adminDisconnectAll() {
  actionOK=function() {
	  loadContent("../tool/adminFunctionalities.php?adminFunctionality=disconnectAll&element=Audit", "resultDiv", "adminForm", true, 'admin');
  };
  msg=i18n('confirmDisconnectAll');
  showConfirm (msg, actionOK);
}

function maintenance(operation,item) {
  if (operation=="updateReference")	{
	loadContent("../tool/adminFunctionalities.php?adminFunctionality="+operation+"&element="+item, "resultDiv", "adminForm", true, 'admin');  
  } else {
    var nb=dijit.byId(operation+item+"Days").get('value');
    loadContent("../tool/adminFunctionalities.php?adminFunctionality=maintenance&operation="+operation+"&item="+item+"&nbDays="+nb,"resultDiv", "adminForm", true, 'admin');
  }
}
function adminSetApplicationTo(newStatus) {
  var url="../tool/adminFunctionalities.php?adminFunctionality=setApplicationStatusTo&newStatus="+newStatus;
  showWait();
  dojo.xhrPost({
	url: url,
	form: "adminForm",
	handleAs: "text",
	load: function(data,args) {
	  loadContent("../view/admin.php","centerDiv")
    },
	error: function() { }
  });		
}

function lockDocument() {
  if (checkFormChangeInProgress()) {
	return false;
  }
  dijit.byId('locked').set('checked',true);
  dijit.byId('idLocker').set('value',dojo.byId('idCurrentUser').value);
  var curDate = new Date();
  dijit.byId('lockedDate').set('value',curDate);
  dijit.byId('lockedDateBis').set('value',curDate);
  formChanged();
  submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
  return true;
}

function unlockDocument() {
  if (checkFormChangeInProgress()) {
	return false;
  }
  dijit.byId('locked').set('checked',false);
  dijit.byId('idLocker').set('value',null);
  dijit.byId('lockedDate').set('value',null);
  dijit.byId('lockedDateBis').set('value',null);
  formChanged();
  submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
  return true;
}

/* ========================================================================
 * Planning columns management
 * ========================================================================
 */
function openPlanningColumnMgt() {
  //alert("openPlanningColumnMgt");
}

function changePlanningColumn(col,status,order) {
	if (status) {
	  order=planningColumnOrder.indexOf('Hidden'+col);
	  planningColumnOrder[order]=col;
	  movePlanningColumn(col,col);
	} else {
	  order=planningColumnOrder.indexOf(col);
	  planningColumnOrder[order]='Hidden'+col;
	} 
	dojo.xhrGet({
		url: '../tool/savePlanningColumn.php?action=status&status='
			+ ((status)?'visible':'hidden')+'&item='+col,
		handleAs: "text",
		load: function(data,args) { },
		error: function() { }
	  });	
}
function validatePlanningColumn() {
	dijit.byId('planningColumnSelector').closeDropDown();
	showWait();
	setGanttVisibility(g);
	JSGantt.changeFormat(g.getFormat(),g);
	hideWait(); 
}
function movePlanningColumn(source,destination) {
  var mode='';
  var list='';
  var nodeList=dndPlanningColumnSelector.getAllNodes();
  planningColumnOrder=new Array();
  for (i=0; i<nodeList.length; i++) {
	var itemSelected=nodeList[i].id.substr(14);
	check=(dijit.byId('checkColumnSelector'+itemSelected).get('checked'))?'':'hidden';
    list+=itemSelected+"|";
    planningColumnOrder[i]=check+itemSelected;
  }
  var url='../tool/movePlanningColumn.php?orderedList='+list;
  dojo.xhrPost({
	url: url,
	handleAs: "text",
	load: function(data,args) { }
  });  
  //loadContent(url, "planResultDiv");
}

/* ========================================================================
 * List columns management
 * ========================================================================
 */

function changeListColumn(tableId,fieldId,status,order) {
  var spinner=dijit.byId('checkListColumnSelectorWidthId'+fieldId);
  spinner.set('disabled',! status);
  dojo.xhrGet({
		url: '../tool/saveSelectedColumn.php?action=status&status='
			+ ((status)?'visible':'hidden')+'&item='+tableId,
		handleAs: "text",
		load: function(data,args) { },
		error: function() { }
	  });	
  recalculateColumnSelectorName();
}

function changeListColumnWidth(tableId,fieldId,width) {
  if (width<1) {
    width=1;
    dijit.byId('checkListColumnSelectorWidthId'+fieldId).set('value',width);
  } else if (width>50) {
    width=50;
    dijit.byId('checkListColumnSelectorWidthId'+fieldId).set('value',width);
  }
  dojo.xhrGet({
    url: '../tool/saveSelectedColumn.php?action=width&item='+tableId+'&width='+width,
    handleAs: "text",
    load: function(data,args) { },
    error: function() { }
    });
  recalculateColumnSelectorName();
}

function validateListColumn() {
	showWait();
	dijit.byId('listColumnSelector').closeDropDown();
	loadContent("objectList.php?objectClass="+dojo.byId('objectClass').value
    		+"&objectId="+dojo.byId('objectId').value,"listDiv");
}

function resetListColumn() {
	var actionOK=function() {
	  showWait();
	  dijit.byId('listColumnSelector').closeDropDown();
	  dojo.xhrGet({
		url: '../tool/saveSelectedColumn.php?action=reset&objectClass='+dojo.byId('objectClass').value,
		handleAs: "text",
		load: function(data,args) { 
			loadContent("objectList.php?objectClass="+dojo.byId('objectClass').value
		    		+"&objectId="+dojo.byId('objectId').value,"listDiv");
		},
		error: function() {	
		}
	  });	
	};
	showConfirm (i18n('confirmResetList'), actionOK);
}

function moveListColumn(source,destination) {
  var mode='';
  var list='';
  var nodeList=dndListColumnSelector.getAllNodes();
  listColumnOrder=new Array();
  for (i=0; i<nodeList.length; i++) {
	var itemSelected=nodeList[i].id.substr(20);
	//check=(dijit.byId('checkListColumnSelector'+itemSelected).get('checked'))?'':'hidden';
    list+=itemSelected+"|";
    //listColumnOrder[i]=check+itemSelected;
  }
  //dijit.byId('listColumnSelector').closeDropDown();
  var url='../tool/moveListColumn.php?orderedList='+list;
  dojo.xhrPost({
	url: url,
	handleAs: "text",
	load: function(data,args) {  
    }
  });  
  //loadContent(url, "planResultDiv");
  //setGanttVisibility(g);
  //JSGantt.changeFormat(g.getFormat(),g);
  //hideWait();
}

function recalculateColumnSelectorName() {
  cpt=0;
  tot=0;
  while (cpt<999) {
    var itemSelected=dijit.byId('checkListColumnSelectorWidthId'+cpt);
    if (itemSelected) {
      if (! itemSelected.get('disabled')) {
        tot+=itemSelected.get('value');
      }
    } else {
      cpt=999;
    }
    cpt++;
  }
  name="checkListColumnSelectorWidthId"+dojo.byId('columnSelectorNameFieldId').value;
  nameWidth=100-tot;
  color="";
  if (nameWidth<10) {
    nameWidth=10;
    color="#FFAAAA";
  }
  dijit.byId(name).set('value',nameWidth);
  totWidth=tot+nameWidth;
  totWidthDisplay="";
  if (color) {
    totWidthDisplay='<div style="background-color:'+color+'">'+totWidth+'&nbsp;%</div>';
  }
  dojo.byId('columnSelectorTotWidthTop').innerHTML=totWidthDisplay;
  dojo.byId('columnSelectorTotWidthBottom').innerHTML=totWidthDisplay;
  dojo.xhrGet({
    url: '../tool/saveSelectedColumn.php?action=width&item='
       +dojo.byId('columnSelectorNameTableId').value+'&width='+nameWidth,
    handleAs: "text",
    load: function(data,args) { },
    error: function() { }
    });
}

// =========================================================
// Other
// =========================================================
function showMailOptions() {
	dojo.byId('mailRefType').value=dojo.byId('objectClass').value;
	dojo.byId('mailRefId').value=dojo.byId('objectId').value;
	title=i18n('buttonMail', new Array(i18n(dojo.byId('objectClass').value)));
	if (dijit.byId('attendees')) {
		dijit.byId('dialogMailToOther').set('checked','checked');
		dijit.byId('dialogOtherMail').set('value',extractEmails(dijit.byId('attendees').get('value')));
		dialogMailToOtherChange();
	}
	if (dojo.byId('objectClass').value=='Activity') {
	  enableWidget('dialogMailToAssigned');
	} else {
	  disableWidget('dialogMailToAssigned');
	  dijit.byId('dialogMailToAssigned').set('checked','');
	}
	dijit.byId("dialogMail").set('title',title);
	dijit.byId("dialogMail").show();
	
}

function dialogMailToOtherChange() {
  var show=dijit.byId('dialogMailToOther').get('checked');
  if (show) {
	  showField('dialogOtherMail');
  } else {
	  hideField('dialogOtherMail');
  }
}

function extractEmails(str) {
  var current='';
  var result='';
  var name=false;
  for (i=0; i<str.length; i++) {
    car=str.charAt(i);
    if (car=='"') {
      if (name==true) {
        name=false;
        current="";
      } else {
        if (current!='') {
          if ($result!='') {
            result+=', ';
          }
          result+=trimTag(current);
          current='';
        }
        name=true;
      }
    } else if (name==false) {		  
      if (car==',' || car==';' || car==' ') {
        if (current!='') {
          if (result!='') {
        	result+=', ';
          }
          result+=trimTag(current);
          current='';
        }
      } else {
        current+=car;
	  }
	}
  }
  if (current!="") {
	if (result!='') {
      result+=', ';
    }
    result+=trimTag(current);
  }
  return result;
}

function sendMail() {
  loadContent("../tool/sendMail.php?className=Mailable", "resultDiv", "mailForm", true, 'mail');
  dijit.byId("dialogMail").hide();
}

function lockRequirement() {
  if (checkFormChangeInProgress()) {
	return false;
  }
  dijit.byId('locked').set('checked',true);
  dijit.byId('idLocker').set('value',dojo.byId('idCurrentUser').value);
  var curDate = new Date();
  dijit.byId('lockedDate').set('value',curDate);
  dijit.byId('lockedDateBis').set('value',curDate);
  formChanged();
  submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
  return true;
}

function unlockRequirement() {
  if (checkFormChangeInProgress()) {
	return false;
  }
  dijit.byId('locked').set('checked',false);
  dijit.byId('idLocker').set('value',null);
  dijit.byId('lockedDate').set('value',null);
  dijit.byId('lockedDateBis').set('value',null);
  formChanged();
  submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
  return true;
}

function loadDialog(dialogDiv,callBack, autoShow, params) {
  if (! dijit.byId(dialogDiv) ) {
	  dialog = new dijit.Dialog({
	  id: dialogDiv,
      title: i18n(dialogDiv),
      width: '500px',
	  content: i18n("loading")
    });
  } else {
	dialog=dijit.byId(dialogDiv);
  }
  if (!params) {params=""};
  showWait();
  dojo.xhrGet({
	url: '../tool/dynamicDialog.php?dialog='+dialogDiv+'&isIE='+((dojo.isIE)?dojo.isIE:'')+params,
	handleAs: "text",
	load: function (data) {
	  var contentWidget = dijit.byId(dialogDiv);
	  if (! contentWidget) {return;}
	  contentWidget.set('content',data);
	  if (autoShow) { setTimeout("dijit.byId('"+dialogDiv+"').show();",100);}
	  hideWait();
	  if (callBack) {
	    setTimeout(callBack,10);
	  }
	},
    error: function () {
		//console.log to keep
		console.log("error loading dialog "+dialogDiv);
		hideWait();
	}	
  });
}
/* ========================================================================
 * Today management
 * ========================================================================
 */
function saveTodayParameters() {
  loadContent('../tool/saveTodayParameters.php','centerDiv','todayParametersForm');
  dijit.byId('dialogTodayParameters').hide();
}

function setTodayParameterDeleted(id) {
  dojo.byId('dialogTodayParametersDelete'+id).value=1;
  dojo.byId('dialogTodayParametersRow'+id).style.display='none';
}

function loadReport(url,dialogDiv) {
  var contentWidget = dijit.byId(dialogDiv);
  contentWidget.set('content','<img src="../view/css/images/treeExpand_loading.gif" />');
  dojo.xhrGet({
	url: url,
	handleAs: "text",
	load: function (data) {
	  var contentWidget = dijit.byId(dialogDiv);
	  if (! contentWidget) {return;}
	  contentWidget.set('content',data);
	},
    error: function () {
		//console.log to keep
		console.log("error loading report "+url+" into "+dialogDiv);
	}	
  });
}

function reorderTodayItems() { 
  var nodeList=dndTodayParameters.getAllNodes();
  for (i=0; i<nodeList.length; i++) {
	item=nodeList[i].id.substr(24);
	var order=dojo.byId("dialogTodayParametersOrder"+item);
	if (order) {
	  order.value=i+1;
	}
  }
}

var multiSelection=false;
var switchedModeBeforeMultiSelection=false;
function startMultipleUpdateMode(objectClass) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	grid = dijit.byId("objectGrid"); // if the element is not a widget, exit.
	if ( ! grid) { 
	  return;
	}
	multiSelection=true;
	//dojo.xhrPost({url: "../tool/saveDataToSession.php?id=multipleMode&value=true"});
	formChangeInProgress=true;
	switchedModeBeforeMultiSelection=switchedMode;
	if (switchedModeBeforeMultiSelection) {
	  switchMode();
	}
	unselectAllRows("objectGrid");
	dijit.byId('objectGrid').selection.setMode('multiple');	
	loadContent('../view/objectMultipleUpdate.php?objectClass='+objectClass,'detailDiv')
}  

function saveMultipleUpdateMode(objectClass) {
	//submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
  grid = dijit.byId("objectGrid"); // if the element is not a widget, exit.
  if ( ! grid) { 
    return;
  }
  dojo.byId("selection").value=""
  var items=grid.selection.getSelected();
  if (items.length) {
    dojo.forEach(items, function(selectedItem) {
      if (selectedItem !== null) {
        dojo.byId("selection").value+=parseInt(selectedItem.id)+";";
      }
    });
  }
  loadContent('../tool/saveObjectMultiple.php?objectClass='+objectClass,'resultDivMultiple','objectFormMultiple');
}  

function endMultipleUpdateMode(objectClass) {
	if (dijit.byId('objectGrid')) {
	  dijit.byId('objectGrid').selection.setMode('single');
	  unselectAllRows("objectGrid");
	}
	multiSelection=false;
	//dojo.xhrPost({url: "../tool/saveDataToSession.php?id=multipleMode&value=false"});
	formChangeInProgress=false;
	if (switchedModeBeforeMultiSelection) {
	  switchMode();
    }
	if (objectClass) {
	  loadContent('../view/objectDetail.php?noselect=true&objectClass='+objectClass,'detailDiv');
	}
}  

function deleteMultipleUpdateMode(objectClass) {
	showError("delete is no designed yet");
}
function updateSelectedCountMultiple() {
  if (dijit.byId('selectedCount')) {
    dijit.byId('selectedCount').set('value',countSelectedItem('objectGrid'));
  }
}

function showImage(objectClass, objectId, imageName) {
  imageUrl="../tool/download.php?class="+objectClass+"&id="+objectId; 
  var dialogShowImage = dijit.byId("dialogShowImage");
  if (! dialogShowImage) {
	dialogShowImage = new dojox.image.LightboxDialog({});
	dialogShowImage.startup();
  }
  if(dialogShowImage && dialogShowImage.show){
	  if (dojo.isFF) {
		  dojo.xhrGet({
				url: imageUrl,
				handleAs: "text",
				load: function (data) {
			        dialogShowImage.show({ title:imageName, href:imageUrl });
			  		dijit.byId('formDiv').resize();
				}
			});
	  } else {
        dialogShowImage.show({ title:imageName, href:imageUrl });
  		dijit.byId('formDiv').resize();
	  }
	//dialogShowImage.show({ title:imageName, href:imageUrl });
  }	else {
	showError ("Error loading image "+imageName);
  }
  //dijit.byId('formDiv').resize();
}


//*******************************************************
// Dojo code to position into a tree
//*******************************************************
function recursiveHunt(lookfor, model, buildme, item){
    var id = model.getIdentity(item);
    buildme.push(id);
    if(id == lookfor){
        return buildme;
    }
    for(var idx in item.children){
        var buildmebranch = buildme.slice(0);
        var r = recursiveHunt(lookfor, model, buildmebranch, item.children[idx]);
        if(r){ return r; }
    }
    return undefined;
}

function selectTreeNodeById(tree, lookfor){
    var buildme = [];
    var result = recursiveHunt(lookfor, tree.model, buildme, tree.model.root);
    if(result && result.length > 0){
        tree.set('path', result);
    }
}


// ************************************************************
// Code to select columns to be exported
// ************************************************************
var ExportType ='';
//open the dialog with checkboxes
function openExportDialog (Type) {
  ExportType=Type;
  if (checkFormChangeInProgress()) {
          showAlert(i18n('alertOngoingChange'));
          return;
  }
  loadDialog("dialogExport",null, true,'&objectClass='+dojo.byId('objectClass').value);  
}

//close the dialog with checkboxes 
function closeExportDialog () {
  dijit.byId("dialogExport").hide(); 
}


//save current state of checkboxes
function saveCheckboxExport(obj,idUser){
  var val = dojo.byId('column0').value;
  var toStore="";
  val = eval(val);
  for(i=1; i<=val;i++){
    var checkbox=dijit.byId('column'+i);
    if(checkbox) {
      if(! checkbox.get('checked')) {
        var field=checkbox.value;
        toStore+=field+";";
      }
    }
  }
  dojo.xhrPost({
	url: "../tool/saveCheckboxes.php?&objectClass="+obj+"&toStore="+toStore,
	handleAs: "text",
	load: function () {}
  });
}

//Executes the report (shows the print/pdf/csv)
function executeExport(obj,idUser) {  
  var verif=0;
  var val = dojo.byId('column0').value;
  val = eval(val);
  var toExport = "";
  for(i=1; i<=val;i++){
    var checkbox=dijit.byId('column'+i);
    if(checkbox) {
      if (checkbox.get('checked')) {
        verif=1;
      } else {
    	var field=checkbox.value;
        toExport+=field+";";
      }
    }
  }
  if(verif==1) {
    if(ExportType=='csv') {
      showPrint("../tool/jsonQuery.php?hiddenFields="+toExport, 'list', null, 'csv');  
    }
    saveCheckboxExport(obj,idUser);
    closeExportDialog(obj,idUser);
  } else {
    showAlert(i18n('alertChooseOneAtLeast'));
  }
}

//Check or uncheck all boxes
function checkExportColumns(scope) {
  if (scope=='aslist') {
	showWait();  
	dojo.xhrGet({
	  url: "../tool/getColumnsList.php?objectClass="+dojo.byId('objectClass').value,
	  load: function(data) {
		var list=";"+data;
		var val = dojo.byId('column0').value;
		val = eval(val);
		var allChecked=true;
		for(i=1; i<=val;i++){
		   var checkbox=dijit.byId('column'+i);
           if (checkbox) {
		     var search=";"+checkbox.value+";";
		     if(list.indexOf(search)>=0) {
		       checkbox.set('checked',true);
		     } else {
			   checkbox.set('checked',false);  
			   allChecked=false;
		     }
           }
		}	 
		dijit.byId('checkUncheck').set('checked',allChecked);
		hideWait();
	  },
	  error: function() {hideWait();}
	});
  } else {
	  var check = dijit.byId('checkUncheck').get('checked');
	  var val = dojo.byId('column0').value;
	  val = eval(val);
	  for(i=1; i<=val;i++){
	     var checkbox=dijit.byId('column'+i);
	      if(checkbox) {
	    	  checkbox.set('checked',check);
	      }
	  }
  }
}

// ==================================================================
// Project Selector Functions
// ==================================================================
function changeProjectSelectorType(displayMode) {
  dojo.xhrPost({
    url: "../tool/saveDataToSession.php?id=projectSelectorDisplayMode&value="+displayMode,
    load: function() {loadContent("../view/menuProjectSelector.php", 'projectSelectorDiv');}
  });
  if (dijit.byId('dialogProjectSelectorParameters')) {
    dijit.byId('dialogProjectSelectorParameters').hide();
  }
}

function refreshProjectSelectorList() {   
  dojo.xhrPost({
    url: "../tool/refreshVisibleProjectsList.php",
    load: function() {loadContent('../view/menuProjectSelector.php', 'projectSelectorDiv');}
  });
  if (dijit.byId('dialogProjectSelectorParameters')) {
    dijit.byId('dialogProjectSelectorParameters').hide();
  }
}