<?php 
/* ============================================================================
 * Profile defines right to the application or to a project.
 */ 
class Profile extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $profileCode;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_col_2_2;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="name" width="85%" formatter="translateFormatter">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  public $_isNameTranslatable = true;
  
  private static $_fieldsAttributes=array();
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    if ($this->profileCode=="ADM" or $this->profileCode=="PL") {
      self::$_fieldsAttributes["profileCode"]="readonly";
    }
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

  public function deleteControl() {
    $result="";
    if ($this->profileCode=='ADM' or $this->profileCode=='PL') {    
      $result="<br/>" . i18n("msgCannotDeleteProfile");
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
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
}
?>