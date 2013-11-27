<?php 
/* ============================================================================
 * Menu defines list of items to present to users.
 */ 
class WorkflowStatus extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idWorkflow;
  public $idStatusFrom;
  public $idStatusTo;
  public $idProfile;
  public $allowed;
  
  public $_noHistory;
  
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
    
}
?>