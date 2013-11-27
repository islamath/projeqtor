<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";

$idType=$_REQUEST['idType'];

$detail=new ExpenseDetailType($idType);

if (array_key_exists('expenseDetailId',$_REQUEST)) {
	$expenseDetailId=$_REQUEST['expenseDetailId'];
	$detail=new ExpenseDetail($expenseDetailId);
}

echo "<table>";

showLine('01',$detail->value01, $detail->unit01);
showLine('02',$detail->value02, $detail->unit02);
showLine('03',$detail->value03, $detail->unit03);

function showLine($nb, $value, $unit) {
	if ($unit) {			
		echo '<tr>';
		echo '<td class="dialogLabel" >';
	    echo '<label for="expenseDetailValue' . $nb . '" >' . ($nb=='01'?'':'x&nbsp;') . '</label>';
	    echo '</td>';
	    echo '<td>';
	    //if ($value) {
	    //  echo $value . " ";	
		  //echo '<input id="expenseDetailValue' . $nb . '" name="expenseDetailValue' . $nb . '" value="' . $value . '"'; 
		  //echo '  type="hidden"/>';	
	    //} else {
	      echo '<input id="expenseDetailValue' . $nb . '" name="expenseDetailValue' . $nb . '" value="' . $value . '"'; 
          echo '  dojoType="dijit.form.NumberTextBox"'; 
          echo '  constraints="{min:0}" ';
          echo '  onChange=expenseDetailRecalculate();';
          echo '  style="width:97px"';              
          echo '  />';	
	    //}
	    echo  " " . $unit;
		echo '</td>';
		echo '</tr>';
	} else {
		echo '<input id="expenseDetailValue' . $nb . '" name="expenseDetailValue' . $nb . '" value=""'; 
		echo '  type="hidden"/>';	
	}
	echo '<input id="expenseDetailUnit' . $nb . '" name="expenseDetailUnit' . $nb . '" value="' . $unit .'"';
	echo '  type="hidden"/>';	
}

?>
