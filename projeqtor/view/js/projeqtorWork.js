// ============================================================================
// All specific ProjeQtOr functions for work management
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

/**
 * Open / Close Group : hide sub-lines
 */
function workOpenCloseLine(line, scope) {
	var nbLines=dojo.byId('nbLines').value;
	var wbsLine=dojo.byId('wbs_'+line).value;
	var wbsLineTop=wbsLine.substr(0,wbsLine.lastIndexOf("."));
	var action=(dojo.byId('status_'+line).value=='opened')?"close":"open";
	if (action=="close") {
		dojo.byId('group_' + line).className="ganttExpandClosed";
		dojo.byId('status_'+line).value="closed";
		saveCollapsed(scope);
	} else {
		dojo.byId('group_' + line).className="ganttExpandOpened";
		dojo.byId('status_'+line).value="opened";
		saveExpanded(scope);
	}
	for (i=line+1; i<=nbLines; i++) {
		var wbs=dojo.byId('wbs_'+i).value;
		var wbsTop=wbs.substr(0,wbs.lastIndexOf("."));
		if (wbs.length <= wbsLine.length ) {
			break;
		} 
		if (wbsTop.substr(0,wbsLineTop.length)!=wbsLineTop) {
		  break;
		}
		if (action=="close") {
		  dojo.byId('line_' + i).style.display = "none";
		} else {
			dojo.byId('line_' + i).style.display = "";
			var status=dojo.byId('status_'+i).value;
			if (status=='closed') {
				var wbsClosed=dojo.byId('wbs_'+i).value;
				for (j=i+1; j<=nbLines; j++) {
					var wbsSub=dojo.byId('wbs_'+j).value;
					if (wbsSub.indexOf(wbsClosed)==-1) {
						break;
					}
				}
				i=j-1;
			}
		}
	}	
}

/**
 * Refresh the imputation list
 * @return
 */
function refreshImputationList() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return false;
	}
	formInitialize();
	dojo.byId('userId').value=dijit.byId('userName').get("value");
	dojo.byId('idle').checked=dojo.byId('listShowIdle').checked;
	dojo.byId('showPlannedWork').checked=dojo.byId('listShowPlannedWork').checked;
	loadContent('../view/refreshImputationList.php', 'workDiv', 'listForm', false);
	return true;
}

/**
 * Refresh the imputation list after period update (check format first)
 * @return
 */
var noRefreshImputationPeriod=false;
function refreshImputationPeriod(directDate) {
	if (noRefreshImputationPeriod) {
		return;
	}
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		noRefreshImputationPeriod=true;
		var period=dojo.byId('rangeValue').value;
		var year=period.substr(0,4);
        dijit.byId('yearSpinner').set('value',year);
        var week=period.substr(4,2);
        dijit.byId('weekSpinner').set('value',week);
		//var week=dijit.byId('weekSpinner').get('value') + '';
		var day=getFirstDayOfWeek(week,year);
		dijit.byId('dateSelector').set('value',day);
		noRefreshImputationPeriod=false;
		return false;
	}
	noRefreshImputationPeriod=true;
	if (directDate) {
		var year=directDate.getFullYear();
		var week=getWeek(directDate.getDate(),directDate.getMonth()+1,directDate.getFullYear())+'';
		dijit.byId('yearSpinner').set('value',year);
		dijit.byId('weekSpinner').set('value',week);
	} else {
		var year=dijit.byId('yearSpinner').get('value');
		var week=dijit.byId('weekSpinner').get('value') + '';
	}
	if (week.length==1 || parseInt(week,10)<10) {
		week='0' + week;
	}
	if (week=='00') {
		week=getWeek(31,12,year-1);
		if (week==1) {
			var day=getFirstDayOfWeek(1,year);
			//day=day-1;
			week=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
		}
		year=year-1;
		dijit.byId('yearSpinner').set('value',year);
		dijit.byId('weekSpinner').set('value', week);
	} else if (parseInt(week,10)>53) {
    	week='01';
	    year+=1;
		dijit.byId('yearSpinner').set('value', year);
		dijit.byId('weekSpinner').set('value', '1');
	} else if (parseInt(week,10)>52) {
		lastWeek=getWeek(31,12,year);
		if (lastWeek==1) {
			var day=getFirstDayOfWeek(1,year+1);
			//day=day-1;
			lastWeek=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
		}
		if (parseInt(week,10)>parseInt(lastWeek,10)) {
			week='01';
		    year+=1;
			dijit.byId('yearSpinner').set('value', year);
			dijit.byId('weekSpinner').set('value', '1');
		}
	}
	var day=getFirstDayOfWeek(week,year);
	dijit.byId('dateSelector').set('value',day);
	dojo.byId('rangeValue').value='' + year + week;
	if ((year+'').length==4) {
		refreshImputationList();
	}
	setTimeout("noRefreshImputationPeriod=false",100);
	return true;
}

/**
 * Dispatch updates for a work value : to column sum, real work, left work and planned work
 * @param rowId
 * @param colId
 * @return
 */
function dispatchWorkValueChange(rowId, colId) {
	var oldWorkValue=dojo.byId('workOldValue_' + rowId + '_' + colId).value;
	if (oldWorkValue==null || oldWorkValue=='') {oldWorkValue=0;}		
	var newWorkValue=dijit.byId('workValue_' + rowId + '_' + colId).get('value');
	if (isNaN(newWorkValue)) {
		newWorkValue=0;
	}
	var diff=newWorkValue-oldWorkValue;
	// Update sum for column
	var oldSum=dijit.byId('colSumWork_' + colId).get("value");
	var newSum=oldSum + diff;
	newSum=Math.round(newSum*100)/100;
	dijit.byId('colSumWork_' + colId).set("value",newSum);
  //Update real work
	var oldReal=dijit.byId('realWork_' + rowId).get("value");
	var newReal=oldReal + diff;
	dijit.byId('realWork_' + rowId).set("value",newReal);
  //Update left work
	var assigned=dijit.byId('assignedWork_' + rowId).get("value");
	var oldLeft=dijit.byId('leftWork_' + rowId).get("value");
	if (assigned>0 || diff>0 || oldLeft>0) {
	  var newLeft=oldLeft - diff;
	  newLeft=(newLeft<0)?0:newLeft;
      dijit.byId('leftWork_' + rowId).set("value",newLeft);
	} else {
      var newLeft=oldLeft;	
	}
  //Update planned work
	var newPlanned=newReal+newLeft;
	dijit.byId('plannedWork_' + rowId).set("value",newPlanned);
	// store new value for next calculation...
	dojo.byId('workOldValue_' + rowId + '_' + colId).value=newWorkValue;
	formChanged();
	checkCapacity();
}

function isOffDay(vDate) {
  if ( vDate.getDay() % 6 == 0) {
	  var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
	  if (workDayList.lastIndexOf('#'+day+'#')>=0) {
		return false; 
	  } else {
	    return true;
	  }
  } else {
	  var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
	  if (offDayList.lastIndexOf('#'+day+'#')>=0) {
		  return true; 
	  } else {
	    return false;
	  }
  }
	
}
function isOffDayNotWeekEnd(vDate) {
  /*if ( vDate.getDay() % 6 == 0) {
	  var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
	  if (workDayList.lastIndexOf('#'+day+'#')>=0) {
		return false; 
	  } else {
	    return true;
	  }
  } else {*/
	  var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
	  if (offDayList.lastIndexOf('#'+day+'#')>=0) {
		return true; 
	  } else {
	    return false;
	  }
  /*}*/
	
}
/**
 * Dispatch updates for left work : re-calculate planned work 
 */
function dispatchLeftWorkValueChange(rowId) {

	var newLeft=dijit.byId('leftWork_' + rowId).get("value");
	if (newLeft==null || isNaN(newLeft) || newLeft=='') {
		dijit.byId('leftWork_' + rowId).set("value",'0');
		newLeft=0;
	}
	var newReal=dijit.byId('realWork_' + rowId).get("value");
	var newPlanned=newReal+newLeft;
	dijit.byId('plannedWork_' + rowId).set("value",newPlanned);
	formChanged();
}


function startMove(id) {
	document.body.style.cursor='help';
}

function endMove(id) {
	document.body.style.cursor='normal';
}

//==========================================================
//Work Period Locking
//==========================================================

function submitWorkPeriod(action) {
  if (checkFormChangeInProgress()) {
	return false;
  }
  var rangeValue=dojo.byId('rangeValue').value;
  var rangeType='week';
  var resource=dijit.byId('userName').get('value');
  dojo.xhrGet({
	url: '../tool/submitWorkPeriod.php?action='+action+'&rangeType='+rangeType+'&rangeValue='+rangeValue+'&resource='+resource,
	  handleAs: "text",
	  load: function(data,args) { refreshImputationList();},
	  error: function() { }
  });		
}

function checkCapacity() {
  var capacity=dojo.byId('resourceCapacity').value;
  for (colId=1; colId<=7; colId++) {
	valSum=dijit.byId('colSumWork_' + colId).get("value");  
	if (valSum > capacity) {
		//dojo.style('colSumWork_' + colId, "backgroung","red");
		dijit.byId('colSumWork_' + colId).set("class","imputationInvalidCapacity");
	} else {
		dijit.byId('colSumWork_' + colId).set("class","displayTransparent");
		//domClass.remove('colSumWork_' + colId, "imputationInvalidCapacity");
	}  
  }
}

function saveImputation() {
  var futureInput=false;	
  for (colId=1; colId<=7; colId++) {
	valSum=dijit.byId('colSumWork_' + colId).get("value");
	isFuture=dojo.byId('colIsFuture_' + colId).value;
	if (isFuture=='1' && valSum>0) {
	  futureInput=true;
	}
  }	
  if (futureInput) {
	valid=function() {
      formChangeInProgress=false; 
      submitForm("../tool/saveImputation.php","resultDiv", "listForm", true);
    };
    nbDays=dojo.byId('nbFutureDays').value;
    var msg=i18n('msgRealWorkInTheFuture',new Array(nbDays));
	showConfirm(msg,valid);
  } else {
    formChangeInProgress=false; 
    submitForm("../tool/saveImputation.php","resultDiv", "listForm", true);
  }
}