<?php
/* ============================================================================
 * Planning element is an object included in all objects that can be planned.
 */ 
class ProjectPlanningElement extends PlanningElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_tab_8_5 = array('requested', 'validated', 'assigned', 'planned', 'real', 'left', '', '', 'startDate', 'endDate', 'duration', 'work', 'cost');
  public $initialStartDate;
  public $validatedStartDate;
  public $_void_13;
  public $plannedStartDate;
  public $realStartDate;
  public $_void_15;
  public $_label_priority;
  public $priority;
  public $initialEndDate;
  public $validatedEndDate;
  public $_void_23;
  public $plannedEndDate;
  public $realEndDate;
  public $_void_25;
  public $_void_26;
  public $_void_27;
  public $initialDuration;
  public $validatedDuration;
  public $_void_33;
  public $plannedDuration;
  public $realDuration;
  public $_void_35;
  public $_label_wbs;
  public $wbs;
  public $_void_41;
  public $validatedWork;
  public $assignedWork;
  public $plannedWork;
  public $realWork;
  public $leftWork;
  public $_label_progress;
  public $progress;
  public $_void_51;
  public $validatedCost;
  public $assignedCost;
  public $plannedCost;
  public $realCost;
  public $leftCost;
  public $_label_expected;
  public $expectedProgress;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $idle;
  private static $_fieldsAttributes=array(
    "plannedStartDate"=>"readonly,noImport",
    "realStartDate"=>"readonly,noImport",
    "plannedEndDate"=>"readonly,noImport",
    "realEndDate"=>"readonly,noImport",
    "plannedDuration"=>"readonly,noImport",
    "realDuration"=>"readonly,noImport",
    "initialWork"=>"hidden,noImport",
    "plannedWork"=>"readonly,noImport",
    "realWork"=>"readonly,noImport",
    "leftWork"=>"readonly,noImport",
    "assignedWork"=>"readonly,noImport",
    "idPlanningMode"=>"hidden,noImport"
  );   
  
  private static $_databaseTableName = 'planningelement';
  
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

    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
    
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
}
?>