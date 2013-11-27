<?php 
/* ============================================================================
 * Menu defines list of items to present to users.
 */ 
class MenuList extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $name;
//  public $idMenu;
//  public $type;
//  public $sortOrder=0;
  public $idle;
  
//  public $_isNameTranslatable = true;
  public $_noHistory=true; // Will never save history for this object
    private static $_databaseTableName = 'menu';
  
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
}
?>