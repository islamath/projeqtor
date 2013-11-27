<?php 
/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
class Mail extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_MailDescription;
  public $id;    // redefine $id to specify its visible place 
  public $idUser;
  public $mailDateTime;
  public $mailTo;
  public $mailStatus;
  public $idle;
  public $_col_2_2_MailItem;
  public $idProject;
  public $idMailable;
  public $refId;
  public $idStatus;
  public $_col_1_1_MailText;
  public $mailTitle;
  public $mailBody;
  
  
  public $_noHistory=true;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameUser" width="10%" >${sender}</th>
    <th field="mailTitle" width="50%" >${mailTitle}</th>
    <th field="mailDateTime" width="10%" >${mailDateTime}</th>
    <th field="mailStatus" width="10%" >${mailStatus}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
    
    private static $_fieldsAttributes=array('mailBody'=>'displayHtml',
        'mailTitle'=>'displayHtml');
       
    private static $_databaseColumnName = array('idMailable'=>'refType');
    
    private static $_colCaptionTransposition = array('refId'=> 'id');
    
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
  
    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
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
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  public function save() {
  	$this->mailBody=substr($this->mailBody,0,65536); // Limit for MySql Text field
  	return parent::save();
  }
}
?>