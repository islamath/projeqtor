<?php 
/* ============================================================================
 * defines recipient for a bill
 */ 
class Recipient extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $companyNumber;
  public $numTax;  
  public $taxFree;
  public $idle;
  public $_sec_IBAN;
  public $bank;
  public $ibanCountry;
  public $ibanKey;
  public $ibanBban;
  public $_col_2_2_Address;
  public $designation;
  public $street;
  public $complement;
  public $zip;
  public $city;
  public $state;
  public $country;  
  //public $_spe_projects;
  //public $_sec_Contacts;
  //public $_spe_contacts;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${name}</th>
    <th field="companyNumber" width="20%">${companyNumber}</th>
    <th field="numTax" width="20%">${numTax}</th>
    <th field="bank" width="10%">${bank}</th>
    <th field="idle" formatter="booleanFormatter" width="5%">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required");

  
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
  
/** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    $result="";
    if ($item=='projects') {
      $prj=new Project();
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('projects') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      $result .= $prj->drawProjectsList(array('idRecipient'=>$this->id,'idle'=>'0'));
      $result .="</td></tr></table>";
      return $result;
    } else if ($item=='contacts') {
      $con=new Contact();
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('contacts') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      $result .= $con->drawContactsList(array('idRecipient'=>$this->id,'idle'=>'0'));
      $result .="</td></tr></table>";
      return $result;
    }
  }
  
    /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="ibanCountry") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  calculateIbanKey();';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="ibanBban") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  calculateIbanKey(); ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
    return $colScript;
  }
  
}
?>