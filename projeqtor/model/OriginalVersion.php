<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class OriginalVersion extends Version {

    private static $_databaseTableName = 'version';
    public $_constructForName;
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    if ($this->name) {
      if ($this->realEisDate){
        $this->name.=" [" . htmlFormatDate($this->realEisDate) . "]";
      } else if ($this->plannedEisDate){
        $this->name.=" (" . htmlFormatDate($this->plannedEisDate) . ")";
      }
    }
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