<?php
/* ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/parameter.php');

$type=$_REQUEST['type'];
$criteriaRoot=array();
$user=$_SESSION['user'];
$manual=ucfirst($type);

$collapsedList=Collapsed::getCollaspedList();

$parameterList=Parameter::getParamtersList($type);
switch ($type) {
	case ('userParameter'):
		$criteriaRoot['idUser']=$user->id;
		$criteriaRoot['idProject']=null;
		break;
	case ('projectParameter'):
		$criteriaRoot['idUser']=null;
		$criteriaRoot['idProject']=null;
		break;
	case ('globalParameter'):
		$criteriaRoot['idUser']=null;
		$criteriaRoot['idProject']=null;
		break;
	case ('habilitation'):
	case ('habilitationReport'):
	case ('accessRight'):
	case ('habilitationOther'):
		break;
	default:
		traceHack('parameter : unknown parameter type '.$type);
		exit;
		 
}

/** =========================================================================
 * Design the html tags for parameter page depending on list of paramters
 * defined in $parameterList
 * @param $objectList array of parameters with format
 * @return void
 */
function drawTableFromObjectList($objectList) { 
	global $criteriaRoot, $type, $collapsedList;
	$displayWidth='98%';
	$longTextWidth="500px";
	if (array_key_exists('destinationWidth',$_REQUEST)) {
	  $width=$_REQUEST['destinationWidth'];
	  $width-=30;
	  $displayWidth=$width . 'px';
	  $longTextWidth=($displayWidth-30-300).'px';
	} else {
	  if (array_key_exists('screenWidth',$_SESSION)) {
	    $detailWidth = round(($_SESSION['screenWidth'] * 0.8) - 15) ; // 80% of screen - split barr - padding (x2)
	  } else {
	    $displayWidth='98%';
	  }
	}
	echo '<table style="width:99%"><tr><td style="width:50%;vertical-align:top;">';
	echo '<div>';
	echo '<table>';
	foreach($objectList as $code => $format) {
		$criteria=$criteriaRoot;
		$criteria['parameterCode']=$code;
		// fetch the parameter saved in Database
		$obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $criteria);
		if ($type=='userParameter') { // user parameters may be stored in session
			if (array_key_exists($code,$_SESSION) ) {
				$obj->parameterValue=$_SESSION[$code];
			}
		}
		if ($format=='newColumn') {
			echo '</table></div></td><td style="width:50%;vertical-align:top;"><div><table>';
		} else if ($format=='newColumnFull') {
      echo '</table></div></td></tr><tr><td colspan="2" style="width:50%;vertical-align:top;"><div><table>';
    } else {
			if ($format!="section") {
				echo '<tr>';
				echo '<td class="crossTableLine"><label class="label largeLabel" for="' . $code . '" title="' . i18n('help' . ucfirst($code)) . '">' . i18n('param' . ucfirst($code) ) . ' :&nbsp;</label></td><td>';
			} else {
				echo '</table></div><br/>';
				$divName=$type.'_'.$code;
				echo '<div id="' . $divName . '" dojoType="dijit.TitlePane"';
				echo ' open="' . (array_key_exists($divName, $collapsedList)?'false':'true') . '"';
				echo ' onHide="saveCollapsed(\'' . $divName . '\');"';
				echo ' onShow="saveExpanded(\'' . $divName . '\');"';
				echo ' title="' . i18n($code) . '"';
				echo '>';
				echo '<table>';
				echo '<tr>';
			}
			if ($format=='list') {
				$listValues=Parameter::getList($code);
				echo '<select dojoType="dijit.form.FilteringSelect" class="input" name="' . $code . '" id="' . $code . '" ';
				echo ' title="' . i18n('help' . ucfirst($code)) . '" style="width:200px">';
				if ($type=='userParameter') {
					echo $obj->getValidationScript($code);
				}
				foreach ($listValues as $value => $valueLabel ) {
					$selected = ($obj->parameterValue==$value)?'selected':'';
					$value=str_replace(',','#comma#',$value); // Comma sets an isse (not selected) when in value
					echo '<option value="' . $value . '" ' . $selected . '>' . $valueLabel . '</option>';
				}
				echo '</select>';
			} else if ($format=='time') {
				echo '<div dojoType="dijit.form.TimeTextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . i18n('help' . ucfirst($code)) . '"';
				echo ' type="text" maxlength="5" ';
				echo ' style="width:50px; text-align: center;" class="input" ';
				echo ' value="T' . $obj->parameterValue . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $obj->getValidationScript($code);
				echo '</div>';
			} else if ($format=='number' or $format=='longnumber') {
				echo '<div dojoType="dijit.form.NumberTextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . i18n('help' . ucfirst($code)) . '"';
				echo ($format=='longnumber')?' style="width: 100px;" ':' style="width: 50px;" ';
				echo ' class="input" ';
				echo ' value="' .  $obj->parameterValue  . '" ';
				echo ' >';
				echo $obj->getValidationScript($code);
				echo '</div>';
			} else if ($format=='text') {
				echo '<div dojoType="dijit.form.TextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . i18n('help' . ucfirst($code)) . '"';
				echo ' style="width: 200px;" ';
				echo ' class="input" ';
				echo ' value="' .  $obj->parameterValue  . '" ';
				echo ' >';
				echo $obj->getValidationScript($code);
				echo '</div>';
			} else if ($format=='longtext') {
				echo '<textarea dojoType="dijit.form.Textarea" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . i18n('help' . ucfirst($code)) . '"';
				echo ' style="width: '.$longTextWidth.';" ';
				echo ' class="input" ';
				echo ' >';
				echo $obj->parameterValue;
				//echo $obj->getValidationScript($code);
				echo '</textarea>';
			}
			echo '</td></tr>';
		}
	}
	echo '</table>';
	echo '</td></tr></table>';
}
?>
<input
  type="hidden" name="objectClassManual" id="objectClassManual"
  value="<?php echo $manual;?>" />
<div class="container" dojoType="dijit.layout.BorderContainer">
<div id="parameterButtonDiv" class="listTitle"
  dojoType="dijit.layout.ContentPane" region="top">
<table width="100%">
  <tr>
    <td width="50px" align="center"><img
      src="css/images/icon<?php echo ucfirst($type);?>32.png" width="32"
      height="32" /></td>
    <td NOWRAP width="50px" class="title"><?php echo str_replace(" ","&nbsp;",i18n('menu'.ucfirst($type)))?>&nbsp;&nbsp;&nbsp;
    </td>
    <td width="10px">&nbsp;</td>
    <td width="50px">
    <button id="saveParameterButton" dojoType="dijit.form.Button"
      showlabel="false"
      title="<?php echo i18n('buttonSaveParameters');?>"
      iconClass="dijitEditorIcon dijitEditorIconSave"><script
      type="dojo/connect" event="onClick" args="evt">
        	submitForm("../tool/saveParameter.php","resultDiv", "parameterForm", true);
<?php if ($type=='habilitation') {
?>
          forceRefreshMenu="<?php echo $type;?>";
<?php	
}
?>
          </script></button>
    <div dojoType="dijit.Tooltip" connectId="saveButton"><?php echo i18n("buttonSaveParameter")?></div>
    </td>
    <td>
    <div id="resultDiv" dojoType="dijit.layout.ContentPane"
      region="center"></div>
    </td>
  </tr>
</table>
</div>
<div id="formDiv" dojoType="dijit.layout.ContentPane" region="center"
  style="overflow-y: auto; overflow-x: hidden;">
<form dojoType="dijit.form.Form" id="parameterForm" jsId="objectForm"
  name="objectForm" encType="multipart/form-data" action="" method=""><input
  type="hidden" name="parameterType" value="<?php echo $type;?>" /> <?php 
  if ($type=='habilitation') {
  	htmlDrawCrossTable('menu', 'idMenu', 'profile', 'idProfile', 'habilitation', 'allowAccess', 'check', null,'idMenu') ;
  } else if ($type=='accessRight') {
  	htmlDrawCrossTable('menuProject', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfile', 'idMenu') ;
  } else if ($type=='habilitationReport') {
  	htmlDrawCrossTable('report', 'idReport', 'profile', 'idProfile', 'habilitationReport', 'allowAccess', 'check', null, 'idReportCategory') ;
  } else if ($type=='habilitationOther') {
  	$titlePane="habilitationOther_Imputation";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionImputation') . '">';
  	htmlDrawCrossTable(array('imputation'=>i18n('imputationAccess'), 'workValid'=>i18n('workValidate')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'accessScope') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_WorkCost";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionWorkCost') . '">';
  	htmlDrawCrossTable(array('work'=>i18n('workAccess'),'cost'=>i18n('costAccess')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'visibilityScope') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_ComboDetail";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionComboDetail') . '">';
  	htmlDrawCrossTable(array('combo'=>i18n('comboDetailAccess')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_PlanningRight";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionPlanningRight') . '">';
  	htmlDrawCrossTable(array('planning'=>i18n('planningRight')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Unlock";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionUnlock') . '">';
  	htmlDrawCrossTable(array('document'=>i18n('documentUnlockRight'),'requirement'=>i18n('requirementUnlockRight')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  } else {
  	drawTableFromObjectList($parameterList);
  }
  ?></form>
</div>
</div>
