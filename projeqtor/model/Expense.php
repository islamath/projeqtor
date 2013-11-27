<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Expense extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $idProject;
  public $idResource;
  public $idUser;
  //public $idExpenseType;
  public $name;
  public $description;
  public $_col_2_2_treatment;
  public $idStatus;  
  public $expensePlannedDate;
  public $plannedAmount;
  public $expenseRealDate;
  public $realAmount;
  public $day;
  public $week;
  public $month;
  public $year;
  public $idle;
  //public $_col_1_1_Detail;
  public $_ExpenseDetail=array();
  public $_Attachement=array();
  public $_Note=array();


  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameExpenseType" width="15%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("idProject"=>"required",
                                  "name"=>"required",
                                  "idExpenseType"=>"required",
                                  "idStatus"=>"required",
  								                "idUser"=>"hidden",
                                  "day"=>"hidden",
                                  "week"=>"hidden",
                                  "month"=>"hidden",
                                  "year"=>"hidden"
  );  
  
  private static $_colCaptionTransposition = array('expensePlannedDate'=>'plannedDate',
  'expenseRealDate'=>'realDate'
  );
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    
    if (count($this->getExpenseDetail())>0) {
    	self::$_fieldsAttributes['realAmount']="readonly";
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }

  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    }
    return $colScript;
  }

  public function control() {
  	$result="";
  	//if (! $this->plannedAmount and ! $this->realAmount) {
  	//	$result.= '<br/>' . i18n('msgEnterRPAmount');
  	//}
    //if (! $this->expensePlannedDate and ! $this->expenseRealDate) {
    //  $result.= '<br/>' . i18n('msgEnterRPDate');
    //}
    if ( ($this->plannedAmount and ! $this->expensePlannedDate ) 
      or (! $this->plannedAmount and $this->expensePlannedDate ) ){
      $result.= '<br/>' . i18n('msgEnterPlannedDA');	
    }
    if ( ($this->realAmount and ! $this->expenseRealDate ) 
      or (! $this->realAmount and $this->expenseRealDate ) ){
      $result.= '<br/>' . i18n('msgEnterRealDA');  
    }
    if ($result=="") {
    	return 'OK';
    } else {
    	return $result;
    }
  }
  
  public function save() {
    $this->idUser=$this->idResource;
    if ($this->expenseRealDate) {
    	$this->setDates($this->expenseRealDate);
    } else {
    	$this->setDates($this->expensePlannedDate);
    }
    return parent::save();
  }

  public function getExpenseDetail() {
  	$result=array();
    $ed=new ExpenseDetail();
    $crit=array('idExpense'=>$this->id);
    $edList=$ed->getSqlElementsFromCriteria($crit, false, null, 'expenseDate');
    return $edList;
  }
  
  public function updateAmount() {
  	if (count($this->_ExpenseDetail)==0) {
  		return;
  	}
  	$total=0;
  	$date=null;
  	foreach ($this->_ExpenseDetail as $ed) {
  		$total+=$ed->amount;
  		$date=$ed->expenseDate;
  	} 
  	$this->realAmount=$total;
  	if (! $this->expenseRealDate) {
  	  $this->expenseRealDate=$date;
  	}
  	$this->save();
  }
  
  public function setDates($workDate) {
    $year=substr($workDate,0,4);
    $month=substr($workDate,5,2);
    $day=substr($workDate,8,2);
    $this->day=$year . $month . $day;
    $this->month=$year . $month; 
    $this->year=$year;
    $this->week=$year . weekNumber($workDate);
  }
  
}
?>