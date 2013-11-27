<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class Status extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $setHandledStatus;
  public $setDoneStatus;
  public $setIdleStatus;
  public $setCancelledStatus;
  public $color;
  public $sortOrder=0;
  public $idle;
  public $_col_2_2;
  public $isCopyStatus;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="30%">${name}</th>
    <th field="setHandledStatus" width="10%" formatter="booleanFormatter">${setHandledStatus}</th>
    <th field="setDoneStatus" width="10%" formatter="booleanFormatter">${setDoneStatus}</th>
    <th field="setIdleStatus" width="10%" formatter="booleanFormatter">${setIdleStatus}</th>
    <th field="setCancelledStatus" width="10%" formatter="booleanFormatter">${setCancelledStatus}</th>
    <th field="color" width="10%" formatter="colorFormatter">${color}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>  
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("isCopyStatus"=>"hidden");
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
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
    /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    /*if ($colName=="setIdleStatus") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (! dijit.byId("setDoneStatus").get("checked")) {';
      $colScript .= '      dijit.byId("setDoneStatus").set("checked", true);';
      $colScript .= '    }';      
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="setDoneStatus") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (! this.checked) { ';
      $colScript .= '    if ( dijit.byId("setIdleStatus").get("checked")) {';
      $colScript .= '      dijit.byId("setIdleStatus").set("checked", false);';
      $colScript .= '    }';      
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }*/
    return $colScript;
  }
  
  public function deleteControl() {
    $result="";
    if ($this->isCopyStatus==1) {    
      $result="<br/>" . i18n("msgCannotDeleteStatus");
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
}
?>