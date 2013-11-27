<?php 
/* ============================================================================
 * defines a term for a payment
 */ 
class Term extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idProject;
  public $idBill;
  public $idle;
  public $_col_2_2_Price;
  public $_tab_3_2 = array('real', 'validated', 'planned', 'amount', 'date');
  public $amount;
  public $validatedAmount; 
  public $plannedAmount;
  public $date;
  public $validatedDate;
  public $plannedDate;
  public $_col_1_1_trigger;
  public $_Dependency_Predecessor=array();
  public $_Note=array();
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameProject" width="20%">${idProject}</th>
    <th field="name" width="30%">${name}</th>
    <th field="amount" width="15%" fomatter="numericFormatter">${amount}</th>
    <th field="date" width="15%" formatter="dateFormatter">${date}</th>
    <th field="idBill" width="10%" formatter="booleanFormatter" >${isBilled}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required",
                                          "idProject"=>"required",
  								                        "idBill"=>"readonly",
                                          "validatedAmount"=>"readonly",
                                          "validatedDate"=>"readonly",
                                          "plannedAmount"=>"readonly",
                                          "plannedDate"=>"readonly"
  );  
  //private static $_databaseColumnName = array('realAmount'=>'amount');
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    if ($this->id) {
    	$crit=array('successorRefType'=>'Term', 'successorRefId'=>$this->id);
    	$dep=new Dependency();
    	$depList=$dep->getSqlElementsFromCriteria($crit, false);
    	$valAmount=0;
    	$valDate=null;
    	$plaAmount=0;
      $plaDate=null;
    	foreach ($depList as $dep) {
    		$obj=new PlanningElement($dep->predecessorId);
    		$valAmount+=$obj->validatedCost;
    		$plaAmount+=$obj->plannedCost;
    		if ($obj->validatedEndDate and (! $valDate or $valDate<$obj->validatedEndDate)) {
    		  $valDate=$obj->validatedEndDate;	
    		}
    	  if ($obj->plannedEndDate and (! $plaDate or $plaDate<$obj->plannedEndDate)) {
          $plaDate=$obj->plannedEndDate; 
        }
    	}
    	$this->validatedAmount=$valAmount;
    	$this->plannedAmount=$plaAmount;
    	$this->validatedDate=$valDate;
    	$this->plannedDate=$plaDate;
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
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
/** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
//  protected function getStaticDatabaseColumnName() {
//    return self::$_databaseColumnName;
//  }
 /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl() {
  	$result = "";
  	if ($this->idBill){
  		$result .= "<br/>" . i18n("cannotDeleteBilledTerm");
  	}
  	if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
/** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  

	public function save() {
		$result = parent::save();		
		return $result;
	}
  
}
?>