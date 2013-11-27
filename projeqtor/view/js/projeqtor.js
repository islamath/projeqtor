// ============================================================================
// All specific ProjeQtOr functions and variables
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

//=============================================================================
//= Variables (global)
//=============================================================================

var i18nMessages=null;                 // array containing i18n messages
var currentLocale=null;                // the locale, from browser or user set
var browserLocale=null;                // the locale, from browser
var cancelRecursiveChange_OnGoingChange = false; // boolean to avoid
                          // recursive change trigger
var formChangeInProgress = false;      // boolean to avoid exit from form when
                    // changes are not saved
var currentRow = null;                 // the row num of the current selected
                    // element in the main grid
var currentFieldId = '';               // Id of the ciurrent form field (got
                    // via onFocus)
var currentFieldValue = '';            // Value of the current form field (got
                    // via onFocus)
var g;                                 // Gant chart for JsGantt : must be
                    // named "g"
var quitConfirmed=false;
var noDisconnect=false;
var forceRefreshMenu=false;
var directAccessIndex=null;

// =============================================================================
// = Functions
// =============================================================================

/**
 * ============================================================================
 * Refresh the ItemFileReadStore storing Data for the main grid
 * 
 * @param className
 *            the class of objects in the list
 * @param idle
 *            the idle filter parameter
 * @return void
 */
function refreshJsonList(className, keepUrl) {
  var grid = dijit.byId("objectGrid");
  if (grid) {
	var sortIndex=grid.getSortIndex();
  	var sortAsc=grid.getSortAsc();
  	var scrollTop=grid.scrollTop;
    // store = grid.store;
    // store.close();
    unselectAllRows("objectGrid");
    url="../tool/jsonQuery.php?objectClass=" + className;
    if ( dojo.byId('comboDetail') ) {
      url = url + "&comboDetail=true";
      if (dojo.byId('comboDetailId') ) {
        dojo.byId('comboDetailId').value='';
      }
    }
    if ( dojo.byId('listShowIdle') ) {
      if (dojo.byId('listShowIdle').checked) { url = url + "&idle=true"; }
    }
    if ( dijit.byId('listTypeFilter') ) {
      if (dijit.byId('listTypeFilter').get("value")!='') {
        url = url + "&objectType=" + dijit.byId('listTypeFilter').get("value"); 
        //url = url + "&objectType=" + encodeURIComponent(dijit.byId('listTypeFilter').get("value"));
      }
    }
    if ( dijit.byId('quickSearchValue') ) {
      if (dijit.byId('quickSearchValue').get("value")!='') {
        //url = url + "&quickSearch=" + dijit.byId('quickSearchValue').get("value"); 
        url = url + "&quickSearch=" + encodeURIComponent(dijit.byId('quickSearchValue').get("value"));
      }
    }

    // store.fetch();
    if (! keepUrl) {
	    grid.setStore(new dojo.data.ItemFileReadStore({
	      url: url, 
	      clearOnClose: 'true'
	    }));
    }
    store = grid.store;
    store.close();
    store.fetch({onComplete: function(){
    	grid._refresh();
    	var objectId=dojo.byId('objectId');
    	setTimeout('dijit.byId("objectGrid").setSortIndex('+sortIndex+','+sortAsc+');',10);
        setTimeout('dijit.byId("objectGrid").scrollTo('+scrollTop+');',20);
        setTimeout('selectRowById("objectGrid", '+parseInt(objectId.value)+');',30);
        filterJsonList();
      }
    });
  }
}

/**
 * ============================================================================
 * Refresh the ItemFileReadStore storing Data for the planning (gantt)
 * 
 * @return void
 */
function refreshJsonPlanning() {
  if (dojo.byId("resourcePlanning")) {
	url="../tool/jsonResourcePlanning.php";  
  } else {
	url="../tool/jsonPlanning.php";
  }
  param=false;
  if ( dojo.byId('listShowIdle') ) {
    if (dojo.byId('listShowIdle').checked) { 
      url += (param)?"&":"?";
      url += "idle=true";
      param=true;
    }
  }
  if ( dojo.byId('showWBS') ) {
    if (dojo.byId('showWBS').checked) { 
      url += (param)?"&":"?";
      url += "showWBS=true";
      param=true;
    }
  }
  if ( dojo.byId('listShowResource') ) {
    if (dojo.byId('listShowResource').checked) { 
      url += (param)?"&":"?";
      url += "showResource=true";
      param=true;
    }
  }
  if ( dojo.byId('listShowLeftWork') ) {
    if (dojo.byId('listShowLeftWork').checked) { 
      url += (param)?"&":"?";
      url += "showWork=true";
      param=true;
    }
  }
  if ( dojo.byId('listShowProject') ) {
    if (dojo.byId('listShowProject').checked) { 
      url += (param)?"&":"?";
      url += "showProject=true";
      param=true;
    }
  }
  if ( dijit.byId('listShowMilestone') ) {
    url += (param)?"&":"?";
    url += "showMilestone="+dijit.byId('listShowMilestone').get("value");
    param=true;
  }
  loadContent(url, "planningJsonData",'listForm',false);
}

/**
 * ============================================================================
 * Filter the Data of the main grid on Id and/or Name
 * 
 * @return void
 */
function filterJsonList() {
  var filterId=dojo.byId('listIdFilter');
  var filterName=dojo.byId('listNameFilter');
  var grid = dijit.byId("objectGrid");
  if (grid && (filterId || filterName)) {
    filter = {};
    unselectAllRows("objectGrid");
    filter.id='*'; // delfault
    if (filterId) {
      if (filterId.value && filterId.value!='') {
        filter.id = '*' + filterId.value + '*';
      }
    }
    if (filterName) {
      if (filterName.value && filterName.value!='') {
        filter.name = '*' + filterName.value + '*';
      }
    }
    grid.query=filter;
    grid._refresh();
  }
  refreshGridCount();
}

function refreshGrid() {
  if (dijit.byId("objectGrid")) {
    showWait();refreshJsonList(dojo.byId('objectClass').value, true);
  } else {
	showWait();refreshJsonPlanning();  
  }
}
/**
 * Refresh de display of number of items in the grid
 * 
 * @param repeat
 *            internal use only
 */
avoidRecursiveRefresh=false;
function refreshGridCount(repeat) {
  var grid = dijit.byId("objectGrid");
  if (grid.rowCount==0 && ! repeat) {
    // dojo.byId('gridRowCount').innerHTML="?";
    setTimeout("refreshGridCount(1);",100);
  } else {
  dojo.byId('gridRowCount').innerHTML=grid.rowCount;
  dojo.byId('gridRowCountShadow1').innerHTML=grid.rowCount;
  dojo.byId('gridRowCountShadow2').innerHTML=grid.rowCount;
  }
  /*objClass=dojo.byId("objectClass").value;
  if (avoidRecursiveRefresh==false && (objClass=='Resource' || objClass=='User' || objClass=='Contact') ) {
	// If list may contain image, refresh once to fix issue : list not complete on Chrome
	avoidRecursiveRefresh=true;
    setTimeout('dijit.byId("objectGrid")._refresh();',100);
  } else {
	avoidRecursiveRefresh=false;
  }*/
}

/**
 * ============================================================================
 * Return the current time, correctly formated as HH:MM
 * 
 * @return the current time correctly formated
 */
function getTime() {
  var currentTime = new Date();
  var hours = currentTime.getHours();
  var minutes = currentTime.getMinutes();
  if (minutes < 10){
    minutes = "0" + minutes;
  }
  return hours + ":" + minutes;
}

/**
 * ============================================================================
 * Add a new message in the message Div, on top of messages (last being on top)
 * 
 * @param msg
 *            the message to add
 * @return void
 */
function addMessage(msg) {
  var msgDiv = dojo.byId("messageDiv");
  if (msgDiv) {
    msgDiv.innerHTML= "[" + getTime() + "] " + msg + "<br/>" + msgDiv.innerHTML;
  }
}

/**
 * ============================================================================
 * Change display theme to a new one. Themes must be defined is projeqtor.css.
 * The change is also stored in Session.
 * 
 * @param newTheme
 *            the new theme
 * @return void
 */
function changeTheme(newTheme) {
  if (newTheme!="") {
    dojo.byId('body').className='tundra '+newTheme;
    dojo.xhrPost({
      url: "../tool/saveDataToSession.php?id=theme&value=" + newTheme,
      handleAs: "text"
      // , load: function(data,args) { addMessage("Theme=" + newTheme ); }
    });
  }
}

function saveUserParameter(parameter, value) {
  dojo.xhrPost({
    url: "../tool/saveUserParameter.php?parameter="+parameter+"&value=" + value,
    handleAs: "text",
    load: function(data,args) {}
  });	 
}
/**
 * ============================================================================
 * Save the browser locale to session. Needed for number formating under PHP 5.2
 * compatibility
 * 
 * @param none
 * @return void
 */
function saveBrowserLocaleToSession() {
  browserLocale=dojo.locale;
  dojo.xhrPost({
    url: "../tool/saveDataToSession.php?id=browserLocale&value=" + browserLocale,
    handleAs: "text",
    load: function(data,args) { }
  });
  var date = new Date(2000, 11, 31, 0, 0, 0, 0);
  var formatted=dojo.date.locale.format(date, {formatLength: "short", selector: "date"});
  /*var format="YYYYMMDD";
  if (formatted.substr(0,2)=='31') {
    format='DDMMYYYY';  
  } else if (formatted.substr(0,2)=='12') {
	format='MMDDYYYY';
  }*/
  var reg=new RegExp("(2000)", "g");
  format=formatted.replace(reg,'YYYY');
  reg=new RegExp("(00)", "g");
  format=format.replace(reg,'YYYY');
  reg=new RegExp("(12)", "g");
  format=format.replace(reg,'MM');
  reg=new RegExp("(31)", "g");
  format=format.replace(reg,'DD');
  dojo.xhrPost({
    url: "../tool/saveDataToSession.php?id=browserLocaleDateFormat&value=" + format,
    handleAs: "text",
    load: function(data,args) { }
  });
  var fmt=""+dojo.number.format(1.1)+" ";
  var decPoint=fmt.substr(1,1);
  dojo.xhrPost({
	url: "../tool/saveDataToSession.php?id=browserLocaleDecimalPoint&value=" + decPoint,
	handleAs: "text",
	load: function(data,args) { }
  });
  var fmt=dojo.number.format(100000)+' ';
  var thousandSep=fmt.substr(3,1);
  if (thousandSep=='0') {
	  thousandSep='';
  }
  dojo.xhrPost({
	url: "../tool/saveDataToSession.php?id=browserLocaleThousandSeparator&value=" + thousandSep,
	handleAs: "text",
	load: function(data,args) { }
  });
  
}

/**
 * ============================================================================
 * Change the current locale. Has an impact on i18n function. The change is also
 * stored in Session.
 * 
 * @param locale
 *            the new locale (en, fr, ...)
 * @return void
 */
function changeLocale(locale) {
  if (locale!="") {
    currentLocale=locale;
    dojo.xhrPost({
      url: "../tool/saveDataToSession.php?id=currentLocale&value=" + locale,
      handleAs: "text",
      load: function(data,args) {
        // action = function() {
    	  showWait();
          noDisconnect=true;
          quitConfirmed=true;
          window.location=("../view/main.php?directAccessPage=parameter.php&menuActualStatus=" + menuActualStatus + "&p1name=type&p1value=userParameter"); 
        // };
        // showConfirm (i18n('confirmLocaleChange'), action);
        // showInfo(i18n('infoLocaleChange'));
        },
      error: function(error,args){}
    });
  }
}

/**
 * ============================================================================
 * Change display theme to a new one. Themes must be defined is projeqtor.css.
 * The change is also stored in Session.
 * 
 * @param newTheme
 *            the new theme
 * @return void
 */
function saveResolutionToSession() {
  var height=screen.height;
  var width=screen.width;
  dojo.xhrPost({
	    url: "../tool/saveDataToSession.php?id=screenWidth&value=" + width,
	    handleAs: "text",
	    load: function(data,args) {}
	  });
  dojo.xhrPost({
    url: "../tool/saveDataToSession.php?id=screenHeight&value=" + height,
    handleAs: "text",
    load: function(data,args) { }
  });
}

/**
 * ============================================================================
 * Check if the recived key is able to change content of field or not
 * 
 * @param keyCode
 *            the code of the key
 * @return boolean : true if able to change field, else false
 */
function isUpdatableKey(keyCode) {
  if (keyCode==9                      // tab
   || (keyCode>=16 && keyCode<=20)    // shift, ctrl, alt, pause, caps lock
   || (keyCode>=33 && keyCode<=40)    // Home, end, page up, page down, arrows
                    // (left, right, up, down)
   || (keyCode==67)                   // ctrl+C
   || keyCode==91                     // Windows
// || (keyCode>=112 && keyCode<=123) // Function keys
   || keyCode==144                     // numlock
   || keyCode==145                     // stop
   || keyCode>=166                    // Media keys
   ) {
    return false;
  } 
  return true;                        // others
}

/**
 * ============================================================================
 * Clean the content of a Div. To be sure all widgets are cleaned before setting
 * new data in the Div. If fadeLoading is true, the Div fades away before been
 * cleaned. (fadeLoadsing is a global var definied in main.php)
 * 
 * @param destination
 *            the name of the Div to clean
 * @return void
 */
function cleanContent(destination) {
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  if ( ! (contentNode && contentWidget) ) {
    return;
  }
  if (contentWidget) {
    contentWidget.set('content',null);
  }
  return;

}

/**
 * ============================================================================
 * Load the content of a Div with a new page. If fadeLoading is true, the Div
 * fades away before, and fades back in after. (fadeLoadsing is a global var
 * definied in main.php)
 * 
 * @param page
 *            the url of the page to fetch
 * @param destination
 *            the name of the Div to load into
 * @param formName
 *            the name of the form containing data to send to the page
 * @param isResultMessage
 *            boolean to specify that the destination must show the result of
 *            some treatment, calling finalizeMessageDisplay
 * @return void
 */
function loadContent(page, destination, formName, isResultMessage, validationType, directAccess) {
//console.log to keep
//console.log("loadcontent("+page+", "+destination+", "+formName+", "+isResultMessage+", "+validationType+", "+directAccess+")");
  // Test validity of destination : must be a node and a widget
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  if ( ! (contentNode && contentWidget) ) {
    console.warn(i18n("errorLoadContent", new Array(page, destination, formName, isResultMessage,destination)));
    return;
  }
  if (contentNode) {
    destinationWidth=dojo.style(contentNode, "width");
    if (destination=='detailFormDiv') {
      widthNode=dojo.byId('detailDiv');
      if (widthNode) {
        destinationWidth=dojo.style(widthNode, "width");
      }
    }
    if ( page.indexOf("?")>0) {
      page+="&destinationWidth="+destinationWidth;
    } else {
      page+="?destinationWidth="+destinationWidth;
    }
  }
  if (directAccessIndex) {
    if ( page.indexOf("?")>0) {
	   page+="&directAccessIndex="+directAccessIndex;  
    } else {
	   page+="?directAccessIndex="+directAccessIndex;
    }	   
  } 
  showWait();
  // Direct mode, without fading effect =====
  // IE Issue : must not fade load
  if ( (top.dojo.isIE < 8) || ! top.fadeLoading) {
    // send Ajax request
    dojo.xhrPost({
      url: page,
      form: dojo.byId(formName),
      handleAs: "text",
      load: function(data,args){
        // update the destination when ajax request is received
        // cleanContent(destination);
        var contentWidget = dijit.byId(destination);
        if (! contentWidget) {return;}
        contentWidget.set('content',data);
        checkDestination(destination);
        if (destination=="detailDiv" || destination=="centerDiv") {
          finaliseButtonDisplay();
        }
        if (destination=="centerDiv") {
          showList();
        }
        if (destination=="dialogLinkList") {
      	  selectLinkItem();
        }
        if (destination=="directFilterList") {
        	if (validationType!='returnFromFilter') {
        	  if (dojo.byId('noFilterSelected') && dojo.byId('noFilterSelected').value=='true') {
	              dijit.byId("listFilterFilter").set("iconClass","iconFilter16");	
	        	} else {
	        	  dijit.byId("listFilterFilter").set("iconClass","iconActiveFilter16");
	            }
	        	refreshJsonList(dojo.byId('objectClass').value);
        	  }
        	}
        if (destination=="expenseDetailDiv") {
          expenseDetailRecalculate();
        }
        if (directAccess) {
          if (dijit.byId('listIdFilter')) {
            // dijit.byId('listIdFilter').set('value',directAccess);
            // setTimeout("filterJsonList();",100);
            dojo.byId('objectId').value=directAccess;
            // dijit.byId("listDiv").resize({h: 0});
            // dijit.byId("mainDivContainer").resize();
            loadContent("objectDetail.php", "detailDiv", 'listForm');
            showWait();
            hideList();
          }
        }
        if (isResultMessage) {
          var contentNode = dojo.byId(destination);
          // Set the Div visible, needed if destination is result message
      // (invisible before needed)
          dojo.fadeIn({
            node: contentNode, 
            duration: 1,
            onEnd: function() {
              if (isResultMessage) {
                // finalize message is return from treatment
                finalizeMessageDisplay(destination,validationType);
              }
            }
            }).play();
        } else if (destination=="loginResultDiv") {
          checkLogin();
        } else if (destination=="passwordResultDiv") {
          checkLogin();
        } else if (page.indexOf("planningMain.php")>=0 || page.indexOf("planningList.php")>=0
             || page.indexOf("jsonPlanning.php")>=0
             || page.indexOf("resourcePlanningMain.php")>=0 || page.indexOf("resourcePlanningList.php")>=0
             || page.indexOf("jsonResourcePlanning.php")>=0
             || page.indexOf("portfolioPlanningMain.php")>=0 || page.indexOf("portfolioPlanningList.php")>=0
             || page.indexOf("jsonPortfolioPlanning.php")>=0) {                
          drawGantt();
          hideWait();
        } else if (destination=="resultDivMultiple") {
          finalizeMultipleSave();
        } else {
          hideWait();
        }
      },
      error: function(error,args){
        console.warn(i18n("errorXhrPost", new Array(page, destination, formName, isResultMessage, error)));
        hideWait();}
    });
    return;
  }
  // Smooth mode, with fading effect =====
  // fade out the destination, for smooth effect
  dojo.fadeOut({ 
    node: contentNode ,
    duration: 100, 
    onEnd: function() {
      // send Ajax request
    dojo.xhrPost({
        url: page,
        form: dojo.byId(formName),
        handleAs: "text",
        load: function(data,args){
          // update the destination when ajax request is received
          // cleanContent(destination);
          var contentWidget = dijit.byId(destination);
          if (! contentWidget) {return;};
          if (dijit.byId('planResultDiv')) {
        	  dijit.byId('planResultDiv').set('content',"");
          }
          contentWidget.set('content',data);
          checkDestination(destination);
          var contentNode = dojo.byId(destination);
          if (destination=="detailDiv" || destination=="centerDiv" ) {
            finaliseButtonDisplay();
          }
          if (destination=="centerDiv" && switchedMode) {
            showList();
          }
          if (destination=="dialogLinkList") {
        	  selectLinkItem();
          }
          if (destination=="directFilterList") {
            if (!validationType && validationType!='returnFromFilter') {    
  	        	if (top.dojo.byId('noFilterSelected') && top.dojo.byId('noFilterSelected').value=='true') {
  	              dijit.byId("listFilterFilter").set("iconClass","iconFilter16");	
  	        	} else {
  	        	  dijit.byId("listFilterFilter").set("iconClass","iconActiveFilter16");
  	            }
  	        	refreshJsonList(dojo.byId('objectClass').value);
        	  }
          }
          if (destination=="expenseDetailDiv") {
              expenseDetailRecalculate();
          }
          if (directAccess) {
            if (dijit.byId('listIdFilter')) {
              // dijit.byId('listIdFilter').set('value',directAccess);
              // setTimeout("filterJsonList();",100);
              dojo.byId('objectId').value=directAccess;
              // dijit.byId("listDiv").resize({h: 0});
              // dijit.byId("mainDivContainer").resize();
              showWait();
              loadContent("objectDetail.php", "detailDiv", 'listForm');
              showWait();
              hideList();
            }
          }
          // fade in the destination, to set is visible back
          dojo.fadeIn({
            node: contentNode, 
            duration: 200,
            onEnd: function() {
        	  if (isResultMessage) {
                // finalize message is return from treatment      		  
        		  finalizeMessageDisplay(destination, validationType);
              } else if (destination=="loginResultDiv") {
                checkLogin();
               } else if (destination=="passwordResultDiv") {
                checkLogin();
               } else if (page.indexOf("planningMain.php")>=0 || page.indexOf("planningList.php")>=0
                       || (page.indexOf("jsonPlanning.php")>=0 && dijit.byId("startDatePlanView"))
                       || page.indexOf("resourcePlanningMain.php")>=0 || page.indexOf("resourcePlanningList.php")>=0
                       || (page.indexOf("jsonResourcePlanning.php")>=0 && dijit.byId("startDatePlanView")) 
                       || page.indexOf("portfolioPlanningMain.php")>=0 || page.indexOf("portfolioPlanningList.php")>=0
                       || (page.indexOf("jsonPortfolioPlanning.php")>=0 && dijit.byId("startDatePlanView"))) {                
                 drawGantt();
                 hideWait();
               } else if (destination=="resultDivMultiple") {
                   finalizeMultipleSave();
               } else {
                hideWait();
              }
            }
          }).play();
        },
        error: function(error,args){
          console.warn(i18n("errorXhrPost", new Array(page, destination, formName, isResultMessage, error)));
        }
      },true);
    }
  }).play();
}

/**
 * ============================================================================
 * Check if destnation is correct If not in main page and detect we have login
 * page => wrong destination
 */
function checkDestination(destination){
  if (dojo.byId("isLoginPage") && destination!="loginResultDiv") {
    if (dojo.isFF) {
      quitConfirmed=true;
      noDisconnect=true;
      window.location="main.php?lostConnection=true";
    } else {
      hideWait();
      showAlert(i18n("errorConnection"));
    }
  }
  if (! dijit.byId('objectGrid') && dojo.byId('multiUpdateButtonDiv')) {
	  dojo.byId('multiUpdateButtonDiv').style.display='none';
  }		  
}
/**
 * ============================================================================
 * Chek the return code from login check, if valid, refresh page to continue
 * 
 * @return void
 */
function checkLogin() {
  resultNode=dojo.byId('validated');
  resultWidget=dojo.byId('validated');
  if (resultNode && resultWidget) {
	saveResolutionToSession();  
    // showWait();
    if (changePassword) {
      quitConfirmed=true;
      noDisconnect=true;
      window.location="main.php?changePassword=true";
    } else {
      quitConfirmed=true;
      noDisconnect=true;
      url="main.php";
      if (dojo.byId("objectClass") && dojo.byId("objectId")) {
    	  url+="?objectClass="+dojo.byId("objectClass").value+"&objectId="+dojo.byId("objectId").value;
      }
      window.location=url;
    }
  } else {
    hideWait();
  }
}

/**
 * ============================================================================
 * Submit a form, after validating the data
 * 
 * @param page
 *            the url of the page to fetch
 * @param destination
 *            the name of the Div to load into
 * @param formName
 *            the name of the form containing data to send to the page
 * @return void
 */
function submitForm(page, destination, formName) {
  var formVar = dijit.byId(formName);
  if ( ! formVar) {
    showError(i18n("errorSubmitForm", new Array(page, destination, formName)));
    return;
  }
  // validate form Data
  if(formVar.validate()){
    formLock();
    // form is valid, continue and submit it
    var isResultDiv=true;
    if (formName=='passwordForm') { isResultDiv=false; };
    loadContent(page,destination, formName, isResultDiv);
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

/**
 * ============================================================================
 * Finalize some operations after receiving validation message of treatment
 * 
 * @param destination
 *            the name of the Div receiving the validation message
 * @return void
 */
function finalizeMessageDisplay(destination, validationType) {
//console.log to keep
//console.log("finalizeMessageDisplay("+destination+", "+validationType+")");
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  var lastOperationStatus = dojo.byId('lastOperationStatus');
  var lastOperation = dojo.byId('lastOperation');
  // scpecific Plan return
  if (destination=="planResultDiv" && ! validationType) {
    lastOperationStatus = dojo.byId('lastPlanStatus');
    lastOperation = "plan";
  }
  var noHideWait=false;
  if ( ! (contentWidget && contentNode && lastOperationStatus && lastOperation) ) {
    returnMessage="";
    if (contentWidget) {
      returnMessage=contentWidget.get('content');
    }
    showError(i18n("errorFinalizeMessage", new Array(destination,returnMessage)));
    hideWait();
    return;    
  }
  if (! contentWidget) {return;};
  // fetch last message type
  var message=contentWidget.get('content');
  posdeb=message.indexOf('class="')+7;
  posfin=message.indexOf('>')-1;
  typeMsg=message.substr(posdeb, posfin-posdeb);
  // if operation is OK
  if (lastOperationStatus.value=="OK") {
    posdeb=posfin+2;
    posfin=message.indexOf('<',posdeb);
    msg=message.substr(posdeb, posfin-posdeb);
    // add the message in the message Div (left part) and prepares form to new
    // changes
    addMessage(msg);
    //alert('validationType='+validationType);
    if (validationType) {
      if (validationType=='note') {
        loadContent("objectDetail.php?refreshNotes=true", dojo.byId('objectClass').value+'_note', 'listForm');
      } else if (validationType=='attachement') {
    	if (dojo.byId('objectClass') 
    	  && (dojo.byId('objectClass').value=='Resource' || dojo.byId('objectClass').value=='User' || dojo.byId('objectClass').value=='Contact') ) {
    	  loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
    	  refreshGrid();
    	} else {
          loadContent("objectDetail.php?refreshAttachements=true", dojo.byId('objectClass').value+'_attachment', 'listForm');
    	}
    	dojo.style(dojo.byId('downloadProgress'), {display:'none'});
      } else if (validationType=='billLine') {
        loadContent("objectDetail.php?refreshBillLines=true", dojo.byId('objectClass').value+'_billLine', 'listForm');
        loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
      //} else if (validationType=='documentVersion') {
      //    loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
      } else if (validationType=='testCaseRun') {
    	loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
    	loadContent("objectDetail.php?refreshHistory=true", dojo.byId('objectClass').value+'_history', 'listForm');    	
        //loadContent("objectDetail.php?refreshTestCaseRun=true", dojo.byId('objectClass').value+'_TestCaseRun', 'listForm');
        //loadContent("objectDetail.php?refreshLinks=true", dojo.byId('objectClass').value+'_Link', 'listForm');
      } else if (validationType=='copyTo' || validationType=='copyProject') {
    	  if (validationType=='copyProject') {
    		dojo.byId('objectClass').value="Project";
    	  } else {
    		dojo.byId('objectClass').value=copyableArray[dijit.byId('copyToClass').get('value')];  
    	  }
    	  var lastSaveId=dojo.byId('lastSaveId');
          var lastSaveClass=dojo.byId('objectClass');
          if (lastSaveClass && lastSaveId) {
        	 waitingForReply=false;
             gotoElement(lastSaveClass.value, lastSaveId.value);
             waitingForReply=true;
          }
      } else if (validationType=='admin'){
    	  hideWait();
      } else if (validationType=='link' && 
    		  (dojo.byId('objectClass').value=='Requirement' || dojo.byId('objectClass').value=='TestSession')) {
    	  loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
    	  refreshGrid();
      } else  if (validationType=='report'){
    	  hideWait();  
      } else if (lastOperation!='plan') {
    	  if (dijit.byId('detailFormDiv')) { // only refresh is detail is show (possible when DndLing on planning
            loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
    	  }
          if (validationType=='assignment' || validationType=='documentVersion') {
        	refreshGrid();
          } else if (validationType=='dependency' && 
        		  (dojo.byId(destination)=="planResultDiv" || dojo.byId("GanttChartDIV")) ) {
              noHideWait=true;
              refreshJsonPlanning();  
          }
    	  //hideWait();
      }
    } else {
      formInitialize();
      // refresh the grid to reflect changes
      var lastSaveId=dojo.byId('lastSaveId');
      var objectId=dojo.byId('objectId');
      if (objectId && lastSaveId && destination!="planResultDiv") {
      	objectId.value=lastSaveId.value;
      }
      // Refresh the Grid list (if visible)
      var grid = dijit.byId("objectGrid");  
      if (grid) {
    	var sortIndex=grid.getSortIndex();
    	var sortAsc=grid.getSortAsc();
    	var scrollTop=grid.scrollTop;
        store = grid.store;
        store.close();
        store.fetch({onComplete: function(){
        	grid._refresh();
        	setTimeout('dijit.byId("objectGrid").setSortIndex('+sortIndex+','+sortAsc+');',10);
            setTimeout('dijit.byId("objectGrid").scrollTo('+scrollTop+');',20);
            setTimeout('selectRowById("objectGrid", '+parseInt(objectId.value)+');',30);
        	}
        });
      }
      // Refresh the planning Gantt (if visible)
      if (dojo.byId(destination)=="planResultDiv" || dojo.byId("GanttChartDIV") ) {
        noHideWait=true;
        refreshJsonPlanning();
        // loadContent("planningList.php", "listDiv", 'listForm');
        
      }
      // last operations depending on the executed operatoin (insert, delete,
    // ...)
      if (lastOperation.value=="insert") {
        dojo.byId('id').value=lastSaveId.value;
        // TODO : after insert select the current line in the grid
        // selectRowById("objectGrid", lastSaveId.value); // does not work
      // because grid is refreshing...
      }
      if (lastOperation.value=="copy") {
        // TODO : after copy select the current line in the grid
        // selectRowById("objectGrid", lastSaveId.value); // does not work
      // because grid is refreshing...
      }
      if (lastOperation.value=="delete") {
        zone=dijit.byId("formDiv");
        msg=dojo.byId("noDataMessage");
        if (zone && msg) {
          zone.set('content',msg.value);
        }
        // unselectAllRows("objectGrid");
        finaliseButtonDisplay();
      }
      if ( (grid || dojo.byId("GanttChartDIV")) && dojo.byId("detailFormDiv") && refreshUpdates=="YES" && lastOperation.value!="delete") {
        // loadContent("objectDetail.php?refresh=true", "formDiv",
      // 'listForm');
        if (lastOperation.value=="copy") {
          loadContent("objectDetail.php?", "detailDiv", 'listForm');
        } else {
          loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
          // Need also to refresh History
          if (dojo.byId(dojo.byId('objectClass').value+'_history')) {
            loadContent("objectDetail.php?refreshHistory=true", dojo.byId('objectClass').value+'_history', 'listForm');
          }
          if (dojo.byId(dojo.byId('objectClass').value+'_billLine')) {
              loadContent("objectDetail.php?refreshBillLines=true", dojo.byId('objectClass').value+'_billLine', 'listForm');
          }
          var refreshDetailElse=false;
          if (lastOperation.value=="insert") {
        	  refreshDetailElse=true;
          } else {
        	  if (dijit.byId('idle') && dojo.byId('attachementIdle')) {
        		  if (dijit.byId('idle').get("value")!=dojo.byId('attachementIdle').value) {
        			  refreshDetailElse=true;
        		  }
        	  }
        	  if (dijit.byId('idle') && dojo.byId('noteIdle')) {
        		  if (dijit.byId('idle').get("value")!=dojo.byId('noteIdle').value) {
        			  refreshDetailElse=true;
        		  }
        	  }
        	  if (dijit.byId('idle') && dojo.byId('billLineIdle')) {
        		  if (dijit.byId('idle').get("value")!=dojo.byId('billLineIdle').value) {
        			  refreshDetailElse=true;
        		  }
        	  }
          }
          if (refreshDetailElse && ! validationType) {
            if (dojo.byId(dojo.byId('objectClass').value+'_attachment')) {
              loadContent("objectDetail.php?refreshAttachements=true", dojo.byId('objectClass').value+'_attachment', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value+'_note')) {
              loadContent("objectDetail.php?refreshNotes=true", dojo.byId('objectClass').value+'_note', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value+'_billLine')) {
                loadContent("objectDetail.php?refreshBillLines=true", dojo.byId('objectClass').value+'_billLine', 'listForm');
            }
          }
        }
      } else {
        if ( !noHideWait ) {
          hideWait();
        }
      }
    }
    var classObj=dojo.byId('objectClass');
    if (classObj && classObj.value=='DocumentDirectory') {
    	dijit.byId("documentDirectoryTree").model.store.clearOnClose = true;
    	dijit.byId("documentDirectoryTree").model.store.close();
  	    // Completely delete every node from the dijit.Tree     
   	    dijit.byId("documentDirectoryTree")._itemNodesMap = {};
   	    dijit.byId("documentDirectoryTree").rootNode.state = "UNCHECKED";
   	    dijit.byId("documentDirectoryTree").model.root.children = null;
   	    // Destroy the widget
   	    dijit.byId("documentDirectoryTree").rootNode.destroyRecursive();
   	    // Recreate the model, (with the model again)
   	    dijit.byId("documentDirectoryTree").model.constructor(dijit.byId("documentDirectoryTree").model);
   	    // Rebuild the tree
   	    dijit.byId("documentDirectoryTree").postMixInProperties();
   	    dijit.byId("documentDirectoryTree")._load();
    }
    if (forceRefreshMenu) {
    	//loadContent("../view/menuTree.php", "mapDiv",null,false);
    	//loadContent("../view/menuBar.php", "toolBarDiv",null,false);
    	showWait();
    	noDisconnect=true;
        quitConfirmed=true;        
    	window.location="../view/main.php?directAccessPage=parameter.php&menuActualStatus=" + menuActualStatus + "&p1name=type&p1value="+forceRefreshMenu;
    	forceRefreshMenu="";
    }
  } else if (lastOperationStatus.value=="INVALID") {
    if (formChangeInProgress) {
      formInitialize();
      formChanged();
    } else {
      formInitialize();
    }
  } else {
    if (validationType!='note' && validationType!='attachement') {
      formInitialize();
    }
    hideWait();
  }
  // If operation is correct (not an error) slowly fade the result message
  if ((lastOperationStatus.value!="ERROR" && lastOperationStatus.value!="INVALID")) {
    dojo.fadeOut({node: contentNode, duration: 3000}).play();
  } else {
    if (lastOperationStatus.value=="ERROR") {
      showError(message);
    } else {
      showAlert(message);
      if (destination=="planResultDiv") {
    	  dojo.fadeOut({node: contentNode, duration: 1000}).play();
    	  setTimeout("dijit.byId('planResultDiv').set('content','');",1000);    	  
      }
    }
    hideWait();
  }
}

/**
 * ============================================================================
 * Operates locking, hide and show correct buttons after loadContent, when
 * destination is detailDiv
 * 
 * @return void
 */
function finaliseButtonDisplay() {
  id = dojo.byId("id");
  if ( id ) {
    if (id.value=="") {
      // id exists but is not set => new item, all buttons locked until first
    // change
      formLock();
      enableWidget('newButton');
      enableWidget('saveButton');
      disableWidget('undoButton');
      disableWidget('mailButton');
      if (dijit.byId("objectGrid")) {
        enableWidget('multiUpdateButton');
      } else {
    	disableWidget('multiUpdateButton');
      }
    }
  } else {
    // id does not exist => not selected, only new button possible
    formLock();
    enableWidget('newButton');
    if (dijit.byId("objectGrid")) {
      enableWidget('multiUpdateButton');
    } else {
	  disableWidget('multiUpdateButton');
    }
    // but show print buttons if not in objectDetail (buttonDiv exists)
    if (! dojo.byId("buttonDiv")) {
      enableWidget('printButton');
      enableWidget('printButtonPdf');
    }
  }
  buttonRightLock();
}
function finalizeMultipleSave() {
  //refreshGrid();
  var grid = dijit.byId("objectGrid");  
  if (grid) {
	//unselectAllRows("objectGrid");
    var sortIndex=grid.getSortIndex();
	var sortAsc=grid.getSortAsc();
	var scrollTop=grid.scrollTop;
	store = grid.store;
	store.close();
	store.fetch({
	  onComplete: function(items){
	    grid._refresh();
  	    setTimeout('dijit.byId("objectGrid").setSortIndex('+sortIndex+','+sortAsc+');',10);
        setTimeout('dijit.byId("objectGrid").scrollTo('+scrollTop+');',20);
        selection=';'+dojo.byId('selection').value;
        dojo.forEach(items, function (item, index) {
          if (selection.indexOf(";"+parseInt(item.id)+";")>=0) {
  		    grid.selection.setSelected(index,true);
          } else {
        	grid.selection.setSelected(index,false);  
          }
  	    }) 
  	  }
    });
  }
  if (dojo.byId('summaryResult')) {
    contentNode=dojo.byId('resultDiv');
	contentNode.innerHTML=dojo.byId('summaryResult').value;
	msg=dojo.byId('summaryResult').value;
	msg=msg.replace(" class='messageERROR' ","");
	msg=msg.replace(" class='messageOK' ","");
	msg=msg.replace(" class='messageWARNING' ","");
	msg=msg.replace("</div><div>",", ");
	msg=msg.replace("<div>","");
	msg=msg.replace("</div>","");
	addMessage(msg);
	dojo.fadeIn({
      node: contentNode, 
      duration: 10,
      onEnd: function() {
	    dojo.fadeOut({node: contentNode, duration: 3000}).play();
	  }
	}).play();
  }
  hideWait();  
}
/**
 * ============================================================================
 * Operates locking, hide and show correct buttons when a change is done on form
 * to be able to validate changes, and avoid actions that may lead to loose
 * change
 * 
 * @return void
 */
function formChanged() {
  var updateRight=dojo.byId('updateRight');
  if (updateRight && updateRight.value=='NO') {
    rerurn;
  }
  disableWidget('newButton');
  enableWidget('saveButton');
  disableWidget('printButton');
  disableWidget('printButtonPdf');
  disableWidget('copyButton');
  enableWidget('undoButton');
  disableWidget('deleteButton');
  disableWidget('refreshButton');
  disableWidget('mailButton');
  disableWidget('multiUpdateButton');
  formChangeInProgress=true;
  grid=dijit.byId("objectGrid");
  if (grid) {
    // TODO : lock grid selection
    // saveSelection=grid.selection;
    grid.selectionMode="none";
    
  }
  buttonRightLock();
}

/**
 * ============================================================================
 * Operates unlocking, hide and show correct buttons when a form is refreshed to
 * be able to operate actions only available on forms with no change ongoing,
 * and avoid actions that may lead to unconsistancy
 * 
 * @return void
 */
function formInitialize() {
  enableWidget('newButton');
  enableWidget('saveButton');
  enableWidget('printButton');
  enableWidget('printButtonPdf');
  enableWidget('copyButton');
  disableWidget('undoButton');
  enableWidget('deleteButton');
  enableWidget('refreshButton');
  enableWidget('mailButton');
  if (dijit.byId("objectGrid")) {
    enableWidget('multiUpdateButton');
  } else {
    disableWidget('multiUpdateButton');
  }
  formChangeInProgress=false;
  buttonRightLock();
}

/**
 * ============================================================================
 * Operates locking, to disable all actions during form submition
 * 
 * @return void
 */
function formLock() {
  disableWidget('newButton');
  disableWidget('saveButton');
  disableWidget('printButton');
  disableWidget('printButtonPdf');
  disableWidget('copyButton');
  disableWidget('undoButton');
  disableWidget('deleteButton');
  disableWidget('refreshButton');
  disableWidget('mailButton');
  disableWidget('multiUpdateButton');
}

/**
 * ============================================================================
 * Lock some buttons depending on access rights
 */
function buttonRightLock() {
  var createRight=dojo.byId('createRight');
  var updateRight=dojo.byId('updateRight');
  var deleteRight=dojo.byId('deleteRight');
  if (createRight) {
    if (createRight.value!='YES') {
      disableWidget('newButton');
      disableWidget('copyButton');
    }
  }
  if (updateRight) {
    if (updateRight.value!='YES') {
      disableWidget('saveButton');
      disableWidget('undoButton');
      disableWidget('multiUpdateButton');
    }
  }
  if (deleteRight) {
    if (deleteRight.value!='YES') {
      disableWidget('deleteButton');
    }
  }
}

/**
 * ============================================================================
 * Disable a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function disableWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('disabled',true);
  }
}

/**
 * ============================================================================
 * Enable a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function enableWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('disabled',false);
  }
}

/**
 * ============================================================================
 * Loack a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function lockWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('readOnly',true);
  }
}

/**
 * ============================================================================
 * Unlock a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function unlockWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('readOnly',false);
  }
}

/**
 * ============================================================================
 * Check if change is possible : to avoid recursive change when computing data
 * from other changes
 * 
 * @return boolean indicating if change is allowed or not
 */
function testAllowedChange(val) {
  if (cancelRecursiveChange_OnGoingChange==true) {
    return false;
  } else {
    if (val==null) {
      return false;
    } else {
      cancelRecursiveChange_OnGoingChange=true;
      return true;
    }
  }
}

/**
 * ============================================================================
 * Checks that ongoing change is finished, so another change cxan be taken into
 * account so that testAllowedChange() can return true
 * 
 * @return void
 */
function terminateChange() {
  window.setTimeout("cancelRecursiveChange_OnGoingChange=false;",100);
}

/**
 * ============================================================================
 * Check if a change is waiting for form submission to be able to avoid unwanted
 * actions leading to loose of data change
 * 
 * @return boolean indicating if change is in progress for the form
 */
function checkFormChangeInProgress(actionYes, actionNo) {
  if (waitingForReply)  {
    showInfo(i18n("alertOngoingQuery"));
    return true;
  } else if (formChangeInProgress) {
	if (multiSelection) {
		endMultipleUpdateMode();
		return false;
	}
    if (actionYes) {
      if (! actionNo) {
        actionNo=function() {  };
      }
      showQuestion(i18n("confirmChangeLoosing"), actionYes, actionNo);
    } else {
      showAlert(i18n("alertOngoingChange"));
    }
    return true;
  } else {
    if (actionYes) {
      actionYes();
    }
    return false;
  }
}

/**
 * ============================================================================
 * Unselect all the lines of the grid
 * 
 * @param gridName
 *            the name of the grid
 * @return void
 */
function unselectAllRows(gridName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if ( ! grid) { 
    return;
  }
  grid.store.fetch({ 
	onComplete: function (items) { 
	  dojo.forEach(items, function (item, index) { 
		  grid.selection.setSelected(index,false);
	  }) 
	} 
  }); 
}

function selectAllRows(gridName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if ( ! grid) { 
    return;
  }
  grid.store.fetch({ 
	onComplete: function (items) { 
	  dojo.forEach(items, function (item, index) { 
		  grid.selection.setSelected(index,true);
	  }) 
	} 
  }); 
}


function countSelectedItem(gridName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if ( ! grid) { 
    return 0;
  }
  return grid.selection.getSelectedCount();
}
/**
 * ============================================================================
 * Select a given line of the grid, corresponding to the given id
 * 
 * @param gridName
 *            the name of the grid
 * @param id
 *            the searched id
 * @return void
 */
var gridReposition=false;
function selectRowById(gridName, id) {
  var grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if ( ! grid) { 
    return;
  }
  unselectAllRows(gridName); // first unselect, to be sure to select only 1 line 
  //De-activate this function for IE8 : grid.getItem does not work
  if (dojo.isIE && parseInt(dojo.isIE,10)<='8') { 
	return;
  }
  var nbRow=grid.rowCount;
  gridReposition=true;
  for (i=0; i<nbRow; i++) {
	item=grid.getItem(i);
    //itemId=item.id;
    if (item && item.id==id) {
      grid.selection.setSelected(i,true);
      gridReposition=false;
      return;
    }
  }
  gridReposition=false;
}

/**
 * ============================================================================
 * i18n (internationalization) function to return all messages and caption in
 * the language corresponding to the locale File lang.js must exist in directory
 * tool/i18n/nls/xx (xx as locale) otherwise default is uses (english) (similar
 * function exists in php, using same resource)
 * 
 * @param str
 *            the code of the string message
 * @param vars
 *            an array of parameters to replace in the message. They appear as
 *            ${n}.
 * @return the formated message, in the correct language
 */
function i18n(str, vars) {
  if ( ! i18nMessages ) {
	try {
	  //dojo.registerModulePath('i18n', '/tool/i18n'); 
      dojo.requireLocalization("i18n","lang", currentLocale);
      i18nMessages=dojo.i18n.getLocalization("i18n","lang", currentLocale);
	} catch(err) {
	  i18nMessages=new Array();
    }
  }
  if (i18nMessages[str]) {
    ret = i18nMessages[str];
    if (vars) {
      for (i=0; i<vars.length; i++) {
        rep='${' + (parseInt(i,10)+1) +'}';
        pos=ret.indexOf(rep);
        if (pos>=0) {
          ret=ret.substring(0,pos) + vars[i] + ret.substring(pos+rep.length);
          pos=ret.indexOf(rep);
        }
      }
    }
    return ret;
  } else {
    return "["+ str + "]";
  }
}

/**
 * ============================================================================
 * set the selected project (transmit it to session)
 * 
 * @param idProject
 *            the id of the selected project
 * @param nameProject
 *            the name of the selected project
 * @param selectionField
 *            the name of the field where selection is executed
 * @return void
 */
function setSelectedProject(idProject, nameProject, selectionField) {
  if (selectionField) {
	dijit.byId(selectionField).set("label",'<div style="width:160px; overflow: hidden;text-align: left;" >'+nameProject+'</div>');
  }
  if (idProject!="") {
    dojo.xhrPost({
      url: "../tool/saveDataToSession.php?id=project&value=" + idProject,
      handleAs: "text",
      load: function(data,args) { 
        addMessage(i18n("Project")+ "=" + nameProject );
        if (dojo.byId("GanttChartDIV")) {
          if (dojo.byId("resourcePlanning") ) {
        	loadContent("resourcePlanningList.php", "listDiv", 'listForm');  
          } else if (dojo.byId("portfolioPlanning") ) {
          	loadContent("portfolioPlanningList.php", "listDiv", 'listForm');
          } else {
            loadContent("planningList.php", "listDiv", 'listForm');
          }
        } else if (dijit.byId("listForm") && dojo.byId('objectClass') && dojo.byId('listShowIdle')) {
          refreshJsonList(dojo.byId('objectClass').value);
        } else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value=='Today') {
          loadContent("../view/today.php", "centerDiv");  
        }
      }
    });
  }
  if (idProject!="" && idProject!="*" && dijit.byId("idProjectPlan")) {
    dijit.byId("idProjectPlan").set("value",idProject);
  }
  if (selectionField) {
    dijit.byId(selectionField).closeDropDown();
  }
  loadContent('../view/shortcut.php',"projectLinkDiv");
}

/**
 * Ends current user session
 * 
 * @return
 */
function disconnect() {
  disconnectFunction = function() {
    quitConfirmed=true;
    dojo.xhrPost({
      url: "../tool/saveDataToSession.php?id=disconnect",
      handleAs: "text",
      load: function(data,args) { window.location="../index.php"; }
    });
  };
  if ( ! checkFormChangeInProgress() ) {
    showConfirm(i18n('confirmDisconnection'),disconnectFunction);
  }
}

/**
 * Disconnect (kill current session)
 * 
 * @return
 */
function quit() {
  if (! noDisconnect) {
    showWait();
    dojo.xhrGet({
      url: "../tool/saveDataToSession.php?id=disconnect",
      load: function(data,args) { 
          hideWait();
          }
    });
    setTimeout("window.location='../index.php'",100);
  }
}

/**
 * Before quitting, check for updates
 * 
 * @return
 */
function beforequit() {
  if (checkFormChangeInProgress()) {
      return (i18n("alertQuitOngoingChange"));
  } else {
    if (! quitConfirmed) {
      return(i18n('confirmDisconnection'));
    }
  }
  // return false;
}

/**
 * Draw a gantt chart using jsGantt
 * 
 * @return
 */
function drawGantt() {
  // first, if detail is displayed, reload class
  if (dojo.byId("objectClass") && !dojo.byId("objectClass").value && dojo.byId("className") && dojo.byId("className").value) {
	  dojo.byId("objectClass").value=dojo.byId("className").value;
  } 
  if (dojo.byId("objectId") && ! dojo.byId("objectId").value && dijit.byId("id") && dijit.byId("id").get("value")) {
	  dojo.byId("objectId").value=dijit.byId("id").get("value");
  } 
  if (dijit.byId('startDatePlanView')) {
    var startDateView=dijit.byId('startDatePlanView').get('value');
  } else {
    var startDateView=new Date();
  }
  if (dijit.byId('endDatePlanView')) {
    var endDateView=dijit.byId('endDatePlanView').get('value');
  } else {
    var endDateView=null;
  }
  if (dijit.byId('showWBS')) {
    var showWBS=dijit.byId('showWBS').get('checked');
  } else {
    var showWBS=null;
  }
  // showWBS=true;
  var gFormat="day";
  if (g) {
    gFormat=g.getFormat();
  }
  g = new JSGantt.GanttChart('g',dojo.byId('GanttChartDIV'), gFormat); 
  setGanttVisibility(g);
  g.setCaptionType('Caption');           // Set to Show Caption
                          // (None,Caption,Resource,Duration,Complete)
  //g.setShowStartDate(1);                 // Show/Hide Start Date(0/1)
  //g.setShowEndDate(1);                   // Show/Hide End Date(0/1)
  g.setDateInputFormat('yyyy-mm-dd');   // Set format of input dates
                    // ('mm/dd/yyyy', 'dd/mm/yyyy',
                    // 'yyyy-mm-dd')
  g.setDateDisplayFormat('default'); // Set format to display dates
                    // ('mm/dd/yyyy', 'dd/mm/yyyy',
                    // 'yyyy-mm-dd')
  g.setFormatArr("day","week","month","quarter"); // Set format options (up to 4 :
                    // "minute","hour","day","week","month","quarter")
  if (ganttPlanningScale) {g.setFormat(ganttPlanningScale);}
  g.setStartDateView(startDateView);
  g.setEndDateView(endDateView);
  var contentNode = dojo.byId('gridContainerDiv');
  if (contentNode) {
    g.setWidth(dojo.style(contentNode, "width"));
  }
  jsonData=dojo.byId('planningJsonData');
  if ( jsonData.innerHTML.indexOf('{"identifier"')<0) {
      if (jsonData.innerHTML.length>10) {
	    showAlert(jsonData.innerHTML);
      }
      hideWait();
      return;
  }
  // g.AddTaskItem(new JSGantt.TaskItem( 0, 'project', '', '', 'ff0000', '',
  // 0, '', '10', 1, '', 1, '' , 'test'));
  if( g && jsonData) {
    var store=eval('('+jsonData.innerHTML+')');
    var items=store.items;
    //var arrayKeys=new Array();
    var keys="";
    for (var i=0; i< items.length; i++) {
      var item=items[i];
      //var topId=(i==0)?'':item.topid;
      var topId=item.topid;
      // pStart : start date of task
      var pStart="";
      pStart=(item.initialstartdate!=" ")?item.initialstartdate:pStart;
      pStart=(item.validatedstartdate!=" ")?item.validatedstartdate:pStart;
      pStart=(item.plannedstartdate!=" ")?item.plannedstartdate:pStart;
      pStart=(item.realstartdate!=" ")?item.realstartdate:pStart;
      if (item.plannedstartdate!=" " 
       && item.realstartdate!=" " 
       && item.plannedstartdate<item.realstartdate) {
        pStart=item.plannedstartdate;
      }
      // pEnd : end date of task
      var pEnd="";
      pEnd=(item.initialenddate!=" ")?item.initialenddate:pEnd;
      pEnd=(item.validatedenddate!=" ")?item.validatedenddate:pEnd;
      pEnd=(item.plannedenddate!=" ")?item.plannedenddate:pEnd;
      pRealEnd="";
      pPlannedStart="";
      pWork="";
      if (dojo.byId('resourcePlanning')) {
    	pRealEnd=item.realenddate;
    	pPlannedStart=item.plannedstartdate;
        pWork=item.leftworkdisplay;
        g.setSplitted(true);
      } else {
    	pEnd=(item.realenddate!=" ")?item.realenddate:pEnd;
      }
      var realWork=parseFloat(item.realwork);
      var plannedWork=parseFloat(item.plannedwork);
      var progress=0;
      if (plannedWork>0) {
        progress=Math.round(100*realWork/plannedWork);
      } else {
        if (item.done==1) {
          progress=100;
        }
      }
      // pGroup : is the tack a group one ?
      var pGroup=(item.elementary=='0')?1:0;
      // runScript : JavaScript to run when click on task (to display the
      // detail of the task)
      var runScript="runScript('"+item.reftype + "','"+item.refid+"','"+item.id+"');";
      // display Name of the task
      var pName=( (showWBS)?item.wbs:'') + " " + item.refname; // for testeing
                                // purpose, add
                                // wbs code
      // var pName=item.refname;
      // display color of the task bar
      var pColor='50BB50';
      // show in red not respected constraints
      if (item.validatedenddate!=" " && item.validatedenddate < pEnd) {
        pColor='BB5050';  
      }
      // pMile : is it a milestone ?
      var pMile=(item.reftype=='Milestone')?1:0;
      if (pMile) { pStart=pEnd; }
      pClass=item.reftype;
      pId=item.refid;
      pScope="Planning_"+pClass+"_"+pId;
      pOpen=(item.collapsed=='1')?'0':'1';
      var pResource=item.resource;
      var pCaption="";
      if ( dojo.byId('listShowResource') ) {
	    if (dojo.byId('listShowResource').checked) { 
	    	pCaption=pResource;
	    }
	  }
      if ( dojo.byId('listShowLeftWork') && dojo.byId('listShowLeftWork').checked ) {
  	    if (item.leftwork>0) { 
  	    	pCaption=item.leftworkdisplay;
  	    } else {
  	    	pCaption="";
  	    }
  	  }
      var pDepend=item.depend;
      topKey="#"+topId+"#";
      curKey="#"+item.id+"#";
      if (keys.indexOf(topKey)==-1) {
	     topId='';
      } 
      keys+="#"+curKey+"#";
      g.AddTaskItem(new JSGantt.TaskItem(item.id, pName, pStart, pEnd, pColor, runScript, pMile, 
    		                             pResource,   progress, pGroup, topId,   pOpen,     pDepend  , 
    		                             pCaption, pClass, pScope, pRealEnd, pPlannedStart, 
    		                             item.validatedworkdisplay, item.assignedworkdisplay, 
    		                             item.realworkdisplay, item.leftworkdisplay, item.plannedworkdisplay,
    		                             item.priority, item.planningmode));
    }
    g.Draw();  
    g.DrawDependencies();
  }
  else
  {
    // showAlert("Gantt chart not defined");
    return;
  }
  /* Issue 985 : removed this update 
   * seems no use and generate issue moving assignments from one activity to another
    // Refresh class and id
    var listId=dojo.byId('objectId');
    var listClass=dojo.byId('objectClass');
    var objId=dojo.byId('id');
    var objClass=dojo.byId('className');
    if (listId && listClass && objId && objClass) {
      listClass.value=objClass.value;
      listId.value=objId.value;
    }
  */
  highlightPlanningLine();
}

function runScript(refType, refId, id) {
  if (waitingForReply)  {
	showInfo(i18n("alertOngoingQuery"));
    return;
  }
  if (checkFormChangeInProgress() ) {
	return false;
  }
  dojo.byId('objectClass').value=refType;
  dojo.byId('objectId').value=refId;
  hideList();
  loadContent('objectDetail.php?planning=true','detailDiv','listForm');
  highlightPlanningLine(id);
}
function highlightPlanningLine(id) {
  if (id==null) id=vGanttCurrentLine;
  if (id<0) return;
  vGanttCurrentLine=id;
  vTaskList=g.getList();
  for (i=0;i<vTaskList.length;i++) {
	JSGantt.ganttMouseOut(i); 	
  }	
  var vRowObj1 = JSGantt.findObj('child_' + id);
  if (vRowObj1) vRowObj1.className = "dojoxGridRowSelected dojoDndItem";// ganttTask" + pType;
  var vRowObj2 = JSGantt.findObj('childrow_' + id);
  if (vRowObj2) vRowObj2.className = "dojoxGridRowSelected";
}
/**
 * calculate diffence (in work days) between dates
 */ 
function workDayDiffDates_old(paramStartDate, paramEndDate) {
  var startDate=paramStartDate;
  var endDate=paramEndDate;
  var valDay=(24 * 60 * 60 * 1000);
  if ( ! ( ( startDate!=null && startDate!="") && ( endDate!=null && endDate!="") ) ) {
    return "";
  }
  if (getDay(endDate)>=6) {
    endDate.setDate(endDate.getDate()+5-getDay(endDate));
  }
  if (getDay(startDate)>=6) {
    startDate.setDate(startDate.getDate()+8-getDay(startDate));
  }
  if (startDate>endDate) {
    return 0;
  }
  var duration=(endDate - startDate) / valDay;
  duration=Math.round(duration);
  if (duration>=7) {
    duration-=Math.floor(duration/7)*2;
  }
  if (getDay(endDate) < getDay(startDate)) {
    duration-=2;
  }
  // add 1 day to include first day, dayDiffDates(X,X)=1,
  // dayDiffDates(X,X+1)=2
  duration+=1;
  return duration;
}

function workDayDiffDates(paramStartDate, paramEndDate) {
  var currentDate=new Date();
  currentDate.setFullYear(paramStartDate.getFullYear(), paramStartDate.getMonth(), paramStartDate.getDate());
  var endDate=paramEndDate;
  if (paramEndDate<paramStartDate) {
	return 0;
  }
  var duration=1;
  while (currentDate<=endDate) {	
	if (! isOffDay(currentDate)) {
      duration++;
	}
	currentDate=addDaysToDate(currentDate,1);
  }
  return duration;
}
/**
 * calculate diffence (in days) between dates
 */ 
function dayDiffDates(paramStartDate, paramEndDate) {
  var startDate=paramStartDate;
  var endDate=paramEndDate;
  var valDay=(24 * 60 * 60 * 1000);
  var duration=(endDate - startDate) / valDay;
  duration=Math.round(duration);
  return duration;
}

/**
 * Return the day of the week like php function : date("N",$valDate) Monday=1,
 * Tuesday=2, Wednesday=3, Thursday=4, Friday=5, Saturday=6, Sunday=7 (not 0 !)
 */
function getDay(valDate) {
  var day=valDate.getDay();
  day=(day==0)?7:day;
  return day;
}

/**
 * ============================================================================
 * Calculate new date after adding some days
 * 
 * @param $ate
 *            start date
 * @param days
 *            numbers of days to add (can be < 0 to subtract days)
 * @return new calculated date
 */
function addDaysToDate(paramDate, paramDays) {
  var date=paramDate;
  var days=paramDays;
  var endDate=date;
  endDate.setDate(date.getDate()+days);
  return endDate;
}

/**
 * ============================================================================
 * Calculate new date after adding some work days, subtracting week-ends
 * 
 * @param $ate
 *            start date
 * @param days
 *            numbers of days to add (can be < 0 to subtract days)
 * @return new calculated date
 */
function addWorkDaysToDate_old(paramDate, paramDays) {
  var startDate=paramDate;
  var days=paramDays;
  if (days<=0) {
    return startDate;
  }
  days-=1;
  if (getDay(startDate)>=6) {
    // startDate.setDate(startDate.getDate()+8-getDay(startDate));
  }
  var weekEnds=Math.floor(days/5);
  var additionalDays=days-(5*weekEnds);
  if (getDay(startDate) + additionalDays >= 6) {
    weekEnds+=1;
  }
  days+=(2*weekEnds);
  var endDate=startDate;
  endDate.setDate(startDate.getDate()+days);
  return endDate;
}

function addWorkDaysToDate(paramDate, paramDays) {
  endDate=paramDate;
  left=paramDays;
  left--;
  while (left>0) {
	endDate=addDaysToDate(endDate,1);
	if (! isOffDay(endDate)) {
	  left--;
	}
  }
  return endDate;
}
/**
 * Check "all" checkboxes on workflow definition
 * 
 * @return
 */
function workflowSelectAll(line, column, profileList) {
  workflowChange(null,null,null);
  var reg=new RegExp("[ ]+", "g");
  var profileArray=profileList.split(reg);
  var check=dijit.byId('val_' + line + "_" + column);
  if (check) {
    var newValue=(check.get("checked"))? 'checked': '';
    for (i=0; i < profileArray.length; i++) {
      var checkBox=dijit.byId('val_' + line + "_" + column + "_" + profileArray[i]);
      if (checkBox) {
        checkBox.set("checked",newValue);
      }
    }
  } else {
    var newValue=dojo.byId('val_' + line + "_" + column).checked;
    for (i=0; i < profileArray.length; i++) {
      var checkBox=dojo.byId('val_' + line + "_" + column + "_" + profileArray[i]);
      if (checkBox) {
        checkBox.checked=newValue;
      }
    }
  }
}

/**
 * Flag a change on workflow definition
 * 
 * @return
 */
function workflowChange(line, column, profileList) {
  var change=dojo.byId('workflowUpdate');
  change.value=new Date();
  formChanged();
  if (line==null) {return;}
  var allChecked=true;
  var reg=new RegExp("[ ]+", "g");
  var profileArray=profileList.split(reg);
  var check=dijit.byId('val_' + line + "_" + column);
  if (check) {
    // var newValue=(check.get("checked"))? 'checked': '';
    for (i=0; i < profileArray.length; i++) {
      var checkBox=dijit.byId('val_' + line + "_" + column + "_" + profileArray[i]);
      if (checkBox) {
        if (checkBox.get("checked")=='false') {
          allChecked=false;
        }
      }
    }
    check.set('checked',(allChecked?'true':'false'));
  } else {
    // var newValue=dojo.byId('val_' + line + "_" + column).checked;
    for (i=0; i < profileArray.length; i++) {
      var checkBox=dojo.byId('val_' + line + "_" + column + "_" + profileArray[i]);
      if (checkBox) {
        if (! checkBox.checked) {
          allChecked=false;
        }
      }
    }
    dojo.byId('val_' + line + "_" + column).checked=allChecked;
  }
  
}

/**
 * refresh Projects List on Today screen
 */
function refreshTodayProjectsList(value) {
  loadContent("../view/today.php?refreshProjects=true", "Today_project", "todayProjectsForm", false);  
}

var clickTimer;
function gotoElement(eltClass, eltId, noHistory) {
  if (checkFormChangeInProgress() ) {
    return false;
  }
  selectTreeNodeById(dijit.byId('menuTree'), eltClass);
  formChangeInProgress=false;
  if ( dojo.byId("GanttChartDIV") && (eltClass=='Project' || eltClass=='Activity' || eltClass=='Milestone') ) {
	refreshJsonPlanning();
	dojo.byId('objectClass').value=eltClass;
	dojo.byId('objectId').value=eltId;
    loadContent('objectDetail.php','detailDiv','listForm');
  } else {
	if (dojo.byId("detailDiv")) {
	  cleanContent("detailDiv");
	}
    loadContent("objectMain.php?objectClass="+eltClass,"centerDiv", false, false, false, eltId);
  }
  if (! noHistory) {
    stockHistory(eltClass,eltId);
  }
}

function runReport() {
  var fileName=dojo.byId('reportFile').value;
  loadContent("../report/"+ fileName , "detailReportDiv", "reportForm", false);  
}
function saveReportInToday() {
  var fileName=dojo.byId('reportFile').value;
  loadContent("../tool/saveReportInToday.php" , "resultDiv", "reportForm", true, 'report');  
}

/**
 * Global save function through [CTRL)+s
 */
function globalSave() {
  if (dijit.byId('dialogDetail') && dijit.byId('dialogDetail').open) {
	var button=dijit.byId('comboSaveButton');
  } else if (dijit.byId('dialogNote') && dijit.byId('dialogNote').open) {
	var button=dijit.byId('dialogNoteSubmit');
  } else if (dijit.byId('dialogLine') && dijit.byId('dialogLine').open) {
		var button=dijit.byId('dialogLineSubmit');
  } else if (dijit.byId('dialogLink') && dijit.byId('dialogLink').open) {
		var button=dijit.byId('dialogLinkSubmit');
  } else if (dijit.byId('dialogOrigin') && dijit.byId('dialogOrigin').open) {
		var button=dijit.byId('dialogOriginSubmit');
  } else if (dijit.byId('dialogCopy') && dijit.byId('dialogCopy').open) {
		var button=dijit.byId('dialogCopySubmit');
  } else if (dijit.byId('dialogCopyProject') && dijit.byId('dialogCopyProject').open) {
		var button=dijit.byId('dialogProjectCopySubmit');
  } else if (dijit.byId('dialogAttachement') && dijit.byId('dialogAttachement').open) {
		var button=dijit.byId('dialogAttachementSubmit');
  } else if (dijit.byId('dialogDocumentVersion') && dijit.byId('dialogDocumentVersion').open) {
		var button=dijit.byId('submitDocumentVersionUpload');
  } else if (dijit.byId('dialogAssignment') && dijit.byId('dialogAssignment').open) {
		var button=dijit.byId('dialogAssignmentSubmit');
  } else if (dijit.byId('dialogExpenseDetail') && dijit.byId('dialogExpenseDetail').open) {
		var button=dijit.byId('dialogExpenseDetailSubmit');
  } else if (dijit.byId('dialogPlan') && dijit.byId('dialogPlan').open) {
		var button=dijit.byId('dialogPlanSubmit');
  } else if (dijit.byId('dialogDependency') && dijit.byId('dialogDependency').open) {
		var button=dijit.byId('dialogDependencySubmit');
  } else if (dijit.byId('dialogResourceCost') && dijit.byId('dialogResourceCost').open) {
		var button=dijit.byId('dialogResourceCostSubmit');
  } else if (dijit.byId('dialogVersionProject') && dijit.byId('dialogVersionProject').open) {
		var button=dijit.byId('dialogVersionProjectSubmit');
  } else if (dijit.byId('dialogAffectation') && dijit.byId('dialogAffectation').open) {
		var button=dijit.byId('dialogAffectationSubmit');
  } else if (dijit.byId('dialogFilter') && dijit.byId('dialogFilter').open) {
		var button=dijit.byId('dialogFilterSubmit');
  } else if (dijit.byId('dialogBillLine') && dijit.byId('dialogBillLine').open) {
		var button=dijit.byId('dialogBillLineSubmit');
  } else if (dijit.byId('dialogMail') && dijit.byId('dialogMail').open) {
		var button=dijit.byId('dialogMailSubmit');
  } else {
    var button=dijit.byId('saveButton');
  }
  if (! button) {
    button=dijit.byId('saveParameterButton');
  }
  if (! button) {
	button=dijit.byId('saveButtonMultiple');
  }
  if ( button && button.isFocusable() ) {
    button.focus();
    button.onClick();
  }
}

function getFirstDayOfWeek(week, year) {
   var testDate=new Date(year,0, 5+(week-1)*7);
   var day=testDate.getDate();
   var month=testDate.getMonth()+1;
   var year=testDate.getFullYear();
   var testWeek=getWeek(day, month, year);

   while (testWeek>=week) {
     testDate.setDate(testDate.getDate()-1);     
     day=testDate.getDate();
     month=testDate.getMonth()+1;
     year=testDate.getFullYear();
     testWeek=getWeek(day, month, year);
     if (testWeek>10 && week==1) {testWeek=0;}
   }
   testDate.setDate(testDate.getDate()+1);
   return testDate;
}

dateGetWeek = function (paramDate,dowOffset) {
/*
 * getWeek() was developed by Nick Baicoianu at MeanFreePath:
 * http://www.meanfreepath.com
 */
  dowOffset = (dowOffset==null) ? 1 : dowOffset; // default dowOffset to 1
                          // (ISO 8601)
  var newYear = new Date(paramDate.getFullYear(),0,1);
  var day = newYear.getDay() - dowOffset; // the day of week the year begins
                      // on
  day = (day >= 0 ? day : day + 7);
  var daynum = Math.floor((paramDate.getTime() - newYear.getTime() - 
  (paramDate.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
  var weeknum;
  // if the year starts before the middle of a week
  if(day < 4) {
    weeknum = Math.floor((daynum+day-1)/7) + 1;
    if(weeknum > 52) {
      nYear = new Date(paramDate.getFullYear() + 1,0,1);
      nday = nYear.getDay() - dowOffset;
      nday = nday >= 0 ? nday : nday + 7;
      /*
		 * if the next year starts before the middle of the week, it is week #1
		 * of that year
		 */
      weeknum = nday < 4 ? 1 : 55;
    }
  }
  else {
    weeknum = Math.floor((daynum+day-1)/7);
    if(weeknum > 52) {
        nYear = new Date(paramDate.getFullYear() + 1,0,1);
        nday = nYear.getDay() - dowOffset;
        nday = nday >= 0 ? nday : nday + 7;
        /*
  		 * if the next year starts before the middle of the week, it is week #1
  		 * of that year
  		 */
        weeknum = nday < 4 ? 1 : 55;
      }
  }
  return weeknum;
};

function getWeek(day, month, year) {  
  var paramDate=new Date(year, month-1,day);
  return dateGetWeek(paramDate,1);
}  

function moveTask(source,destination) {
  var mode='';
  var nodeList=dndSourceTable.getAllNodes();
  for (i=0; i<nodeList.length; i++) {
    if (nodeList[i].id==source) {
      mode='before';
      break;
    } else if (nodeList[i].id==destination) {
      mode='after';
      break;
    }      
  }
  var url='../tool/moveTask.php?from='+source+'&to='+destination+'&mode='+mode;
  loadContent(url, "planResultDiv", null, true,null);
}

function saveCollapsed(scope){
  if (waitingForReply==true) return;
  if (! scope) {
	if (dijit.byId(scope)) {
	  scope=dijit.byId(scope);
	} else {
	  return;
	}  
  }
  dojo.xhrPost({
	url: "../tool/saveCollapsed.php?scope=" + scope + "&value=true",
	handleAs: "text",
	load: function(data,args) { }
  });
}

function saveExpanded(scope){
  if (waitingForReply==true) return;	
  if (! scope) {
	if (dijit.byId(scope)) {
	  scope=dijit.byId(scope);
	} else {
	  return;
	}  
  }
  dojo.xhrPost({
	url: "../tool/saveCollapsed.php?scope=" + scope + "&value=false",
	handleAs: "text",
	load: function(data,args) { }
  });
}

function togglePane(pane) {
  if (waitingForReply==true) return;
  titlepane=dijit.byId(pane);
  if (titlepane) {
	  if (titlepane.get('open')) {
	    saveExpanded(pane);
	  } else {
		saveCollapsed(pane);
	  }
  }
  
}
// *********************************************************************************
// IBAN KEY CALCULATOR
// *********************************************************************************
function calculateIbanKey() {
	var country=ibanFormater(dijit.byId('ibanCountry').get('value'));
	var bban=ibanFormater(dijit.byId('ibanBban').get('value'));
	var number=ibanConvertLetters(bban.toString()+country.toString())+"00";	
	var calculateKey=0;
	var pos=0;
	while (pos<number.length) {
		calculateKey=parseInt(calculateKey.toString()+number.substr(pos,9),10) % 97;
		pos+=9;
	}
	calculateKey=98-(calculateKey % 97);
	var key=(calculateKey<10 ? "0" : "")+calculateKey.toString();
	dijit.byId('ibanKey').set('value',key);
}

function ibanFormater(text) {
	var text=(text==null ? "" : text.toString().toUpperCase());	
	return text;	
}

function ibanConvertLetters(text) {
	convertedText="";
	for (i=0;i<text.length;i++) {
		car=text.charAt(i);
		if (car>"9") {
			if (car>="A" && car<="Z") {
				convertedText+=(car.charCodeAt(0)-55).toString();
			}
		}else if (car>="0"){
			convertedText+=car;
		}
	}
	return convertedText;
}

function trim (myString, car) {
  if (! myString) {return myString;};
  myStringAsTring=myString+"";
  return myStringAsTring.replace(/^\s+/g,'').replace(/\s+$/g,'');
} 
function trimTag (myString, car) {
  if (! myString) {return myString;};
  myStringAsTring=myString+"";
  return myStringAsTring.replace(/^</g,'').replace(/>$/g,'');  
} 

function moveMenuBar(way) {
	var bar=dojo.byId('menubarContainer');
	left=parseInt(bar.style.left.substr(0,bar.style.left.length-2),10);
	var step=80;
	if (way=='left')  {pos=left+step;}
	if (way=='right') {pos=left-step;}
	if (pos>0) pos=0;
	dojo.fx.slideTo({ node: bar, left: pos}).play();
	//bar.style.left=pos+'px';
}

function isHtml5() {
	if (dojo.isIE && dojo.isIE<=9) {
		return false;
	} else if (dojo.isFF && dojo.isFF<4) {
		return false;
	} else {
		return true;
	}
}

function updateCommandTotal() {
	var initialWork=dijit.byId("initialWork").get("value");
	var initialAmount=dijit.byId("initialAmount").get("value");
	var initialPricePerDayAmount=dijit.byId("initialPricePerDayAmount").get("value");
	if (!initialWork) initialWork=0;
	if (!initialAmount) initialAmount=0;
	if (!initialPricePerDayAmount) initialPricePerDayAmount=0;
	var addWork=dijit.byId("addWork").get("value");
	var addAmount=dijit.byId("addAmount").get("value");
	var addPricePerDayAmount=dijit.byId("addPricePerDayAmount").get("value");
	if (!addWork) addWork=0;
	if (!addAmount) addAmount=0;
	if (!addPricePerDayAmount) addPricePerDayAmount=0;
	dijit.byId("validatedWork").set("value", initialWork+addWork);
	dijit.byId("validatedAmount").set("value", initialAmount+addAmount);
	validatedPricePerDayAmount=null;
	if ( (initialWork+addWork)>0) {
	  validatedPricePerDayAmount=Math.round((initialAmount+addAmount)/(initialWork+addWork)*100)/100;
	}
	dijit.byId("validatedPricePerDayAmount").set("value", validatedPricePerDayAmount);	
	terminateChange();
}

function copyDirectLinkUrl() {
  dojo.byId('directLinkUrlDiv').style.display='block';
  dojo.byId('directLinkUrlDiv').select();
  setTimeout("dojo.byId('directLinkUrlDiv').style.display='none';",5000);
  return false;
}

/*function copyToClipboard(inElement) {
  if (inElement.createTextRange) {
    var range = inElement.createTextRange();
    if (range && BodyLoaded==1) {
      range.execCommand('Copy');
    }
  } else {
    var flashcopier = 'flashcopier';
    if(!document.getElementById(flashcopier)) {
      var divholder = document.createElement('div');
	  divholder.id = flashcopier;
	  document.body.appendChild(divholder);
	}
	document.getElementById(flashcopier).innerHTML = '';
	var divinfo = '<embed src="_clipboard.swf" FlashVars="clipboard='+escape(inElement.value)+'" width="0" height="0" type="application/x-shockwave-flash"></embed>';
	document.getElementById(flashcopier).innerHTML = divinfo;
  }
}*/