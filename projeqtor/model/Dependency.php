<?php 
/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
class Dependency extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $predecessorId;
  public $predecessorRefType;
  public $predecessorRefId;
  public $successorId;
  public $successorRefType;
  public $successorRefId;
  public $dependencyType;
  public $dependencyDelay;
  
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
  

 /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
  	if ($this->id) return "OK";
    $result="";
    $this->predecessorRefId=intval($this->predecessorRefId);
    $this->successorRefId=intval($this->successorRefId);
    // control duplicate
    $crit=array('successorRefType'=>$this->successorRefType, 'successorRefId'=>$this->successorRefId,
                'predecessorRefType'=>$this->predecessorRefType, 'predecessorRefId'=>$this->predecessorRefId);
    $list=$this->getSqlElementsFromCriteria($crit);
    if (count($list)>0) {
    	$result.='<br/>' . i18n('errorDuplicateDependency');
    }
    if ($this->predecessorId) { // Case PlanningElement Dependency
      $prec=new PlanningElement($this->predecessorId);
      $precList=$prec->getPredecessorItemsArray();
      $precParentList=$prec->getParentItemsArray();
      if (array_key_exists('#' . $this->successorId,$precList)) {
        $result.='<br/>' . i18n('errorDependencyLoop');
      }
      // cannot create dependency into parent hierarchy
	    if (array_key_exists('#' . $this->successorId,$precParentList)) {
	      $result.='<br/>' . i18n('errorDependencyHierarchy');
	    }
    } else {
    	$precList=$this->getPredecessorList();
    	$precParentList=array();
      if (array_key_exists($this->successorRefType . '#' . $this->successorRefId,$precList)) {
        $result.='<br/>' . i18n('errorDependencyLoop');
      }
    }
    if ($this->successorId) { // Case PlanningElement Dependency
      $succ=new PlanningElement($this->successorId);    
      $succList=$succ->getSuccessorItemsArray();
      $succParentList=$succ->getParentItemsArray();
      if (array_key_exists('#' .$this->predecessorId,$succList)) {
        $result.='<br/>' . i18n('errorDependencyLoop');
      }
      // cannot create dependency into parent hierarchy
	    if (array_key_exists('#' .$this->predecessorId,$succParentList)) {
	      $result.='<br/>' . i18n('errorDependencyHierarchy');
	    }
    } else {
    	$succList=array();
    	$succParentList=array();
      if (array_key_exists($this->predecessorRefType . '#' . $this->predecessorRefId,$succList)) {
        $result.='<br/>' . i18n('errorDependencyLoop');
      }
    } 
    if ($this->predecessorRefType==$this->successorRefType and $this->predecessorRefId==$this->successorRefId) {
      $result.='<br/>' . i18n('errorDependencyLoop');
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  private function getPredecessorList() {
  	$crit=array('successorRefType'=>$this->predecessorRefType, 'successorRefId'=>$this->predecessorRefId);
  	$list=$this->getSqlElementsFromCriteria($crit, false, null, null, true);
  	$result=array();
  	foreach ($list as $obj) {
  		$result[$obj->predecessorRefType.'#'.$obj->predecessorRefId]=$obj;  
      if ($obj->id!=$this->id) {		
  	    $result=array_merge_preserve_keys($result,$obj->getPredecessorList());
      }
  	}
  	return $result;
  }
  
  private function getSuccessorList() {
    $crit=array('predecessorRefType'=>$this->successorRefType, 'predeccessorRefId'=>$this->succecessorRefId);
    $list=$this->getSqlElementsFromCriteria($crit, false, null, null, true);
    $result=array();
    foreach ($list as $obj) {
      $result[$obj->successorRefType.'#'.$obj->successorRefId]=$obj;  
      if ($obj->id!=$this->id) {    
        $result=array_merge_preserve_keys($result,$obj->getSuccessorList());
      }
    }
    return $result;    
  }
  
}
?>