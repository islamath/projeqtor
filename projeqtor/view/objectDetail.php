<?php
/* ============================================================================
 * Presents the detail of an object, for viewing or editing purpose.
 *
 * TODO : modify visibility depending on profile
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/objectDetail.php');
if (! isset($comboDetail)) {
	$comboDetail=false;
}
$collapsedList=Collapsed::getCollaspedList();
$readOnly=false;
/** ===========================================================================
 * Draw all the properties of object as html elements, depending on type of data
 * @param $obj the object to present
 * @param $included boolean indicating wether the function is called recursively or not
 * @return void
 */

function drawTableFromObject($obj, $included=false, $parentReadOnly=false) {
	global $cr, $print, $treatedObjects, $displayWidth, $outMode, $comboDetail, 
	 $collapsedList,$printWidth, $detailWidth, $readOnly;
	/*if ($print===null) {
	 $print=$_REQUEST['print'];
	 }
	 if ($collapsedList===null) {
	 $collapsedList=Collapsed::getCollaspedList();
	 }
	 $callFromMail=false;
	 if (array_key_exists('callFromMail', $_REQUEST)) {
	 $callFromMail=true;
	 }*/
	if ($outMode=='pdf') {
		$obj->splitLongFields();
	}
	
	$currency=Parameter::getGlobalParameter('currency');
	$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
	$treatedObjects[]=$obj;
	$dateWidth='75';
	$verySmallWidth='44';
	$smallWidth='75';
	$mediumWidth='200';
	$largeWidth='406';
	$labelWidth=175; // To be changed if changes in css file (label and .label)
	$labelStyleWidth='10%';
	if ($outMode=='pdf') {
	  $labelWidth=50;
	  $labelStyleWidth=$labelWidth.'px';
  }
	$fieldWidth=$smallWidth;
	$currentCol=0;
	$nbCol=1;
	$extName="";
	$user=$_SESSION['user'];
	$displayComboButton=false;
	$habil=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'combo'));
	if ($habil) {
		$list=new ListYesNo($habil->rightAccess);
		if ($list->code=='YES') {
			$displayComboButton=true;
		}
	}
	if ($comboDetail) {
		$extName="_detail";
	}
	$detailWidth=null; // Default detail div width
	// Check screen resolution, to determine max field width (largeWidth)
	//var_dump($obj);
	if (array_key_exists('destinationWidth',$_REQUEST)) {
		$detailWidth=$_REQUEST['destinationWidth'];
	} else {
		if (array_key_exists('screenWidth',$_SESSION)) {
			$detailWidth = round(($_SESSION['screenWidth'] * 0.8) - 15) ; // 80% of screen - split barr - padding (x2)
		}
	}
	//echo "screenWidth=" . $_SESSION['screenWidth'] . "<br/>detailWidth=" . $detailWidth . "<br/>";
	// Define internalTable values, to present data as a table
	$internalTable=0;
	$internalTableCols=0;
	$internalTableRows=0;
	$internalTableCurrentRow=0;
	$internalTableRowsCaptions=array();
	$classObj=get_class($obj);
	if ($obj->id=='0') {$obj->id=null;}
	$type=$classObj . 'Type';
	$idType='id' . $type;
	$objType=null;
	if (property_exists($obj, $idType)) {
		$objType=new $type($obj->$idType);
	}
	$section=''; $nbLineSection=0;
	// Loop on each propertie of the object
	if ( ! $included) {
		echo '<table id="mainTable" >'; // Main table to present multi-column
	}
	if (is_subclass_of($obj,'PlanningElement')) {
		$obj->setVisibility();
		$workVisibility=$obj->_workVisibility;
		$costVisibility=$obj->_costVisibility;
		if (get_class($obj)=="MeetingPlanningElement") {
      $obj->setAttributes($workVisibility,$costVisibility);
		}
	} else 	if (method_exists($obj, 'setAttributes')) {
		$obj->setAttributes();
	}
	$nobr=false;
	$canUpdate=(securityGetAccessRightYesNo('menu' . $classObj, 'update', $obj)=='YES');
  if ( (isset($obj->locked) and $obj->locked and $classObj!='User') or isset($obj->_readOnly)) {
    $canUpdate=false;
  }
	foreach ($obj as $col => $val) {
		if ($detailWidth) {
			$colWidth = ( $detailWidth) / $nbCol;        // 2 columns should be displayable
			$maxWidth= $colWidth - $labelWidth ;          // subtract label width and a margin for slider place
			if ($maxWidth >= $mediumWidth) {
				$largeWidth = $maxWidth;
			} else {
				$largeWidth = $mediumWidth;
			}
		}
		$hide=false;
		$nobr_before=$nobr;
		$nobr=false;
		if ( $included and ($col=='id' or $col=='refId' or $col=='refType' or $col=='refName') ) {
			$hide=true;
		}
		// If field is _tab_x_y, start a table presentation with x columns and y lines
		// the filed _tab_x_y must be an array containing x + y values :
		//   - the x column headers
		//   - the y line headers
		if (substr($col,0,4)=='_tab') {
			$decomp=explode("_",$col);
			$internalTableCols=$decomp[2];
			$internalTableRows=$decomp[3];
			$internalTable=$internalTableCols * $internalTableRows;
			$internalTableRowsCaptions=array_slice($val,$internalTableCols);
			$internalTableCurrentRow=0;
			$colWidth = ( $detailWidth) / $nbCol;
			if (is_subclass_of($obj,'PlanningElement') and $internalTableRows>=3) {
				if ($workVisibility=='NO') {
					$internalTableRowsCaptions[$internalTableRows-2]='';
				}
				if ($costVisibility=='NO') {
					$internalTableRowsCaptions[$internalTableRows-1]='';
				}
				if ($workVisibility!='ALL' and $costVisibility!='ALL') {
					$val[2]='';
					$val[5]='';
				}
			}
			echo '</table><table id="' . $col .'" class="detail"><tr class="detail">';
			echo '<td class="detail"><label></label></td>' . $cr; // Empty label, to have column header in front of columns
			for ($i=0 ; $i<$internalTableCols ; $i++) { // draw table headers
				echo '<td class="detail">';
				if ($val[$i]) {
					if ($print) {
						echo '<div class="tabLabel" style="text-align:left;">' . htmlEncode($obj->getColCaption($val[$i])) . '</div>';
					} else {
						echo '<input type="text" class="tabLabel" style="text-align:left;" value="' . htmlEncode($obj->getColCaption($val[$i])) . '" tabindex="-1" />' . $cr;
					}
				} else {
					echo '<div class="tabLabel" style="text-align:left;">&nbsp;</div>';
				}

				if ( $i < $internalTableCols-1) { echo '</td>'; }
			}
			// echo '</tr>'; NOT TO DO HERE -  WILL BE DONE AFTER
		} else if (substr($col,0,5)=='_col_') { // if field is _col, draw a new main column
			$previousCol=$currentCol;
			$currentCol=substr($col,5,1);
			$nbCol=substr($col,7,1);
			$widthPct=round(98/$nbCol) . "%";
			if ($nbCol=='1') {
				$widthPct=$displayWidth;
			}
			if (substr($displayWidth,-2,2)=="px") {
				$val=substr($displayWidth,0,strlen($displayWidth)-2);
				$widthPct=round( ($val/$nbCol) - 2 * ($nbCol-1) ) . "px";
			}
			if ($print) {
				$widthPct= round( ( $printWidth / $nbCol) - 2 * ($nbCol-1) ) . "px";
			}
			$prevSection=$section;
			if (strlen($col)>8) {
				$section=substr($col,9);
			} else {
				$section='';
			}
			 
			if ($currentCol=='1') {
				if ($previousCol==0) {
					echo '</table>';
				} else {
					echo '</table>';
					if ($prevSection and ! $print) {
						echo '</div>';
					}
					echo '</td></tr></table>';
				}
				echo '<table id="col1_' . $col .'" class="detail"><tr class="detail"><td class="detail" style="width:' . $widthPct . ';" valign="top"><table style="width:' . $widthPct . ';" id="Subcol1_' . $col .'" >';
				$nbLineSection++;
			} else {
				echo '</table>';
				if ($prevSection and ! $print) {
					echo '</div>';
				}
				echo '</td><td class="detail" style="width: 2px;">&nbsp;</td><td class="detail" style="width:' . $widthPct . ';" valign="top"><table style="width:' . $widthPct . ';" id="subcol' . $currentCol . '_' . $col .'" >';
			}
			if (strlen($section)>1) {
				if ($nbLineSection>1) {
					echo '<tr><td></td><td>&nbsp;</td></tr>';
				}
				if (! $print) {
					echo '</table>';
					// Extra div closure : leads to scrollbar error (mostly on workflow)
					//if ($prevSection) {
					//echo 'z</div>';
					//}
					$titlePane=$classObj."_".$section;
					echo '<div dojoType="dijit.TitlePane" title="' . i18n('section' . ucfirst($section)) . '" ';
					echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '" ';
					echo ' id="' . $titlePane . '" ';
					echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
					echo ' onShow="saveExpanded(\'' . $titlePane . '\');">';
					echo '<table class="detail" style="width:' . $widthPct . ';" >';
				} else {
					echo '<tr><td colspan=2 class="section" style="width' . $widthPct . '">' . i18n('section' . ucfirst($section)) . '</td></tr>';
				}
				if ($print and $outMode=="pdf") {
					echo '<tr class="detail" style="height:2px;font-size:2px;">';
					echo '<td class="detail" style="width:10%;">&nbsp;</td>';
					echo '<td style="width: 120px">&nbsp;</td>';
					echo '</tr>';
				}
			}
		} else if (substr($col,0,5)=='_sec_') { // if field is _col, draw a new main column
			echo '<tr><td colspan=2 style="width: 100%" class="halfLine">&nbsp;</td></tr>';
			if ($section and !$print) {
				echo '</table></div>';
			}
			if (strlen($col)>8) {
				$section=substr($col,5);
			} else {
				$section='';
			}
			if (! $print) {
				$titlePane=$classObj."_".$section;
				echo '<div dojoType="dijit.TitlePane" title="' . i18n('section' . ucfirst($section)) . '" ';
				echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '" ';
				echo ' id="' . $titlePane . '" ';
				echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
				echo ' onShow="saveExpanded(\'' . $titlePane . '\');">';
				echo '<table class="detail" style="width:' . $widthPct . ';" >';
			} else {
				echo '<tr><td colspan=2 style="width: 100%" class="section">' . i18n('section' . ucfirst($section)) . '</td></tr>';
			}
		} else if (substr($col,0,5)=='_spe_') { // if field is _spe_xxxx, draw the specific item xxx
			$item=substr($col,5);
			echo '<tr><td colspan=2>';
			echo $obj->drawSpecificItem($item); // the method must be implemented in the corresponidng class
			echo '</td></tr>';
		} else if (substr($col,0,6)=='_calc_') { // if field is _calc_xxxx, draw calculated item
			$item=substr($col,6);
			echo $obj->drawCalculatedItem($item); // the method must be implemented in the corresponidng class
		} else if (substr($col,0,5)=='_lib_') { // if field is just a caption
			$item=substr($col,5);
			if (strpos($obj->getFieldAttributes($col), 'nobr')!==false) {
				$nobr=true;
			}
			if ($obj->getFieldAttributes($col)!='hidden') {
				if ($nobr) echo '&nbsp;';
				echo  i18n($item);
				echo '&nbsp;';
			}

			if (!$nobr) {
				echo "</td></tr>";
			}
		} else if (substr($col,0,5)=='_Link') { // Display links to other objects
			$linkClass=null;
			if (strlen($col)>5) {
				$linkClass=substr($col,6);
			}
			drawLinksFromObject($val, $obj,$linkClass);
		} else if (substr($col,0,11)=='_Assignment') { // Display Assignments
			drawAssignmentsFromObject($val, $obj);
		} else if (substr($col,0,11)=='_Approver') { // Display Assignments
			drawApproverFromObject($val, $obj);
		} else if (substr($col,0,15)=='_VersionProject') { // Display Version Project
			drawVersionProjectsFromObject($val, $obj);
		} else if (substr($col,0,11)=='_Dependency') { // Display Dependencies
			$depType=(strlen($col)>11)?substr($col,12):"";
			drawDependenciesFromObject($val, $obj, $depType);
		} else if ($col=='_ResourceCost') { // Display ResourceCost
			drawResourceCostFromObject($val, $obj, false);
		} else if ($col=='_DocumentVersion') { // Display ResourceCost
			drawDocumentVersionFromObject($val, $obj, false);
		} else if ($col=='_ExpenseDetail') { // Display ExpenseDetail
			if ($obj->getFieldAttributes($col)!='hidden') {
				drawExpenseDetailFromObject($val, $obj, false);
			}
		} else if (substr($col,0,12)=='_TestCaseRun') { // Display TestCaseRun
			drawTestCaseRunFromObject($val, $obj);
		} else if (substr($col,0,1)=='_' and substr($col,0,6)!='_void_'
		and substr($col,0,7)!='_label_') { // field not to be displayed
			//
		} else {
			$attributes=''; $isRequired=false; $readOnly=false;
			$specificStyle='';
			if ( ($col=="idle" or $col=="done" or $col=="handled" or $col=="cancelled") and $objType ) {
				$lock='lock' . ucfirst($col);
				if ( ! $obj->id or (property_exists($objType,$lock) and $objType->$lock) ) {
					$attributes.=' readonly tabindex="-1"';
					$readOnly=true;
				}
			}
			if (strpos($obj->getFieldAttributes($col), 'required')!==false) {
				$attributes.=' required="true" missingMessage="' . i18n('messageMandatory',array($obj->getColCaption($col))). '" invalidMessage="' . i18n('messageMandatory',array($obj->getColCaption($col))) .'"';
				$isRequired=true;
			}
			if (strpos($obj->getFieldAttributes($col), 'hidden')!==false) {
				$hide=true;
			}
			if (strpos($obj->getFieldAttributes($col), 'nobr')!==false) {
				$nobr=true;
			}
			if (strpos($obj->getFieldAttributes($col), 'invisible')!==false) {
				$specificStyle.=' visibility:hidden';
			}
			if (strpos($obj->getFieldAttributes($col), 'title')!==false) {
				$attributes.=' title="' . $obj->getTitle($col) . '"';
			}

			if ( ! $canUpdate
			or (strpos($obj->getFieldAttributes($col), 'readonly')!==false)
			or $parentReadOnly
			or ($obj->idle==1 and $col!='idle' and $col!='idStatus') ) {
				$attributes.=' readonly tabindex="-1"';
				$readOnly=true;
			}
			if ($internalTable==0) {
				if (! is_object($val) and ! is_array($val) and ! $hide and !$nobr_before) {
					echo '<tr class="detail"><td class="label" style="width:'.$labelStyleWidth.';">';
					echo '<label for="' . $col . '" >' . htmlEncode($obj->getColCaption($col)) . '&nbsp;:&nbsp;</label>' . $cr;
					echo '</td>';
					if ($print and $outMode=="pdf") {
						echo '<td style="width: 120px">';
					} else {
						echo '<td width="90%">';
					}
				}
			} else {
				if ($internalTable % $internalTableCols == 0) {
					echo '</td></tr>' . $cr;
					echo '<tr class="detail">';
					echo '<td class="label" style="width:'.$labelStyleWidth.';">';
					if ($internalTableRowsCaptions[$internalTableCurrentRow]) {
						echo '<label>' . htmlEncode($obj->getColCaption($internalTableRowsCaptions[$internalTableCurrentRow])) . '&nbsp;:&nbsp;</label>';
					}
					echo '</td><td style="width:90%">';
					$internalTableCurrentRow++;
				} else {
					echo '</td><td class="detail">';
				}
			}
			$dataType = $obj->getDataType($col);
			$dataLength = $obj->getDataLength($col);
			//echo $col . "/" . $dataType . "/" . $dataLength;
			if ($dataLength) {
				if ($dataLength <= 3) {
					$fieldWidth=$verySmallWidth;
				} else if ($dataLength <= 10) {
					$fieldWidth=$smallWidth;
				} else if ($dataLength <= 25) {
					$fieldWidth=$mediumWidth;
				} else {
					$fieldWidth=$largeWidth;
				}
			}
			if (substr($col,0,2)=='id' and $dataType=='int' and strlen($col)>2
			and substr($col,2,1)==strtoupper(substr($col,2,1)) ) {
				$fieldWidth=$largeWidth;
			}
			if (strpos($obj->getFieldAttributes($col), 'Width')!==false) {
				if (strpos($obj->getFieldAttributes($col), 'smallWidth')!==false) {
					$fieldWidth=$smallWidth;
				}
				if (strpos($obj->getFieldAttributes($col), 'mediumWidth')!==false) {
					$fieldWidth=$mediumWidth;
				}
			}
			//echo $dataType . '(' . $dataLength . ') ';
			if ($included) {
				$name=' id="' . $classObj . '_' . $col . '" name="' . $classObj . '_' . $col . $extName . '" ';
				$nameBis=' id="' . $classObj . '_' . $col . 'Bis" name="' . $classObj . '_' . $col . 'Bis' . $extName . '" ';
				$fieldId=$classObj . '_' . $col;
			} else {
				$name=' id="' . $col . '" name="' . $col . $extName . '" ';
				$nameBis=' id="' . $col . 'Bis" name="' . $col . 'Bis' . $extName . '" ';
				$fieldId=$col;
			}
			// prepare the javascript code to be executed
			$colScript = $obj->getValidationScript($col);
			$colScriptBis="";
			if ($dataType=='datetime') {
				$colScriptBis = $obj->getValidationScript($col."Bis");
			}
			//if ($comboDetail) {
			//  $colScript=str_replace($col,$col . $extName,$colScript);
			//  $colScriptBis=str_replace($col,$col . $extName,$colScriptBis);
			//}
			if (is_object($val) ) {
				if (! $obj->isAttributeSetToField($col,'hidden')) {
					if ($col=='Origin') {
						  drawOrigin($val->originType, $val->originId, $obj, $col, $print);
					} else {
						// Draw an included object (recursive call) =========================== Type Object
						$visibileSubObject=true;
						if (get_class($val)=='WorkElement') {
							$hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$user->idProfile,'scope'=>'work'));
							if ($hWork and $hWork->id) {
								$visibility=SqlList::getFieldFromId('VisibilityScope', $hWork->rightAccess, 'accessCode', false);				
								if ($visibility!='ALL') {
									$visibileSubObject=false;
								}
							}
						}
						if ($visibileSubObject) {
						  drawTableFromObject($val, true, $readOnly);
						  $hide=true; // to avoid display of an extra field for the object and an additional carriage return
						}
					}
				}
			} else if (is_array($val)) {
				// Draw an array ====================================================== Type Array
				// TODO : impement array fields
				//echo $col . ' is an array' . $cr;
			} else if (substr($col,0,6)=='_void_') {
				// Empty field for tabular presentation
				//echo $col . ' is an array' . $cr;
				//
			} else if (substr($col,0,7)=='_label_') {
				$captionName=substr($col,7);
				echo '<label class="label shortlabel">' . i18n('col' . ucfirst($captionName)) . '&nbsp;:&nbsp;</label>';
			} else if ($print) {   //================================================ Printing areas
				if ($hide) { // hidden field
					// nothing
				} else  if (strpos($obj->getFieldAttributes($col), 'displayHtml')!==false) {
					// Display full HTML ================================================== Hidden field
					//echo '<div class="displayHtml">';
					if ($outMode=='pdf') {
						echo htmlRemoveDocumentTags($val);
					} else {
						echo $val;
					}
				} else if ($col=='id') { // id
					echo '<span style="color:grey;">#' . $val."&nbsp;&nbsp;&nbsp;</span>";
				} else if ($col=='password') {
					echo "..."; // nothing
				} else if ($dataType=='date' and $val!=null and $val != '') {
					echo htmlFormatDate($val);
				} else if ($dataType=='datetime' and $val!=null and $val != '') {
					echo htmlFormatDateTime($val,false);
				} else if ($dataType=='time' and $val!=null and $val != '') {
					echo htmlFormatTime($val,false);
				} else if ($col=='color' and $dataLength == 7 ) { // color
					echo '<table><tr><td style="width: 100px;">';
					echo '<div class="colorDisplay" readonly tabindex="-1" ';
					echo '  value="' . htmlEncode($val) . '" ';
					echo '  style="width: ' . $smallWidth / 2 . 'px; ';
					echo ' color: ' . $val . '; ';
					echo ' background-color: ' . $val . ';"';
					echo ' >';
					echo '</div>';
					echo '</td>';
					if ($val!=null and $val!='') {
						//echo '<td  class="detail">&nbsp;(' . htmlEncode($val) . ')</td>';
					}
					echo '</tr></table>';
				} else if ($dataType=='int' and $dataLength==1) { // boolean
					$checkImg="checkedKO.png";
					if ($val!='0' and ! $val==null) {
						$checkImg= 'checkedOK.png';
					}
					echo '<img src="img/' . $checkImg . '" />';
				} else if (substr($col,0,2)=='id' and $dataType=='int' and strlen($col)>2
				and substr($col,2,1)==strtoupper(substr($col,2,1)) ) { // Idxxx
					echo htmlEncode(SqlList::getNameFromId(substr($col,2),$val));
				} else  if ($dataLength > 100) { // Text Area (must reproduce BR, spaces, ...
					echo htmlEncode($val,'print');
					$fldFull='_'.$col.'_full';
					if ($outMode=='pdf' and isset($obj->$fldFull)) {
						echo '<img src="../view/css/images/doubleArrowDown.png" />';
					}
				} else if ($dataType=='decimal' and (substr($col, -4,4)=='Cost' or substr($col,-6,6)=='Amount' or $col=='amount') ) {
					if ($currencyPosition=='after') {
						echo htmlEncode($val,'print') . ' ' . $currency;
					} else {
						echo $currency . ' ' . htmlEncode($val,'print');
					}
				} else if ($dataType=='decimal' and substr($col, -4,4)=='Work') {
					echo Work::displayWork($val) . ' ' . Work::displayShortWorkUnit();
				} else {
					if ($obj->isFieldTranslatable($col))  {
						$val=i18n($val);
					}
					if (0 and $internalTable==0) {
						echo '<div style="width: 80%;"> ';
						if (strpos($obj->getFieldAttributes($col), 'html')!==false) {
							echo $val;
						} else {
							echo htmlEncode($val,'print');
						}
						echo '</div>';
					} else {
						if (strpos($obj->getFieldAttributes($col), 'html')!==false) {
							echo $val;
						} else {
							echo htmlEncode($val,'print');
						}
					}
				}
			} else if ($hide) {
				// Don't draw the field =============================================== Hidden field
				if (! $print) {
					echo '<div dojoType="dijit.form.TextBox" type="hidden"  ';
					echo $name;
					echo ' value="' . htmlEncode($val) . '" ></div>';
				}
			} else if (strpos($obj->getFieldAttributes($col), 'displayHtml')!==false) {
				// Display full HTML ================================================== Simple Display html field
				echo '<div class="displayHtml">';
				echo $val;
				echo '</div>';
			} else if ($col=='id') {
				// Draw Id (only visible) ============================================= ID
				// id is only visible
				$ref=$obj->getReferenceUrl();
				echo '<span style="font-size:8pt;color:#AAAAAA">';
				echo '  <a href="' . $ref . '" onClick="copyDirectLinkUrl();return false;" title="'.i18n("rightClickToCopy").'">';
				echo '    <span style="color:grey;vertical-align:middle;">#</span>';
				echo '    <span dojoType="dijit.form.TextBox" type="text"  ';
				echo       $name;
				echo '     class="display" ';
				echo '     readonly tabindex="-1" style="width: ' . $smallWidth . 'px;" ' ;
				echo '     value="' . htmlEncode($val) . '" >';
				echo '    </span>';
        echo '  </a>';
        echo '</span>';
        echo '<input disabled=disabled type="text" onClick="this.select();" id="directLinkUrlDiv" style="display:none;font-size:9px; color: #000000;position :absolute; top: 9px; left: 157px; border: 0;background: transparent;width:'.$largeWidth.'px;" value="'.$ref.'" />';
			  $alertLevelArray=$obj->getAlertLevel(true);
        $alertLevel=$alertLevelArray['level'];
        $colorAlert="background-color:#FFFFFF";
        if ($alertLevel!='NONE') {
          if ($alertLevel=='ALERT') {
            $colorAlert='background-color:#FFAAAA;';
          }   else if ($alertLevel=='WARNING') {
            $colorAlert='background-color:#FFFFAA;';         
          }
          echo '<span style="width:20px; position: absolute; left: 5px;" id="alertId" >';
          if ($alertLevel=='ALERT') {
            echo '<image src="../view/css/images/iconAlert32.png" />';
          } else {
          	echo '<image src="../view/css/images/iconDecision32.png" />';
          }
          echo '</span>';
          echo '<div dojoType="dijit.Tooltip" connectId="alertId" position="below">';
          echo $alertLevelArray['description'];
          echo '</div>';
        }
			} else if ($col=='reference') {
				// Draw reference (only visible) ============================================= ID
				// id is only visible
				echo '<span dojoType="dijit.form.TextBox" type="text"  ';
				echo $name;
				echo ' class="display" ';
				echo ' readonly tabindex="-1" style="width: ' . ($largeWidth - $smallWidth -20) . 'px;" ' ;
				echo ' value="' . htmlEncode($val) . '" ></span>';
			} else if ($col=='password') {
				$paramDefaultPassword=Parameter::getGlobalParameter('paramDefaultPassword');
				// Password specificity  ============================================= PASSWORD
				echo '<button id="resetPassword" dojoType="dijit.form.Button" showlabel="true"';
				echo $attributes;
				$salt=hash('sha256',"projeqtor".date('YmdHis'));
				echo ' title="' . i18n('helpResetPassword') . '" >';
				echo '<span>' . i18n('resetPassword') . '</span>';
				echo '<script type="dojo/connect" event="onClick" args="evt">';
				echo '  dijit.byId("salt").set("value","'.$salt.'");';
				echo '  dijit.byId("crypto").set("value","sha256");';
				echo '  dojo.byId("password").value="' . hash('sha256',$paramDefaultPassword.$salt) . '";';
				echo '  formChanged();';
				echo '  showInfo("' . i18n('passwordReset',array($paramDefaultPassword)) . '");';
				echo '</script>';
				echo '</button>';
				// password not visible
				echo '<input type="password"  ';
				echo $name;
				echo ' class="display" style="width:150px"';
				echo ' readonly tabindex="-1" ' ;
				echo ' value="' . htmlEncode($val) . '" />';
			} else if ($col=='color' and $dataLength == 7 ){
				// Draw a color selector ============================================== COLOR
				echo "<table><tr><td class='detail'>";
				echo '<input xdojoType="dijit.form.TextBox" class="colorDisplay" type="text" readonly tabindex="-1" ';
				echo $name;
				echo $attributes;
				echo '  value="' . htmlEncode($val) . '" ';
				echo '  style="border: 0;width: ' . $smallWidth . 'px; ';
				echo ' color: ' . $val . '; ';
				if ($val) {
					echo ' background-color: ' . $val . ';';
				} else {
					echo ' background-color: transparent;';
				}
				echo '" />';
				//echo $colScript;
				//echo '</div>';
				echo '</td><td class="detail">';
				if (! $readOnly) {
					echo '<div id="' . 'colorButton" dojoType="dijit.form.DropDownButton"  ';
					//echo '  style="width: 100px; background-color: ' . $val . ';"';
					echo ' showlabel="false" iconClass="colorSelector" >';
					echo '  <span>Select color</span>';
					echo '  <div dojoType="dijit.ColorPalette" >';
					echo '    <script type="dojo/method" event="onChange" >';
					echo '      var fld=dojo.byId("color");';
					echo '      fld.style.color=this.value;';
					echo '      fld.style.backgroundColor=this.value;';
					echo '      fld.value=this.value;';
					echo '      formChanged();';
					echo '    </script>';
					echo '  </div>';
					echo '</div>';
				}
				echo '</td><td>';
				if (! $readOnly) {
					echo '<button id="resetColor" dojoType="dijit.form.Button" showlabel="true"';
					echo ' title="' . i18n('helpResetColor') . '" >';
					echo '<span>' . i18n('resetColor') . '</span>';
					echo '<script type="dojo/connect" event="onClick" args="evt">';
					echo '      var fld=dojo.byId("color");';
					echo '      fld.style.color="transparent";';
					echo '      fld.style.backgroundColor="transparent";';
					echo '      fld.value="";';
					echo '      formChanged();';
					echo '</script>';
					echo '</button>';
				}
				echo '</td></tr></table>';
			} else if ($col=='durationSla'){
				// Draw a color selector ============================================== SLA as a duration
				echo '<div dojoType="dijit.form.TextBox" class="colorDisplay" type="text"  ';
				echo $name;
				echo $attributes;
				echo '  value="' . htmlEncode($val) . '" ';
				echo '  style="width: 30px; "';
				echo ' >';
				echo '</div>';
				echo i18n("shortDay") . "  ";
				echo '<div dojoType="dijit.form.TextBox" class="colorDisplay" type="text"  ';
				echo $attributes;
				echo '  value="' . htmlEncode($val) . '" ';
				echo '  style="width: 30px; "';
				echo ' >';
				echo '</div>';
				echo i18n("shortHour") . "  ";
				echo '<div dojoType="dijit.form.TextBox" class="colorDisplay" type="text"  ';
				echo $attributes;
				echo '  value="' . htmlEncode($val) . '" ';
				echo '  style="width: 30px; "';
				echo ' >';
				echo '</div>';
				echo i18n("shortMinute") . "  ";
			} else if ($dataType=='date') {
				// Draw a date ======================================================== DATE
				if ($col=='creationDate' and ($val=='' or $val==null) and ! $obj->id) {
					$val=date('Y-m-d');
				}
				echo '<div dojoType="dijit.form.DateTextBox" ';
				echo $name;
				echo $attributes;
				echo ' invalidMessage="' . i18n('messageInvalidDate') . '"';
				echo ' type="text" maxlength="' . $dataLength . '" ';
				//echo ' constraints="{datePattern:\'yy-MM-dd\'}" ';
				echo ' style="width:' . $dateWidth . 'px; text-align: center;' . $specificStyle . '" class="input" ';
				echo ' value="' . htmlEncode($val) . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $colScript;
				echo '</div>';
			} else if ($dataType=='datetime') {
				// Draw a date ======================================================== DATETIME
				if (strlen($val>11)) {
					$valDate=substr($val,0,10);
					$valTime=substr($val,11);
				} else {
					$valDate=$val;
					$valTime='';
				}
				if ($col=='creationDateTime' and ($val=='' or $val==null) and ! $obj->id) {
					$valDate=date('Y-m-d');
					$valTime=date("H:i");
				}
				echo '<div dojoType="dijit.form.DateTextBox" ';
				echo $name;
				echo $attributes;
				echo ' invalidMessage="' . i18n('messageInvalidDate') . '"';
				echo ' type="text" maxlength="10" ';
				//echo ' constraints="{datePattern:\'yy-MM-dd\'}" ';
				echo ' style="width:' . $dateWidth . 'px; text-align: center;' . $specificStyle . '" class="input" ';
				echo ' value="' . $valDate . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $colScript;
				echo '</div>';
				echo '<div dojoType="dijit.form.TimeTextBox" ';
				echo $nameBis;
				echo $attributes;
				echo ' invalidMessage="' . i18n('messageInvalidTime') . '"';
				echo ' type="text" maxlength="5" ';
				//echo ' constraints="{datePattern:\'yy-MM-dd\'}" ';
				echo ' style="width:50px; text-align: center;' . $specificStyle . '" class="input" ';
				echo ' value="T' . $valTime . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $colScriptBis;
				echo '</div>';
			} else if ($dataType=='time') {
				// Draw a date ======================================================== TIME
				if ($col=='creationTime' and ($val=='' or $val==null) and ! $obj->id) {
					$val=date("H:i");
				}
				echo '<div dojoType="dijit.form.TimeTextBox" ';
				echo $name;
				echo $attributes;
				echo ' invalidMessage="' . i18n('messageInvalidTime') . '"';
				echo ' type="text" maxlength="' . $dataLength . '" ';
				//echo ' constraints="{datePattern:\'yy-MM-dd\'}" ';
				echo ' style="width:50px; text-align: center;' . $specificStyle . '" class="input" ';
				echo ' value="T' . $val . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $colScript;
				echo '</div>';
			} else if ($dataType=='int' and $dataLength==1) {
				// Draw a boolean (as a checkbox ====================================== BOOLEAN
				echo '<div dojoType="dijit.form.CheckBox" type="checkbox" ';
				echo $name;
				echo $attributes;
				echo ' style="' . $specificStyle . '" ';
				//echo ' value="' . $col . '" ' ;
				if ($val!='0' and ! $val==null) { echo 'checked'; }
				echo ' >';
				echo $colScript;
				echo '</div>';
			} else if (substr($col,0,2)=='id' and $dataType=='int' and strlen($col)>2
			and substr($col,2,1)==strtoupper(substr($col,2,1))) {
				// Draw a reference to another object (as combo box) ================== IDxxxxx => ComboBox
				$displayComboButtonCol=$displayComboButton;
				$displayDirectAccessButton=true;
				$canCreateCol=false;
				if ($comboDetail or strpos($attributes, 'readonly')!==false) {
					$displayComboButtonCol=false;
				}
				if (strpos($obj->getFieldAttributes($col), 'nocombo')!==false) {
					$displayComboButtonCol=false;
					$displayDirectAccessButton=false;
				}
				if ($displayComboButtonCol or $displayDirectAccessButton) {
					$idMenu=($col=="idResourceSelect")?'menuResource':'menu' . substr($col,2);
					$menu=SqlElement::getSingleSqlElementFromCriteria('Menu', array('name'=>$idMenu));
					$crit=array();
					$crit['idProfile']=$user->idProfile;
					$crit['idMenu']=$menu->id;
					$habil=SqlElement::getSingleSqlElementFromCriteria('Habilitation', $crit);
					if ($habil and $habil->allowAccess) {
						$accessRight=SqlElement::getSingleSqlElementFromCriteria('AccessRight', array('idMenu'=>$menu->id, 'idProfile'=>$user->idProfile));
						if ($accessRight) {
							$accessProfile=new AccessProfile($accessRight->idAccessProfile);
							if ($accessProfile) {
								$accessScope=new AccessScope($accessProfile->idAccessScopeCreate);
								if ($accessScope and $accessScope->accessCode!='NO') {
									$canCreateCol=true;
								}
							}
						}
					} else {
						$displayComboButtonCol=false;
						$displayDirectAccessButton=false;
					}
				}
				if ($col=='idProject') {
					if ($obj->id==null) {
						if (array_key_exists('project',$_SESSION) and ! $obj->$col) {
							$val=$_SESSION['project'];
						}
						$accessRight=securityGetAccessRight('menu' . $classObj, 'create');
					} else {
						$accessRight=securityGetAccessRight('menu' . $classObj, 'update');
					}
					if ( securityGetAccessRight('menu' . $classObj, 'read')=='PRO' and $classObj!='Project') {
						$isRequired=true;
					}
				}
				$critFld=null;
				$critVal=null;
				$valStore='';
				if ($col=='idResource' or $col=='idActivity'
				or $col=='idVersion' or $col=='idOriginalVersion' or $col=='idTargetVersion'
				or $col=='idTestCase' or $col=='idRequirement'
				or $col=='idContact' or $col=='idTicket' or $col=='idUser') {
					if (property_exists($obj,'idProject')
					and get_class($obj)!='Project' and get_class($obj)!='Affectation') {
						if ($obj->id) {
							$critFld='idProject';
							$critVal=$obj->idProject;
						} else if (array_key_exists('project',$_SESSION) and $_SESSION['project']!='*') {
							$critFld='idProject';
							$critVal=$_SESSION['project'];
						} else {
							$table=SqlList::getList('Project','name',null);
							if (count($table)>0) {
								foreach ($table as $idTable=>$valTable) {
									$firstId=$idTable;
									break;
								}
								$critFld='idProject';
								$critVal=$firstId;
							}
						}			
					}
				}
				// if version and idProduct exists and is set : criteria is product
				if ( isset($obj->idProduct)
				and ($col=='idVersion' or $col=='idOriginalVersion' or $col=='idTargetVersion'
				or $col=='idTestCase' or $col=='idRequirement') ) {
					$critFld='idProduct';
					$critVal=$obj->idProduct;
				}
				if ( get_class($obj)=='IndicatorDefinition'  ) {
					if ($col=='idIndicator') {
						$critFld='idIndicatorable';
						$critVal=$obj->idIndicatorable;
					}
					if ($col=='idType') {
						$critFld='scope';
						$critVal=SqlList::getNameFromId('Indicatorable', $obj->idIndicatorable);
					}
					if ($col=='idWarningDelayUnit' or $col=='idAlertDelayUnit') {
						$critFld='idIndicator';
						$critVal=$obj->idIndicatorable;
					}

				}
				if ( get_class($obj)=='PredefinedNote'  ) {
					if ($col=='idType') {
						$critFld='scope';
						$critVal=SqlList::getNameFromId('Textable', $obj->idTextable, false);
					}
				}
				if ( get_class($obj)=='StatusMail'  ) {
					if ($col=='idType') {
						$critFld='scope';
						$critVal=SqlList::getNameFromId('Mailable', $obj->idMailable, false);
					}
				}
				if ($displayComboButtonCol or $displayDirectAccessButton) {
					$fieldWidth -= 20;
				}
				if ($nobr_before or strpos($obj->getFieldAttributes($col), 'size1/3')!==false) {
					$fieldWidth=$fieldWidth/3-3;
				}
				$hasOtherVersion=false;
				$versionType='';
				$otherVersion='';
				if ( substr($col,7)=='Version'
				or ($col=='idOriginalVersion' and isset($obj->_OtherOriginalVersion))
				or ($col=='idTargetVersion' and isset($obj->_OtherTargetVersion))) {
					$versionType=substr($col,2);
					$otherVersion='_Other'.$versionType;
					if (isset($obj->$otherVersion) and ! $obj->isAttributeSetToField($col,'hidden') 
					    and ! $obj->isAttributeSetToField($col,'readonly') and $canUpdate and !$obj->idle) {
						$hasOtherVersion=true;
						$fieldWidth -= 20;
					}
				}
				echo '<select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html" ';
				//echo '  style="width: ' . $fieldWidth . 'px;' . $specificStyle . '"';
				echo '  style="width: ' . ($fieldWidth) . 'px;' . $specificStyle . '"';
				echo $name;
				echo $attributes;
				echo $valStore;
				echo ' >';
				htmlDrawOptionForReference($col, $val, $obj, $isRequired,$critFld, $critVal);
				echo $colScript;
				echo '</select>';
				if ($displayComboButtonCol) {
					echo '<button id="' . $col . 'Button" dojoType="dijit.form.Button" showlabel="false"';
					echo ' title="' . i18n('showDetail') . '" style="position: relative; top:1px"';
					echo ' iconClass="iconView">';
					echo ' <script type="dojo/connect" event="onClick" args="evt">';
				  echo '   if (clickTimer) clearTimeout(clickTimer);';
          echo '   clickTimer = setTimeout(function() { showDetail("' . $col . '",' . (($canCreateCol)?1:0) . '); }, 300);';
					echo ' </script>';
					echo ' <script type="dojo/method" event="onDblClick" args="evt">';
					echo '  clearTimeout(clickTimer);';
					echo '  var linkedSelect=dijit.byId("'.$fieldId.'");';
					echo '  if (linkedSelect && trim(linkedSelect.get("value")) ) {';
					echo '    gotoElement("' . substr($col,2) . '","' .$val. '");';
					echo '  } else {';
					echo '  showAlert("'.i18n('cannotGoto').'");';
          echo '  }';
          echo ' </script>';
					echo '</button>';
				} else if ($displayDirectAccessButton) {
					echo '<button id="' . $col . 'Button" dojoType="dijit.form.Button" showlabel="false"';
          echo ' title="' . i18n('showDirectAccess') . '" style="position: relative; top:1px"';
          echo ' iconClass="iconDirectAccess" >';
          echo ' <script type="dojo/connect" event="onClick" args="evt">';
          echo '  var linkedSelect=dijit.byId("'.$fieldId.'");';
          echo '  if (linkedSelect && trim(linkedSelect.get("value")) ) {';
          echo '    gotoElement("' . substr($col,2) . '","' .$val. '");';
          echo '  } else {';
          echo '  showAlert("'.i18n('cannotGoto').'");';
          echo '  }';
          echo ' </script>';
          echo '</button>';
				}
				if ($hasOtherVersion) {
					if ($obj->id and $canUpdate) {
						echo '<span style="text-align:center; vertical-align:middle;">';
						echo '<img src="css/images/smallButtonAdd.png" style="position:relative; top:2px; left:2px;"'
						. 'onClick="addOtherVersion(' . "'" . $versionType . "'"
						. ');" ';
						echo ' title="' . i18n('otherVersionAdd') . '" class="smallButton"/> ';
						echo '</span>';
					}
					if (count($obj->$otherVersion)>0) {
						drawOtherVersionFromObject($obj->$otherVersion, $obj, $versionType);
					}
				}
			} else if (strpos($obj->getFieldAttributes($col), 'display')!==false) {
				echo '<div ';
				echo ' class="display" ';
				//echo ' style="width:10%; border:1px solid red;"';
				echo' >';
				if (strpos($obj->getFieldAttributes($col), 'html')!==false) {
					echo $val;
				} else {
					echo htmlEncode($val);
				}
				if (! $print) {
					echo '<input type="hidden" ' . $name . ' value="' . htmlEncode($val) . '" />';
				}
				if (strtolower(substr($col,-8,8))=='progress') {
					echo '&nbsp;%';
				}
				echo '</div>';

			} else if ($dataType=='int' or $dataType=='decimal'){
				// Draw a number field ================================================ NUMBER
				$isCost=false;
				$isWork=false;
				$isDuration=false;
				$isPercent=false;
				if ($dataType=='decimal' and (substr($col, -4,4)=='Cost' or substr($col,-6,6)=='Amount'  or $col=='amount') ) {
					$isCost=true;
					$fieldWidth=$smallWidth;
				}
				if ($dataType=='decimal' and (substr($col, -4,4)=='Work') ) {
					$isWork=true;
					$fieldWidth=$smallWidth;
				}
				if ($dataType=='int' and (substr($col, -8,8)=='Duration') ) {
					$isDuration=true;
					$fieldWidth=$smallWidth;
				}
				if ($dataType=='int' and strtolower(substr($col, -8,8)=='progress')) {
					$isPercent=true;
				}
				$spl=explode(',',$dataLength);
				$dec=0;
				if (count($spl)>1) {
					$dec=$spl[1];
				}
				$ent=$spl[0]-$dec;
				$max=substr('99999999999999999999',0,$ent);
				if ($isCost and $currencyPosition=='before') {
					echo $currency;
				}
				echo '<div dojoType="dijit.form.NumberTextBox" ';
				echo $name;
				echo $attributes;
				//echo ' style="text-align:right; width: ' . $fieldWidth . 'px;' . $specificStyle . '" ';
				echo ' style="width: ' . $fieldWidth . 'px;' . $specificStyle . '" ';
				echo ' constraints="{min:-' . $max . ',max:' . $max . '}" ';
				echo ' class="input" ';
				//echo ' layoutAlign ="right" ';
				echo ' value="' . (($isWork)?Work::displayWork($val):htmlEncode($val)) . '" ';
				//echo ' value="' . htmlEncode($val) . '" ';
				echo ' >';
				echo $colScript;
				echo '</div>';
				if ($isCost and $currencyPosition=='after') {
					echo $currency;
				}
				if ($isWork) {
					echo Work::displayShortWorkUnit();
				}
				if ($isDuration) {
					echo i18n("shortDay");
				}
				if ($isPercent) {
					echo '%';
				}
			} else if ($dataLength > 100 and ! array_key_exists('testingMode', $_REQUEST) ){
				// Draw a long text (as a textarea) =================================== TEXTAREA
				echo '<textarea dojoType="dijit.form.Textarea" ';
				echo ' onKeyPress="if (isUpdatableKey(event.keyCode)) {formChanged();}" '; // hard coding default event
				echo $name;
				echo $attributes;
				if (strpos($attributes, 'readonly')>0) {
					$specificStyle.=' color:grey; background:none; background-color: #F0F0F0; ';
				}
				echo ' rows="2" style="width: ' . $largeWidth . 'px;' . $specificStyle . '" ';
				echo ' maxlength="' . $dataLength . '" ';
				//        echo ' maxSize="4" ';
				echo ' class="input" ' . '>';
				echo htmlEncode($val);
				//echo $colScript; // => this leads to the display of script in textarea
				echo '</textarea>';
			} else {
				// Draw defaut data (text medium size) ================================ TEXT (default)
				if ($obj->isFieldTranslatable($col)) {
					$fieldWidth = $fieldWidth / 2;
				}
				echo '<div type="text" dojoType="dijit.form.ValidationTextBox" ';
				echo $name;
				echo $attributes;
				echo '  style="width: ' . $fieldWidth . 'px;' . $specificStyle . '" ';
				echo ' trim="true" maxlength="' . $dataLength . '" class="input" ';
				echo ' value="' . htmlEncode($val) . '" ';
				if ($obj->isFieldTranslatable($col)) {
					echo ' title="' . i18n("msgTranslatable") . '" ';
				}
				echo ' >';
				echo $colScript;
				echo '</div>';
				if ($obj->isFieldTranslatable($col)) {
					echo '<div dojoType="dijit.form.TextBox" type="text"  ';
					echo ' class="display" ';
					echo ' readonly tabindex="-1" style="width: ' . $fieldWidth . 'px;" ' ;
					echo ' title="' . i18n("msgTranslation") . '" ';
					echo ' value="' . htmlEncode(i18n($val)) . '" ></div>';
				}
			}
			if ($internalTable>0) {
				$internalTable--;
				if ($internalTable==0) {
					echo "</td></tr></table><table>";
				}
			} else {
				if ($internalTable==0 and !$hide and !$nobr) {
					echo '</td></tr>' . $cr;
				}
			}
		}
	}
	if ( ! $included) {
		if ($currentCol==0) {
			if ($section and !$print) {
				echo '</div>';
			}
			echo '</table>';
		} else {
			echo '</table>';
			if ($section and !$print) {
				echo '</div>';
			}
			echo '</td></tr></table>';
		}
	}
	if ($outMode=='pdf') {
	  $cpt=0;
		foreach ($obj as $col => $val) {
		  if (substr($col,0,1)=='_' and substr($col,-5)=='_full') {
		  	$cpt++;
			  $section=substr($col,1,strlen($col)-6);
			  //echo '</page><page>';
			  if ($cpt==1) echo '<page><br/>';
			  echo '<table style="width:'.$printWidth.'px;"><tr><td class="section">' . $obj->getColCaption($section) .'</td></tr></table>';
			  echo htmlEncode($val,'print');
			  echo '<br/><br/>';
		  }
	  } 
	  if ($cpt>0) echo '</page>';  
	}
}

function drawDocumentVersionFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->locked) {
		$canUpdate=false;
	}
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	$typeEvo="EVO";
	$type=new VersioningType($obj->idVersioningType);
	$typeEvo=$type->code;
	$num="";
	$vers=new DocumentVersion($obj->idDocumentVersion);
	if ($typeEvo=='SEQ') {
		$num=intVal($vers->name)+1;
	}
	echo '<tr>';
	if (! $print) {
		$statusTable=SqlList::getList('Status','name',null);
		reset($statusTable);
		echo '<td class="assignHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" '
			. 'onClick="addDocumentVersion(' . "'" . key($statusTable) . "'"
			. ",'" . $typeEvo . "'"
			. ",'" . $num . "'"
			. ",'" . $vers->name . "'"
			. ",'" . $vers->name . "'"
			. ');" ';
			echo ' title="' . i18n('addDocumentVersion') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="assignHeader" style="width:15%" >' . i18n('colIdVersion'). '</td>';
	echo '<td class="assignHeader" style="width:15%" >' . i18n('colDate'). '</td>';
	echo '<td class="assignHeader" style="width:15%">' . i18n('colIdStatus') . '</td>';
	echo '<td class="assignHeader" style="width:' . ( ($print)?'55':'45' ) . '%">' . i18n('colFile') . '</td>';
	echo '</tr>';
	$preserveFileName=Parameter::getGlobalParameter('preserveUploadedFileName');
	if (!$preserveFileName) {$preserveFileName="NO";}
	foreach($list as $version) {
		echo '<tr>';
		if (! $print) {
			echo '<td class="assignData" style="text-align:center; white-space: nowrap;">';
			if (! $print) {
				echo '<a href="../tool/download.php?class=DocumentVersion&id='. $version->id . '"';
				echo ' target="printFrame" title="' . i18n('helpDownload') . "\n". (($preserveFileName=='YES')?$version->fileName:$version->fullName). '"><img src="css/images/smallButtonDownload.png" /></a>';
			}
			if ($canUpdate and ! $print) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editDocumentVersion(' . "'" . $version->id . "'"
				. ",'" . $version->version . "'"
				. ",'" . $version->revision . "'"
				. ",'" . $version->draft . "'"
				. ",'" . $version->versionDate . "'"
				. ",'" . $version->idStatus . "'"
				. ",'" . $version->isRef . "'"
				. ",'" . $typeEvo . "'"
				. ",'" . $version->name . "'"
				. ",'" . $version->name . "'"
				. ",'" . $version->name . "'"
				. ');" '
				. 'title="' . i18n('editDocumentVersion') . '" class="smallButton"/> ';
			}
			if ($canUpdate and ! $print )  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeDocumentVersion(' . "'" . $version->id . "'"
				. ', \'' . $version->name . '\');" '
				. 'title="' . i18n('removeDocumentVersion') . '" class="smallButton"/> ';
			}
			echo '<input type="hidden" id="documentVersion_'.$version->id.'" name="documentVersion_'.$version->id.'" value="'.$version->description.'"/>';
			echo '</td>';
		}
		echo '<td class="assignData">' . (($version->isRef)?'<b>':'') . htmlEncode($version->name)  . (($version->isRef)?'</b>':'');
		if ($version->approved) { echo '&nbsp;&nbsp;<img src="../view/img/check.png" height="12px" title="' . i18n('approved') . '"/>';}
		echo '</td>';
		echo '<td class="assignData">' . htmlFormatDate($version->versionDate) . '</td>';
		$objStatus=new Status($version->idStatus);
		echo '<td class="assignData" style="width:15%">' . colorNameFormatter($objStatus->name . "#split#" . $objStatus->color) . '</td>';
		echo '<td class="assignData" title="' . htmlencode($version->description) . '">';
		echo '  <table><tr >';
		echo '   <td>';
		echo     htmlEncode($version->fileName,'print');
		echo '   </td>';
		if ($version->description and ! $print) {
			echo '<td>&nbsp;&nbsp;<img src="img/note.png" /></td>';
		}
		echo '</tr></table>';
		echo '</td></tr>';
	}
	echo '</table></td></tr>';
}

function drawOrigin ($refType, $refId, $obj, $col, $print) {
	echo '<tr class="detail"><td class="label" style="width:10%;">';
	echo '<label for="' . $col . '" >' . htmlEncode($obj->getColCaption($col)) . '&nbsp;:&nbsp;</label>';
	echo '</td>';
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	if ($print) {
		echo '<td style="width: 120px">';
	} else {
		echo '<td width="90%">';
	}
	if ($refType and $refId) {
		echo '<table width="100%"><tr height="20px"><td xclass="noteData" width="1%" xvalign="top">';
		if (! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonRemove.png" ';
			echo ' onClick="removeOrigin(\'' . $obj->$col->id . '\',\'' . $refType . '\',\'' . $refId . '\');" title="' . i18n('removeOrigin') . '" class="smallButton"/> ';
		}
		echo '</td><td width="5%" xclass="noteData" xvalign="top">';
		echo '&nbsp;&nbsp;' . i18n($refType) . '&nbsp;#' . $refId . '&nbsp;:&nbsp;';
		echo '</td><td xclass="noteData" style="height: 15px">';
		$orig=new $refType($refId);
		echo htmlEncode($orig->name);
		echo '</td></tr></table>';
	} else {
		echo '<table><tr height="20px"><td>';
		if ($obj->id and! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addOrigin();" title="' . i18n('addOrigin') . '" class="smallButton"/> ';
		}
		echo '</td></tr></table>';
	}
}

function drawHistoryFromObjects($refresh=false) {
	global $cr, $print, $treatedObjects, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$inList="( ('x',0)"; // initialize with non existing element, to avoid error if 1 only object involved
	foreach($treatedObjects as $obj) {
		//$inList.=($inList=='')?'(':', ';
		if ($obj->id) {
			$inList.=", ('" . get_class($obj) . "', " . Sql::fmtId($obj->id) . ")";
		}
	}
	$inList.=')';
	$where = ' (refType, refId) in ' . $inList;
	$order = ' operationDate desc, id asc';
	$hist=new History();
	$historyList=$hist->getSqlElementsFromCriteria(null,false,$where,$order);
	echo '<table style="width:100%;">';
	echo '<tr>';
	echo '<td class="historyHeader" style="width:10%">' . i18n('colOperation'). '</td>';
	echo '<td class="historyHeader" style="width:14%">' . i18n('colColumn'). '</td>';
	echo '<td class="historyHeader" style="width:23%">' . i18n('colValueBefore'). '</td>';
	echo '<td class="historyHeader" style="width:23%">' . i18n('colValueAfter'). '</td>';
	echo '<td class="historyHeader" style="width:15%">' . i18n('colDate') . '</td>';
	echo '<td class="historyHeader" style="width:15%">' . i18n('colUser'). '</td>';
	echo '</tr>';
	$stockDate=null;
	$stockUser=null;
	$stockOper=null;
	foreach($historyList as $hist) {
		if (substr($hist->colName,0,25)=='subDirectory|Attachement|'
		or substr($hist->colName,0,19)=='idTeam|Attachement|') {
			continue;
		}
		$colName=($hist->colName==null)?'':$hist->colName;
		$split=explode('|', $colName);
		if (count($split)==3) {
			$colName=$split[0];
			$refType=$split[1];
			$refId=$split[2];
			$refObject='';
		} else if (count($split)==4) {
			$refObject=$split[0];
			$colName=$split[1];
			$refType=$split[2];
			$refId=$split[3];
		} else {
			$refType='';
			$refId='';
			$refObject='';
		}
		$curObj=null; $dataType=""; $dataLength=0;
		$hide=false;
		$oper=i18n('operation' . ucfirst($hist->operation) );
		$user=$hist->idUser;
		$user=SqlList::getNameFromId('User',$user);
		$date=htmlFormatDateTime($hist->operationDate);
		$class="NewOperation";
		if ($stockDate==$hist->operationDate
		and $stockUser==$hist->idUser
		and $stockOper==$hist->operation) {
			$oper="";
			$user="";
			$date="";
			$class="ContinueOperation";
		}
		if ($colName!='' or $refType!="") {
			if ($refType) {
				if ($refType=="TestCase") {
					$curObj=new TestCaseRun();
				} else {
					$curObj=new $refType();
				}
			} else {
				$curObj=new $hist->refType();
			}
			if ($curObj) {
				if ($refType) {
					$colCaption=i18n($refType). ' #' . $refId . ' ' . $curObj->getColCaption($colName);
					if ($refObject) {
						$colCaption=i18n($refObject) . ' - ' . $colCaption;
					}
				} else {
					$colCaption=$curObj->getColCaption($colName);
				}
				$dataType=$curObj->getDataType($colName);
				$dataLength=$curObj->getDataLength($colName);
				if (strpos($curObj->getFieldAttributes($colName), 'hidden')!==false) {
					$hide=true;
				}
			}
		} else {
			$colCaption='';
		}
		if (substr($hist->refType,-15)=='PlanningElement' and $hist->operation=='insert') {
			$hide=true;
		}
		if (! $hide) {
			echo '<tr>';
			echo '<td class="historyData'. $class .'" width="10%">' . $oper . '</td>';

			echo '<td class="historyData" width="14%">' . $colCaption . '</td>';
			$oldValue=$hist->oldValue;
			$newValue=$hist->newValue;
			if ($dataType=='int' and $dataLength==1) { // boolean
				$oldValue=htmlDisplayCheckbox($oldValue);
				$newValue=htmlDisplayCheckbox($newValue);
			} else if (substr($colName,0,2)=='id' and strlen($colName)>2
			and strtoupper(substr($colName,2,1))==substr($colName,2,1)) {
				if ($oldValue!=null and $oldValue!='') {
					if ($oldValue==0 and $colName=='idStatus') {
						$oldValue='';
					} else {
						$oldValue=SqlList::getNameFromId(substr($colName,2),$oldValue);
					}
				}
				if ($newValue!=null and $newValue!='') {
					$newValue=SqlList::getNameFromId(substr($colName,2),$newValue);
				}
			} else if ($colName=="color") {
				$oldValue=htmlDisplayColored("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$oldValue);
				$newValue=htmlDisplayColored("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$newValue);
			} else if ($dataType=='date') {
				$oldValue=htmlFormatDate($oldValue);
				$newValue=htmlFormatDate($newValue);
			} else if ($dataType=='datetime') {
				$oldValue=htmlFormatDateTime($oldValue);
				$newValue=htmlFormatDateTime($newValue);
			} elseif ($dataType=='decimal' and substr($colName, -4,4)=='Work') {
				$oldValue = Work::displayWork($oldValue) . ' ' . Work::displayShortWorkUnit();
				$newValue = Work::displayWork($newValue) . ' ' . Work::displayShortWorkUnit();
			} else {
				$oldValue=htmlEncode($oldValue,'print');
				$newValue=htmlEncode($newValue,'print');
			}
			echo '<td class="historyData" width="23%">' . $oldValue . '</td>';
			echo '<td class="historyData" width="23%">' . $newValue . '</td>';
			echo '<td class="historyData'. $class .'" width="15%">' . $date . '</td>';
			echo '<td class="historyData'. $class .'" width="15%">' . $user . '</td>';
			echo '</tr>';
			$stockDate=$hist->operationDate;
			$stockUser=$hist->idUser;
			$stockOper=$hist->operation;
		}
	}
	echo '<tr>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '<td class="historyDataClosetable">&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
}

function drawNotesFromObject($obj, $refresh=false) {
	global $cr, $print, $user, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	if (isset($obj->_Note)) {
		$notes=$obj->_Note;
	} else {
		$notes=array();
	}
	echo '<input type="hidden" id="noteIdle" value="'.$obj->idle.'" />';
	echo '<table width="100%">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="noteHeader" style="width:5%">';
		if ($obj->id!=null and ! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addNote();" title="' . i18n('addNote') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="noteHeader" style="width:5%">' . i18n('colId') . '</td>';
	echo '<td class="noteHeader" style="width:' . ( ($print)?'65':'60' ) . '%">' . i18n('colNote'). '</td>';
	echo '<td class="noteHeader" style="width:15%">' . i18n('colDate') . '</td>';
	echo '<td class="noteHeader" style="width:15%">' . i18n('colUser'). '</td>';
	echo '</tr>';
	foreach($notes as $note) {
		$ress=new Resource($user->id);
		if ($user->id==$note->idUser or $note->idPrivacy==1 or ($note->idPrivacy==2 and $ress->idTeam==$note->idTeam)) {
			$userId=$note->idUser;
			$userName=SqlList::getNameFromId('User',$userId);
			$creationDate=$note->creationDate;
			$updateDate=$note->updateDate;
			if ($updateDate==null) {$updateDate='';}
			echo '<tr>';
			if (! $print) {
				echo '<td class="noteData" style="text-align:center;">';
				if ($note->idUser==$user->id and ! $print and $canUpdate) {
					echo ' <img src="css/images/smallButtonEdit.png" onClick="editNote(' . $note->id . ',' . $note->idPrivacy. ');" title="' . i18n('editNote') . '" class="smallButton"/> ';
					echo ' <img src="css/images/smallButtonRemove.png" onClick="removeNote(' . $note->id . ');" title="' . i18n('removeNote') . '" class="smallButton"/> ';
				}
				echo '</td>';
			}
			echo '<td class="noteData">#' . $note->id  . '</td>';
			echo '<td class="noteData"><table style="width:100%"><tr><td>';
			if (! $print) {
				echo '<input type="hidden" id="note_' . $note->id . '" value="' . htmlEncode($note->note,'none') .'"/>';
			}
			// ADDED BRW
      $strDataHTML = htmlEncode($note->note,'print');
      $strDataHTML = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $strDataHTML);
      echo $strDataHTML;
      // END ADDED BRW
			echo "</td>";
			if ($note->idPrivacy==3) {
				echo '<td style="width:16px;vertical-align: top;" title="' . i18n('private') .'"><img src="img/private.png" /></td>';
			} else if ($note->idPrivacy==2) {
				echo '<td style="width:16px;vertical-align: top;" title="' . i18n('team') . " : " . SqlList::getNameFromId('Team', $note->idTeam) .'"><img src="img/team.png" /></td>';
			}
			echo '</tr></table>';
			echo '</td>';
			echo '<td class="noteData">' . htmlFormatDateTime($creationDate) . '<br/>';
			if ($note->fromEmail) {echo '<b>'.i18n('noteFromEmail').'</b>';}
			echo '<i>' . htmlFormatDateTime($updateDate) . '</i></td>';
			echo '<td class="noteData">' . $userName . '</td>';
			echo '</tr>';
		}
	}
	echo '<tr>';
	if (! $print) {
		echo '<td class="noteDataClosetable">&nbsp;</td>';
	}
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
}

function drawBillLinesFromObject($obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale;
	//$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	$lock=false;
	if ($obj->done or $obj->idle or $obj->billingType=="N") {
		$lock=true;
	}
	if (isset($obj->_BillLine)) {
		$lines=$obj->_BillLine;
	} else {
		$lines=array();
	}
	echo '<input type="hidden" id="billLineIdle" value="'.$obj->idle.'" />';
	echo '<table width="100%">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="noteHeader" style="width:5%">';  //changer le header
		if ($obj->id!=null and ! $print and ! $lock) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addBillLine(' . (count($lines)+1) . ');" title="' . i18n('addLine') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="noteHeader" style="width:5%">' . i18n('colId') . '</td>';
	echo '<td class="noteHeader" style="width:5%">' . i18n('colLineNumber') . '</td>';
	echo '<td class="noteHeader" style="width:5%">' . i18n('colQuantity') . '</td>';
	echo '<td class="noteHeader" style="width:25%">' . i18n('colDescription') . '</td>';
	echo '<td class="noteHeader" style="width:35%">' . i18n('colDetail') . '</td>';
	echo '<td class="noteHeader" style="width:10%">' . i18n('colPrice') . '</td>';
	echo '<td class="noteHeader" style="width:15%">' .  strtolower(i18n('sum')) . '</td>';
	echo '</tr>';

	$fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::INTEGER );
	$fmtd = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
	$lines=array_reverse($lines);
	foreach($lines as $line) {
		echo '<tr>';
		if ( ! $print) {
			echo '<td class="noteData" style="text-align:center;">';
			if ($lock==0) {
				echo ' <img src="css/images/smallButtonEdit.png" onClick="editBillLine(
		      ' . "'" . $line->id . "'" 	        
		      . ",'" . $line->line . "'"
		      . ",'" . $fmtd->format($line->quantity) . "'"
		      . ",'" . $line->idTerm . "'"
		      . ",'" . $line->idResource . "'"
		      . ",'" . $line->idActivityPrice . "'"
		      . ",'" . $line->startDate . "'"
		      . ",'" . $line->endDate . "'"
		      . ",'" . $fmtd->format($line->price) . "'"
		      . ');" title="' . i18n('editLine') . '" class="smallButton"/> ';
		      echo ' <img src="css/images/smallButtonRemove.png"'
		      .' onClick="removeBillLine(' . $line->id . ');"'
		      .' title="' . i18n('removeLine') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="noteData">#' . $line->id  . '</td>';
		echo '<td class="noteData">' . $line->line . '</td>';
		echo '<td class="noteData">' . $line->quantity . '</td>';
		echo '<td class="noteData">' . htmlEncode($line->description,'withBR');
		echo '<input type="hidden" id="billLineDescription_' . $line->id . '" value="' . $line->description . '" />';
		echo '</td>';
		echo '<td class="noteData">' . htmlEncode($line->detail,'withBR');
		echo '<input type="hidden" id="billLineDetail_' . $line->id . '" value="' . $line->detail . '" />';
		echo '</td>';
		echo '<td class="noteData">' . $line->price . '</td>';
		echo '<td class="noteData">' . $line->amount . '</td>';
		echo '</tr>';
	}
	echo '<tr>';
	if (! $print) {
		echo '<td class="noteDataClosetable">&nbsp;</td>';
	}
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '<td class="noteDataClosetable">&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
}

function drawAttachementsFromObject($obj, $refresh=false) {
	global $cr, $print, $user, $comboDetail;
	if ($comboDetail) {
		return;
	}
	echo '<input type="hidden" id="attachementIdle" value="'.$obj->idle.'" />';
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	if (isset($obj->_Attachement)) {
		$attachements=$obj->_Attachement;
	} else {
		$attachements=array();
	}
	echo '<table width="100%">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="attachementHeader" style="width:5%">';
		if ($obj->id!=null and ! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addAttachement(\'file\');" title="' . i18n('addAttachement') . '" class="smallButton"/> ';
			echo '<img src="css/images/smallButtonLink.png" onClick="addAttachement(\'link\');" title="' . i18n('addHyperlink') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="attachementHeader" style="width:5%">' . i18n('colId') . '</td>';
	echo '<td class="attachementHeader" style="width:10%;">' . i18n('colSize'). '</td>';
	echo '<td class="attachementHeader" style="width:5%;">' . i18n('colType'). '</td>';
	echo '<td class="attachementHeader" style="width:' . ( ($print)?'50':'45' ) . '%">' . i18n('colFile'). '</td>';
	echo '<td class="attachementHeader" style="width:15%">' . i18n('colDate') . '</td>';
	echo '<td class="attachementHeader" style="width:15%">' . i18n('colUser'). '</td>';
	echo '</tr>';
	foreach($attachements as $attachement) {
		$userId=$attachement->idUser;
		$ress=new Resource($user->id);
		if ($user->id==$attachement->idUser or $attachement->idPrivacy==1 or ($attachement->idPrivacy==2 and $ress->idTeam==$attachement->idTeam)) {
			$userName=SqlList::getNameFromId('User',$userId);
			$creationDate=$attachement->creationDate;
			echo '<tr>';
			if (! $print) {
				echo '<td class="attachementData" style="text-align:center;width:5%"">';
				if ($attachement->fileName and $attachement->subDirectory and ! $print) {
					echo '<a href="../tool/download.php?class=Attachement&id='. $attachement->id . '"';
					echo ' target="printFrame" title="' . i18n('helpDownload') . '"><img src="css/images/smallButtonDownload.png" /></a>';
				}
				if ($attachement->link and ! $print) {
					echo '<a href="' . $attachement->link .'"';
					echo ' target="#" title="' . urldecode($attachement->link) . '"><img src="css/images/smallButtonLink.png" /></a>';
				}
				if ($attachement->idUser==$user->id and ! $print and $canUpdate) {
					echo ' <img src="css/images/smallButtonRemove.png" onClick="removeAttachement(' . $attachement->id . ');" title="' . i18n('removeAttachement') . '" class="smallButton"/>';
				}
				echo '</td>';
			}
			echo '<td class="attachementData" style="width:5%;">#' . $attachement->id  . '</td>';
			echo '<td class="attachementData" style="width:10%;text-align:center;">' . htmlGetFileSize($attachement->fileSize) . '</td>';
			echo '<td class="attachementData" style="width:5%;text-align:center;">';
		  if ($attachement->isThumbable()) {
        echo '<img src="'. getImageThumb($attachement->getFullPathFileName(),32).'" '
           . ' title="'.$attachement->fileName.'" style="cursor:pointer"'
           . ' onClick="showImage(\'Attachement\',\''.$attachement->id.'\',\''.$attachement->fileName.'\');" />';
      } else {
			  echo htmlGetMimeType($attachement->mimeType,$attachement->fileName);
      }
			echo  '</td>';
			echo '<td class="attachementData" style="width:' . ( ($print)?'50':'45' ) . '%" title="' . $attachement->description . '">';
			echo '<table style="width:100%"><tr >';
			echo ' <td>';
			echo htmlEncode($attachement->fileName,'print');
			echo ' </td>';
			if ($attachement->description and ! $print) {
				echo '<td style="width:18px; vertical-align: top;"><img src="img/note.png" /></td>';
			}
			if ($attachement->idPrivacy==3) {
				echo '<td style="width:18px;vertical-align: top;" title="' . i18n('private') .'"><img src="img/private.png" /></td>';
			} else if ($attachement->idPrivacy==2) {
				echo '<td style="width:18px;vertical-align: top;" title="' . i18n('team') . " : " . SqlList::getNameFromId('Team', $attachement->idTeam) .'"><img src="img/team.png" /></td>';
			}
			echo '</tr></table>';
			echo '</td>';
			 
			echo '<td class="attachementData" style="width:15%">' . htmlFormatDateTime($creationDate) . '<br/></td>';
			echo '<td class="attachementData" style="width:15%">' . $userName . '</td>';
			echo '</tr>';
		}
	}
	echo '<tr>';
	if (! $print) {
		echo '<td class="attachementDataClosetable">&nbsp;';
		echo '<input type="hidden" name="nbAttachements" id="nbAttachements" value="'.count($attachements).'" />';
		echo '</td>';
	}
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '<td class="attachementDataClosetable">&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
}

function drawLinksFromObject($list, $obj, $classLink, $refresh=false) {
  if ($obj->isAttributeSetToField("_Link", "hidden")) { return; }
	global $cr, $print, $user, $comboDetail;
	if ($comboDetail) {
		return;
	}
	if (get_class($obj)=='Document') {
		$dv=new DocumentVersion();
		$lstVers=$dv->getSqlElementsFromCriteria(array('idDocument'=>$obj->id));
		foreach ($lstVers as $dv) {
			$crit="(ref1Type='DocumentVersion' and ref1Id=".$dv->id.")";
			$crit.="or (ref2Type='DocumentVersion' and ref2Id=".$dv->id.")";
			$lnk=new Link();
			$lstLnk=$lnk->getSqlElementsFromCriteria(null, null, $crit);
			foreach ($lstLnk as $lnk) {
        if ($lnk->ref1Type=='DocumentVersion') {
        	$lnk->ref1Type='Document';
        	$lnk->ref1Id=$obj->id;
        } else {
        	$lnk->ref2Type='Document';
        	$lnk->ref2Id=$obj->id;
        }
				$list[]=$lnk;
			}
		}
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan="2" style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="linkHeader" style="width:5%">';
		if ($obj->id!=null and ! $print and $canUpdate) {
			$linkable=SqlElement::getSingleSqlElementFromCriteria('Linkable', array('name'=>get_class($obj)));
			$default=$linkable->idDefaultLinkable;
			echo '<img src="css/images/smallButtonAdd.png" onClick="addLink(' . "'" . $classLink  . "','" . $default . "'" . ');" title="' . i18n('addLink') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	if ( ! $classLink ) {
		echo '<td class="linkHeader" style="width:10%">' . i18n('colType') . '</td>';
	}
	echo '<td class="linkHeader" style="width:' . ( ($print)?'10':'5' ) . '%">' . i18n('colId') . '</td>';
	echo '<td class="linkHeader" style="width:' . ( ($classLink)?'45':'35' ) . '%">' . i18n('colName') . '</td>';
	//if ($classLink and property_exists($classLink, 'idStatus')) {
	echo '<td class="linkHeader" style="width:15%">' . i18n('colIdStatus'). '</td>';
	//}
	echo '<td class="linkHeader" style="width:15%">' . i18n('colDate') . '</td>';
	echo '<td class="linkHeader" style="width:15%">' . i18n('colUser'). '</td>';
	echo '</tr>';
	foreach($list as $link) {
		$linkObj=null;
		if ($link->ref1Type==get_class($obj) and $link->ref1Id==$obj->id) {
			$linkObj=new $link->ref2Type($link->ref2Id);
		} else {
			$linkObj=new $link->ref1Type($link->ref1Id);
		}
		$userId=$link->idUser;
		$userName=SqlList::getNameFromId('User',$userId);
		$creationDate=$link->creationDate;
		$prop='_Link_'.get_class($linkObj);
		if( $classLink or ! property_exists($obj,$prop ) ) {
			$gotoObj=(get_class($linkObj)=='DocumentVersion')?new Document($linkObj->idDocument):$linkObj;
			$canGoto=(securityCheckDisplayMenu(null,get_class($gotoObj))
			and securityGetAccessRightYesNo('menu' . get_class($gotoObj), 'read', $gotoObj)=="YES")?true:false;
			echo '<tr>';
			if (substr(get_class($linkObj),0,7)=='Context') {
				$classLinkName=SqlList::getNameFromId('ContextType', substr(get_class($linkObj),7,1));
			} else {
				$classLinkName=i18n(get_class($linkObj));
			}
			if (! $print) {
				echo '<td class="linkData" style="text-align:center;width:5%;">';
				if ( $canGoto
				and (get_class($linkObj)=='DocumentVersion' or get_class($linkObj)=='Document')
				and isset( $gotoObj->idDocumentVersion) and $gotoObj->idDocumentVersion ) {
					echo '<a href="../tool/download.php?class=' . get_class($linkObj) . '&id='. $linkObj->id . '"';
					echo ' target="printFrame" title="' . i18n('helpDownload') . '"><img src="css/images/smallButtonDownload.png" /></a>';
				}
				if ($canUpdate) {
					echo '  <img src="css/images/smallButtonRemove.png" onClick="removeLink(' . "'" . $link->id . "','" . get_class($linkObj) . "','" . $linkObj->id . "','" . $classLinkName . "'" . ');" title="' . i18n('removeLink') . '" class="smallButton"/> ';
				}
				echo '</td>';
			}
			if ( ! $classLink ) {
				echo '<td class="linkData" style="width:10%">' . $classLinkName . '</td>';
			}
			echo '<td class="linkData" style="width:' . ( ($print)?'10':'5' ) . '%">#' . $linkObj->id;
			echo '</td>';
			$goto="";
			if (! $print and $canGoto) {
				$goto=' onClick="gotoElement(' . "'" . get_class($gotoObj) . "','" . $gotoObj->id . "'" . ');" style="cursor: pointer;" ';
			}
			echo '<td class="linkData" ' . $goto . ' style="width:' . ( ($classLink)?'45':'35' ) . '%" title="' . $link->comment . '">';
			echo (get_class($linkObj)=='DocumentVersion')?htmlEncode($linkObj->fullName):htmlEncode($linkObj->name);
			if ($link->comment and ! $print) {
				echo '&nbsp;&nbsp;<img src="img/note.png" />';
			}
			echo '</td>';
			if (property_exists($linkObj, 'idStatus')) {
				$objStatus=new Status($linkObj->idStatus);
				//$color=$objStatus->color;
				//$foreColor=getForeColor($color);
				//echo '<td class="linkData"><table width="100%"><tr><td style="background-color: ' . $objStatus->color . '; color:' . $foreColor . ';width: 100%;">' . $objStatus->name . '</td></tr></table></td>';
				echo '<td class="dependencyData"  style="width:15%">' . colorNameFormatter($objStatus->name . "#split#" . $objStatus->color) . '</td>';
			}
			echo '<td class="dependencyData"  style="width:15%">' . htmlFormatDateTime($creationDate) . '<br/></td>';
			echo '<td class="dependencyData"  style="width:15%">' . $userName . '</td>';
			echo '</tr>';
		}
	}
	echo '</table></td></tr>';
}

function drawApproverFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $comboDetail;
	if ( $comboDetail ) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="dependencyHeader" style="width:5%">';
		if ($obj->id!=null and ! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addApprover();" title="' . i18n('addApprover') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="dependencyHeader" style="width:' . ( ($print)?'10':'5' ) . '%">' . i18n('colId') . '</td>';
	echo '<td class="dependencyHeader" style="width:40%">' . i18n('colName') . '</td>';
	echo '<td class="dependencyHeader" style="width:50%">' . i18n('colIdStatus'). '</td>';
	echo '</tr>';
	if ($obj and get_class($obj)=='Document') {
		$docVers=new DocumentVersion($obj->idDocumentVersion);
	}
	foreach($list as $app) {
		$appName=SqlList::getNameFromId('Affectable',$app->idAffectable);
		echo '<tr>';
		if (! $print) {
			echo '<td class="dependencyData" style="text-align:center;">';
			if ($canUpdate) {
				echo '  <img src="css/images/smallButtonRemove.png" onClick="removeApprover(' . "'" . $app->id . "','" . $appName .  "'" . ');" title="' . i18n('removeApprover') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="dependencyData">#' . $app->id  . '</td>';
		echo '<td class="dependencyData">' . htmlEncode($appName) . '</td>';
		echo '<td class="dependencyData">';
		$approved=0;
		$compMsg="";
		$date="";
		$approverId=null;
		if ($obj and get_class($obj)=='Document') {
			$crit=array('refType'=>'DocumentVersion','refId'=>$obj->idDocumentVersion,'idAffectable'=>$app->idAffectable);
			$versApp=SqlElement::getSingleSqlElementFromCriteria('Approver',$crit);
			if ($versApp->id) {
				$approved=$versApp->approved;
				$compMsg=' ' .  $docVers->name;
				$date=" (".htmlFormatDateTime($versApp->approvedDate,false). ")";
				$approverId=$versApp->id;
			}
		} else {
			$approved=$app->approved;
			$approverId=$app->id;
			$date=" (".htmlFormatDateTime($app->approvedDate,false). ")";
		}
		if ($approved) {
			echo '<img src="../view/img/check.png" height="12px"/>&nbsp;';
			echo i18n("approved"). $compMsg . $date;
		} else {
			echo i18n("notApproved"). $compMsg;
			if ($user->id==$app->idAffectable) {
				echo '&nbsp;&nbsp;<button dojoType="dijit.form.Button" showlabel="true" >';
				echo i18n('approveNow');
				echo '  <script type="dojo/connect" event="onClick" args="evt">';
				echo '   approveItem(' . $approverId . ');';
				echo '  </script>';
				echo '</button>';
			}
		}

		echo '</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawDependenciesFromObject($list, $obj, $depType, $refresh=false) {
	global $cr, $print, $user, $comboDetail;
	if ( $comboDetail ) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	$canEdit=$canUpdate;
	if (get_class($obj)=="Term" or get_class($obj)=="Requirement" or get_class($obj)=="TestCase") {
		$canEdit=false;
	}
	if(get_class($obj)=="Term")
	{
		if($obj->idBill) $canUpdate=false;
	}
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="dependencyHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canUpdate) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addDependency(' . "'" . $depType . "'" . ');" title="' . i18n('addDependency' . $depType) . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="dependencyHeader" style="width:15%">' . i18n('colType') . '</td>';
	echo '<td class="dependencyHeader" style="width:' . ( ($print)?'15':'5' ) . '%">' . i18n('colId') . '</td>';
	echo '<td class="dependencyHeader" style="width:55%">' . i18n('colName') . '</td>';
	echo '<td class="dependencyHeader" style="width:15%">' . i18n('colIdStatus'). '</td>';
	echo '</tr>';
	foreach($list as $dep) {
		$depObj=null;
		if ($dep->predecessorRefType==get_class($obj) and $dep->predecessorRefId==$obj->id) {
			$depObj=new $dep->successorRefType($dep->successorRefId);
			//$depType="Successor";
		} else {
			$depObj=new $dep->predecessorRefType($dep->predecessorRefId);
			//$depType="Predecessor";
		}
		echo '<tr>';
		if (! $print) {
			echo '<td class="dependencyData" style="text-align:center;">';
			if ($canEdit) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. ' onClick="editDependency(' . "'" . $depType . "','" . $dep->id . "','" . SqlList::getIdFromName('Dependable',i18n(get_class($depObj))) . "','" . get_class($depObj) . "','" . $depObj->id . "','" . $dep->dependencyDelay . "'" . ');" '
				. ' title="' . i18n('editDependency' . $depType) . '" class="smallButton"/> ';
			}
			if ($canUpdate) {
				echo '  <img src="css/images/smallButtonRemove.png" onClick="removeDependency(' . "'" . $dep->id . "','" . get_class($depObj) . "','" . $depObj->id . "'" . ');" title="' . i18n('removeDependency' . $depType) . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="dependencyData">' . i18n(get_class($depObj)) . '</td>';
		echo '<td class="dependencyData">#' . $depObj->id  . '</td>';
		echo '<td class="dependencyData"';
		$goto="";
		if (securityCheckDisplayMenu(null,get_class($depObj))
		and securityGetAccessRightYesNo('menu' . get_class($depObj), 'read', $depObj)=="YES") {
			$goto=' onClick="gotoElement(' . "'" . get_class($depObj) . "','" . $depObj->id . "'" . ');" style="cursor: pointer;" ';
		}
		if (! $print) { echo $goto;}
		echo '>' . htmlEncode($depObj->name) ;
		if ($dep->dependencyDelay!=0 and $canEdit) {
			echo  '&nbsp;<span style="background-color:#FFF8DC; color:#696969; border:1px solid #A9A9A9;font-size:80%;" title="'.i18n("colDependencyDelay") .'">&nbsp;'
			. $dep->dependencyDelay . '&nbsp;' . i18n('shortDay')
			. '&nbsp;</span>' ;
		}
		echo '</td>';
		if (property_exists($depObj,'idStatus')) {
			$objStatus=new Status($depObj->idStatus);
		} else {
			$objStatus=new Status();
		}
		//$color=$objStatus->color;
		//$foreColor=getForeColor($color);
		//echo '<td class="dependencyData"><table><tr><td style="background-color: ' . $objStatus->color . '; color:' . $foreColor . ';">' . $objStatus->name . '</td></tr></table></td>';
		//echo '<td class="dependencyData" style="background-color: ' . $objStatus->color . '; color:' . $foreColor . ';">' . $objStatus->name . '</td>';
		echo '<td class="dependencyData" style="width:15%">' . colorNameFormatter($objStatus->name . "#split#" . $objStatus->color) . '</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawAssignmentsFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	$pe=new PlanningElement();
	$pe->setVisibility();
	$workVisible=($pe->_workVisibility=='ALL')?true:false;
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="assignHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle and $workVisible) {
			echo '<img src="css/images/smallButtonAdd.png" ';
			echo ' onClick="addAssignment(\'' . Work::displayShortWorkUnit() . '\',\''. Work::getWorkUnit() . '\',\''.Work::getHoursPerDay().'\');" ';
			echo ' title="' . i18n('addAssignment') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="assignHeader" style="width:' . ( ($print)?'40':'30' ) . '%">' . i18n('colIdResource') . '</td>';
	echo '<td class="assignHeader" style="width:15%" >' . i18n('colRate'). '</td>';
	if ($workVisible) {
		echo '<td class="assignHeader" style="width:15%">' . i18n('colAssigned'). ' (' . Work::displayShortWorkUnit() . ')' . '</td>';
		echo '<td class="assignHeader"style="width:15%">' . i18n('colReal'). ' (' . Work::displayShortWorkUnit() . ')' . '</td>';
		echo '<td class="assignHeader" style="width:15%">' . i18n('colLeft'). ' (' . Work::displayShortWorkUnit() . ')' . '</td>';
	}
	echo '</tr>';
	$fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
	foreach($list as $assignment) {
		echo '<tr>';
		if (! $print) {
			echo '<td class="assignData" style="text-align:center;">';
			if ($canUpdate and ! $print and $workVisible) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editAssignment(' . "'" . $assignment->id . "'"
				. ",'" . $assignment->idResource . "'"
				. ",'" . $assignment->idRole . "'"
				. ",'" . ($assignment->dailyCost * 100) . "'"
				. ",'" . $assignment->rate . "'"
				. ",'" . Work::displayWork($assignment->assignedWork)*100 . "'"
				. ",'" . Work::displayWork($assignment->realWork)*100 . "'"
				. ",'" . Work::displayWork($assignment->leftWork)*100 . "'"
				. ",'" . Work::displayShortWorkUnit() . "'"
				. ');" '
				. 'title="' . i18n('editAssignment') . '" class="smallButton"/> ';
				echo '<input type="hidden" id="comment_assignment_'.$assignment->id.'" value="'.$assignment->comment.'" />';
			}
			if ($assignment->realWork==0 and $canUpdate and ! $print and $workVisible )  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeAssignment(' . "'" . $assignment->id . "','"
				. Work::displayWork($assignment->realWork)*100 . "','"
				. htmlEncode(SqlList::getNameFromId('Resource', $assignment->idResource),'quotes')  . "'" . ');" '
				. 'title="' . i18n('removeAssignment') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="assignData" ';
		if (! $print) {echo 'title="' . htmlEncodeJson($assignment->comment) . '"';}
		echo '>';
		echo '<table><tr>';
		$goto="";
		if (!$print and securityCheckDisplayMenu(null,'Resource')
		and securityGetAccessRightYesNo('menuResource', 'read', '')=="YES") {
			$goto=' onClick="gotoElement(\'Resource\',\'' . $assignment->idResource . '\');" style="cursor: pointer;" ';
		}
		echo '<td '. $goto .'>' . SqlList::getNameFromId('Resource', $assignment->idResource);
		echo ($assignment->idRole)?' ('.SqlList::getNameFromId('Role', $assignment->idRole).')':'';
		echo '</td>';
		if ($assignment->comment and ! $print) {
			echo '<td>&nbsp;&nbsp;<img src="img/note.png" /></td>';
		}
		echo '</tr></table>';
		echo '</td>';
		echo '<td class="assignData" align="center">' . $assignment->rate  . '</td>';
		if ($workVisible) {
			echo '<td class="assignData" align="right">' . $fmt->format(Work::displayWork($assignment->assignedWork))  . '</td>';
			echo '<td class="assignData" align="right">' . $fmt->format(Work::displayWork($assignment->realWork))  . '</td>';
			echo '<td class="assignData" align="right">' . $fmt->format(Work::displayWork($assignment->leftWork))  . '</td>';
		}
		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawExpenseDetailFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	//  $pe=new PlanningElement();
	//  $pe->setVisibility();
	//  $workVisible=($pe->_workVisibility=='ALL')?true:false;
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (! $print) {
		echo '<td class="assignHeader" style="width:5%">';
		//if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle and $workVisible) {
		if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addExpenseDetail();" title="' . i18n('addExpenseDetail') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="assignHeader" style="width:' . ( ($print)?'15':'10' ) . '%">' . i18n('colDate') . '</td>';
	echo '<td class="assignHeader"style="width:35%">' . i18n('colName'). '</td>';
	echo '<td class="assignHeader" style="width:15%" >' . i18n('colType'). '</td>';
	echo '<td class="assignHeader"style="width:25%">' . i18n('colDetail'). '</td>';
	//  if ($workVisible) {
	echo '<td class="assignHeader" style="width:10%">' . i18n('colAmount'). '</td>';
	//  }
	echo '</tr>';
	$fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
	foreach($list as $expenseDetail) {
		echo '<tr>';
		if (! $print) {
			echo '<td class="assignData" style="text-align:center;">';
			//      if ($canUpdate and ! $print and $workVisible) {
			if ($canUpdate and ! $print) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editExpenseDetail(' . "'" . $expenseDetail->id . "'"
				. ",'" . $expenseDetail->idExpense . "'"
				. ",'" . $expenseDetail->idExpenseDetailType . "'"
				. ",'" . $expenseDetail->expenseDate . "'"
				. ",'" . $fmt->format($expenseDetail->amount) . "'"
				. ');" '
				. 'title="' . i18n('editExpenseDetail') . '" class="smallButton"/> ';
			}
			//      if ($canUpdate and ! $print and $workVisible )  {
			if ($canUpdate and ! $print)  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeExpenseDetail(' . "'" . $expenseDetail->id . "'" . ');" '
				. 'title="' . i18n('removeExpenseDetail') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="assignData" >' . htmlFormatDate($expenseDetail->expenseDate) . '</td>';
		echo '<td class="assignData" ';
		if (! $print) {echo 'title="' . htmlEncodeJson($expenseDetail->description) . '"';}
		echo '>' . $expenseDetail->name ;
		if ($expenseDetail->description and ! $print) {
			echo '<span>&nbsp;&nbsp;<img src="img/note.png" /></span>';
		}
		echo '<input type="hidden" id="expenseDetail_' . $expenseDetail->id . '" value="' . htmlEncode($expenseDetail->name,'none') .'"/>';

		echo '</td>';
		echo '<td class="assignData" >' . SqlList::getNameFromId('ExpenseDetailType', $expenseDetail->idExpenseDetailType) .'</td>';
		echo '<td class="assignData" >';
		echo $expenseDetail->getFormatedDetail();
		echo '</td>';
		echo '<td class="assignData" style="text-align:right;">' . htmlDisplayCurrency($expenseDetail->amount) . '</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawResourceCostFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	$pe=new PlanningElement();
	$pe->setVisibility();
	$workVisible=($pe->_workVisibility=='ALL')?true:false;
	if (! $workVisible) return;
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	$funcList=' ';
	foreach($list as $rcost) {
		$key='#' . $rcost->idRole . '#';
		if (strpos($funcList, $key)===false) {
			$funcList.=$key;
		}
	}
	if (! $print) {
		echo '<td class="assignHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addResourceCost(\'' . $obj->id . '\', \'' . $obj->idRole . '\',\''. $funcList . '\');" title="' . i18n('addResourceCost') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="assignHeader" style="width:' . (($print)?'40':'30') . '%">' . i18n('colIdRole') . '</td>';
	echo '<td class="assignHeader" style="width:20%">' . i18n('colCost'). '</td>';
	echo '<td class="assignHeader" style="width:20%">' . i18n('colStartDate'). '</td>';
	echo '<td class="assignHeader" style="width:20%">' . i18n('colEndDate'). '</td>';

	echo '</tr>';
	$fmt = new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );
	foreach($list as $rcost) {
		echo '<tr>';
		if (! $print) {
			echo '<td class="assignData" style="text-align:center;">';
			if (! $rcost->endDate and $canUpdate and ! $print) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editResourceCost(' . "'" . $rcost->id . "'"
				. ",'" . $rcost->idResource . "'"
				. ",'" . $rcost->idRole . "'"
				. ",'" . $rcost->cost*100 . "'"
				. ",'" . $rcost->startDate . "'"
				. ",'" . $rcost->endDate . "'"
				. ');" '
				. 'title="' . i18n('editResourceCost') . '" class="smallButton"/> ';
			}
			if (! $rcost->endDate and $canUpdate and ! $print)  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeResourceCost(' . "'" . $rcost->id . "'"
				. ",'" . $rcost->idRole . "'"
				. ",'" . SqlList::getNameFromId('Role', $rcost->idRole)  . "'"
				. ",'" . htmlFormatDate($rcost->startDate) . "'"
				. ');" '
				. 'title="' . i18n('removeResourceCost') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		echo '<td class="assignData" align="left">' . SqlList::getNameFromId('Role', $rcost->idRole) . '</td>';
		echo '<td class="assignData" align="right">' . htmlDisplayCurrency($rcost->cost);
		if($rcost->idRole==7) echo " / " . i18n('shortMonth');
		else echo " / " . i18n('shortDay');
		echo '</td>';
		echo '<td class="assignData" align="center">' . htmlFormatDate($rcost->startDate) . '</td>';
		echo '<td class="assignData" align="center">' . htmlFormatDate($rcost->endDate) . '</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawVersionProjectsFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (get_class($obj)=='Project') {
		$idProj=$obj->id;
		$idVers=null;
	} else if (get_class($obj)=='Version') {
		$idProj=null;
		$idVers=$obj->id;
	}
	if (! $print) {
		echo '<td class="assignHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canUpdate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" onClick="addVersionProject(\'' . $idVers . '\', \'' . $idProj . '\');" title="' . i18n('addVersionProject') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	if ($idProj) {
		echo '<td class="assignHeader" style="width:' . (($print)?'50':'40') . '%">' . i18n('colIdVersion') . '</td>';
	} else {
		echo '<td class="assignHeader" style="width:' . (($print)?'50':'40') . '%">' . i18n('colIdProject') . '</td>';
	}
	echo '<td class="assignHeader" style="width:20%">' . i18n('colStartDate'). '</td>';
	echo '<td class="assignHeader" style="width:20%">' . i18n('colEndDate'). '</td>';
	echo '<td class="assignHeader" style="width:10%">' . i18n('colIdle'). '</td>';

	echo '</tr>';
	foreach($list as $vp) {
		echo '<tr>';
		if (! $print) {
			echo '<td class="assignData" style="text-align:center;">';
			if ($canUpdate and ! $print) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editVersionProject(' . "'" . $vp->id . "'"
				. ",'" . $vp->idVersion . "'"
				. ",'" . $vp->idProject . "'"
				. ",'" . $vp->startDate . "'"
				. ",'" . $vp->endDate . "'"
				. ",'" . $vp->idle . "'"
				. ');" '
				. 'title="' . i18n('editVersionProject') . '" class="smallButton"/> ';
			}
			if ($canUpdate and ! $print)  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeVersionProject(' . "'" . $vp->id . "'"
				. ');" '
				. 'title="' . i18n('removeVersionProject') . '" class="smallButton"/> ';
			}
			echo '</td>';
		}
		$goto="";
		if ($idProj) {
			if (!$print and securityCheckDisplayMenu(null,'Version')
			and securityGetAccessRightYesNo('menuVersion', 'read', '')=="YES") {
				$goto=' onClick="gotoElement(\'Version\',\'' . $vp->idVersion . '\');" style="cursor: pointer;" ';
			}
			echo '<td class="assignData" align="left"' . $goto . '>' . htmlEncode(SqlList::getNameFromId('Version', $vp->idVersion)) . '</td>';
		} else {
			if (!$print and securityCheckDisplayMenu(null,'Project')
			and securityGetAccessRightYesNo('menuProject', 'read', '')=="YES") {
				$goto=' onClick="gotoElement(\'Project\',\'' . $vp->idProject . '\');" style="cursor: pointer;" ';
			}
			echo '<td class="assignData" align="left"' . $goto . '>' . htmlEncode(SqlList::getNameFromId('Project', $vp->idProject)) . '</td>';
		}
		echo '<td class="assignData" align="center">' . htmlFormatDate($vp->startDate) . '</td>';
		echo '<td class="assignData" align="center">' . htmlFormatDate($vp->endDate) . '</td>';
		echo '<td class="assignData" align="center"><img src="../view/img/checked' . (($vp->idle)?'OK':'KO') . '.png" /></td>';

		echo '</tr>';
	}
	echo '</table></td></tr>';
}

function drawAffectationsFromObject($list, $obj, $type, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$canCreate=securityGetAccessRightYesNo('menuAffectation', 'create')=="YES";
	if ($obj->idle==1) {
		$canUpdate=false;
		$canCreate=false;
		$canDelete=false;
	}
	echo '<table style="width:100%">';
	echo '<tr><td colspan=2 style="width:100%;"><table style="width:100%;">';
	echo '<tr>';
	if (get_class($obj)=='Project') {
		$idProj=$obj->id;
		$idRess=null;
	} else if (get_class($obj)=='Resource' or get_class($obj)=='Contact' or get_class($obj)=='User') {
		$idProj=null;
		$idRess=$obj->id;
	} else {
		$idProj=null;
		$idRess=null;
	}

	if (! $print) {
		echo '<td class="assignHeader" style="width:10%">';
		if ($obj->id!=null and ! $print and $canCreate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" ' .
           ' onClick="addAffectation(\'' . get_class($obj) . '\',\'' . $type . '\',\''. $idRess . '\', \'' . $idProj . '\');" title="' . i18n('addAffectation') . '" class="smallButton"/> ';
		}
		echo '</td>';
	}
	echo '<td class="assignHeader" style="width:10%">' . i18n('colId') . '</td>';
	echo '<td class="assignHeader" style="width:' . (($print)?'70':'60') . '%">' . i18n('colId'.$type) . '</td>';
	echo '<td class="assignHeader" style="width:20%">' . i18n('colRate'). '</td>';
	//echo '<td class="assignHeader" style="width:10%">' . i18n('colIdle'). '</td>';

	echo '</tr>';
	foreach($list as $aff) {
		$canUpdate=securityGetAccessRightYesNo('menuAffectation', 'update',$aff)=="YES";
		$canDelete=securityGetAccessRightYesNo('menuAffectation', 'delete',$aff)=="YES";
		if ($obj->idle==1) {
	    $canUpdate=false;
	    $canCreate=false;
	    $canDelete=false;
	  }
		$idleClass=($aff->idle)?' idleClass':'';
		if ($type=='Project') {
			$name=SqlList::getNameFromId($type, $aff->idProject);
		} else {
			$name=SqlList::getNameFromId($type, $aff->idResource);
		}
		if ($aff->idResource!=$name) {
			echo '<tr>';
			if (! $print) {
				echo '<td class="assignData'.$idleClass.'" style="text-align:center;">';
				if ($canUpdate and ! $print) {
					echo '  <img src="css/images/smallButtonEdit.png" '
					. 'onClick="editAffectation(' . "'" . $aff->id . "'"
					. ",'" . get_class($obj) . "'"
					. ",'" . $type . "'"
					. ",'" . $aff->idResource . "'"
					. ",'" . $aff->idProject . "'"
					. ",'" . $aff->rate . "'"
					. ",'" . $aff->idle . "'"
					. ');" '
					. 'title="' . i18n('editAffectation') . '" class="smallButton"/> ';
				}
				if ($canDelete and ! $print)  {
					echo '  <img src="css/images/smallButtonRemove.png" '
					. 'onClick="removeAffectation(' . "'" . $aff->id . "'"
					. ');" '
					. 'title="' . i18n('removeAffectation') . '" class="smallButton"/> ';
				}
				if ($aff->idle) {
					echo '  <img src="css/images/tabClose.gif" '
          . 'title="' . i18n('colIdle') . '" class="smallButton"/> ';
				}
				echo '</td>';
			}
			$goto="";
			if (!$print and securityCheckDisplayMenu(null,'Affectation')
			and securityGetAccessRightYesNo('menuAffectation', 'read', '')=="YES") {
				$goto=' onClick="gotoElement(\'Affectation\',\'' . $aff->id . '\');" style="cursor: pointer;" ';
			}
			echo '<td class="assignData'.$idleClass.'" align="center">' . $aff->id . '</td>';
			if ($idProj) {
				echo '<td class="assignData'.$idleClass.'" align="left"' . $goto . '>' . htmlEncode(SqlList::getNameFromId($type, $aff->idResource)) . '</td>';
			} else {
				echo '<td class="assignData'.$idleClass.'" align="left"' . $goto . '>' . htmlEncode(SqlList::getNameFromId('Project', $aff->idProject)) . '</td>';
			}
			echo '<td class="assignData'.$idleClass.'" align="center">' . $aff->rate . '</td>';
			//echo '<td class="assignData" align="center"><img src="../view/img/checked' . (($aff->idle)?'OK':'KO') . '.png" /></td>';
			echo '</tr>';
		}
	}
	echo '</table></td></tr>';
	echo '</table>';
}

function drawTestCaseRunFromObject($list, $obj, $refresh=false) {
	global $cr, $print, $user, $browserLocale, $comboDetail;
	if ($comboDetail) {
		return;
	}
	$class=get_class($obj);
	$otherClass=($class=='TestCase')?'TestSession':'TestCase';
	$nameWidth=67;
	$canCreate=securityGetAccessRightYesNo('menu'.$class, 'update', $obj)=="YES";
	$canUpdate=$canCreate;
	$canDelete=$canCreate;
	if ($obj->idle==1) {
		$canUpdate=false;
		$canCreate=false;
		$canDelete=false;
	}
	echo '<tr><td colspan="2" style="width:100%;">';
	echo '<table style="width:100%;">';
	echo '<tr>';
	if (! $print and $class=='TestSession') {
		$nameWidth-=10;
		echo '<td class="assignHeader" style="width:10%;">';
		if ($obj->id!=null and ! $print and $canCreate and !$obj->idle) {
			echo '<img src="css/images/smallButtonAdd.png" ' .
           ' onClick="addTestCaseRun();" title="' . i18n('addTestCaseRun') . '" class="smallButton"/> ';
		}
		echo '</td>';
		// also count colDetail size
		$nameWidth-=10;
	}
	echo '<td class="assignHeader" colspan="3" style="width:' . ($nameWidth+15) . '%">' . i18n('col'.$otherClass) . '</td>';
	if (! $print and $class=='TestSession') {
		echo '<td class="assignHeader" style="width:10%">' . i18n('colDetail') . '</td>';
	}
	echo '<td class="assignHeader" colspan="2" style="width:15%">' . i18n('colIdStatus'). '</td>';
	echo '</tr>';
	foreach($list as $tcr) {
		if ($otherClass=='TestCase') {
			$tc=new TestCase($tcr->idTestCase);
		} else {
			$tc=new TestSession($tcr->idTestSession);
		}
		$st=new RunStatus($tcr->idRunStatus);
		echo '<tr>';
		if (! $print and $class=='TestSession') {
			echo '<td class="assignData" style="width:10%;text-align:center;">';
			echo '<table style="width:100%"><tr><td style="width:50%">';
			if ($canUpdate and ! $print) {
				echo '  <img src="css/images/smallButtonEdit.png" '
				. 'onClick="editTestCaseRun(' . "'" . $tcr->id . "'"
				. ",'" . $tcr->idTestCase . "'"
				. ",'" . $tcr->idRunStatus . "'"
				. ",'" . $tcr->idTicket . "'"
				. ');" '
				. 'title="' . i18n('editTestCaseRun') . '" class="smallButton"/> ';
			}
			if ($canDelete and ! $print)  {
				echo '  <img src="css/images/smallButtonRemove.png" '
				. 'onClick="removeTestCaseRun(' . "'" . $tcr->id . "'"
				. ",'" . $tcr->idTestCase . "'"
				. ');" '
				. 'title="' . i18n('removeTestCaseRun') . '" class="smallButton"/> ';
			}
			if (! $print) {
				echo '<input type="hidden" id="comment_' . $tcr->id . '" value="' . htmlEncode($tcr->comment,'none') .'"/>';
			}
			echo '</td><td style="width:50%">';
			if ($tcr->idRunStatus==1 or $tcr->idRunStatus==3 or $tcr->idRunStatus==4) {
				echo '  <img src="css/images/iconPassed16.png" '
				. 'onClick="passedTestCaseRun(' . "'" . $tcr->id . "'"
				. ",'" . $tcr->idTestCase . "'"
				. ",'" . $tcr->idRunStatus . "'"
				. ",'" . $tcr->idTicket . "'"
				. ');" '
				. 'title="' . i18n('passedTestCaseRun') . '" class="smallButton"/> ';
			}
			if ($tcr->idRunStatus==1 or $tcr->idRunStatus==4) {
				echo '  <img src="css/images/iconFailed16.png" '
				. 'onClick="failedTestCaseRun(' . "'" . $tcr->id . "'"
				. ",'" . $tcr->idTestCase . "'"
				. ",'" . $tcr->idRunStatus . "'"
				. ",'" . $tcr->idTicket . "'"
				. ');" '
				. 'title="' . i18n('failedTestCaseRun') . '" class="smallButton"/> ';
			}
			if ($tcr->idRunStatus==1 or $tcr->idRunStatus==3) {
				echo '  <img src="css/images/iconBlocked16.png" '
				. 'onClick="blockedTestCaseRun(' . "'" . $tcr->id . "'"
				. ",'" . $tcr->idTestCase . "'"
				. ",'" . $tcr->idRunStatus . "'"
				. ",'" . $tcr->idTicket . "'"
				. ');" '
				. 'title="' . i18n('blockedTestCaseRun') . '" class="smallButton"/> ';
			}
			echo '</td></tr></table>';
			echo '</td>';
		}
		$goto="";
		if (!$print and securityCheckDisplayMenu(null,'TestCase')
		and securityGetAccessRightYesNo('menuTestCase', 'read', '')=="YES") {
			$goto=' onClick="gotoElement(\'' . $otherClass . '\',\'' . $tc->id . '\');" style="cursor: pointer;" ';
		}
		$typeClass='id'.$otherClass.'Type';
		echo '<td class="assignData" align="center" style="width:10%">' . htmlEncode(SqlList::getNameFromId($otherClass.'Type', $tc->$typeClass)) . '</td>';
		echo '<td class="assignData" align="center" style="width:5%">#' . $tc->id . '</td>';
		echo '<td class="assignData" align="left"' . $goto . ' style="width:' . $nameWidth . '%" title="' . $tcr->comment . '" >' . htmlEncode($tc->name) ;
		if ($tcr->comment and ! $print) {
			echo '&nbsp;&nbsp;<img src="img/note.png" />';
		}
		echo '</td>';
		if (! $print and $class=='TestSession') {
			echo '<td class="assignData" style="width:10%" align="center">';
			if ($tc->description) {
				echo '<img src="../view/css/images/description.png" title="' . i18n('colDescription') . ":\n\n" . htmlEncode($tc->description) . '" alt="desc" />';
				echo '&nbsp;';
			}
			if ($tc->result) {
				echo '<img src="../view/css/images/result.png" title="' . i18n('colExpectedResult') . ":\n\n" . htmlEncode($tc->result) . '" alt="desc" />';
				echo '&nbsp;';
			}
			if (isset($tc->prerequisite) and $tc->prerequisite) {
				echo '<img src="../view/css/images/prerequisite.png" title="' . i18n('colPrerequisite') . ":\n\n" . htmlEncode($tc->prerequisite) . '" alt="desc" />';
			}
			echo '</td>';
		}
		echo '<td class="assignData" style="width:8%;text-align:left;border-right:0px;">';
		echo colorNameFormatter(i18n($st->name) . '#split#' . $st->color);
		echo '</td>';
		echo '<td class="assignData" style="width:10%;border-left:0px;font-size:' . (($tcr->idTicket and $tcr->idRunStatus=='3')?'100':'80') . '%; text-align: center;">';
		if ($tcr->idTicket and $tcr->idRunStatus=='3') {
			echo i18n('Ticket') . ' #' . $tcr->idTicket;
		} else if ($tcr->statusDateTime) {
			echo ' <i>(' . htmlFormatDateTime($tcr->statusDateTime, false) . ')</i> ';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</td></tr>';
}

function drawOtherVersionFromObject($otherVersion, $obj, $type) {
	global $print;
	usort($otherVersion,"OtherVersion::sort");
	$canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj)=="YES";
	if ($obj->idle==1) {$canUpdate=false;}
	if (!$otherVersion or count($otherVersion)==0) return;
	echo '<table>';
	foreach($otherVersion as $vers) {
		if ($vers->id) {
			echo '<tr>';
			if ($obj->id and $canUpdate and ! $print ) {
				echo '<td style="width:20px">';
				echo '<img src="css/images/smallButtonRemove.png" '
				. ' onClick="removeOtherVersion(' . "'" . $vers->id . "'"
				. ', \'' . SqlList::getNameFromId('Version',$vers->idVersion) . '\''
				. ', \'' . $vers->scope . '\''
				.');" '
				. 'title="' . i18n('otherVersionDelete') . '" class="smallButton"/> ';
				echo '</td>';
				echo '<td style="width:20px">';
				echo '<img src="css/images/smallButtonSwitch.png" '
				. ' onClick="swicthOtherVersionToMain(' . "'" . $vers->id . "'"
				. ', \'' . SqlList::getNameFromId('Version',$vers->idVersion) . '\''
				. ', \'' . $vers->scope . '\''
				.');" '
				. 'title="' . i18n('otherVersionSetMain') . '" class="smallButton"/> ';
				echo '</td>';
			}
			echo '<td>' . htmlEncode(SqlList::getNameFromId('Version', $vers->idVersion)) .'</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
}

// ********************************************************************************************************
// MAIN PAGE
// ********************************************************************************************************
// fetch information depending on, request
$objClass=$_REQUEST['objectClass'];
if (isset($_REQUEST['noselect'])) {
	$noselect=true;
}
if (! isset($noselect)) {
	$noselect=false;
}
if ( $noselect ) {
	$objId="";
	$obj=null;
} else {
	$objId=$_REQUEST['objectId'];
	$obj=new $objClass($objId);
	if ( array_key_exists('refreshNotes',$_REQUEST) ) {
		drawNotesFromObject($obj, true);
		exit;
	}
	if ( array_key_exists('refreshBillLines',$_REQUEST) ) {
		drawBillLinesFromObject($obj, true);
		exit;
	}
	if ( array_key_exists('refreshAttachements',$_REQUEST) ) {
		drawAttachementsFromObject($obj, true);
		exit;
	}
	if ( array_key_exists('refreshAssignment',$_REQUEST) ) {
		drawAttachementsFromObject($obj, true);
		exit;
	}
	if ( array_key_exists('refreshResourceCost',$_REQUEST) ) {
		drawResourceCostFromObject($obj->$_ResourceCost,$obj, true);
		exit;
	}
	if ( array_key_exists('refreshVersionProject',$_REQUEST) ) {
		
		FromObjectFromObject($obj->$_VersionProject,$obj, true);
		exit;
	}
	if ( array_key_exists('refreshDocumentVersion',$_REQUEST) ) {
		drawVersionFromObjectFromObject($obj->$_DocumentVersion,$obj, true);
		exit;
	}
	if ( array_key_exists('refreshTestCaseRun',$_REQUEST) ) {
		drawTestCaseRunFromObject($obj->_TestCaseRun, $obj, true);
		exit;
	}
	if ( array_key_exists('refreshLinks',$_REQUEST) ) {
		if (property_exists($obj, '_Link')) {
			drawLinksFromObject($obj->_Link ,$obj, null, true);
		}
		exit;
	}
	if ( array_key_exists('refreshHistory',$_REQUEST) ) {
		$treatedObjects[]=$obj;
		foreach ($obj as $col => $val) {
			if (is_object($val)) {
				$treatedObjects[]=$val;
			}
		}
		drawHistoryFromObjects(true);
		exit;
	}
}

// save the current object in session
$print=false;
if ( array_key_exists('print',$_REQUEST) or isset($callFromMail) ) {
	$print=true;
}
if (! $print and ! $comboDetail and $obj) {
	if (isset($_REQUEST['directAccessIndex'])) {
	  $_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']]=$obj;
	} else {
	  $_SESSION['currentObject']=$obj;
	}
}
$refresh=false;
if ( array_key_exists('refresh',$_REQUEST) ) {
	$refresh=true;
}


$treatedObjects=array();

$displayWidth='98%';
if ($print and isset($outMode) and $outMode=='pdf') {
	$printWidth=1080;
} else {
  $printWidth=980;
}
if (array_key_exists('destinationWidth',$_REQUEST)) {
	$width=$_REQUEST['destinationWidth'];
	$width-=30;
	$displayWidth=$width . 'px';
} else {
	if (array_key_exists('screenWidth',$_SESSION)) {
		$detailWidth = round(($_SESSION['screenWidth'] * 0.8) - 15) ; // 80% of screen - split barr - padding (x2)
	} else {
		$displayWidth='98%';
	}
}
if ($print) {
	$displayWidth=$printWidth.'px'; // must match iFrmae size (see main.php)
}

if ($print) {
	echo '<br/>';
	echo '<div class="reportTableHeader" style="width:'.($printWidth-10).'px;font-size:150%;border: 0px solid #000000;">' . i18n($objClass) . ' #' . ($objId+0) . '</div>';
	echo '<br/>';
}

// New refresh method
if ( array_key_exists('refresh',$_REQUEST) ) {
	if (! $print) {
		echo '<input type="hidden" id="className" name="className" value="' . $objClass . '" />' . $cr;
	}
	drawTableFromObject($obj);
	exit;
}
?>
<div <?php echo ($print)?'x':'';?>
  dojoType="dijit.layout.BorderContainer" class="background"><?php
  if ( ! $refresh and  ! $print ) { ?>
<div id="buttonDiv" dojoType="dijit.layout.ContentPane" region="top">
<div dojoType="dijit.layout.BorderContainer">
<div id="buttonDivContainer" dojoType="dijit.layout.ContentPane"
  region="left"><?php  include 'objectButtons.php'; ?></div>
<div id="resultDiv" dojoType="dijit.layout.ContentPane" region="center">
</div>
<div id="detailBarShow" onMouseover="hideList('mouse');"
  onClick="hideList('click');">
<div id="detailBarIcon" align="center"></div>
</div>
</div>
</div>
<div id="formDiv" dojoType="dijit.layout.ContentPane" region="center"><?php 
  }
  if ( ! $print) { ?>  
<form dojoType="dijit.form.Form" id="objectForm" jsId="objectForm"
  name="objectForm" encType="multipart/form-data" action="" method=""><script
  type="dojo/method" event="onSubmit">
        // Don't do anything on submit, just cancel : no button is default => must click
		    //submitForm("../tool/saveObject.php","resultDiv", "objectForm", true);
		    return false;        
        </script>
<div style="width: 100%; height: 100%;">
<div id="detailFormDiv" dojoType="dijit.layout.ContentPane" region="top"
  style="width: 100%; height: 100%;"><?php 
  }
  $noData=htmlGetNoDataMessage($objClass);
  if ( $noselect) {
  	echo $noData;
  } else {
  	if (! $print or $comboDetail) {
  		echo '<input type="hidden" id="className" name="className" value="' . $objClass . '" />' . $cr;
  	}
  	drawTableFromObject($obj);
  }
  if ( ! $print ) { ?></div>
</div>
</form>
  <?php
  }
  $displayAttachement='YES_OPENED';
  if (array_key_exists('displayAttachement',$_SESSION)) {
  	$displayAttachement=$_SESSION['displayAttachement'];
  }
  if (! isset($isAttachementEnabled)) {
  	$isAttachementEnabled=true;   // allow attachement
  	if (! Parameter::getGlobalParameter('paramAttachementDirectory') or ! Parameter::getGlobalParameter('paramAttachementMaxSize')) {
  		$isAttachementEnabled=false;
  	}
  }
  if (! $noselect and isset($obj->_Attachement) and $isAttachementEnabled and ! $comboDetail ) { ?>
<br />
  <?php if ($print) {?>
<table width="<?php echo $printWidth;?>px;">
  <tr>
    <td class="section"><?php echo i18n('sectionAttachements');?></td>
  </tr>
  <tr>
    <td><?php drawAttachementsFromObject($obj); ?></td>
  </tr>
</table>
  <?php } else {
  	$titlePane=$objClass."_attachment"; ?>

<?php if (! isIE() and ! $readOnly) {?>
<div dojoType="dojox.form.Uploader" type="file" id="attachementFileDirect" name="attachementFile" 
MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachementMaxSize');?>"
url="../tool/saveAttachement.php"
multiple="true"
label="<?php echo i18n("dragAndDrop");?>"
uploadOnSelect="true"
target="resultPost"
onBegin="saveAttachement();"
onError="dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
style="position: absolute; left: <?php echo ($detailWidth/2);?>px; width: <?php echo ($detailWidth/2 - 28);?>px; height: 17px; 
 border: 1px dashed #EEEEEE; margin:0; padding:0; text-align: center; font-size: 7pt; background-color: #FFFFFF; opacity: 0.7;">
<script type="dojo/connect" event="onComplete" args="dataArray">
saveAttachementAck(dataArray);
</script>
<script type="dojo/connect" event="onProgress" args="data">
  saveAttachementProgress(data);
</script>
 </div>
<?php }?>
<div style="width: <?php echo $displayWidth;?>" dojoType="dijit.TitlePane" 
     title="<?php echo i18n('sectionAttachements');?>"
     open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
     id="<?php echo $titlePane;?>" 
     onHide="saveCollapsed('<?php echo $titlePane;?>');"
     onShow="saveExpanded('<?php echo $titlePane;?>');" ><?php drawAttachementsFromObject($obj); ?>
</div>

<?php }?> <?php
  }
  if ( ! $noselect and isset($obj->_BillLine)) { ?> <br />
  <?php if ($print) {?>
<table width="<?php echo $printWidth;?>px;">
  <tr>
    <td class="section"><?php echo i18n('sectionBillLines');?></td>
  </tr>
  <tr>
    <td><?php drawBillLinesFromObject($obj);?></td>
  </tr>
</table>
  <?php } else {
  	$titlePane=$objClass."_billLine"; ?>
<div style="width: <?php echo $displayWidth;?>" dojoType="dijit.TitlePane" 
     title="<?php echo i18n('sectionBillLines');?>"
     open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
     id="<?php echo $titlePane;?>"       
     onHide="saveCollapsed('<?php echo $titlePane;?>');"
     onShow="saveExpanded('<?php echo $titlePane;?>');" ><?php drawBillLinesFromObject($obj); ?>
</div>
<?php }?> <?php
  }
  if (! $noselect and isset($obj->_Note) and ! $comboDetail) { ?> <br />
  <?php if ($print) {?>
<table width="<?php echo $printWidth;?>px;">
  <tr>
    <td class="section"><?php echo i18n('sectionNotes');?></td>
  </tr>
  <tr>
    <td><?php drawNotesFromObject($obj); ?></td>
  </tr>
</table>
  <?php } else {
  	$titlePane=$objClass."_note"; ?>
<div style="width: <?php echo $displayWidth;?>" dojoType="dijit.TitlePane" 
     title="<?php echo i18n('sectionNotes');?>"
     open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
     id="<?php echo $titlePane;?>" 
     onHide="saveCollapsed('<?php echo $titlePane;?>');"
     onShow="saveExpanded('<?php echo $titlePane;?>');" ><?php drawNotesFromObject($obj); ?>
</div>
<?php }?> <?php
  }

  $displayHistory='NO';
  if (array_key_exists('displayHistory',$_SESSION)) {
  	$displayHistory=$_SESSION['displayHistory'];
  }
  if ($obj and (property_exists($obj, '_noHistory') or property_exists($obj, '_noDisplayHistory') ) ) {
  	$displayHistory='NO';
  }
  if ($print and Parameter::getUserParameter('printHistory')!='YES') {
  	$displayHistory='NO';
  }
  echo '<br/>';
  if (  ( ! $noselect) and $displayHistory != 'NO' and ! $comboDetail) {
  	if ($print) {?>
<table width="<?php echo $printWidth;?>px;">
  <tr>
    <td class="section"><?php echo i18n('elementHistoty');?></td>
  </tr>
</table>
<?php drawHistoryFromObjects();?> <?php } else {
	$titlePane=$objClass."_history"; ?>
<div style="width: <?php echo $displayWidth;?>;" dojoType="dijit.TitlePane" 
       title="<?php echo i18n('elementHistoty');?>"
       open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
       id="<?php echo $titlePane;?>"         
       onHide="saveCollapsed('<?php echo $titlePane;?>');"
       onShow="saveExpanded('<?php echo $titlePane;?>');" ><?php drawHistoryFromObjects();?>
</div>
<br />
<?php }?> <?php } else {
	$titlePane=$objClass."_history";?>
<div style="display:none; width: <?php echo $displayWidth;?>;" dojoType="dijit.TitlePane" 
       title="<?php echo i18n('elementHistoty');?>"
       open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
       id="<?php echo $titlePane;?>"         
       onHide="saveCollapsed('<?php echo $titlePane;?>');"
       onShow="saveExpanded('<?php echo $titlePane;?>');" ></div>
	<?php
} ?> <?php if ( ! $refresh and  ! $print) { ?></div>
<?php
}?></div>