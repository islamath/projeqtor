<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class Work extends GeneralWork {

	 public $idBill;
  
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