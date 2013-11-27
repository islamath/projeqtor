<?php
/** ===========================================================================
 * Display the column selector div
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/listColumnSelector');
//echo "$objectClass<br/>";
//$columns=Parameter::getPlanningColumnOrder();
//$columnsAll=Parameter::getPlanningColumnOrder(true);
$listColumns=ColumnSelector::getColumnsList($objectClass);
//echo "<textarea>$listColumns</textarea>";
//asort($columns);
//$pe=new ProjectPlanningElement();
//$pe->setVisibility();
//$workVisibility=$pe->_workVisibility;
//$costVisibility=$pe->_costVisibility;
$cpt=0;
//echo '<table style="width:100%"><tr><td>';
foreach ($listColumns as $col) {
	if ( ! SqlElement::isVisibleField($col->attribute) ) {
		// nothing
	} else {
		echo '<div style="width:100%;" class="dojoDndItem" id="listColumnSelectorId'.$col->id.'" dndType="planningColumn">';
		echo '<span class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
		echo '<span dojoType="dijit.form.CheckBox" type="checkbox" id="checkListColumnSelectorId'.$cpt.'" '
		. ((! $col->hidden)?' checked="checked" ':'')
		. (( $col->field=='id' or $col->field=='name')?' disabled="disabled" ':'')
		. ' onChange="changeListColumn(\'' . $col->id . '\','.$cpt.',this.checked,\'' . $col->sortOrder . '\')" '
		. '></span><label for="checkListColumnSelectorId'.$cpt.'" class="checkLabel">';
		echo '&nbsp;';
		echo $col->_displayName . "</label>&nbsp;&nbsp;";
		echo '<div style="float: right; text-align:right">';
		if ($col->attribute=='name') {
      echo '<div class="input" dojoType="dijit.form.NumberTextBox" id="checkListColumnSelectorWidthId'.$cpt.'" ';
      echo 'disabled="disabled" ';     
      echo ' style="width:17px; background: #F0F0F0; text-align: center;" value="'.$col->widthPct.'" ></div>';
      echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      echo '<input type="hidden" id="columnSelectorNameFieldId" value="'.$cpt.'" />';
      echo '<input type="hidden" id="columnSelectorNameTableId" value="'.$col->id.'" />';
		} else {
			echo '<div dojoType="dijit.form.NumberSpinner" id="checkListColumnSelectorWidthId'.$cpt.'" ';
			echo ($col->hidden or $col->attribute=='name')?'disabled="disabled" ':'';
			if ($col->attribute!='name') {	
			  echo ' onChange="changeListColumnWidth(\'' . $col->id . '\','.$cpt.',this.value)" ';
			  echo ' onClick="recalculateColumnSelectorName()" ';
			}	 
			echo ' constraints="{ min:1, max:50, places:0 }"';
			echo ' style="width:35px; text-align: center;" value="'.$col->widthPct.'" >';
			echo '</div>';
		}
		echo '&nbsp;</div>';
		echo '</div>';
		$cpt++;
		//if ($cpt%10==0) {echo '</td><td>';}
	}
}
//echo '</td></tr></table>';
?>