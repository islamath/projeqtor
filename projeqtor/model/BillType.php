<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class BillType extends Type {

  // Define the layout that will be used for lists
    
  private static $_databaseCriteria = array('scope'=>'Bill');
  
  private static $_fieldsAttributes=array(
    "mandatoryResultOnDone"=>"hidden",
    "_lib_mandatoryOnDoneStatus"=>"hidden",
    "lockHandled"=>"hidden",
    "_lib_statusMustChangeHandled"=>"hidden",
    "mandatoryResourceOnHandled"=>"hidden",
    "_lib_mandatoryOnHandledStatus"=>"hidden");
  
   private static $_colCaptionTransposition = array('mandatoryDescription'=>'comment');   
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
  
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }
}
?>