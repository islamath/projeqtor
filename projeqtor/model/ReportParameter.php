<?php 
/* ============================================================================
 * Stauts defines list of Priorities an activity or action can get in (lifecylce).
 */ 
class ReportParameter extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idReport;
  public $name;
  public $paramType;
  public $defaultValue;
  public $sortOrder;
  public $idle; 
  // Define the layout that will be used for lists
  
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
   * Return the specific layout
   * @return the layout
   */
  
}
?>