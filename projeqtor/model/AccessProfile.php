<?php 
/* ============================================================================
 * Profile defines right to the application or to a project.
 */ 
class AccessProfile extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idAccessScopeRead;
  public $idAccessScopeCreate;
  public $idAccessScopeUpdate;
  public $idAccessScopeDelete;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_col_2_2;
  
  public $_isNameTranslatable = true;
  
  private static $_fieldsAttributes=array("name"=>"required", 
                                  "idAccessScopeRead"=>"required",
                                  "idAccessScopeCreate"=>"required",
                                  "idAccessScopeUpdate"=>"required",
                                  "idAccessScopeDelete"=>"required"
  );  

  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="25%" formatter="translateFormatter">${name}</th>
    <th field="nameAccessScopeRead" width="15%" formatter="translateFormatter">${idAccessScopeRead}</th>
    <th field="nameAccessScopeCreate" width="15%" formatter="translateFormatter">${idAccessScopeCreate}</th>
    <th field="nameAccessScopeUpdate" width="15%" formatter="translateFormatter">${idAccessScopeUpdate}</th>
    <th field="nameAccessScopeDelete" width="15%" formatter="translateFormatter">${idAccessScopeDelete}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>         
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
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
 
}
?>