<?php 
/* ============================================================================
 * Client is the owner of a project.
 */ 
class Message extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idMessageType;
  public $idProfile;
  public $idProject;
  public $idUser;
  public $idle;
  public $_col_2_2_Message;
  public $description;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="40%">${title}</th>
    <th field="colorNameMessageType" width="10%" formatter="colorNameFormatter">${idMessageType}</th>
    <th field="nameProfile" width="10%" formatter="translateFormatter">${idProfile}</th>
    <th field="nameProject" width="10%">${idProject}</th>
    <th field="nameUser" width="15%">${idUser}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array('name'=> 'title', 'description'=>'message');
  
  private static $_fieldsAttributes=array("name"=>"required", 
                                  "idMessageType"=>"required"
  );  
  
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
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