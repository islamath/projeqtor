<?php 
/* ============================================================================
 * ListYesNo defines a short List .
 */ 
class ListYesNo extends SqlElement {

  // Define the layout that will be used for lists
  public $id;
  public $list;
  public $name;
  public $code;
  public $sortOrder;
  public $idle;
	
  public $_isNameTranslatable = true;
   
    
  private static $_databaseCriteria = array('list'=>'yesNo');
  private static $_databaseTableName = 'list';
  
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
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
      /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
}
?>