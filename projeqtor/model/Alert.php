<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class Alert extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $idIndicatorValue;
  public $idUser;
  public $alertType;
  public $alertInitialDateTime;
  public $alertDateTime;
  public $readFlag;
  public $alertReadDateTime; 
  public $_spe_markAsRead;
  public $idle;
  public $_col_2_2_Message;
  public $title;
  public $message;  
  // Define the layout that will be used for lists
  
  public $_noHistory=true;
  
  private static $_fieldsAttributes=array("idIndicatorValue"=>"hidden",
                                          "readFlag"=>"nobr", 
                                          "refType"=>"display,nobr", 
                                          "refId"=>"display",
                                          "title"=>"display, html",
                                          "message"=>"display, html");
  
    private static $_colCaptionTransposition = array('alertType'=>'type',
                                                     'refType'=>'element',
                                                     'idUser'=>'alertReceiver');
    
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameUser" width="10%" >${alertReceiver}</th>
    <th field="refType" width="10%" formatter="translatterFormatter" >${element}</th>
    <th field="refId" width="5%" >${id}</th>
    <th field="alertType" width="10%">${type}</th>
    <th field="title" width="40%" >${title}</th>
    <th field="readFlag" width="5%" formatter="booleanFormatter" >${readFlag}</th>
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
    
  public function drawSpecificItem($item){
    $result="";
    if ($item=='markAsRead') {
    	$user=$_SESSION['user'];
    	if ($user->id==$this->idUser and ! $this->readFlag) {
    	  $result .='<table><tr><td class="label">&nbsp;</td><td>';
        $result .='<button dojoType="dijit.form.Button" onclick="setAlertReadMessageInForm();">';
        $result .= i18n("markAsRead");
        $result .='</button>';
        $result .='</td></tr></table>';
    	}
    }
    return $result;
  }
  
}
?>