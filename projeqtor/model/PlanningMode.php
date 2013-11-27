<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class PlanningMode extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2;
  public $id;
  public $name;
  public $code;
  public $sortOrder=0;
  public $mandatoryStartDate;
  public $mandatoryEndDate;
  public $mandatoryDuration;
  public $applyTo;
  public $idle ;
  public $_col_2_2;
  
  public $_isNameTranslatable = true;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="85%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

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
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
}
?>