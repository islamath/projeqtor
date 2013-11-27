<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Ticket extends SqlElement {

  // List of fields that will be exposed in general user interface
  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idTicketType;
  public $name;
  public $externalReference;
  public $idUrgency;
  public $creationDateTime;
  public $idUser;
  public $idContact;
  public $Origin;
  public $idTicket;
  public $idContext1;
  public $idContext2;
  public $idContext3;
  public $idProduct;
  public $idOriginalVersion;
  public $_OtherOriginalVersion=array();
  public $description;
  public $_col_2_2_treatment;
  public $idActivity;
  public $idStatus;
  public $idResource;
  public $idCriticality;
  public $idPriority;
  public $initialDueDateTime; // is an object
  public $actualDueDateTime;
  public $WorkElement;
  public $handled;
  public $handledDateTime;
  public $done;
  public $doneDateTime;
  public $idle;
  public $idleDateTime;
  public $cancelled;
  public $_lib_cancelled;
  public $idTargetVersion;
  public $_OtherTargetVersion=array();
  public $result;
  public $_col_1_1_Link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="nameProject" width="7%" >${idProject}</th>
    <th field="nameTicketType" width="7%" >${idTicketType}</th>
    <th field="name" width="16%" >${name}</th>
    <th field="nameUser" width="7%" >${issuer}</th>
    <th field="colorNameUrgency" width="7%" formatter="colorNameFormatter">${idUrgency}</th>
    <th field="colorNamePriority" width="7%" formatter="colorNameFormatter">${idPriority}</th>
    <th field="colorNameStatus" width="7%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="actualDueDateTime" width="7%" formatter="dateTimeFormatter">${actualDueDateTime}</th>
    <th field="nameProduct" width="7%" >${idProduct}</th>
    <th field="nameTargetVersion" width="7%" >${targetVersion}</th>
    <th field="nameResource" width="7%" >${responsible}</th>
    <th field="handled" width="3%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="3%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="3%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idTicketType"=>"required",
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDateTime"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "idContext1"=>"nobr,size1/3,title",
                                  "idContext2"=>"nobr,title", 
                                  "idContext3"=>"title"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
                                                   'idActivity' => 'planningActivity',
                                                   'idContact' => 'requestor',
                                                   'idTargetVersion'=>'targetVersion',
                                                   'idOriginalVersion'=>'originalVersion',
                                                   'idTicket'=>'duplicateTicket',
                                                   'idContext1'=>'idContext');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array('idTargetVersion'=>'idVersion');
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    if ($this->idActivity and $this->WorkElement->realWork>0) {
      self::$_fieldsAttributes['idActivity']='readonly';
    }
    if (Parameter::getGlobalParameter('realWorkOnlyForResponsible')=='YES') {
      if ($this->id and $this->idResource != $_SESSION['user']->id) {
        WorkElement::lockRealWork();
      }
    }
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
   * Return the specific databaseColumnName
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
    if ($colName=="idCriticality" or $colName=="idUrgency") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';  
      $colScript .= htmlGetJsTable('Urgency', 'value');
      $colScript .= htmlGetJsTable('Criticality', 'value');
      $colScript .= htmlGetJsTable('Priority', 'value');
      $colScript .= '  var urgencyValue=0; var criticalityValue=0; var priorityValue=0;';
      $colScript .= '  var filterUrgency=dojo.filter(tabUrgency, function(item){return item.id==dijit.byId("idUrgency").value;});';
      $colScript .= '  var filterCriticality=dojo.filter(tabCriticality, function(item){return item.id==dijit.byId("idCriticality").value;});';
      $colScript .= '  dojo.forEach(filterUrgency, function(item, i) {urgencyValue=item.value;});';
      $colScript .= '  dojo.forEach(filterCriticality, function(item, i) {criticalityValue=item.value;});';
      $colScript .= '  calculatedValue = Math.round(urgencyValue*criticalityValue/2);';
      $colScript .= '  var filterPriority=dojo.filter(tabPriority, function(item){return item.value==calculatedValue;});';
      $colScript .= '  if ( filterPriority.length==0) {';
      $colScript .= '    calculatedValue = Math.round(calculatedValue/2);';
      $colScript .= '    var filterPriority=dojo.filter(tabPriority, function(item){varChanged=true; return item.value==calculatedValue;});';
      $colScript .= '  }';
      $colScript .= '  var setVar="";';
      $colScript .= '  dojo.forEach(filterPriority, function(item, i) {if (setVar=="") dijit.byId("idPriority").set("value",item.id);});';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="actualDueDateTime") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var upd=dijit.byId("initialDueDateTime");';
      $colScript .= '  if (upd && upd.get("value")==null) { ';
      $colScript .= '    upd.set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';      
      $colScript .= '</script>';     
    } else if ($colName=="actualDueDateTimeBis") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var upd=dijit.byId("initialDueDateTimeBis");';
      $colScript .= '  if (upd && upd.get("value")==null) { ';
      $colScript .= '    upd.set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="initialDueDateTime") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var upd=dijit.byId("actualDueDateTime");';
      $colScript .= '  if (upd && upd.get("value")==null) { ';
      $colScript .= '    upd.set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="initialDueDateTimeBis") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var upd=dijit.byId("actualDueDateTimeBis");';
      $colScript .= '  if (upd && upd.get("value")==null) { ';
      $colScript .= '    upd.set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    }
    return $colScript;
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if (trim($this->idActivity)) {    
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
      }
    }
    if ($this->idTicket) {
    	if ($this->idTicket==$this->id) {
    		$result.='<br/>' . i18n('duplicateIsSame');
    	} else {
    	  $duplicate=new Ticket($this->idTicket);
    	  if ($duplicate->idTicket and $duplicate->idTicket!=$this->id) {
    		  $result.='<br/>' . i18n('duplicateAlreadyLinked');
    	  }
    	}
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
  
  public function deleteControl() { 
    $result='';
    if ($this->WorkElement and $this->WorkElement->realWork>0) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  public function save() {
  	$old=$this->getOld();
  	if (! trim($this->creationDateTime)) {
  	  $this->creationDateTime=date('Y-m-d H:i');
  	}
  	if ($this->idTicketType != $old->idTicketType 
  	 or $this->idUrgency != $old->idUrgency
  	 or $this->creationDateTime != $old->creationDateTime) {
  	 	$crit=array('idTicketType'=>$this->idTicketType, 'idUrgency'=>$this->idUrgency, 'idle'=>'0');
  		$delay=SqlElement::getSingleSqlElementFromCriteria('TicketDelay', $crit);
  		if ($delay and $delay->id) {
  			$unit=new DelayUnit($delay->idDelayUnit);
  			$this->initialDueDateTime=addDelayToDatetime($this->creationDateTime,$delay->value, $unit->code);
  			if (! trim($this->actualDueDateTime) or ($old->actualDueDateTime==$old->initialDueDateTime 
  			                                     and $old->actualDueDateTime==$this->actualDueDateTime) ) {
  			  $this->actualDueDateTime=$this->initialDueDateTime;                                 	
  			}
  		}
  	}
  	if (isset($this->WorkElement)) {
  	  $this->WorkElement->done=$this->done;
  	  $this->WorkElement->idle=$this->idle;
  	}
  	$result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
  	if ($this->idTicket and ! $old->idTicket) {
      $duplicate=new Ticket($this->idTicket);
      if (! $duplicate->idTicket) {
      	$duplicate->idTicket=$this->id;
      	$duplicate->save();
      }
  	}
  	return $result;
  }

  public function getTitle($col) {
  	if (substr($col,0,9)=='idContext') {
  	  return SqlList::getNameFromId('ContextType', substr($col, 9));
  	} else {
  		return parent::getTitle($col);
  	} 
  	
  }
  
}
?>