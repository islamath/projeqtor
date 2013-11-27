<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class WorkPeriod extends SqlElement {

	 public $id;
	 public $idResource;
   public $periodRange;
   public $periodValue;
   public $submitted;
   public $submittedDate;
   public $validated;
   public $validatedDate;
   public $idLocker;
   public $comment;
   public $idle;
  
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