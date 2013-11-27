<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class Delay extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place
  //public $scope; 
  public $idType;
  public $idUrgency;
  public $value;
  public $idDelayUnit;
  public $idle;
  public $_col_2_2;
  
  public $_noCopy;
  
  // Define the layout that will be used for lists
  
  private static $_fieldsAttributes=array("idType"=>"hidden", 
                                          "idUrgency"=>"required",
                                          "value"=>"required",
                                          "idDelayUnit"=>"required",
                                          "scope"=>"hidden");
  
  private static $_databaseCriteria = array();
  private static $_databaseTableName = 'delay';
  
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
// GET STATIC DATA FUNCTIONS
// ============================================================================**********

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  
}
?>