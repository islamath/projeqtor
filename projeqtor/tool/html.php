<?php
/** ===========================================================================
 * Html specific functions
 */
require_once "../tool/projeqtor.php";
//DO NOT SCRIPT LOG : html.php is included in projeqtor.php, so for each script 
//scriptLog('   ->/tool/html.php');
/** ===========================================================================
 * Draw the options list for a select  
 * @param $col the name of the field, as idXxx. The table ref is then xxx.
 * @param $selection the value of the field, to be selected in the list
 * @param $obj optional - object for which list is generated
 * @param $required optional - indicates wether the list may present an empty value or not
 * @return void
 */
function htmlDrawOptionForReference($col, $selection, $obj=null, $required=false, $critFld=null, $critVal=null, $limitToActiveProjects=true) {
//traceLog("      =>htmlDrawOptionForReference($col,$selection," . (($obj)?get_class($obj).'#'.$obj->id:'null' ).",$required,$critFld,$critVal)");
	$listType=substr($col,2);
	$column='name';
	if ($listType=='DocumentDirectory') {
		$column='location';
	}	
  
  if ($col=='idResource' and $critFld=='idProject') {
  	$prj=new Project($critVal);
    $lstTopPrj=$prj->getTopProjectList(true);
    $in=transformValueListIntoInClause($lstTopPrj);
    $where="idProject in " . $in; 
    $aff=new Affectation();
    $list=$aff->getSqlElementsFromCriteria(null,null, $where);
    $nbRows=0;
    $table=array();
    if ($selection) {
       $table[$selection]=SqlList::getNameFromId('Resource', $selection);
    }
    foreach ($list as $aff) {
      if (! array_key_exists($aff->idResource, $table)) {
        $id=$aff->idResource;
        $name=SqlList::getNameFromId('Resource', $id);
        if ($name!=$id) {
          $table[$id]=$name;
        }
      }
    }
    asort($table);
  } else if ($critFld) {
    $critArray=array($critFld=>$critVal);
    $table=SqlList::getListWithCrit($listType,$critArray,$column,$selection);
    if ($col=="idProject") { 
    	$wbsList=SqlList::getListWithCrit($listType,$critArray,'sortOrder',$selection);
    }  
  } else {
    $table=SqlList::getList($listType,$column,$selection, (! $obj)?!$limitToActiveProjects:false );
    if ($col=="idProject") { 
    	$wbsList=SqlList::getList($listType,'sortOrder',$selection, (! $obj)?!$limitToActiveProjects:false );
    }  
  }
  $restrictArray=array();
  $excludeArray=array();
  if ($obj) {
  	$class=get_class($obj);
    if ( $class=='Project' and $col=="idProject" and $obj->id!=null) {
      $excludeArray=$obj->getRecursiveSubProjectsFlatList();
      $excludeArray[$obj->id]=$obj->name;
    } else if ($col=="idProject") {
    	$menuClass=$obj->getMenuClass();
    	if ($class=='DocumentDirectory') {
    		$doc=new Document();
    		$menuClass=$doc->getMenuClass();
    	}
      $user=$_SESSION["user"];
      $controlRightsTable=$user->getAccessControlRights();
      if (! array_key_exists($menuClass,$controlRightsTable)) {
	      // If AccessRight notdefined for object and user profile => empty list + log error
	      traceLog('error in htmlDrawOptionForReference : no control rights for ' . $class);
        return;		
	    }
      $controlRights=$controlRightsTable[$menuClass];
      if ($obj->id==null) {
        // creation mode
        if ($controlRights["create"]=="PRO") {
          $restrictArray=$user->getVisibleProjects();
        }
      } else {
        // read or update mode
        if (securityGetAccessRightYesNo($menuClass, 'update', $obj)=="YES") {
          // update
          if ($controlRights["update"]=="PRO") {
            $restrictArray=$user->getVisibleProjects();
          }            
        }
      }
    } else if ($col=='idStatus') {        
      $idType='id' . $class . 'Type';
      $typeClass=$class . 'Type';
      if (property_exists($obj,$idType) ) {
      	reset($table);
        $fisrtKey=key($table);
        $firstName=current($table);
        if ($obj->$idType and $obj->idStatus) {
          $profile="";
          if (array_key_exists('user', $_SESSION)) {
            $profile=$_SESSION['user']->idProfile;
          } 
          $type=new $typeClass($obj->$idType);
          if (property_exists($type,'idWorkflow') ) {
            $ws=new WorkflowStatus();
            $crit=array('idWorkflow'=>$type->idWorkflow, 'allowed'=>1, 'idProfile'=>$profile, 'idStatusFrom'=>$obj->idStatus);
            $wsList=$ws->getSqlElementsFromCriteria($crit, false);
            $compTable=array($obj->idStatus=>'ok');
            foreach ($wsList as $ws) {
              $compTable[$ws->idStatusTo]="ok";
            }
            $table=array_intersect_key($table,$compTable);
          }
        } else {
           $table=array($fisrtKey=>$firstName);
        }
      }
      if ($selection) {
        $selStatus=new Status($selection);
        if ($selStatus->isCopyStatus) {
        	$table[$fisrtKey]=$firstName;
        }
      }
    }
  } else { // (! $obj)
  	if ($col=="idProject") {
      $user=$_SESSION["user"];
      if (! $user->_accessControlVisibility) {
        $user->getAccessControlRights(); // Force setup of accessControlVisibility
      }      
      if ($user->_accessControlVisibility != 'ALL') {
      	$restrictArray=$user->getVisibleProjects($limitToActiveProjects);
  	  }
    }
  }
  if (! $required) {
    echo '<OPTION value=" " ></OPTION>';
  }
  if ($listType=='Linkable') {
    asort($table);
  }
  if ($col=="idProject") {
    $sepChar=Parameter::getUserParameter('projectIndentChar');
    if (!$sepChar) $sepChar='__';
    $wbsLevelArray=array();
  }
  if (! $obj) $sepChar='no';
  foreach($table as $key => $val) {
    if (! array_key_exists($key, $excludeArray) and ( count($restrictArray)==0 or array_key_exists($key, $restrictArray) ) ) {
      if ($col=="idProject" and $sepChar!='no') {
        $wbs=$wbsList[$key];
        $wbsTest=$wbs;
        $level=1;
        while (strlen($wbsTest)>3) {
          $wbsTest=substr($wbsTest,0,strlen($wbsTest)-4);
          if (array_key_exists($wbsTest, $wbsLevelArray)) {
            $level=$wbsLevelArray[$wbsTest]+1;
            $wbsTest="";
          }
        }
        $wbsLevelArray[$wbs]=$level;
        $sep='';for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
        //$levelWidth = ($level-1) * 2;
        //$sep=($levelWidth==0)?'':substr('_____________________________________________________',(-1)*($levelWidth));
        $val = $sep.$val;
      }
      echo '<OPTION value="' . $key . '"';
      if ( $selection and $key==$selection ) { echo ' SELECTED '; } 
      echo '><span >'. htmlEncode($val) . '</span></OPTION>';
    }
  }
}

function htmlReturnOptionForWeekdays($selection, $required=false) {
	$arrayWeekDay=array('1'=>'Monday', '2'=>'Tuesday', '3'=>'Wednesday', '4'=>'Thursday',
	                    '5'=>'Friday', '6'=>'Saturday', '7'=>'Sunday');
  $result="";
	if (! $required) {
    $result.='<OPTION value=" " ></OPTION>';
  }
  for ($key=1; $key<=7; $key++) {
    $result.= '<OPTION value="' . $key . '"';
    if ( $selection and $key==$selection ) { $result.= ' SELECTED '; } 
    $result.= '>'. i18n($arrayWeekDay[$key]) . '</OPTION>';
  }
  return $result;
}

function htmlReturnOptionForMonths($selection, $required=false) {
  $arrayMonth=array('1'=>'January', '2'=>'February', '3'=>'March', '4'=>'April',
                      '5'=>'May', '6'=>'June', '7'=>'July','8'=>'August',
                      '9'=>'September', '10'=>'October', '11'=>'November','12'=>'December');
  $result="";
  if (! $required) {
    $result.='<OPTION value=" " ></OPTION>';
  }
  for ($key=1; $key<=12; $key++) {
    $result.= '<OPTION value="' . $key . '"';
    if ( $selection and $key==$selection ) { $result.= ' SELECTED '; } 
    $result.= '>'. i18n($arrayMonth[$key]) . '</OPTION>';
  }
  return $result;
}
/** ===========================================================================
 * Display the info of the aplication (name, version) with link to website
 * @return void
 */
function htmlDisplayInfos() {
  global $copyright, $version, $website, $aboutMessage;
  echo "<a class='statusBar' target='#' href='$website' >$copyright $version&nbsp;</a>";
}

/** ===========================================================================
 * Display the info of the aplication (name, version) with link to website
 * @return void
 */
function htmlDisplayDatabaseInfos() {
  $paramDbName=Parameter::getGlobalParameter('paramDbName');
  $paramDbDisplayName=Parameter::getGlobalParameter('paramDbDisplayName');
  if (! $paramDbDisplayName) {
    $paramDbDisplayName=$paramDbName;
  }
  echo "<div class='statusBar' style='text-align:center;'><b>$paramDbDisplayName</b></div>";
}

/** ===========================================================================
 * Display the message No object selected for the corresponding object,
 * translate using i18n
 * @param $className the class of the object
 * @return void
 */
function htmlGetNoDataMessage($className) {
    return '<br/><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . i18n('messageNoData',array(i18n($className))) . '</i>';
}

/** ===========================================================================
 * Draw an html Table as cross reference
 * @param $lineObj the object class containing line data
 * @param $columnTable the table containing column data
 * @param $pivotTable the table containing pivot data (must contain id'ColumnTable' and id'LineTable'
 * @param $pivotValue the name of the field in pivot table containing pivot data
 * @param $format the format of data : check, text, label
 * @return void
 */
function htmlDrawCrossTable($lineObj, $lineProp, $columnObj, $colProp, $pivotObj, $pivotProp, $format='label', $formatList=null, $break=null) {
  global $collapsedList;
	if (is_array($lineObj)) {
    $lineList=$lineObj;
  } else {
    $lineList=SqlList::getList($lineObj);
  }
  
  $columnList=SqlList::getList($columnObj);
  echo '<div style="width:98%; overflow-x:auto;  overflow-y:hidden;">';
  if ( ! ($break and ! is_array($lineObj)) ) {
	  echo '<table class="crossTable" >';
	  // Draw Header
	  echo '<tr><td>&nbsp;</td>';
	  foreach ($columnList as $col) {
	    echo '<td class="tabLabel">' . $col . '</td>';
	  }
	  echo '</tr>';
  }
  $breakVal='';
  $breakNum=0;
  foreach($lineList as $lineId => $lineName) {
  	if ($break and ! is_array($lineObj)) {
  		$class=ucfirst($lineObj);
  		$test=new $class($lineId);
  		if ($test->$break != $breakVal) {
  			$breakNum++;
  			$breakClass=substr($break,2);
  			$breakObj=new $breakClass($test->$break);
  			$breakName="";
  			if ($breakObj->name) {
  			  $breakName=(property_exists($breakObj,'_isNameTranslatable'))?i18n($breakObj->name):$breakObj->name;
  			} 
  			//echo '<tr><td class="tabLabel" style="text-align:left;border-top:2px solid #A0A0A0;">' . $breakName  . '</td>';
  			if ($test->$break) {
  			  $breakCode=$breakObj->name;
  			} else {
  				$breakCode=$breakNum;
  			} 
  			echo '</table></div><br/>';
        $divName='CrossTable_'.$lineObj.'_'.$breakCode;
        echo '<div id="' . $divName . '" dojoType="dijit.TitlePane"';
        echo ' open="' . (array_key_exists($divName, $collapsedList)?'false':'true') . '"';
        echo ' onHide="saveCollapsed(\'' . $divName . '\');"';
        echo ' onShow="saveExpanded(\'' . $divName . '\');"';
        echo ' title="' .$breakName . '"';
        echo ' style="width:98%; overflow-x:auto;  overflow-y:hidden;"';
        echo '>';
        echo '<table class="crossTable">';
        echo '<tr><td>&nbsp;</td>';
			  foreach ($columnList as $col) {
			    echo '<td class="tabLabel">' . $col . '</td>';
			  }
			  echo '</tr>';
        echo '<tr>';  			
  		}
  		$breakVal=$test->$break;
  	}
    echo '<tr><td class="crossTableLine"><label class="label largeLabel">' . $lineName . '</label></td>';
    foreach ($columnList as $colId => $colName) {
      $crit=array();
      $crit[$lineProp]=$lineId;
      $crit[$colProp]=$colId;
      $name=$pivotObj . "_" . $lineId . "_" . $colId;
      $class=ucfirst($pivotObj);
      $obj=SqlElement::getSingleSqlElementFromCriteria($class, $crit);
      $val=$obj->$pivotProp;
      echo '<td class="crossTablePivot">';
      switch ($format) {
        case 'check':
          $checked = ($val!='0' and ! $val==null) ? 'checked' : '';
          echo '<input dojoType="dijit.form.CheckBox" type="checkbox" ' . $checked . ' id="' . $name . '" name="' . $name . '" />'; 
          break;
        case 'text':
          echo '<input dojoType="dijit.form.TextBox id="' . $name . '" name="' . $name . '" type="text" class="input" style="width: 100px;" value="' . $val . '" />';
          break;
        case 'list':
          //echo '<input dojoType="dijit.form.TextBox id="' . $name . '" name="' . $name . '" type="text" class="input" style="width: 100px;" value="' . $val . '" />';
          echo '<select dojoType="dijit.form.FilteringSelect" class="input" '; 
          echo ' style="width: 100px; font-size: 80%;"';
          echo ' id="' . $name . '" name="' . $name . '" ';
          echo ' >';
          htmlDrawOptionForReference('id' . $formatList, $val, null, true); 
          echo '</select>';
          break;  
        case 'label':
          echo $val;
          break;
      }
      echo '</td>';
    }
    echo '</tr>';
  }
  
  
  echo '</table></div>';
  
}

/** ===========================================================================
 * Get the data of a form table designed with htmlDrawCrossTable
 * @param $lineTable the table containing line data
 * @param $columnTable the table containing column data
 * @param $pivotTable the table containing pivot data (must contain id'ColumnTable' and id'LineTable'
 * @param $pivotValue the name of the field in pivot table containing pivot data
 * @param $format the format of data : check, text, label
 * @return an array containing 
 */
function htmlGetCrossTable($lineObj, $columnObj, $pivotObj) {
  if (is_array($lineObj)) {
    $lineList=$lineObj;
  } else {
    $lineList=SqlList::getList($lineObj);
  }
  $columnList=SqlList::getList($columnObj);
  $result=array();
  foreach($lineList as $lineId => $lineName) {
    foreach ($columnList as $colId => $colName) {
      $name=$pivotObj . "_" . $lineId . "_" . $colId;
      $val="";
      if (array_key_exists($name,$_REQUEST)) {
        $val=$_REQUEST[$name];
      }
      $result[$lineId][$colId]=$val;
    }
  }
  return $result;
}

/** ===========================================================================
 * Construct a Js table from a Php table (got from a database table)
 * @param $tableName name of database table containing data
 * @param $colName column name co,ntaining requested data in table
 * @return javascript creating an array 
 */
function htmlGetJsTable($tableName, $colName, $jsTableName=null) {
  $tab=SqlList::getList($tableName,$colName);
  $jsTableName=(! $jsTableName) ? 'tab'.ucfirst($tableName):$jsTableName;
  $script='var ' . $jsTableName . ' = [ ';
  $nb=0;
  foreach ($tab as $id=>$value) {
    $script .= (++$nb>1) ? ', ': '';
    $script .= ' { id: "' . $id . '", ' . $colName . ': "' . $value . '" } ';
  }
  $script.= ' ];';
  return $script;
}

/**
 * Format a date, depending on currentLocale
 * @param $val
 * @return unknown_type
 */
function htmlFormatDate($val,$trunc=false) {
  global $browserLocaleDateFormat;
  if (strlen($val)!=10) {
  	if (strlen($val)==19) {
  		if ($trunc) {
  			$val=substr($val,0,10);
  		} else {
  		  return htmlFormatDateTime($val);
  		}
  	} else {
      return $val;
  	}
  }
  $year=substr($val,0,4);
  $month=substr($val,5,2);
  $day=substr($val,8,2);
  /*if ($browserLocaleDateFormat=='MMDDYYYY') {
    return substr($val,5,2) . "/" . substr($val,8,2)  . "/" . substr($val,0,4);
  } else if ($browserLocaleDateFormat=='DDMMYYYY') {
    return substr($val,8,2) . "/" . substr($val,5,2)  . "/" . substr($val,0,4);
  } else {
  	return $val;
  }*/
  $result=str_replace('YYYY', $year, $browserLocaleDateFormat);
  $result=str_replace('MM', $month, $result);
  $result=str_replace('DD', $day, $result);
  return $result;
}

/**
 * Format a dateTime, depending on currentLocale
 * @param $val
 * @return unknown_type
 */
function htmlFormatDateTime($val, $withSecond=true, $hideZeroTime=false) {
  global $browserLocale;
  $locale=substr($browserLocale, 0,2);
  if (strlen($val)!=19 and strlen($val)!=16) {
    if (strlen($val)=="10") {
      return htmlFormatDate($val);
    } else {
      return $val;
    }
  }
  $result=htmlFormatDate(substr($val,0,10));
  if (! $hideZeroTime or substr($val,11,5)!='00:00') {
    $result.= " " . (($withSecond)?substr($val,11):substr($val,11,5));
  }
  return $result;
}
function htmlFormatTime($val, $withSecond=true) {
  global $browserLocale;
  $locale=substr($browserLocale, 0,2);
  $result= (($withSecond)?$val:substr($val,0,5));
  return $result;
}
/** ============================================================================
 * Transform string to be displays in html, pedending on context 
 * @param $context Printing context : 
 *   'print' : for printing purpose, also converts nl to <br> 
 *   'default' : default for conversion
 *   'none' : no convertion
 * @return string - the formated value 
 */
function htmlEncode($val,$context="default") {
  if ($context=='none') {
    return str_replace('"',"''",$val);
  } else if ($context=='print' or $context=='html') {
    return nl2br(htmlentities($val,ENT_COMPAT,'UTF-8'));
  } else if ($context=='withBR') {
    return nl2br(htmlspecialchars($val,ENT_QUOTES,'UTF-8'));
  } else if ($context=='mail') {
    $str=$val;
    if (get_magic_quotes_gpc()) {
      $str=str_replace('\"','"',$str);
      $str=str_replace("\'","'",$str);
      $str=str_replace('\\\\','\\',$str);
    }
    return nl2br(htmlentities($str,ENT_QUOTES,'UTF-8'));
  } else if ($context=='quotes') {
  	$str=str_replace("'"," ",$val);
  	$str=str_replace('"'," ",$str);
  	return $str;
  } else if ($context=='xml') {
  	$str=$val;
  	$str=str_replace("	"," ",$val);
  	return htmlspecialchars($str,ENT_QUOTES,'UTF-8');
  }
  return htmlspecialchars($val,ENT_QUOTES,'UTF-8');
}

/**
 * Remove all caracters that may lead to error on Json file rendering
 * @param $val
 * @return unknown_type
 */
function htmlEncodeJson($val, $numericLength=0) {
  //$val=htmlspecialchars($val,ENT_QUOTES,'UTF-8');
	/*$val = str_replace('&quot;',"''",$val);
  $val = str_replace("&#039;","'",$val);
  $val = str_replace("&amp;","&",$val);
  $val = str_replace("&lt;","<",$val);
  $val = str_replace("&gt;",">",$val);*/
  $val = str_replace("\\","\\\\",$val);
  $val = str_replace("\"","\\\"",$val);
  $val = str_replace("\n"," ",$val);	     
  
  if ($numericLength>0) {
    $val=str_pad($val,$numericLength,'0', STR_PAD_LEFT);
  }
  return $val;
}

/** ============================================================================
 * Return an error message formated as a resultDiv result
 * @param $message the message to display
 * @return formated html message, with corresponding html input
 */
function htmlGetErrorMessage($message) {
  $returnValue = '<span class="messageERROR" >' . $message . '</span>';
  $returnValue .= '<input type="hidden" id="lastSaveId" value="" />';
  $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
  $returnValue .= '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
  return $returnValue;
}

/** ============================================================================
 * Return a warning message formated as a resultDiv result
 * @param $message the message to display
 * @return formated html message, with corresponding html input
 */
function htmlGetWarningMessage($message) {
  $returnValue = '<span class="messageWARNING" >' . $message . '</span>';
  $returnValue .= '<input type="hidden" id="lastSaveId" value="" />';
  $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
  $returnValue .= '<input type="hidden" id="lastOperationStatus" value="WARNING" />';
  return $returnValue;
}

/** ============================================================================
 * Return an mime/Type formated as an image
 * @param $mimeType the textual mimeType
 * @return formated html mimeType, as an image
 */
function htmlGetMimeType($mimeType,$fileName) {
  $ext = pathinfo($fileName, PATHINFO_EXTENSION);
  if (file_exists("../view/img/mime/$ext.png")) {
    $img="../view/img/mime/$ext.png";
  } else {
    $img= "../view/img/mime/unknown.png";
  }
  return '<img src="' . $img . '" title="' . $mimeType . '" />';
}

/** ============================================================================
 * Return an fileSize formated as GB, MB KB or B 
 * @param $mimeType the textual mimeType
 * @return formated html mimeType, as an image
 */
function htmlGetFileSize($fileSize) {
  $nbDecimals=1;
  $limit=1000;
  if ($fileSize==null) {
    return '';
  }
  if ($fileSize<$limit) {
    return $fileSize . ' ' . i18n('byteLetter');
  } else {
    $fileSize=round($fileSize/1024,$nbDecimals);
    if ($fileSize<$limit) {
      return $fileSize . ' K' . i18n('byteLetter');
    } else {
      $fileSize=round($fileSize/1024,$nbDecimals);
      if ($fileSize<$limit) {
        return $fileSize . ' M' . i18n('byteLetter');
      } else {
        $fileSize=round($fileSize/1024,$nbDecimals);
        if ($fileSize<$limit) {
          return $fileSize . ' G' . i18n('byteLetter');
        } else {
          $fileSize=round($fileSize/1024,$nbDecimals);
          return $fileSize . ' T' . i18n('byteLetter');
        }      
      }
    }
  }
}

/**
 * Extract argument from condition
 * @param $tag String to extract from
 * @param $arg 
 * @return String
 */
function htmlExtractArgument($tag, $arg) {
  $sp=explode($arg . '=', $tag);
  $fld="";
  if (isset($sp[1])) {
    $fld=$sp[1];
    if (strpos($fld,' ')>1) {
      $fld=substr($fld,0,strpos($fld,' '));
    }
    if (strpos($fld,'>')>1) {
      $fld=substr($fld,0,strpos($fld,'>'));
    }
    $fld=trim($fld,'"');
  }
  return $fld;
}

/**
 * Display a, Array of filter criteria
 * @param $filterArray Array
 * @return Void
 */
function htmlDisplayFilterCriteria($filterArray, $filterName="") {
  // Display Result
  echo "<table width='100%'>";
  echo "<tr><td class='dialogLabel'>";
  echo '<label for="filterNameDisplay" >' . i18n("filterName") . '&nbsp;:&nbsp;</label>';
  echo '<div type="text" dojoType="dijit.form.ValidationTextBox" ';
  echo ' name="filterNameDisplay" id="filterNameDisplay"';
  echo '  style="width: 374px;" ';
  echo ' trim="true" maxlength="100" class="input" ';
  echo ' value="' . htmlEncode($filterName) . '" ';
  echo ' >';
  echo '</td><td>';
  echo '<button title="' . i18n('saveFilter') . '" ';  
  echo ' dojoType="dijit.form.Button" '; 
  echo ' id="dialogFilterSave" name="dialogFilterSave" ';
  echo ' iconClass="dijitEditorIcon dijitEditorIconSave" showLabel="false"> ';
  echo ' <script type="dojo/connect" event="onClick" args="evt">saveFilter();</script>';
  echo '</button>';
  echo "</td></tr>";
  echo "<tr>";
  echo "<td class='filterHeader' style='width:525px;'>" . i18n("criteria") . "</td>";
  echo "<td class='filterHeader' style='width:25px;'>";
  echo ' <img src="css/images/smallButtonRemove.png" onClick="removefilterClause(\'all\');" title="' . i18n('removeAllFilters') . '" class="smallButton"/> ';
  echo "</td>";
  echo "</tr>";
  if (count($filterArray)>0) { 
    foreach ($filterArray as $id=>$filter) {
      echo "<tr>";
      echo "<td class='filterData'>" . 
           $filter['disp']['attribute'] . " " .
           $filter['disp']['operator'] . " " .
           $filter['disp']['value'] .
           "</td>";
      echo "<td class='filterData' style='text-align: center;'>";
      echo ' <img src="css/images/smallButtonRemove.png" onClick="removefilterClause(' . $id . ');" title="' . i18n('removeFilter') . '" class="smallButton"/> ';
      echo "</td>";
      echo "</tr>";
    }
  } else {
    echo "<tr><td class='filterData' colspan='2'><i>" . i18n("noFilterClause") . "</i></td></tr>";
  }
  echo "</table>";
  echo '<input id="nbFilterCriteria" name="nbFilterCriteria" type="hidden" value="' . count($filterArray) . '" />';
}

/**
 * Display a, Array of filter criteria
 * @param $filterArray Array
 * @return Void
 */
function htmlDisplayStoredFilter($filterArray,$filterObjectClass,$currentFilter="", $context="") {
  // Display Result
  $param=SqlElement::getSingleSqlElementFromCriteria('Parameter', 
       array('idUser'=>$_SESSION['user']->id, 'parameterCode'=>'Filter'.$filterObjectClass));
  $defaultFilter=($param)?$param->parameterValue:'';
  echo "<table width='100%'>";
  echo "<tr>";
  if ($context!='directFilterList') {
  	echo "<td class='filterHeader' style='width:525px;'>" . i18n("storedFilters") . "</td>";
    echo "<td class='filterHeader' style='width:25px;'>";
  } else {
  	echo "<td class='filterHeader' style='font-size:8pt;width:300px;'>" . i18n("storedFilters") . "</td>";
  }
  echo "</td>";
  echo "</tr>";
  if ($context=='directFilterList') {
    echo "<tr>";
    echo '<td style="cursor:pointer;font-size:8pt;font-style:italic;' 
           . '"' 
           . ' class="filterData" '
           . 'onClick="selectStoredFilter(\'0\',\'' . $context . '\');" ' 
           . ' title="' . i18n("selectStoredFilter") . '" >'
           . i18n("noFilterClause")
           . "</td>";
    echo "</tr>";
  }
  if (count($filterArray)>0) { 
    foreach ($filterArray as $filter) {
      echo "<tr>";
      echo '<td style="font-size:8pt;'. (($filter->name==$currentFilter and $context=='directFilterList')?'color:white; background-color: grey;':'cursor: pointer;') . '"' 
           . ' class="filterData" '
           //. ($filter->name==$currentFilter)?'':'onClick="selectStoredFilter('. "'" . $filter->id . "'" . ');" ')
           . 'onClick="selectStoredFilter(\'' . $filter->id . '\',\'' . $context . '\');" ' 
           . ' title="' . i18n("selectStoredFilter") . '" >'
           . htmlEncode($filter->name)
           . ( ($defaultFilter==$filter->id and $context!='directFilterList')?' (' . i18n('defaultValue') . ')':'')
           . "</td>";
      if ($context!='directFilterList') {
        echo "<td class='filterData' style='text-align: center;'>";      
        echo ' <img src="css/images/smallButtonRemove.png" onClick="removeStoredFilter('. "'" . $filter->id . "','" . htmlEncode(htmlEncode($filter->name)) . "'" . ');" title="' . i18n('removeStoredFilter') . '" class="smallButton"/> ';
        echo "</td>";
      }
      echo "</tr>";
    }
  } else {
  	if ($context!='directFilterList') {
      echo "<tr><td class='filterData' colspan='2'><i>" . i18n("noStoredFilter") . "</i></td></tr>";
  	}
  }
  echo "</table>";

}

function htmlDisplayCheckbox ($value) {
  $checkImg="checkedKO.png";
  if ($value!='0' and ! $value==null) { 
    $checkImg= 'checkedOK.png';
  } 
  return '<img src="img/' . $checkImg . '" />';
}

function htmlDisplayColored($value,$color) {
  $result= "";
  $foreColor='#000000';
  if (strlen($color)==7) {
    $red=base_convert(substr($color,1,2),16,10);
    $green=base_convert(substr($color,3,2),16,10);
    $blue=base_convert(substr($color,5,2),16,10);
    $light=(0.3)*$red + (0.6)*$green + (0.1)*$blue;
    if ($light<128) { $foreColor='#FFFFFF'; }
  }
  $result.= '<table><tr><td style="background-color:' . $color . '; color:' . $foreColor . ';">';
  $result.= $value;
  $result.= "</td></tr></table>";
  return $result;
}

function htmlDisplayCurrency($val,$noDecimal=false) {
  if (! $val and $val!='0') return '';
  global $browserLocale;
  $currency=Parameter::getGlobalParameter('currency');
  $currencyPosition=Parameter::getGlobalParameter('currencyPosition');
  if ($noDecimal) {
    $fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::INTEGER );
  } else {
    $fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
  }
  if (! isset($currencyPosition) or ! isset($currency) or $currencyPosition=='none') {
    return $fmt->format($val) ;
  } 
  if ($currencyPosition=='after') {
    return str_replace(' ','&nbsp;',$fmt->format($val)) . '&nbsp;' . $currency; 
  } else {
    return $currency . '&nbsp;' . str_replace(' ','&nbsp;',$fmt->format($val)) ;
  }
}

function htmlDisplayNumeric($val) {
  global $browserLocale;
  // old version : too restrictive
  $fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
  return $fmt->format($val) ;
  // numflt_* functions unvailable in some PHP versions, so keep old version
  //$fmt = numfmt_create( $browserLocale, NumberFormatter::DECIMAL );
  //$data = numfmt_format($fmt, $val);
  //return $data;
}

function htmlDisplayPct($val) {
  return $val . '&nbsp;%';
}

function htmlRemoveDocumentTags($val) {
  $res=strstr($val, '<body>');
  $res=str_replace(array('<html>','</html>','<body>','</body>') , '', $res);
  return $res;
}

function htmlDrawLink($obj, $display=null) {
	$canRead=securityGetAccessRightYesNo('menu' . get_class($obj), 'read', $obj)=="YES";
	$disp=htmlencode(($display)?$display:$obj->name);
	if ($canRead) {
	  $result='<a class="link" onClick="gotoElement(\'' . get_class($obj) .'\',\''. $obj->id .'\');">' . $disp . '</a>';
	} else {
		$result=$disp;
	}  
	 
	return $result;
}

function htmlFixLengthNumeric($val, $numericLength=0) {  
  if ($numericLength>0) {
    $val=str_pad($val,$numericLength,'0', STR_PAD_LEFT);
  }
  return $val;
}
?>