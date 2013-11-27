<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class TargetVersion extends Version {

	public $_constructForName;
	
    private static $_databaseTableName = 'version';
    
    private static $_databaseCriteria = array('isEis'=>'0');
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
  	unset($this->_VersionProject);
    parent::__construct($id);
    if ($this->name) {
    	if (trim($this->realEisDate)){
    	  $this->name.=" [" . htmlFormatDate($this->realEisDate) . "]";
    	} else if (trim($this->plannedEisDate)){
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
  
    /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  } 
  
}
?>