<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class DelayUnit extends SqlElement {

  // Define the layout that will be used for lists
  public $id;    // redefine $id to specify its visible place 
  public $code;
  public $name;
  public $type;
  public $idle;
  public $_isNameTranslatable = true;
  
  private static $_databaseTableName = 'delayunit';
  private static $_databaseCriteria = array('type'=>'delay');
  
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
    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
 /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
}
?>