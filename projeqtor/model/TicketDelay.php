<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class TicketDelay extends Delay {

  // Define the layout that will be used for lists
    
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place
  public $idTicketType;
  public $idUrgency;
  public $value;
  public $idDelayUnit;
  public $idle;
  public $_col_2_2;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="nameTicketType" width="25%">${idTicketType}</th>
    <th field="nameUrgency" width="25%">${urgency}</th>
    <th field="value" width="10%" formatter="numericFormatter">${value}</th>
    <th field="nameDelayUnit" width="25%" formatter="translateFormatter">${unit}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("idTicketType"=>"required",
                                          "idType"=>"hidden", 
                                          "idUrgency"=>"required",
                                          "value"=>"required, nobr",
                                          "idDelayUnit"=>"required",
                                          "scope"=>"hidden");
  
  private static $_databaseCriteria = array('scope'=>'Ticket');
  
  private static $_databaseColumnName = array("idTicketType"=>"idType");
  
  private static $_colCaptionTransposition = array('idDelayUnit'=>'unit');
  
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

  public function control() {
    $result="";
    $crit="scope='Ticket' and idType='" . Sql::fmtId($this->idTicketType) . "' and idUrgency='" . Sql::fmtId($this->idUrgency) . "' and id<>'" . Sql::fmtId($this->id) . "'";
    $list=$this->getSqlElementsFromCriteria(null, false, $crit);
    if (count($list)>0) {
      $result.="<br/>" . i18n('errorDuplicateTicketDelay',null);
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;    
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
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
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
}
?>