<?php 
/* ============================================================================
 * HabilitationOther defines specific right access (for impoutation, work, budget)
 */ 
class HabilitationOther extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProfile;
  public $scope;
  public $rightAccess;
  
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Execute specific query to dispatch updates so that if a sub-menu is activates
   * its main menu is also activated.
   * Also dispatch to unactivate main parameter if no-submenu is activated
   * @return void
   */
  static function correctUpdates() {

    return;
            
  }

}
?>