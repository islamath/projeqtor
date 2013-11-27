<?php 
/* ============================================================================
 * Assignment defines link of resources to an Activity (or else)
 */ 
class ExpenseDetailType extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_description;
  public $id;
  public $name;
  public $sortOrder;
  public $value01; 
  public $unit01;
  public $value02;
  public $unit02;
  public $value03;
  public $unit03;
  public $idle;
  public $description;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="25%" >${name}</th>
    <th field="sortOrder" width="5%">${sortOrder}</th>
    <th field="value01" width="10%" >${value}</th>
    <th field="unit01" width="10%" >${unit}</th>
    <th field="value02" width="10%" >${value}</th>
    <th field="unit02" width="10%" >${unit}</th>
    <th field="value03" width="10%" >${value}</th>
    <th field="unit03" width="10%" >${unit}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
    
      private static $_fieldsAttributes=array("name"=>"required",
                                              "value01"=>"nobr",
                                              "value02"=>"nobr",
                                              "value03"=>"nobr"
      );
      
      private static $_colCaptionTransposition = array('value01'=>'valueUnit', 
                                                   'value02'=> 'valueUnit',
                                                   'value03' => 'valueUnit',
                                                   'unit01'=>'unit', 
                                                   'unit02'=>'unit',
                                                   'unit03'=>'unit');
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /**
   * Save object 
   * @see persistence/SqlElement#save()
   */
  public function save() {
    $result = parent::save();
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
    if ( ($this->value01 and ! $this->unit01)  
      or ($this->value02 and ! $this->unit02)  
      or ($this->value03 and ! $this->unit03) ) {
    	 $result.='<br/>' . i18n('errorValueWithoutUnit');
    } 
    
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
}
?>