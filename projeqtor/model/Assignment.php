<?php 
/* ============================================================================
 * Assignment defines link of resources to an Activity (or else)
 */ 
class Assignment extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProject;
  public $refType;
  public $refId;
  public $idResource;
  public $idRole;
  public $comment;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $plannedWork;
  public $rate;
  public $realStartDate;
  public $realEndDate;
  public $plannedStartDate;
  public $plannedEndDate;
  public $dailyCost;
  public $newDailyCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  public $plannedCost;
  public $idle;
  public $billedWork;
  
  private static $_fieldsAttributes=array("idProject"=>"required", 
    "idResource"=>"required", 
    "refType"=>"required", 
    "refId"=>"required");
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /**
   * Save object 
   * @see persistence/SqlElement#save()
   */
  public function save() {
    
  	$creation=($this->id)?false:true;
  	
    if (! $this->realWork) { $this->realWork=0; }
    // if cost has changed, update work 
    
    $this->plannedWork = $this->realWork + $this->leftWork;
    
    $this->assignedCost=$this->assignedWork*$this->dailyCost;    
    $r=new Resource($this->idResource);
    // If idRole not set, set to default for resource
    if (! $this->idRole) {
      $this->idRole=$r->idRole;
    }
    $newCost=$r->getActualResourceCost($this->idRole);
    $this->newDailyCost=$newCost;
    $this->leftCost=$this->leftWork*$newCost;
    $this->plannedCost = $this->realCost + $this->leftCost;
    if ($this->dailyCost==null) {
      $this->dailyCost=$newCost;
      if (! $this->idRole) {
        // search idRole found for newDailyCost
        $where="idResource=" . Sql::fmtId($this->idResource);
        $where.= " and endDate is null";
        $where.= " and cost=" . ($newCost)?$newCost:'0';
        $rc=new ResourceCost();
        $lst = $rc->getSqlElementsFromCriteria(null, false, $where, "startDate desc");
        if (count($lst)>0) {
          $this->idRole=$lst[0]->idRole;
        }
      }      
    }
    
    if ($this->refType=='PeriodicMeeting') {
    	$this->idle=1;
    	$this->leftWork=0;
    }
    
    if (! $this->idProject) {
    	$refObj=new $this->refType($this->refId);
    	$this->idProject=$refObj->idProject;
    }
    // Dispatch value
    $result = parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    
    if ($this->refType=='PeriodicMeeting') {
      $meet=new Meeting();
      $lstMeet=$meet->getSqlElementsFromCriteria(array('idPeriodicMeeting'=>$this->refId));
      foreach ($lstMeet as $meet) {
        $critArray=array('refType'=>'Meeting', 'refId'=>$meet->id, 'idResource'=>$this->idResource, 'idRole'=>$this->idRole);
        $ass=SqlElement::getSingleSqlElementFromCriteria('Assignment', $critArray);
        if (!$ass or !$ass->id) {
        	$ass->realWork=0;
            $ass->realCost=0;
        }
      	$ass->refType='Meeting';
      	$ass->refId=$meet->id;
      	$ass->idResource=$this->idResource;
      	$ass->idRole=$this->idRole;
      	$ass->idProject=$this->idProject;
        $ass->comment=$this->comment;
        $ass->assignedWork=$this->assignedWork;
        $ass->leftWork=$ass->assignedWork-$ass->realWork;
        $ass->plannedWork=$ass->assignedWork;
        $ass->rate=$this->rate;
        $ass->dailyCost=$this->dailyCost;
        $ass->assignedCost=$this->assignedCost;
        $ass->leftCost=$ass->assignedCost-$ass->realCost;
        $ass->plannedCost=$ass->assignedCost;
        $ass->idle=0;      	
        $resAss=$ass->save();
      }
    }
    
    PlanningElement::updateSynthesis($this->refType, $this->refId);
    // Recalculate indicators
    if (SqlList::getIdFromTranslatableName('Indicatorable',$this->refType)) {
        $indDef=new IndicatorDefinition();
        $crit=array('nameIndicatorable'=>$this->refType);
        $lstInd=$indDef->getSqlElementsFromCriteria($crit, false);
        if (count($lstInd)>0) {
        	$item=new $this->refType($this->refId);
	        foreach ($lstInd as $ind) {
	          $fldType='id'. $this->refType .'Type';
	          if (! $ind->idType or $ind->idType==$item->$fldType) {
	            IndicatorValue::addIndicatorValue($ind,$item);
	          }
	        }
        }
      }
    
    /*if ($limitedRate==true) {
      $result = i18n("limitedRate", array($affectation->rate)) . $result;      
    }*/
    
    // Dispatch value
    return $result;
  }
  
  public function simpleSave() {
  	$result = parent::save();
  }
  /**
   * Delete object and dispatch updates to top 
   * @see persistence/SqlElement#save()
   */
  public function delete() {    
    if ($this->refType=='PeriodicMeeting') {
      $meet=new Meeting();
      $lstMeet=$meet->getSqlElementsFromCriteria(array('idPeriodicMeeting'=>$this->refId));
      foreach ($lstMeet as $meet) {
        $critArray=array('refType'=>'Meeting', 'refId'=>$meet->id, 'idResource'=>$this->idResource, 'idRole'=>$this->idRole);
        $ass=SqlElement::getSingleSqlElementFromCriteria('Assignment', $critArray);
        if ($ass and $ass->id and ! $ass->realWork) {
        	$ass->delete();
        }
      }
    }
    $result = parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    PlanningElement::updateSynthesis($this->refType, $this->refId);
    
    // Dispatch value
    return $result;
  }
  
  public function refresh() {
    $work=new Work();
    $crit=array('idAssignment'=>$this->id);
    $workList=$work->getSqlElementsFromCriteria($crit,false);
    $realWork=0;
    $realCost=0;
    $this->realStartDate=null;
    $this->realEndDate=null;
    foreach ($workList as $work) {
      $realWork+=$work->work;
      $realCost+=$work->cost;
      if ( !$this->realStartDate or $work->workDate<$this->realStartDate ) {
        $this->realStartDate=$work->workDate;
      }
      if ( !$this->realEndDate or $work->workDate>$this->realEndDate ) {
        $this->realEndDate=$work->workDate;
      }     
    }
    $this->realWork=$realWork;
    $this->realCost=$realCost;
  }
  
  public function saveWithRefresh() {
    $this->refresh();
    return $this->save();
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if (! $this->idResource) {
      $result.='<br/>' . i18n('messageMandatory', array(i18n('colIdResource')));
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
  
  public static function insertAdministrativeLines($resourceId) {
    // Insert new assignment for all administrative activities
    $type=new ProjectType();
    $critType=array('code'=>'ADM', 'idle'=>'0');
    $lstType=$type->getSqlElementsFromCriteria($critType);
    foreach ($lstType as $type) {
    	$proj=new Project();
    	$critProj=array('idProjectType'=>$type->id, 'idle'=>'0');
    	$lstProj=$proj->getSqlElementsFromCriteria($critProj);
    	foreach ($lstProj as $proj) {
    		$acti=new Activity();
    	  $critActi=array('idProject'=>$proj->id, 'idle'=>'0');
    	  $lstActi=$acti->getSqlElementsFromCriteria($critActi);
    	  foreach ($lstActi as $acti) {
          $assi=new Assignment();
          $critAssi=array('refType'=>'Activity', 'refId'=>$acti->id, 'idResource'=>$resourceId);
          $lstAssi=$assi->getSqlElementsFromCriteria($critAssi,false);
          if (count($lstAssi)==0) {
          	$assi->idProject=$proj->id;
          	$assi->refType='Activity';
          	$assi->refId=$acti->id;
          	$assi->idResource=$resourceId;          	
            //$assi->idRole;
            //$assi->comment;
            $assi->assignedWork=0;
            $assi->realWork=0;
            $assi->leftWork=0;
            $assi->plannedWork=0;
            $assi->rate=0;
            //$assi->realStartDate;
            //$assi->$realEndDate;
            //$assi->plannedStartDate;
            //$assi->plannedEndDate;
            //$assi->dailyCost;
            //$assi->newDailyCost;
            //$assi->assignedCost;
            //$assi->realCost;
            //$assi->leftCost;
            //$assi->plannedCost;
            $assi->idle=0;
            //$assi->billedWork;
            $assi->save();
          }
    	  }
    	}
    }
  }
}
?>