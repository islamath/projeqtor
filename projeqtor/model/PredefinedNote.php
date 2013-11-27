<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class PredefinedNote extends SqlElement {

  public $_col_1_2_Description;
  public $id;
  public $scope;
  public $idTextable;
  public $idType;
  public $name;
  public $text;
  public $idle;
	public $_col_2_2;  

	// Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="nameTextable" width="15%" formatter="translateFormatter">${element}</th>
    <th field="nameType" width="15%">${type}</th>
    <th field="name" width="55%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required",
                                          "scope"=>"hidden");
  
  private static $_colCaptionTransposition = array('idTextable'=>'element', 
                                                   'idType'=> 'type');
  
  private static $_databaseCriteria = array('scope'=>'Note');
  private static $_databaseTableName = 'predefinedtext';
  
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
  

  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
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
    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
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

    if ($colName=="idTextable") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  type=textableArray[this.value];';
      $colScript .= "  refreshList('id'+type+'Type', '', '', '', 'idType');";
      $colScript .= '  dijit.byId("idType").reset(); ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
    return $colScript;
  }
  
}
?>