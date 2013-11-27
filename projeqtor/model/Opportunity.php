<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Opportunity extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idOpportunityType;
  public $name;
  public $creationDate;
  public $idUser;
  public $Origin;  
  public $cause;
  public $impact;
  public $idSeverity;
  public $idLikelihood;
  public $idCriticality;
  public $description;
  public $_col_2_2_treatment;
  public $idStatus;
  public $idResource;
  public $idPriority;
  public $initialEndDate; // is an object
  public $actualEndDate;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  //public $_sec_linkAction;
  //public $_Link_Action=array();
  //public $_sec_linkIssue;
  //public $_Link_Issue=array();
  public $_col_1_1_link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameOpportunityType" width="10%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameSeverity" width="5%" formatter="colorNameFormatter" >${idSeverity}</th>
    <th field="colorNameLikelihood" width="5%" formatter="colorNameFormatter" >${opportunityImprovement}</th>
    <th field="colorNameCriticality" width="5%" formatter="colorNameFormatter" >${idCriticality}</th>
    <th field="colorNameStatus" width="8%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" width="8%" >${responsible}</th>
    <th field="colorNamePriority" width="5%" formatter="colorNameFormatter" >${idPriority}</th>
    <th field="actualEndDate" width="8%" formatter="dateFormatter">${actualEndDate}</th>
    <th field="handled" width="4%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="4%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idStatus"=>"required",
                                  "idOpportunityType"=>"required",
                                  "creationDate"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer',
                                                   'idResource'=> 'responsible',
                                                   'idOpportunityType'=>'type',
                                                   'idLikelihood'=>'opportunityImprovement',
                                                   'cause'=>'opportunitySource');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
  
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

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
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

    if ($colName=="idSeverity" or $colName=="idLikelihood") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';  
      $colScript .= htmlGetJsTable('Severity', 'value');
      $colScript .= htmlGetJsTable('Likelihood', 'value');
      $colScript .= htmlGetJsTable('Criticality', 'value');
      $colScript .= '  var serverityValue=0; var likelihoodValue=0; var criticalityValue=0;';
      $colScript .= '  var filterSeverity=dojo.filter(tabSeverity, function(item){return item.id==dijit.byId("idSeverity").value;});';
      $colScript .= '  var filterLikelihood=dojo.filter(tabLikelihood, function(item){return item.id==dijit.byId("idLikelihood").value;});';
      $colScript .= '  dojo.forEach(filterSeverity, function(item, i) {serverityValue=item.value;});';
      $colScript .= '  dojo.forEach(filterLikelihood, function(item, i) {likelihoodValue=item.value;});';
      $colScript .= '  calculatedValue = Math.round(serverityValue*likelihoodValue/2);';
      $colScript .= '  var filterCriticality=dojo.filter(tabCriticality, function(item){return item.value==calculatedValue;});';
      $colScript .= '  dojo.forEach(filterCriticality, function(item, i) {dijit.byId("idCriticality").set("value",item.id);});';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="initialEndDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualEndDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualEndDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualEndDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialEndDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialEndDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    } 
    return $colScript;
  }
}
?>