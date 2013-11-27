<?php 
/* ============================================================================
 * ActionType defines the type of an issue.
 */ 
class DocumentType extends Type {

  // Define the layout that will be used for lists
    
  private static $_databaseCriteria = array('scope'=>'Document');
   
  private static $_fieldsAttributes=array(
    "mandatoryResultOnDone"=>"hidden",
    "_lib_mandatoryOnDoneStatus"=>"hidden",
    "lockHandled"=>"hidden",
    "_lib_statusMustChangeHandled"=>"hidden",
    "lockDone"=>"hidden",
    "_lib_statusMustChangeDone"=>"hidden",
    "lockIdle"=>"hidden",
    "_lib_statusMustChangeIdle"=>"hidden",
    "lockCancelled"=>"hidden",
    "_lib_statusMustChangeCancelled"=>"hidden",
    "mandatoryResourceOnHandled"=>"hidden",
    "_lib_mandatoryOnHandledStatus"=>"hidden");
  
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
      /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
 
}
?>