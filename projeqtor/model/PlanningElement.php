<?php
/* ============================================================================
 * Planning element is an object included in all objects that can be planned.
 */ 
class PlanningElement extends SqlElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $initialStartDate;
  public $validatedStartDate;
  public $plannedStartDate;
  public $realStartDate;
  public $initialEndDate;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $initialDuration;
  public $validatedDuration;
  public $plannedDuration;
  public $realDuration;
  public $initialWork;
  public $validatedWork;
  public $assignedWork;
  public $plannedWork;
  public $leftWork;
  public $realWork;
  public $progress;
  public $validatedCost;
  public $assignedCost;
  public $plannedCost;
  public $leftCost;
  public $realCost;
  public $expectedProgress;
  public $wbs;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $priority;
  public $elementary;
  public $idle;
  public $done;
  public $cancelled;
  public $idPlanningMode;
  public $_workVisibility;
  public $_costVisibility;
  public $idBill;

  private static $_fieldsAttributes=array(
                                  "id"=>"hidden",
                                  "refType"=>"hidden",
                                  "refId"=>"hidden",
                                  "refName"=>"hidden",
                                  "wbs"=>"display,noImport", 
                                  "wbsSortable"=>"hidden,noImport",
                                  "progress"=>"display,noImport",
                                  "expectedProgress"=>"display,noImport",
                                  "topType"=>"hidden",
                                  "topId"=>"hidden",
                                  "topRefType"=>"hidden",
                                  "topRefId"=>"hidden",
                                  "idProject"=>"hidden",
                                  "idle"=>"hidden",
                                  "done"=>"hidden",
                                  "cancelled"=>"hidden",
                                  "plannedStartDate"=>"readonly,noImport",
                                  "plannedEndDate"=>"readonly,noImport",
                                  "plannedDuration"=>"readonly,noImport",
                                  "plannedWork"=>"readonly,noImport",
                                  "realStartDate"=>"readonly,noImport",
                                  "realEndDate"=>"readonly,noImport",
                                  "realDuration"=>"readonly,noImport",
                                  "realWork"=>"readonly,noImport",
                                  "assignedCost"=>"readonly,noImport",
                                  "realCost"=>"readonly,noImport",
                                  "leftCost"=>"readonly,noImport",
                                  "plannedCost"=>"readonly,noImport",
                                  "elementary"=>"hidden",
                                  "idPlanningMode"=>"hidden",
  								                "idBill"=>"hidden"
  );   
  
  private static $predecessorItemsArray = array();

  private static $staticCostVisibility=null;
  private static $staticWorkVisibility=null;
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
  }
  
  
  /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
 
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    $rubr=""; $name="";
    $test = 'initial';
    $pos = stripos( $colName, $test);
    if ($pos!==false) { 
      $rubr=$test; $name=substr($colName,$pos+strlen($test));
    } else {
      $test = 'validated';
      $pos = stripos( $colName, $test);
      if ($pos!==false) { 
        $rubr=$test; $name=substr($colName,$pos+strlen($test));
      } else {
        $test = 'planned';
        $pos = stripos( $colName, $test);
        if ($pos!==false) { 
          $rubr=$test; $name=substr($colName,$pos+strlen($test));      
        } else {
          $test = 'real';
          $pos = stripos( $colName, $test);
          if ($pos!==false) { 
            $rubr=$test; $name=substr($colName,$pos+strlen($test));
          }
        }
      }
    }
   
    if ($name=="StartDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (testAllowedChange(this.value)) {';
      $colScript .= '    var startDate=this.value;';
      $colScript .= '    var endDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").value;';
      $colScript .= '    var duration=workDayDiffDates(startDate, endDate);';
      $colScript .= '    dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").set("value",duration);';
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
      $colScript .= '  }';
      $colScript .= '</script>';
    } else if ($name=="EndDate") { // Not to do any more for end date (not managed this way) ???? Reactivted !
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (testAllowedChange(this.value)) {';    
      $colScript .= '    var endDate=this.value;';
      $colScript .= '    var startDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").value;';
      $colScript .= '    var duration=workDayDiffDates(startDate, endDate);';
      $colScript .= '    dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").set("value",duration);';
      if ($rubr=="real") {
        $colScript .= '   if (dijit.byId("idle")) { ';
        $colScript .= '     if ( endDate!=null && endDate!="") {';
        $colScript .= '       dijit.byId("idle").set("checked", true);';
        $colScript .= '     } else {';
        $colScript .= '       dijit.byId("idle").set("checked", false);';
        $colScript .= '     }';
        $colScript .= '   }';
      }
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
            $colScript .= '  }';   
      $colScript .= '</script>';
    } else if ($name=="Duration") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var value=dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration");';
      $colScript .= '  if (testAllowedChange(value)) {';
      $colScript .= '    var duration=(value==null || value=="")?"":parseInt(value.get("value"));';
      $colScript .= '    var startDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").get("value");';
      $colScript .= '    var endDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").get("value");';
      $colScript .= '    if (duration!=null && duration!="") {';
      $colScript .= '      if (startDate!=null && startDate!="") {';
      $colScript .= '        endDate = addWorkDaysToDate(startDate,duration);';
      $colScript .= '        dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").set("value",endDate);';
      //$colScript .= '      } else if (endDate!=null){';
      //$colScript .= '        startDate= addworkDaysToDate(endDate,"day", duration * (-1));';
      //$colScript .= '        dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").set("value",startDate);';
      $colScript .= '      }';
      $colScript .= '    }';
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
      $colScript .= '  }';
      $colScript .= '</script>';
    }    
    return $colScript;
  }
  
  /** ==========================================================================
   * Extends save functionality to implement wbs calculation
   * Triggers parent::save() to run defaut functionality in the end.
   * @return the result of parent::save() function
   */
  public function save() {  	
  	// Get old element (stored in database) : must be fetched before saving
    $old=new PlanningElement($this->id);
    if (! $this->idProject) {
      if ($this->refType=='Project') {
        $this->idProject=$this->refId;
      } else if ($this->refType) {
        $refObj=new $this->refType($this->refId);
        $this->idProject=$refObj->idProject;
      }
    }
    if (! $this->idProject and $this->refType=='Project') {
    	$this->idProject=$this->refId;
    }
    // If done and no work, set up end date
    if (  $this->leftWork==0 and $this->realWork==0 ) {
      $refType=$this->refType;
      if ($refType) {
        $refObj=new $refType($this->refId);
        if ($this->done and property_exists($refObj, 'doneDate')) {
          $this->realEndDate=$refObj->doneDate;
          $this->progress=100;
          $this->expectedProgress=100;
        } else {
          $this->realEndDate=null;
          $this->progress=0;
          $this->expectedProgress=0;
        }
        if (property_exists($refObj, 'handled') and property_exists($refObj, 'handledDate')) {
        	if ($refObj->handled) {
        		$this->realStartDate=$refObj->handledDate;
        	} else {
        		$this->realStartDate=null;
        	}
        }
      }
    } else {
    	$this->progress = round($this->realWork / ($this->realWork + $this->leftWork) * 100);
    }
    if ($this->validatedWork!=0) {
      $this->expectedProgress=round($this->realWork / ($this->validatedWork) *100);
      if ($this->expectedProgress>999999) { $this->expectedProgress=999999; }
    } else {
    	if (!$this->expectedProgress) {
    	  $this->expectedProgress=0;
    	}  
    }
    
    // update topId if needed
    $topElt=null;
    if ( (! $this->topId or trim($this->topId)=='') and ( $this->topRefId and trim($this->topRefId)!='') ) {
      $crit=array("refType"=>$this->topRefType, "refId"=>$this->topRefId);
      $topElt=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$crit);
      if ($topElt) {
        $this->topId=$topElt->id;
        $topElt->elementary=0;        
      }
    }
    
    // calculate wbs
    $dispatchNeeded=false;
    //$wbs="";
    $crit='';
    if (! $this->wbs or trim($this->wbs)=='') {
      $wbs="";
      //if ( $this->topId and trim($this->topId)!='') {
      if ($topElt) {
        //$elt=new PlanningElement($this->topId);
        $wbs=$topElt->wbs . ".";
        $crit=" topId=" . Sql::fmtId($this->topId);
      } else {
        $crit=" (topId is null) ";
      }
      if ($this->id) {
        $crit.=" and id<>" . Sql::fmtId($this->id);
      }
      $lst=$this->getSqlElementsFromCriteria(null, null, $crit, 'wbsSortable desc');
      if (count($lst)==0) {
        $localSort=1;
      } else {
        if ( !$lst[0]->wbsSortable or $lst[0]->wbsSortable=='') {
          $localSort=1;
        } else {
          $localSort=substr($lst[0]->wbsSortable,-3,3)+1;
        }
      }
      $wbs.=$localSort;
      $this->wbs=$wbs;
      $dispatchNeeded=true;
    }
    $wbsSortable=formatSortableWbs($this->wbs);
    if ($wbsSortable != $this->wbsSortable) {
      $dispatchNeeded=true;
    }
    $this->wbsSortable=$wbsSortable;
    // search for dependant elements
    $crit=" topId=" . Sql::fmtId($this->id);
    $this->elementary=1;
    $lstElt=$this->getSqlElementsFromCriteria(null, null, $crit ,'wbsSortable asc');
    if ($lstElt and count($lstElt)>0) {
      $this->elementary=0;
    } else {
      $this->elementary=1;
    }

    if (! $this->priority or $this->priority==0) {
      $this->priority=500; // default value for priority
    }
    
    $this->realDuration=workDayDiffDates($this->realStartDate, $this->realEndDate);
    $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    if ($this->validatedStartDate and $this->validatedEndDate) {
      $this->validatedDuration=workDayDiffDates($this->validatedStartDate, $this->validatedEndDate);
    }
    if ($this->initialStartDate and $this->initialEndDate) {
      $this->initialDuration=workDayDiffDates($this->initialStartDate, $this->initialEndDate);
    }
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }

    // Update dependant objects
    if ($dispatchNeeded) {
      $cpt=0;
      foreach ($lstElt as $elt) {
        $cpt++;
        $elt->wbs=$this->wbs . '.' . $cpt;
        if ($elt->refType) { // just security for unit testing 
          $elt->save();
        }
        // TODO : check result to return error message in case of error
      }
    }
    
    // update topObject
    if ($topElt) {
    	if ($topElt->refId) {
        $topElt->save();   
    	} 
    }
    
    if ($this->topId!=$old->topId)
    
    // save old parent (for synthesis update) if parent has changed
    if ($old->topId!='' and $old->topId!=$this->topId) {
      $this->updateSynthesis($old->topRefType, $old->topRefId);
    }
    // save new parent (for synthesis update) if parent has changed
    if ($this->topId!='' and ($old->topId!=$this->topId or $old->cancelled!=$this->cancelled)) {
      $this->updateSynthesis($this->topRefType, $this->topRefId);
    }          
    if ($this->wbsSortable!=$old->wbsSortable) {
    	$refType=$this->refType;
      if ($refType=='Project') {
        $refObj=new $refType($this->refId);
        $refObj->sortOrder=$this->wbsSortable;
        $subRes=$refObj->saveForced(true);
      }
    }
    // remove existing planned work (if any)
    if ($this->idle) {
       $pw=new PlannedWork();
       $crit="refType=".Sql::str($this->refType)." and refId=".$this->refId;
       $pw->purge($crit);
    }
    
    //
    if ($old->realWork==0 and $this->realWork!=0 and $this->refType) {
      $refType=$this->refType;     
      $refObj=new $refType($this->refId);
      if (property_exists($refObj, 'idStatus')) {
    	  $st=new Status($refObj->idStatus);
    	  if (!$st->setHandledStatus) { // if current stauts is not handled, move to first allowed handled status (fitting workflow)
    	 	  $typeClass=$refType.'Type';
    	 	  $typeField='id'.$typeClass;
    	  	$type=new $typeClass($refObj->$typeField);
    	  	$user=$_SESSION['user'];
    	 	  $crit=array('idWorkflow'=>$type->idWorkflow, 'idStatusFrom'=>$refObj->idStatus, 'idProfile'=>$user->idProfile, 'allowed'=>'1');
    	 	  $ws=new WorkflowStatus();
    	 	  $possibleStatus=$ws->getSqlElementsFromCriteria($crit);
    	 	  $in="(0";
    	 	  foreach ($possibleStatus as $ws) {
    	 	  	$in.=",".$ws->idStatusTo;
    	 	  }
    	 	  $in.=")";
    	 	  $st=new Status();
    	 	  $stList=$st->getSqlElementsFromCriteria(null, null, " setHandledStatus=1 and id in ".$in, 'sortOrder asc');
    	 	  if (count($stList)>0) {
    	 	  	$refObj->idStatus=$stList[0]->id;
    	 	  	$refObj->save();
    	 	  }
    	  }
      }
    }
    
    return $result;
  }
  
  public function simpleSave() {
    $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    $result = parent::save();
  }

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
   /** =========================================================================
   * Update the synthesis Data (work).
   * Called by sub-element (assignment, ...) 
   * @param $col the nale of the property
   * @return a boolean 
   */
  private function updateSynthesisObj () {
    $assignedWork=0;
    $leftWork=0;
    $plannedWork=0;
    $realWork=0;
    $assignedCost=0;
    $leftCost=0;
    $plannedCost=0;
    $realCost=0;
    $this->_noHistory=true;
    // Add data from assignments directly linked to this item
    $critAss=array("refType"=>$this->refType, "refId"=>$this->refId);
    $assignment=new Assignment();
    $assList=$assignment->getSqlElementsFromCriteria($critAss, false);
    if ($this->refType=='PeriodicMeeting') {
    	$assList=array();
    }
    $realStartDate=null;
    $realEndDate=null;
    $plannedStartDate=null;
    $plannedEndDate=null;
    foreach ($assList as $ass) {
    	$assignedWork+=$ass->assignedWork;
      $leftWork+=$ass->leftWork;
      $plannedWork+=$ass->plannedWork;
      $realWork+=$ass->realWork;
      if ($ass->assignedCost) $assignedCost+=$ass->assignedCost;
      if ($ass->leftCost) $leftCost+=$ass->leftCost;
      if ($ass->plannedCost) $plannedCost+=$ass->plannedCost;
      if ($ass->realCost) $realCost+=$ass->realCost;
      if ( $ass->realStartDate and (! $realStartDate or $ass->realStartDate<$realStartDate )) {
        $realStartDate=$ass->realStartDate;
      }
      if ( $ass->realEndDate and (! $realEndDate or $ass->realEndDate>$realEndDate )) {
        $realEndDate=$ass->realEndDate;
      }
      if ( $ass->plannedStartDate and (! $plannedStartDate or $ass->plannedStartDate<$plannedStartDate )) {
        $plannedStartDate=$ass->plannedStartDate;
      }
      if ( $ass->plannedEndDate and (! $plannedEndDate or $ass->plannedEndDate>$plannedEndDate )) {
        $plannedEndDate=$ass->plannedEndDate;
      }      
    }
    // Add data from other planningElements dependant from this one
    if (! $this->elementary) {
      $critPla=array("topId"=>$this->id,"cancelled"=>'0');
      $planningElement=new PlanningElement();
      $plaList=$planningElement->getSqlElementsFromCriteria($critPla, false);
      // Add data from other planningElements dependant from this one    
      foreach ($plaList as $pla) {
        $assignedWork+=$pla->assignedWork;
        $leftWork+=$pla->leftWork;
        $plannedWork+=$pla->plannedWork;
        $realWork+=$pla->realWork;
        if ($pla->assignedCost) $assignedCost+=$pla->assignedCost;
        if ($pla->leftCost) $leftCost+=$pla->leftCost;
        if ($pla->plannedCost) $plannedCost+=$pla->plannedCost;
        if ($pla->realCost) $realCost+=$pla->realCost;
        if ( $pla->realStartDate and (! $realStartDate or $pla->realStartDate<$realStartDate )) {
          $realStartDate=$pla->realStartDate;
        }
        if ( $pla->realEndDate and (! $realEndDate or $pla->realEndDate>$realEndDate )) {
          $realEndDate=$pla->realEndDate;
        }  
        if ( $pla->plannedStartDate and (! $plannedStartDate or $pla->plannedStartDate<$plannedStartDate )) {
          $plannedStartDate=$pla->plannedStartDate;
        }
        if ( $pla->plannedEndDate and (! $plannedEndDate or $pla->plannedEndDate>$plannedEndDate )) {
          $plannedEndDate=$pla->plannedEndDate;
        }                
      }
    }
    $this->realStartDate=$realStartDate;
    if ($realWork>0 or $leftWork>0) {
      if ($leftWork==0) {
        $this->realEndDate=$realEndDate;
      } else {
        $this->realEndDate=null;
      }
    }
    $this->plannedStartDate=$plannedStartDate;
    if ($this->elementary and $plannedStartDate and $realStartDate and $realStartDate<$plannedStartDate) {
      $this->plannedStartDate=$realStartDate;
    }
    $this->plannedEndDate=$plannedEndDate;
    // save cumulated data
    $this->assignedWork=$assignedWork;
    $this->leftWork=$leftWork;
    $this->plannedWork=$plannedWork;
    $this->realWork=$realWork;
    $this->assignedCost=$assignedCost;
    $this->leftCost=$leftCost;
    $this->plannedCost=$plannedCost;
    $this->realCost=$realCost;
    $this->save();
    // Dispath to top element
    if ($this->topId) {
        self::updateSynthesis($this->topRefType, $this->topRefId);
    }
    
  }
  
   /** =========================================================================
   * Update the synthesis Data (work).
   * Called by sub-element (assignment, ...) 
   * @param $col the nale of the property
   * @return a boolean 
   */
  public static function updateSynthesis ($refType, $refId) {
    $crit=array("refType"=>$refType, "refId"=>$refId);
    $obj=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
    if ($obj) {
      return $obj->updateSynthesisObj();
    }
  } 
  
    /**
   * Delete object 
   * @see persistence/SqlElement#save()
   */
  public function delete() { 
    // Delete existing Assignment
    //$critAss=array("refType"=>$this->refType, "refId"=>$this->refId);
    //$assignment=new Assignment();
    //$assList=$assignment->getSqlElementsFromCriteria($critAss, false);
    //foreach ($assList as $ass) {
    //  $ass->delete();
    //}
    $refType=$this->topRefType;
    $refId=$this->topRefId;
    $result = parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $topElt=null;
    if ( $refId and trim($refId)!='') {
      $crit=array("refType"=>$refType, "refId"=>$refId);
      $topElt=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$crit);
      if ($topElt) {
      	if ($topElt->refId) {
          $topElt->save();
      	}
        self::updateSynthesis($refType, $refId);          
      }
    }
    
    // Dispatch value
    return $result;
   
  }
  
 /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if ($this->idle and $this->leftWork>0) {
      $result.='<br/>' . i18n('errorIdleWithLeftWork');
    }
    $stat=array('initial','validated','planned','real');
    foreach ($stat as $st) {
      $start=$st.'StartDate';
      $end=$st.'EndDate';
      $startAttr=$this->getFieldAttributes($start);
      $endAttr=$this->getFieldAttributes($end);
      if (strpos($startAttr,'hidden')===false and strpos($startAttr,'readonly')===false 
      and strpos($endAttr,'hidden')===false and strpos($endAttr,'readonly')===false ) {
        if ($this->$start and $this->$end and $this->$start>$this->$end) {
          $result.='<br/>' . i18n('errorStartEndDates',array($this->getColCaption($start),$this->getColCaption($end)));
        }
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function controlHierarchicLoop($parentType, $parentId) {
    $result="";
    $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>$parentType,'refId'=>$parentId));
    $parentList=$parent->getParentItemsArray();
    if (array_key_exists('#' . $this->id,$parentList)) {
      $result='<br/>' . i18n('errorHierarchicLoop');
      return $result;
    }
      
    $precListObj=$this->getPredecessorItemsArray();
    $succListObj=$this->getSuccessorItemsArray();
    $parentListObj=$parent->getParentItemsArray();
    $parentListObj['#'.$parent->id]=$parent;
    foreach ($parentListObj as $parentId=>$parentObj) {
      if (array_key_exists($parentId, $precListObj)) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        return $result;
      }
      if (array_key_exists($parentId, $succListObj)) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        return $result;
      }
    }
    return $result;    
  }
  
  public function getParentItemsArray() {
    // V2.1 refactoring of function
    $result=array();
    if ($this->topId) {
      $parent=new PlanningElement($this->topId);
      $result=$parent->getParentItemsArray();
      $result['#' . $parent->id]=$parent;
    }
    return $result;
  }
  
  /** ==============================================================
   * Retrieve the list of all Predecessors, recursively
   */
  public function getPredecessorItemsArray() {
  	// Imporvement : get static stored value if already fetched 
  	/*if (array_key_exists('#' . $this->id, self::$predecessorItemsArray)) {
  		return self::$predecessorItemsArray['#' . $this->id]; 
  	}*/
    $result=array();
    $crit=array("successorId"=>$this->id);
    $dep=new Dependency();
    $depList=$dep->getSqlElementsFromCriteria($crit, false);
    foreach ($depList as $dep) {
      $elt=new PlanningElement($dep->predecessorId);
      if ($elt->id and ! array_key_exists('#' . $elt->id, $result)) {
        $result['#' . $elt->id]=$elt;
        $resultPredecessor=$elt->getPredecessorItemsArray();
        $result=array_merge($result,$resultPredecessor);
      }
    }
    // Imporvement : static store result to avoid multiple fetch
    //self::$predecessorItemsArray['#' . $this->id]=$result;
    return $result;
  }
  
    /** ==============================================================
   * Retrieve the list of direct Predecessors, and may include direct parents predecessors
   */
  public static function getPredecessorList($idCurrent, $includeParents=false) {
    $dep=new Dependency();
    if (! $includeParents) {
      return $dep->getSqlElementsFromCriteria(array("successorId"=>$idCurrent),false);
    }
    // Include parents successsors
    $testParent=new PlanningElement($idCurrent);
    $resultList=$dep->getSqlElementsFromCriteria(array("successorId"=>$idCurrent),false,null, null, true);
    while ($testParent->topId) {
      $testParent=new PlanningElement($testParent->topId);
      $list=$dep->getSqlElementsFromCriteria(array("successorId"=>$testParent->id),false,null, null, true);
      $resultList=array_merge($resultList,$list);
    }
    return $resultList;
  }
  public function getPredecessorItemsArrayIncludingParents() {
  	$result=$this->getPredecessorItemsArray();
  	$parents=$this->getParentItemsArray();
  	foreach ($parents as $parent) {
  		$resParent=$parent->getPredecessorItemsArray();
  		array_merge($result,$resParent);
  	}
    return $result;
  }
  
   /** ==============================================================
   * Retrieve the list of all Successors, recursively
   */
  public function getSuccessorItemsArray() {
    $result=array();
    $crit=array("predecessorId"=>$this->id);
    $dep=new Dependency();
    $depList=$dep->getSqlElementsFromCriteria($crit, false);
    foreach ($depList as $dep) {
      $elt=new PlanningElement($dep->successorId);
      if ($elt->id and ! array_key_exists('#' . $elt->id, $result)) {
        $result['#' . $elt->id]=$elt;
        $resultSuccessor=$elt->getSuccessorItemsArray();
        $result=array_merge($result,$resultSuccessor);
      }
    }
    return $result;
  }

  public function moveTo($destId,$mode) {
    $status="ERROR";
    $dest=new PlanningElement($destId);
    if ($dest->topRefType!=$this->topRefType
    or $dest->topRefId!=$this->topRefId) {
      $returnValue=i18n('moveCancelled');
    } else {
      if ($this->topRefType) {
        $where="topRefType='" . $this->topRefType . "' and topRefId=" . Sql::fmtId($this->topRefId) ;
      } else {
        $where="topRefType is null and topRefId is null";
      }
      $order="wbsSortable asc";
      $list=$this->getSqlElementsFromCriteria(null,false,$where,$order);
      $idx=0;
      $currentIdx=0;
      foreach ($list as $pe) {
        if ($pe->id==$this->id) {
          // met the one we are moving => skip
        } else {
          if ($pe->id==$destId and $mode=="before") {
            $idx++;
            $currentIdx=$idx;
          }
          $idx++;
          $root=substr($pe->wbs,0,strrpos($pe->wbs,'.'));
          $pe->wbs=($root=='')?$idx:$root.'.'.$idx;
          if ($pe->refType) {
            $pe->save();
          }
          if ($pe->id==$destId and $mode=="after") {
            $idx++;
            $currentIdx=$idx;
          }
        }
      }
      $root=substr($this->wbs,0,strrpos($this->wbs,'.'));
      $this->wbs=($root=='')?$currentIdx:$root.'.'.$currentIdx;
      $this->save();
      $returnValue=i18n('moveDone');
      $status="OK";
    }
    $returnValue .= '<input type="hidden" id="lastOperation" value="move" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $status . '" />';
    $returnValue .= '<input type="hidden" id="lastPlanStatus" value="OK" />';
    return $returnValue;
  }

  public function setVisibility() {
    if (self::$staticCostVisibility and self::$staticWorkVisibility) {
      $this->_costVisibility=self::$staticCostVisibility ;
      $this->_workVisibility=self::$staticWorkVisibility;
      return;
    }
    if (! array_key_exists('user',$_SESSION)) {
      return;
    }
    $user=$_SESSION['user'];
    $list=SqlList::getList('VisibilityScope', 'accessCode', null, false);
    $hCost=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$user->idProfile,'scope'=>'cost'));
    $hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$user->idProfile,'scope'=>'work'));
    if ($hCost->id) {
      $this->_costVisibility=$list[$hCost->rightAccess];
    } else {
      $this->_costVisibility='ALL';
    }
    if ($hWork->id) {
      $this->_workVisibility=$list[$hWork->rightAccess];
    } else {
      $this->_workVisibility='ALL';
    }
    self::$staticCostVisibility=$this->_costVisibility;
    self::$staticWorkVisibility=$this->_workVisibility;
  }
  
  public function getFieldAttributes($fieldName) {
    if (! $this->_costVisibility or ! $this->_workVisibility) {
      $this->setVisibility();
    }
    if ($this->_costVisibility =='NO') {
      if ($fieldName=='validatedCost' or $fieldName=='assignedCost'
       or $fieldName=='plannedCost' or $fieldName=='leftCost' 
       or $fieldName=='realCost') {
         return 'hidden';
      }
    } else if ($this->_costVisibility =='VAL') {
      if ($fieldName=='assignedCost'
       or $fieldName=='plannedCost' or $fieldName=='leftCost' 
       or $fieldName=='realCost') {
         return 'hidden';
      }
    }
    if ($this->_workVisibility=='NO') {
      if ($fieldName=='validatedWork' or $fieldName=='assignedWork'
       or $fieldName=='plannedWork' or $fieldName=='leftWork' 
       or $fieldName=='realWork') {
         return 'hidden';
      }
    } else if ($this->_workVisibility=='VAL') {
      if ($fieldName=='assignedWork'
       or $fieldName=='plannedWork' or $fieldName=='leftWork' 
       or $fieldName=='realWork') {
         return 'hidden';
      }
    }
    return parent::getFieldAttributes($fieldName);
  }  
  
  /**
   * Fulfill a planningElementList with :
   *  - parents for each item
   *  - predecessor for each item
   * @param List of PlanningElements
   */
  public static function initializeFullList($list) {
    $idList=array();
    // $list must be sorted on WBS !
    $result=$list;
    $listProjectsPriority=array();
    // Parents
    foreach ($list as $id=>$pe) {
    	if ($pe->refType=='Project') {		
    		$listProjectsPriority[$pe->refId]=$pe->priority;
    	}
      $idList[$pe->id]=$pe->id;
      $pe->_parentList=array();
      $pe->_childList=array();
      if ($pe->topId) { 
        if (array_key_exists('#'.$pe->topId, $result)) {
          $parent=$result['#'.$pe->topId];
        } else {
          $parent=new PlanningElement($pe->topId);
          $parent->_parentList=array();
          $parent->_predecessorList=array();
          $parent->_predecessorListWithParent=array();
          $parent->_noPlan=true;
          $parent->_childList=array();
          $result['#'.$pe->topId]=$parent;
        }
        if (isset($parent->_parentList)) {
          $pe->_parentList=$parent->_parentList;
        }
        $pe->_parentList['#'.$pe->topId]=$pe->topId;
      }
      $result[$id]=$pe;
    }
    $reverse=array_reverse($result, true);
    foreach ($reverse as $id=>$pe) {
      if ($pe->topId) {
        if (array_key_exists('#'.$pe->topId, $result)) {
          $parent=$result['#'.$pe->topId];
        } else {
          $parent=new PlanningElement($pe->topId);
          $parent->_parentList=array();
          $parent->_predecessorList=array();
          $parent->_predecessorListWithParent=array();
          $parent->_noPlan=true;
          $parent->_childList=array();
          $result['#'.$pe->topId]=$parent;
        } 
        $parent=$result['#'.$pe->topId];
        $parent->_childList=array_merge_preserve_keys($pe->_childList,$parent->_childList);
        $parent->_childList['#'.$pe->id]=$pe->id;
        $result['#'.$pe->topId]=$parent;
      }
    }
    // Predecessors
    $crit='successorId in (0,' . implode(',',$idList) . ')';
    $dep=new Dependency();
    
    $depList=$dep->getSqlElementsFromCriteria(null, false, $crit);
    $directPredecessors=array();
    foreach ($depList as $dep) {
      if (! array_key_exists("#".$dep->successorId, $directPredecessors)) {
        $directPredecessors["#".$dep->successorId]=array();
      }
      $lstPrec=$directPredecessors["#".$dep->successorId];
      //$lstPrec["#".$dep->predecessorId]=$dep->predecessorId;
      $lstPrec["#".$dep->predecessorId]=$dep->dependencyDelay;  // #77 : store delay of dependency
      if (! array_key_exists("#".$dep->predecessorId, $result)) {
      	$parent=new PlanningElement($dep->predecessorId);
        $parent->_parentList=array();
        $parent->_predecessorList=array();
        $parent->_predecessorListWithParent=array();
        $parent->_noPlan=true;
        $parent->_childList=array();
        $result["#".$dep->predecessorId]=$parent;
      }
      $parentChilds=$result["#".$dep->predecessorId]->_childList;
      foreach ($parentChilds as $tmpIdChild=>$tempValChild) {
      	$parentChilds[$tmpIdChild]=$dep->dependencyDelay;
      }
      if (isset($parentChilds["#".$dep->successorId])) { unset($parentChilds["#".$dep->successorId]); } // Self cannot be it own predecessor
      $directPredecessors["#".$dep->successorId]=array_merge_preserve_keys($lstPrec,$parentChilds);
    }
    foreach ($result as $id=>$pe) {
      $pe=$result[$id];
      if (array_key_exists($id, $directPredecessors)) {
        $pe->_directPredecessorList=$directPredecessors[$id];
      } else {
        $pe->_directPredecessorList=array();
      } 
      $pe->_predecessorList=self::getRecursivePredecessor($directPredecessors,$id,$result,'main');
      $pe->_predecessorListWithParent=$pe->_predecessorList;
      foreach ($pe->_parentList as $idParent=>$parent) {
        $pe->_predecessorListWithParent=array_merge($pe->_predecessorListWithParent,self::getRecursivePredecessor($directPredecessors,$idParent,$result,'parent'));
      }
      if (! $pe->realStartDate and ! (isset($pe->_noPlan) and $pe->_noPlan)) {
        $pe->plannedStartDate=null;
      }
      if (! $pe->realEndDate and ! (isset($pe->_noPlan) and $pe->_noPlan)) {
        $pe->plannedEndDate=null;
      }
      $result[$id]=$pe;
    }
    $result['_listProjectsPriority']=$listProjectsPriority;
    return $result;
  }
  
  
  private static function getRecursivePredecessor($directFullList, $id, $result,$scope) {
  	if (isset($result[$id]->_predecessorList)) {
  		return $result[$id]->_predecessorList;
  	}
  	if (array_key_exists($id, $directFullList)) {
      $result=$directFullList[$id];
  	  foreach ($directFullList[$id] as $idPrec=>$prec) {
        $result=array_merge($result,self::getRecursivePredecessor($directFullList,$idPrec,$result,$scope));
      }
    } else {
      $result=array();
    }
  	return $result;
  }
  
  /* not used anymore
  static function comparePlanningElement($a, $b) {
    if (array_key_exists('#'.$a->id, $b->_predecessorListWithParent)) {
    	return -1;
    }
    if (array_key_exists('#'.$b->id, $a->_predecessorListWithParent)) {
      return +1;
    }
    if (array_key_exists('#'.$a->id, $b->_parentList)) {
      return +1;
    }
    if (array_key_exists('#'.$b->id, $a->_parentList)) {
      return -1;
    }
    // idPlanningMode '2'=>REGUL '3'=>FULL '7'=>HALF
    if ($a->idPlanningMode=='2' or $a->idPlanningMode=='3' or $a->idPlanningMode=='2') {
    	return -1;
    }
    if ($b->idPlanningMode=='2' or $b->idPlanningMode=='3' or $b->idPlanningMode=='2') {
      return +1;
    }
    if ($a->priority<$b->priority) {
      return -1;
    }
    if ($a->priority>$b->priority) {
      return +1;
    }
    
    if ($a->wbsSortable<$b->wbsSortable) {
      return -1;
    }
    if ($a->wbsSortable>$b->wbsSortable) {
      return +1;
    }
    return 0;       
  }*/
  
  /* not used anymore
  static function comparePlanningElementPredecessor($a, $b) {
    if (array_key_exists('#'.$a->id, $b->_predecessorListWithParent)) {
      return -1;
    }
    if (array_key_exists('#'.$b->id, $a->_predecessorListWithParent)) {
      return +1;
    }
    return 0;
  }*/
  
  /* not used anymore
  static function comparePlanningElementSimpleOld($a, $b) {
    if ($a->idPlanningMode=='16' and $b->idPlanningMode!='16') { 	
      return -1;
    }
    if ($b->idPlanningMode=='16' and $a->idPlanningMode!='16') {
      return +1;
    }
    // idPlanningMode '2'=>REGUL '3'=>FULL '7'=>HALF
  	if ( ($a->idPlanningMode=='2' or  $a->idPlanningMode=='3' or  $a->idPlanningMode=='7') and 
         ($b->idPlanningMode!='2' and $a->idPlanningMode!='3' and $a->idPlanningMode!='7') ) {
      return -1;
    }
    if ( ( $b->idPlanningMode=='2' or $b->idPlanningMode=='3' or $b->idPlanningMode=='7') and 
         ($a->idPlanningMode!='2' and $a->idPlanningMode!='3' and $a->idPlanningMode!='7') ) {
      return +1;
    }
    if ($a->priority<$b->priority) {
      return -1;
    }
    if ($a->priority>$b->priority) {
      return +1;
    }
    
    if ($a->wbsSortable<$b->wbsSortable) {
      return -1;
    }
    if ($a->wbsSortable>$b->wbsSortable) {
      return +1;
    }
    return 0;       
  }*/
  static function comparePlanningElementSimple($a, $b) {
    if ($a->_sortCriteria<$b->_sortCriteria) {
      return -1;
    }
    if ($a->_sortCriteria>$b->_sortCriteria) {
      return +1;
    }
    return 0;       
  }
}
?>