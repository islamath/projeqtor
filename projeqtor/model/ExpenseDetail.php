<?php 
/* ============================================================================
 * Assignment defines link of resources to an Activity (or else)
 */ 
class ExpenseDetail extends SqlElement {

  // extends SqlElement, so has $id
  public $id;
  public $idProject; 
  public $idExpense; 
  public $idExpenseDetailType; 
  public $name;
  public $description;
  public $expenseDate; 
  public $amount; 
  public $value01;
  public $value02;
  public $value03;
  public $unit01;
  public $unit02;
  public $unit03;
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

// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /**
   * Save object 
   * @see persistence/SqlElement#save()
   */
  public function save() {
    $result = parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $exp=new Expense($this->idExpense);
    $exp->updateAmount();
    return $result;
  }
  
  /**
   * Delete object and dispatch updates to top 
   * @see persistence/SqlElement#save()
   */
  public function delete() {
  	$ref=$this->idExpense;
  	$result = parent::delete();
    $exp=new Expense($ref);
    $exp->updateAmount();  	
  	return $result;
  }
    
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $result = parent::control();
    return $result;
  }
  
  public function getFormatedDetail() {
  	$result="";
  	if ($this->value01 or $this->unit01) {
  		$result.=($result)?' <b>x</b> ':'';
  		$result.=htmlDisplayNumeric($this->value01) . " " . $this->unit01;
  	}
    if ($this->value02 or $this->unit02) {
      $result.=($result)?' <b>x</b> ':'';
      $result.=htmlDisplayNumeric($this->value02) . " " . $this->unit02;
    }
    if ($this->value03 or $this->unit03) {
      $result.=($result)?' <b>x</b> ':'';
      $result.=htmlDisplayNumeric($this->value03) . " " . $this->unit03;
    }
    return $result;
  }
}
?>