<?php 
/* ============================================================================
 * Client is the owner of a project.
 */ 
class Filter extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $refType;
  public $idUser;
  public $_FilterCriteriaArray;
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    if ($id) {
      $crit=array('idFilter'=>$id);
      $obj=new FilterCriteria();
      $this->_FilterCriteriaArray=$obj->getSqlElementsFromCriteria($crit, false);
    }
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
  
}
?>