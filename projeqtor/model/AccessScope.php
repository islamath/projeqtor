<?php 
/* ============================================================================
 * Profile defines right to the application or to a project.
 */ 
class AccessScope extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $accessCode;
  public $sortOrder=0;
  public $idle;
  
  public $_isNameTranslatable = true;

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