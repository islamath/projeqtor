<?php
/* ============================================================================
 * Planning element is an object included in all objects that can be planned.
 */ 
class MilestonePlanningElement extends PlanningElement {

    public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_tab_6_2 = array('requested', 'validated', 'planned', 'real','' ,'' , 'dueDate', '');
  public $initialEndDate;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $_label_planning;
  public $idMilestonePlanningMode;
  public $_void_21;
  public $_void_22;
  public $_void_23;
  public $_void_24;
  public $_label_wbs;
  public $wbs;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $priority;
  public $idle;
  private static $_fieldsAttributes=array(
    "priority"=>"hidden,noImport",
    "initialStartDate"=>"hidden,noImport",
    "validatedStartDate"=>"hidden,noImport",
    "plannedStartDate"=>"hidden,noImport",
    "realStartDate"=>"hidden,noImport",
    "initialDuration"=>"hidden,noImport",
    "validatedDuration"=>"hidden,noImport",
    "plannedDuration"=>"hidden,noImport",
    "realDuration"=>"hidden,noImport",
    "initialWork"=>"hidden,noImport",
    "validatedWork"=>"hidden,noImport",
    "plannedWork"=>"hidden,noImport",
    "realWork"=>"hidden,noImport",
    "plannedEndDate"=>"readonly",
    "assignedWork"=>"hidden,noImport",
    "leftWork"=>"hidden,noImport",
    "validatedCost"=>"hidden,noImport",
    "plannedCost"=>"hidden,noImport",
    "realCost"=>"hidden,noImport",
    "assignedCost"=>"hidden,noImport",
    "leftCost"=>"hidden,noImport",
    "realEndDate"=>"readonly,noImport",
    "idMilestonePlanningMode"=>"required,mediumWidth",
    "progress"=>"hidden,noImport",
    "expectedProgress"=>"hidden,noImport"
  );   
  
  private static $_databaseTableName = 'planningelement';
  
  private static $_databaseColumnName=array(
    "idMilestonePlanningMode"=>"idPlanningMode"
  );
    
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

    /** ========================================================================
   * Return the generic databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $this->initialStartDate=$this->initialEndDate;
    $this->validatedStartDate=$this->validatedEndDate;
    $this->plannedStartDate=$this->plannedEndDate;
    $this->realStartDate=$this->realEndDate;
    $this->initialDuration=0;
    $this->validatedDuration=0;
    $this->plannedDuration=0;
    $this->realDuration=0;
    $this->initialWork=0;
    $this->validatedWork=0;
    $this->plannedWork=0;
    $this->realWork=0;
    $this->elementary=1;
    return parent::save();
  }
  
}
?>