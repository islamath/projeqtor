<?php
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */
class ImputationLine {

	// List of fields that will be exposed in general user interface
	//public $id;    // redefine $id to specify its visible place
	public $refType;
	public $refId;
	public $idProject;
	public $idAssignment;
	public $name;
	public $comment;
	public $wbs;
	public $wbsSortable;
	public $topId;
	public $validatedWork;
	public $assignedWork;
	public $plannedWork;
	public $realWork;
	public $leftWork;
	public $imputable;
	public $elementary;
	public $arrayWork;
	public $arrayPlannedWork;
	public $startDate;
	public $endDate;
	public $idle;
	public $locked;
	public $description;
	public $functionName;

	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id = NULL) {
		$arrayWork=array();
	}

	/** ==========================================================================
	 * Return some lines for imputation purpose, including assignment and work
	 * @return void
	 */
	function __destruct() {
	}

	static function getLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned=true) {
scriptLog("      => ImputationLine->getLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned)");		
		// Insert new lines for admin projects
		Assignment::insertAdministrativeLines($resourceId);

		$user=$_SESSION['user'];
		$user=new User($user->id);
		
		$visibleProjects=$user->getVisibleProjects();
		
		$crit=array('scope'=>'imputation', 'idProfile'=>$user->idProfile);
    $habilitation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    $scope=new AccessScope($habilitation->rightAccess);
    $scopeCode=$scope->accessCode;

		$result=array();
		if ($rangeType=='week') {
			$nbDays=7;
		}
		$crit=array('idResource' => $resourceId);
		if (! $showIdle) {
			$crit['idle']='0';
		}

		$startDate=self::getFirstDay($rangeType, $rangeValue);
		$ass=new Assignment();
		$assList=$ass->getSqlElementsFromCriteria($crit,false);
		$crit=array('idResource' => $resourceId);
		$crit[$rangeType]=$rangeValue;
		$work=new Work();
		$workList=$work->getSqlElementsFromCriteria($crit,false);
		$plannedWork=new PlannedWork();
	  if ($showPlanned) {
      $plannedWorkList=$plannedWork->getSqlElementsFromCriteria($crit,false);
    }
		//echo "scopeCode='$scopeCode'";
		// visibility security : hide line depending on access rights
		if ($user->id != $resourceId and $scopeCode!='ALL') {
			foreach ($assList as $id=>$ass) {
				if (! array_key_exists($ass->idProject, $visibleProjects) or $scopeCode!='PRO') {
					unset ($assList[$id]);
				}
			}
		}
		
		// Check if assignment exists for each work (may be closed, so make it appear)
		foreach ($workList as $work) {
			if ($work->idAssignment) {
				$found=false;
				foreach ($assList as $ass) {
					if ($work->refType==$ass->refType and $work->refId==$ass->refId) {
						$found=true;
						break;
					}
				}
				if (! $found) {
					$ass=new Assignment($work->idAssignment);
					if ($ass->id) {
						$assList[$ass->id]=$ass;
					} else {
						$id=$work->refType.'#'.$work->refId;
						if (! isset($assList[$id])) {
						  $ass->id=null;
						  $ass->name='<span style="color:red;"><i>' . i18n('notAssignedWork') . '</i></span>';
						  if ($work->refType and $work->refId) {
						    $ass->comment=i18n($work->refType) . ' #' . $work->refId;
						  } else {
						    $ass->comment='unexpected case : assignment #' . $work->idAssignment . ' not found';
						  }
						  $ass->realWork=$work->work;
						  $ass->refType=$work->refType;
              $ass->refId=$work->refId;
						} else {
						  $ass=$assList[$id];
						  $ass->realWork+=$work->work;
						}
						$assList[$id]=$ass;
					}
					
				}
			} else {
				$id=$work->refType.'#'.$work->refId;
				if (isset($assList[$id])) {
					$ass=$assList[$id];
				} else {
					$ass=new Assignment();
				}
				if ($work->refType) {
					$obj=new $work->refType($work->refId);
					if ($obj->name) {
					  $obj->name=htmlEncode($obj->name);
					}
				} else {
					$obj=new Ticket();
          $obj->name='<span style="color:red;"><i>' . i18n('notAssignedWork') . '</i></span>';
          if (! $ass->comment) {
            $ass->comment='unexpected case : no reference object';
          }
				}
				//$ass->name=$id . " " . $obj->name;
				$ass->name=$obj->name;
			  if (isset($obj->WorkElement)) {
          $ass->realWork=$obj->WorkElement->realWork;
          $ass->leftWork=$obj->WorkElement->leftWork;
        }
				$ass->id=null;
				$ass->refType=$work->refType;
				$ass->refId=$work->refId;
				if ($work->refType) {
					$ass->comment=i18n($work->refType) . ' #' . $work->refId;
				}
				$assList[$id]=$ass;
			}
		}
		
		$cptNotAssigned=0;
		foreach ($assList as $idAss=>$ass) {
			$elt=new ImputationLine();
			$elt->idle=$ass->idle;
			$elt->refType=$ass->refType;
			$elt->refId=$ass->refId;
			$elt->comment=$ass->comment;
			$elt->idProject=$ass->idProject;
			$elt->idAssignment=$ass->id;
			$elt->assignedWork=$ass->assignedWork;
			$elt->plannedWork=$ass->plannedWork;
			$elt->realWork=$ass->realWork;
			$elt->leftWork=$ass->leftWork;
			$elt->arrayWork=array();
			$elt->arrayPlannedWork=array();
			if ($ass->idRole) {
			  $elt->functionName=SqlList::getNameFromId('Role', $ass->idRole);
			}
			$crit=array('refType'=>$elt->refType, 'refId'=>$elt->refId);
			$plan=null;
			if ($ass->id) {
			  $plan=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
			}
			if ($plan and $plan->id) {
				$elt->name=htmlEncode($plan->refName);
				$elt->wbs=$plan->wbs;
				$elt->wbsSortable=$plan->wbsSortable;
				$elt->topId=$plan->topId;
				$elt->elementary=$plan->elementary;
				$elt->startDate=($plan->realStartDate)?$plan->realStartDate:$plan->plannedStartDate;
				$elt->endDate=($plan->realEndDate)?$plan->realEndDate:$plan->plannedEndDate;
				$elt->imputable=true;
			} else {
				$cptNotAssigned+=1;
				if (isset($ass->name)) {
					$elt->name=$ass->name;
				} else {
          $elt->name='<span style="color:red;"><i>' . i18n('notAssignedWork') . '</i></span>';
          if ($ass->refType and $ass->refId) {
          	$elt->comment=i18n($ass->refType) . ' #' . $ass->refId;
          } else {
            $elt->comment='unexpected case : no assignment name';
          }
				}
				$elt->wbs='0.'.$cptNotAssigned;
				$elt->wbsSortable='000.'. str_pad($cptNotAssigned, 3, "0", STR_PAD_LEFT);
				$elt->elementary=1;
				$elt->topId=null;
				$elt->imputable=true;
				$elt->idAssignment=null;
				$elt->locked=true;
			}
			if ( ! ($user->id = $resourceId or $scopeCode!='ALL' or ($scopeCode='PRO' and array_key_exists($ass->idProject, $visibleProjects) ) ) ) {
				$elt->locked=true;
			}
			$key=$elt->wbsSortable . ' ' . $ass->refType . '#' . $ass->refId;
			if (array_key_exists($key,$result)) {
				$key.= '/#' . $ass->id;
			}
			// fetch all work stored in database for this assignment
			foreach ($workList as $work) {
				if ( ($work->idAssignment and $work->idAssignment==$elt->idAssignment) or (!$work->idAssignment and $work->refType==$elt->refType and $work->refId==$elt->refId) ) {
					$workDate=$work->workDate;
					$offset=dayDiffDates($startDate, $workDate)+1;
					if (isset($elt->arrayWork[$offset])) {
						$elt->arrayWork[$offset]->work+=$work->work;
					} else {
						$elt->arrayWork[$offset]=$work;
					}
				}
			}
			// Fill arrayWork for days without an input
			for ($i=1; $i<=$nbDays; $i++) {
				if ( ! array_key_exists($i, $elt->arrayWork)) {
					$elt->arrayWork[$i]=new Work();
				}
			}
			if ($showPlanned) {
				foreach ($plannedWorkList as $plannedWork) {
					if ($plannedWork->idAssignment==$elt->idAssignment) {
						$workDate=$plannedWork->workDate;
						$offset=dayDiffDates($startDate, $workDate)+1;
						$elt->arrayPlannedWork[$offset]=$plannedWork;
					}
				}
				// Fill arrayWork for days without an input
				for ($i=1; $i<=$nbDays; $i++) {
					if ( ! array_key_exists($i, $elt->arrayPlannedWork)) {
						$elt->arrayPlannedWork[$i]=new PlannedWork();
					}
				}
			}
			$result[$key]=$elt;
		}
		// If some not assigned work exists : add group line
		if ($cptNotAssigned >0) {
			$elt=new ImputationLine();
			$elt->idle=0;
			$elt->arrayWork=array();
			$elt->arrayPlannedWork=array();
			$elt->name=i18n('notAssignedWork');
			$elt->wbs=0;
			$elt->wbsSortable='000';
			$elt->elementary=false;
			$elt->imputable=false;
			$elt->refType='Imputation';
			for ($i=1; $i<=$nbDays; $i++) {
				if ( ! array_key_exists($i, $elt->arrayWork)) {
					$elt->arrayWork[$i]=new Work();
				}
			}
			$result['#']=$elt;
		}
		$act=new Activity();
		$accessRight=securityGetAccessRight($act->getMenuClass(), 'read');
		foreach ($result as $key=>$elt) {
			$result=self::getParent($elt, $result, true, $accessRight);
		}
		ksort($result);
		return $result;
	}

	// Get the parent line for hierarchc display purpose
	private static function getParent($elt, $result, $direct=true, $accessRight){
//scriptLog("      => ImputationLine->getParent($elt->refType#$elt->refId, result[], $direct)");		
		$plan=null;
		$user=$_SESSION['user'];
		$visibleProjectList=$user->getVisibleProjects();
		
		//$visibleProjectList=explode(', ', getVisibleProjectsList());
		if ($elt->topId) {
			$plan=new PlanningElement($elt->topId);
		}
		if ($plan) {
			$key=$plan->wbsSortable . ' ' . $plan->refType . '#' . $plan->refId;
			if (! array_key_exists($key,$result) 
			and ($plan->refType!='Project' or $direct or $accessRight=='ALL' or array_key_exists($plan->refId,$visibleProjectList))) {
				$top=new ImputationLine();
				$top->idle=$plan->idle;
				$top->imputable=false;
				$top->name=htmlEncode($plan->refName);
				$top->wbs=$plan->wbs;
				$top->wbsSortable=$plan->wbsSortable;
				$top->topId=$plan->topId;
				$top->refType=$plan->refType;
				$top->refId=$plan->refId;
				//$top->assignedWork=$plan->assignedWork;
				//$top->plannedWork=$plan->plannedWork;
				//$top->realWork=$plan->realWork;
				//$top->leftWork=$plan->leftWork;
				$result[$key]=$top;
				$result=self::getParent($top, $result, $direct=false, $accessRight);
			}
		}
scriptLog("      => ImputationLine->getParent()-exit");
		return $result;
	}

	private static function getFirstDay($rangeType, $rangeValue) {
		if ($rangeType=='week') {
			$year=substr($rangeValue,0,4);
			$week=substr($rangeValue,4,2);
			$day=firstDayofWeek($week,$year);
			return date('Y-m-d',$day);
		}
	}

	static function drawLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned=true, $print=false) {
scriptLog("      => ImputationLine->drawLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned, $print)");		
		$crit=array('periodRange'=>$rangeType, 'periodValue'=>$rangeValue, 'idResource'=>$resourceId); 
		$period=SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', $crit);
		$user=$_SESSION['user'];		
		$canValidate=false;
		$crit=array('scope'=>'workValid', 'idProfile'=>$user->idProfile);
    $habilitation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    $scope=new AccessScope($habilitation->rightAccess);
    if ($scope->accessCode=='NO') {
      $canValidate=false;
    } else if ($scope->accessCode=='ALL') {
      $canValidate=true;
    } else if ($scope->accessCode=='OWN' and $user->isResource and $resourceId==$user->id) {
      $canValidate=true;
    } else if ($scope->accessCode=='PRO') {
      $crit='idProject in ' . transformListIntoInClause($user->getVisibleProjects());
      $aff=new Affectation();
      $lstAff=$aff->getSqlElementsFromCriteria(null, false, $crit, null, true);
      $fullTable=SqlList::getList('Resource');
      foreach ($lstAff as $id=>$aff) {
        if ($aff->idResource==$resourceId) {
          $canValidate=true;
          continue;
        }
      }
    }
		$locked=false;		
		$oldValues="";
		$nameWidth=220;
		$dateWidth=80;
		$workWidth=60;
		$inputWidth=30;
		$resource=new Resource($resourceId);
		$capacity=work::getConvertedCapacity($resource->capacity);
		$weekendColor="cfcfcf";
		$currentdayColor="ffffaa";
		$today=date('Y-m-d');
		if ($rangeType=='week') {
			$nbDays=7;
		}
		$startDate=self::getFirstDay($rangeType, $rangeValue);
		$plus=$nbDays-1;
		$endDate=date('Y-m-d',strtotime("+$plus days", strtotime($startDate)));
		$rangeValueDisplay=substr($rangeValue,0,4) . '-' . substr($rangeValue,4);
		$colSum=array();
		for ($i=1; $i<=$nbDays; $i++) {
			$colSum[$i]=0;
		}
		$width=600;
		if (isset($_REQUEST['destinationWidth'])) {
		  $width=($_REQUEST['destinationWidth'])-155-30;
		} 
		echo '<table class="imputationTable">';
		echo '<TR class="ganttHeight">';
		echo '<td class="label" ><label for="imputationComment" >'.i18n("colComment").'&nbsp;:&nbsp;</label></td>';
		echo '<td><textarea dojoType="dijit.form.Textarea" id="imputationComment" name="imputationComment"'
		           .' onChange="formChanged();"'
               .' style="width: '.$width.'px;" maxlength="4000" class="input">'.$period->comment.'</textarea></td>';
		echo ' </TR>';
		echo '</table>';
		echo '<input type="hidden" id="resourceCapacity" value="'.$capacity.'" />';
		echo '<table class="imputationTable">';
		echo '<TR class="ganttHeight">';
		echo '  <TD class="ganttLeftTopLine" ></TD>';
		echo '  <TD class="ganttLeftTopLine" colspan="5">';
		echo '<table style="width:98%"><tr><td style="width:99%">' . htmlEncode($resource->name) . ' - ' . i18n($rangeType) . ' ' . $rangeValueDisplay;
		echo '</td>';
		if ($period->submitted) {		
			$msg='<div class="imputationSubmitted"><nobr>'.i18n('submittedWorkPeriod',array(htmlFormatDateTime($period->submittedDate))).'</nobr></div>';	
			if (! $period->validated and ($resourceId==$user->id or $canValidate)) {
				echo '<td style="width:1%">'.$msg.'</td>'; 
				echo '<td style="width:1%">';
			  echo '<button id="unsubmitButton" jsid="unsubmitButton" dojoType="dijit.form.Button" showlabel="true" >'; 
        echo '<script type="dojo/connect" event="onClick" args="evt">submitWorkPeriod("unsubmit");</script>';
        echo i18n('unSubmitWorkPeriod');
        echo '</button>';
        echo '</td>';
        $locked=true;
			} else {
				echo '<td style="width:1%">'.$msg.'</td>'; 
			}
		} else if ($resourceId==$user->id and ! $period->validated) {
			echo '<td style="width:1%">';
	    echo '<button id="submitButton" dojoType="dijit.form.Button" showlabel="true" >'; 
	    echo '<script type="dojo/connect" event="onClick" args="evt">submitWorkPeriod("submit");</script>';
	    echo i18n('submitWorkPeriod');
	    echo '</button>';
	    echo '</td>'; 
		}
		echo '<td style="width:10px">&nbsp;&nbsp;&nbsp;</td>';		
		if ($period->validated) {
			$locked=true;
			$res=SqlList::getNameFromId('User', $period->idLocker);
			$msg='<div class="imputationValidated"><nobr>'.i18n('validatedWorkPeriod',array(htmlFormatDateTime($period->validatedDate),$res)).'</nobr></div>';
		  if ($canValidate) {
		  	echo '<td style="width:1%">'.$msg.'</td>';
		  	//echo '<div xdojoType="dijit.Tooltip" xconnectId="unvalidateButton" xposition="above" >'.$msg.'</div>';
		  	echo '<td style="width:1%">';
		  	echo '<button id="unvalidateButton" jsid="unvalidateButton" dojoType="dijit.form.Button" showlabel="true" >'; 
        echo '<script type="dojo/connect" event="onClick" args="evt">submitWorkPeriod("unvalidate");</script>';
        echo i18n('unValidateWorkPeriod');
        echo '</button>';
        echo '</td>'; 
		  } else {
		  	echo '<td style="width:1%">'.$msg.'</td>';
		  }
		} else if ($canValidate) {
			echo '<td style="width:1%">';
		  echo '<button id="validateButton" dojoType="dijit.form.Button" showlabel="true" >'; 
      echo '<script type="dojo/connect" event="onClick" args="evt">submitWorkPeriod("validate");</script>';
      echo i18n('validateWorkPeriod');
      echo '</button>';
      echo '</td>';
		}
		echo '</tr></table>';
		echo '</TD>';
		echo '  <TD class="ganttLeftTitle" colspan="' . $nbDays . '" '
		. 'style="border-right: 1px solid #ffffff;border-bottom: 1px solid #DDDDDD;">'
		. htmlFormatDate($startDate)
		. ' - '
		. htmlFormatDate($endDate)
		. '</TD>';
		echo '  <TD class="ganttLeftTopLine" colspan="2" style="text-align:center;color: #707070">' .  htmlFormatDate($today) . '</TD>';
		echo '</TR>';
		echo '<TR class="ganttHeight">';
		echo '  <TD class="ganttLeftTitle" style="width:15px;"></TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $nameWidth . 'px;text-align: left; '
		. 'border-left:0px; " nowrap>' .  i18n('colTask') . '</TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $dateWidth . 'px;">'
		. i18n('colStart') . '</TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $dateWidth . 'px;">'
		. i18n('colEnd') . '</TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $workWidth . 'px;">'
		. i18n('colAssigned') . '</TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $workWidth . 'px;">'
		. i18n('colReal') . '</TD>';
		$curDate=$startDate;
		for ($i=1; $i<=$nbDays; $i++) {
			echo '  <TD class="ganttLeftTitle" style="width: ' . $inputWidth . 'px;';
			if ($today==$curDate) {
				echo ' background-color:#' . $currentdayColor . '; color: #aaaaaa;"';
			} else if (isOffDay($curDate)) {
				echo ' background-color:#' . $weekendColor . '; color: #aaaaaa;"';
			}
			echo '">';
			if ($rangeType=='week') {
				echo  i18n('colWeekday' . $i) . " "  . date('d',strtotime($curDate)) . '';
			}
			if (! $print) {
				echo ' <input type="hidden" id="day_' . $i . '" name="day_' . $i . '" value="' . $curDate . '" />';
			}
			echo '</TD>';
			$curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
		}
		echo '  <TD class="ganttLeftTitle" style="width: ' . $workWidth . 'px;">'
		. i18n('colLeft') . '</TD>';
		echo '  <TD class="ganttLeftTitle" style="width: ' . $workWidth . 'px;">'
		. i18n('colPlanned') . '</TD>';
		echo '</TR>';
		$tab=ImputationLine::getLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned);
		if (! $print) {
			echo '<input type="hidden" id="nbLines" name="nbLines" value="' . count($tab) . '" />';
		}
		$nbLine=0;
		$collapsedList=Collapsed::getCollaspedList();
		$closedWbs='';
		$wbsLevelArray=array();
		foreach ($tab as $key=>$line) {
			if ($locked) $line->locked=true;
			$nbLine++;
			if ($line->elementary) {
				$rowType="row";
			} else {
				$rowType="group";
			}
			//if ($closedWbs and strlen($line->wbsSortable)<=strlen($closedWbs)) {
			if ($closedWbs and (strlen($line->wbsSortable)<=strlen($closedWbs) or $closedWbs!=substr($line->wbsSortable,0,strlen($closedWbs)) ) ) {
				$closedWbs="";
			}
			$scope='Imputation_'.$resourceId.'_'.$line->refType.'_'.$line->refId;
			$collapsed=false;
			if ($rowType=="group" and array_key_exists($scope, $collapsedList)) {
				$collapsed=true;
				if (! $closedWbs) {
					$closedWbs=$line->wbsSortable;
				}
			}
			echo '<tr id="line_' . $nbLine . '"class="ganttTask' . $rowType . '"';
			if ($closedWbs and $closedWbs!=$line->wbsSortable) {
				echo ' style="display:none" ';
			}
			echo '>';
			echo '<td class="ganttName" >';
			if (! $print) {
				echo '<input type="hidden" id="wbs_' . $nbLine . '" '
				. ' value="' . $line->wbsSortable . '"/>';
				echo '<input type="hidden" id="status_' . $nbLine . '" ';
				if ($collapsed) {
					echo   ' value="closed"';
				} else {
					echo   ' value="opened"';
				}
				echo '/>';
				echo '<input type="hidden" id="idAssignment_' . $nbLine . '" name="idAssignment[]"'
				. ' value="' . $line->idAssignment . '"/>';
				echo '<input type="hidden" id="imputable_' . $nbLine . '" name="imputable[]"'
				. ' value="' . $line->imputable . '"/>';
				echo '<input type="hidden" id="locked_' . $nbLine . '" name="locked[]"'
        . ' value="' . $line->locked . '"/>';
			}
			if (! $line->refType) {$line->refType='Imputation';};
			echo '<img src="css/images/icon' . $line->refType . '16.png" />';
			echo '</td>';
			if (! $print) {
				echo '<td class="ganttName" title="' . htmlEncodeJson($line->comment) . '">';
			} else {
				echo '<td class="ganttName" >';
			}
			// tab the name depending on level
			echo '<table><tr><td>';
		  $wbs=$line->wbsSortable;
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
			//$level=(strlen($line->wbsSortable)+1)/4;
			$levelWidth = ($level-1) * 16;
			echo '<div style="float: left;width:' . $levelWidth . 'px;">&nbsp;</div>';
			echo '</td>';
			if (! $print) {
				if ($rowType=="group") {
					echo '<td width="16"><span id="group_' . $nbLine . '" ';
					if ($collapsed) {
						echo 'class="ganttExpandClosed"';
					} else {
						echo 'class="ganttExpandOpened"';
					}
					if (! $print) {
						echo 'onclick="workOpenCloseLine(' . $nbLine . ',\''.$scope.'\')"';
					} else {
						echo ' style="cursor:default;"';
					}
					echo '>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;</span><span>&nbsp</span></td>' ;
				} else {
					echo '<td width="16"><div style="float: left;width:16px;">&nbsp;</div></td>';
				}
			}
			if($line->refType == "Project") {
				$description=null;
				$crit=array();
				$crit['id']=$line->refId;
				$description=SqlElement::getSingleSqlElementFromCriteria('Project', $crit);
				if($description) {
					$line->description=$description->description;
				}
			}
			else if ($line->refType == "Activity")
			{
				$descriptionActivity=null;
				$crit2=array();
				$crit2['id']=$line->refId;
				$crit2['idProject']=$line->idProject;
				$descriptionActivity=SqlElement::getSingleSqlElementFromCriteria('Activity', $crit2);
				if($descriptionActivity)
				{
					$line->description=$descriptionActivity->description;
				}
			}
			echo '<td>' . $line->name . '</td>';
			if (isset($line->functionName) and $line->functionName) {
				echo '<div style="float:right; color:#8080DD; font-size:80%;;font-weight:normal;">' . $line->functionName . '</div>';
			}
			if ($line->comment and !$print) {
				echo '<td>&nbsp;&nbsp;<img src="img/note.png" /></td>';
			}
			echo '</tr></table>';
			echo '</td>';
			//echo '<td class="ganttDetail" align="center">' . $line->description . '</td>';
			echo '<td class="ganttDetail" align="center" width="5%">' . htmlFormatDate($line->startDate) . '</td>';
			echo '<td class="ganttDetail" align="center" width="5%">' . htmlFormatDate($line->endDate) . '</td>';
			echo '<td class="ganttDetail" align="center" width="5%">';
			if ($line->imputable) {
				if (!$print) {
					echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
					echo ' constraints="{pattern:\'###0.0#\'}"';
					echo ' style="width: 60px; text-align: center; " ';
					echo ' trim="true" class="displayTransparent" readOnly="true" tabindex="-1" ';
					echo ' id="assignedWork_' . $nbLine . '"';
					echo ' value="' . Work::displayImputation($line->assignedWork) . '" ';
					echo ' >';
					echo '</div>';
				} else {
					echo  Work::displayImputation($line->assignedWork);
				}
			}
			echo '</td>';
			echo '<td class="ganttDetail" align="center" width="5%">';
			if ($line->imputable) {
				if (!$print) {
					echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
					echo ' constraints="{pattern:\'###0.0#\'}"';
					echo ' style="width: 60px; text-align: center;" ';
					echo ' trim="true" class="displayTransparent" readOnly="true" tabindex="-1" ';
					echo ' id="realWork_' . $nbLine . '"';
					echo ' value="' .  Work::displayImputation($line->realWork) . '" ';
					echo ' >';
					echo '</div>';
				} else {
					echo   Work::displayImputation($line->realWork);
				}
			}
			echo '</td>';
			$curDate=$startDate;
			for ($i=1; $i<=$nbDays; $i++) {
				echo '<td class="ganttDetail" align="center" width="5%"';
				if ($today==$curDate) {
					echo ' style="background-color:#' . $currentdayColor . ';"';
				} else if (isOffDay($curDate)) {
					echo ' style="background-color:#' . $weekendColor . '; color: #aaaaaa;"';
				}
				echo '>';
				if ($line->imputable) {
					$valWork=$line->arrayWork[$i]->work;
					$idWork=$line->arrayWork[$i]->id;
					if (! $print) {
						echo '<div style="position: relative">';
						if ($showPlanned) {
							echo '<div style="display: inline;';
							echo ' position: absolute; right: 5px; top: 0px; text-align: right;';
							echo ' color:#8080DD; font-size:80%;">';
							echo  Work::displayImputation($line->arrayPlannedWork[$i]->work);
							echo '</div>';
						}
						echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
						echo ' constraints="{min:0}"';
						echo '  style="width: 45px; text-align: center; ' . (($line->idle or $line->locked)?'color:#A0A0A0; xbackground: #EEEEEE;':'') .' " ';
						echo ' trim="true" maxlength="4" class="input" ';
						echo ' id="workValue_' . $nbLine . '_' . $i . '"';
						echo ' name="workValue_' . $i . '[]"';
						echo ' value="' .  Work::displayImputation($valWork) . '" ';
						if ($line->idle or $line->locked) {
							echo ' readOnly="true" ';
						}
						echo ' >';
						echo '<script type="dojo/method" event="onChange" args="evt">';
						echo '  dispatchWorkValueChange("' . $nbLine . '","' . $i . '");';
						echo '</script>';
						echo '</div>';
						echo '</div>';
						if (! $print) {
								echo '<input type="hidden" id="workId_' . $nbLine . '_' . $i . '"'
								. ' name="workId_' . $i . '[]"'
								. ' value="' . $idWork . '"/>';
							echo '<input type="hidden" id="workOldValue_' . $nbLine . '_' . $i . '"'
							. ' value="' .  Work::displayImputation($valWork) . '"/>';
						}
					} else {
						echo  Work::displayImputation($valWork);
					}
					$colSum[$i]+= Work::displayImputation($valWork);
				} else {
					echo '<input type="hidden" name="workId_' . $i . '[]" />';
					echo '<input type="hidden" name="workValue_' . $i . '[]" />';
				}
				echo '</td>';
				$curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
			}
			echo '<td class="ganttDetail" align="center" width="5%">';
			if ($line->imputable) {
				if (!$print) {
					echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
					echo ' constraints="{min:0}"';
					echo '  style="width: 60px; text-align: center;' . (($line->idle or $line->locked)?'color:#A0A0A0; xbackground: #EEEEEE;':'') .' " ';
					echo ' trim="true" class="input" ';
					echo ' id="leftWork_' . $nbLine . '"';
					echo ' name="leftWork[]"';
					echo ' value="' .  Work::displayImputation($line->leftWork) . '" ';
					if ($line->idle or $line->locked) {
						echo ' readOnly="true" ';
					}
					echo ' >';
					echo '<script type="dojo/method" event="onChange" args="evt">';
					echo '  dispatchLeftWorkValueChange("' . $nbLine . '");';
					echo '</script>';
					echo '</div>';
				} else {
					echo  Work::displayImputation($line->leftWork);
				}
			} else {
				  echo '<input type="hidden" id="leftWork_' . $nbLine . '" name="leftWork[]" />';
			}
			echo '</td>';
			echo '<td class="ganttDetail" align="center" width="5%">';
			if ($line->imputable) {
				if (!$print) {
					echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
					echo ' constraints="{pattern:\'###0.0#\'}"';
					echo '  style="width: 60px; text-align: center;" ';
					echo ' trim="true" class="displayTransparent" readOnly="true" tabindex="-1"';
					echo ' id="plannedWork_' . $nbLine . '"';
					echo ' value="' .  Work::displayImputation($line->plannedWork) . '" ';
					echo ' >';
					echo '</div>';
				} else {
					echo  Work::displayImputation($line->plannedWork);
				}
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '<TR class="ganttDetail" >';
		echo '  <TD class="ganttLeftTopLine" style="width:15px;"></TD>';
		echo '  <TD class="ganttLeftTopLine" colspan="5" style="text-align: left; '
		. 'border-left:0px;" nowrap><NOBR>';
		echo  Work::displayImputationUnit();
		echo '</NOBR></TD>';

		$curDate=$startDate;
		$nbFutureDays=Parameter::getGlobalParameter('maxDaysToBookWork');
		$maxDateFuture=date('Y-m-d',strtotime("+".$nbFutureDays." days", strtotime($today)));
		echo '<input type="hidden" id="nbFutureDays" value="'.$nbFutureDays.'" />';
		echo '<input type="hidden" value="'.$maxDateFuture.'" />';
		for ($i=1; $i<=$nbDays; $i++) {
			echo '  <TD class="ganttLeftTitle" style="width: ' . $inputWidth . 'px;';
			if ($today==$curDate) {
				//echo ' background-color:#' . $currentdayColor . ';';
			}
			echo '"><NOBR>';
			if (!$print) {
				echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
				echo ' constraints="{pattern:\'###0.0#\'}"';
				echo ' trim="true" disabled="true" ';
				echo ($colSum[$i]>$capacity)?' class="imputationInvalidCapacity"':' class="displayTransparent"'; 
				echo '  style="width: 45px; text-align: center; color: #000000 !important;" ';
				echo ' id="colSumWork_' . $i . '"';
				echo ' value="' . $colSum[$i] . '" ';
				echo ' >';
				echo '</div>';
				echo '<input type="hidden" id="colIsFuture_' . $i . '" value="'.(($curDate>$maxDateFuture)?1:0).'" />';
			} else {
				echo $colSum[$i];
			}
			echo '</NOBR></TD>';
			$curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
		}
		echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>'
		.  '</NOBR></TD>';
		echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>'
		.  '</NOBR></TD>';
		echo '</TR>';
		echo '</table>';
	}
	// ============================================================================**********
	// GET STATIC DATA FUNCTIONS
	// ============================================================================**********



	// ============================================================================**********
	// GET VALIDATION SCRIPT
	// ============================================================================**********

	/** ==========================================================================
	 * Return the validation sript for some fields
	 * @return the validation javascript (for dojo frameword)
	 */
	public function getValidationScript($colName) {
		$colScript = parent::getValidationScript($colName);

		if ($colName=="idle") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			$colScript .= '  if (this.checked) { ';
			$colScript .= '    if (dijit.byId("PlanningElement_realEndDate").get("value")==null) {';
			$colScript .= '      dijit.byId("PlanningElement_realEndDate").set("value", new Date); ';
			$colScript .= '    }';
			$colScript .= '  } else {';
			$colScript .= '    dijit.byId("PlanningElement_realEndDate").set("value", null); ';
			//$colScript .= '    dijit.byId("PlanningElement_realDuration").set("value", null); ';
			$colScript .= '  } ';
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
		}
		return $colScript;
	}

	// ============================================================================**********
	// MISCELLANOUS FUNCTIONS
	// ============================================================================**********

	public function save() {
		$finalResult="";
		foreach($this->arrayWork as $work) {
			$result="";
			if ($work->work) {
				//echo "save";
				$result=$work->save();
			} else {
				if ($work->id) {
					//echo "delete";
					$result=$work->delete();
				}
			}
			if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
				$status='ERROR';
				$finalResult=$result;
				break;
			} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
				$status='OK';
				$finalResult=$result;
			} else {
				if ($finalResult=="") {
					$finalResult=$result;
				}
			}
		}
		return $finalResult;
	}
}
?>