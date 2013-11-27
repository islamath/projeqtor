<?php
/** ===========================================================================**********
 * Abstract class defining all methods to interact with database,
 * using Sql class.
 * Give public visibility to elementary methods (save, delete, copy, ...)
 * and constructor.
 */
abstract class SqlElement {
	// List of fields that will be exposed in general user interface
	public $id; // every SqlElement have an id !!!

	private static $staticCostVisibility=null;
	private static $staticWorkVisibility=null;

	// Store the layout of the different object classes
	private static $_tablesFormatList=array();

	// Define the layout that will be used for lists
	private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="85%">${name}</th> 
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

	// Define the specific field attributes
	private static $_fieldsAttributes=array("name"=>"required");

	// Management of cache for queries : cache is only valid during current script
	public static $_cachedQuery=array('Habilitation'=>array(),'Menu'=>array());

	// All dependencies between objects :
	//    control => sub-object must not exist to allow deletion
	//    cascade => sub-objects are automaticaly deleted
	private static $_relationShip=array(
    "AccessProfile" =>      array("AccessRight"=>"cascade"),
    "AccessScopeRead" =>    array("AccessProfile"=>"control"),
    "AccessScopeCreate" =>  array("AccessProfile"=>"control"),
    "AccessScopeUpdate" =>  array("AccessProfile"=>"control"),
    "AccessScopeDelete" =>  array("AccessProfile"=>"control"),
    "Assignment" =>         array("Work"=>"control",
                                  "PlannedWork"=>"cascade"),
    "Action" =>             array("Note"=>"cascade",
                                  "Link"=>"cascade"),
    "ActionType" =>         array("Action"=>"control"),
    "Activity" =>           array("Milestone"=>"control", 
                                  "Activity"=>"control", 
                                  "Ticket"=>"control",
                                  "Assignment"=>"control",
                                  "Note"=>"cascade",
                                  "Attachement"=>"cascade",
                                  "Link"=>"cascade",
                                  "Dependency"=>"cascade",
                                  "PlannedWork"=>"cascade"),
    "ActivityType" =>       array("Activity"=>"control"),
    "Bill" =>               array("BillLine"=>"control"),
    "BillType" =>           array("Bill"=>"control"),
    "Contact" =>            array("Affectation"=>"control",
                                  "Activity"=>"control",
                                  "Bill"=>"control",
                                  "Product"=>"control",
                                  "Project"=>"control",
                                  "Ticket"=>"control",
                                  "Version"=>"control"),
    "Client" =>             array("Project"=>"control"),
    "Criticality" =>        array("Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Requirement"=>"control"),
    "Decision" =>           array("Link"=>"cascade"),
    "DecisionType" =>       array("Decision"=>"control"),
    "Document" =>           array("DocumentVersion"=>"control",
                                  "Link"=>"cascade",
                                  "Approver"=>"control"),
    "DocumentVersion" =>    array("Approver"=>"cascade"),
    "DocumentDirectory" =>  array("Document"=>"control",
                                  "DocumentDirectory"=>"control"),
    "Feasibility" =>        array("Requirement"=>"control"),
    "Filter" =>             array("FilterCriteria"=>"cascade"),
    "Issue" =>              array("Attachement"=>"cascade",
                                  "Note"=>"cascade",
                                  "Link"=>"cascade"),
    "IssueType" =>          array("Issue"=>"control"),
    "Likelihood" =>         array("Risk"=>"control"),
    "Meeting" =>            array("Link"=>"cascade", "Assignment"=>"cascade"),
    "MeetingType" =>        array("Meeting"=>"control","PeriodicMeeting"=>"control"),
    "Menu" =>               array("AccessRight"=>"cascade"),
    "MessageType" =>        array("Message"=>"control"),
    "Milestone" =>          array("Attachement"=>"cascade",
                                  "Note"=>"cascade",
                                  "Link"=>"cascade",
                                  "Dependency"=>"cascade"),
    "MilestoneType" =>      array("Milestone"=>"control"),
    "PeriodicMeeting" =>    array("Meeting"=>"cascade","Assignment"=>"cascade"),
    "Priority" =>           array("Issue"=>"control", 
                                  "Ticket"=>"control"),
    "Profile" =>            array("AccessRight"=>"cascade",
                                  "Habilitation"=>"cascade",
                                  "Message"=>"cascade",
                                  "Resource"=>"control",
                                  "User"=>"control"),
    "ProjectType" =>        array("Project"=>"control"), 
    "Product" =>            array("Version"=>"control",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Project" =>            array("Action"=>"control",
                                  "Activity"=>"control",
                                  "Affectation"=>"control",
                                  "Document"=>"control",
                                  "Issue"=>"control",
                                  "IndividualExpense"=>"control",
                                  "ProjectExpense"=>"control",
                                  "Term"=>"control",
                                  "Bill"=>"control",
                                  "Message"=>"cascade",
                                  "Milestone"=>"control",
                                  "Parameter"=>"cascade", 
                                  "Project"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Work"=>"control",
                                  "Dependency"=>"cascade",
                                  "Decision"=>"control",
                                  "Meeting"=>"control",
                                  "VersionProject"=>"cascade",
                                  "Question"=>"control",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Question" =>           array("Link"=>"cascade"),
    "QuestionType" =>       array("Question"=>"control"),
    "Recipient" =>          array("Bill"=>"control",
                                  "Project"=>"control"),
    "RequirementType" =>    array("Requirement"=>"control"),
    "Requirement" =>        array("Attachement"=>"cascade",
                                  "Note"=>"cascade",
                                  "Link"=>"cascade",
                                  "Requirement"=>"control"),
    "Resource" =>           array("Action"=>"control", 
                                  "Activity"=>"control",
                                  "Affectation"=>"control",
                                  "Assignment"=>"control",
                                  "Issue"=>"control",
                                  "Milestone"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Work"=>"control",
                                  "Decision"=>"control",
                                  "Meeting"=>"control",
                                  "Question"=>"control",
                                  "ResourceCost"=>"cascade",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Risk" =>               array("Attachement"=>"cascade",
                                  "Note"=>"cascade",
                                  "Link"=>"cascade"),
    "RiskLevel" =>           array("Requirement"=>"control"),
    "RiskType" =>           array("Risk"=>"control"),
    "Role" =>               array("Affectation"=>"control", 
                                  "Assignment"=>"control",
                                  "Resource"=>"control",
                                  "ResourceCost"=>"control"),
    "Severity" =>           array("Risk"=>"control"),
    "Status" =>             array("Action"=>"control", 
                                  "Activity"=>"control",
                                  "Issue"=>"control",
                                  "Milestone"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Decision"=>"control",
                                  "Meeting"=>"control",
                                  "Question"=>"control",
                                  "StatusMail"=>"cascade",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Team" =>               array("Resource"=>"control"),
    "Term" =>               array("Dependency"=>"cascade"),
    "TestCase" =>           array("TestCase"=>"control",
                                  "TestCaseRun"=>"control" ),
    "TestCaseType" =>       array("TestCase"=>"control"),
    "TestSession" =>        array("TestCaseRun"=>"cascade" ),
    "TestSessionType" =>    array("TestSession"=>"control"),
    "Ticket" =>             array("Ticket"=>"control",
                                  "Attachement"=>"cascade",
                                  "Note"=>"cascade",
                                  "Link"=>"cascade",
                                  "Work"=>"cascade"),
    "TicketType" =>         array("Ticket"=>"control"),
    "Urgency" =>            array("Ticket"=>"control",
                                  "Requirement"=>"control"),
    "User" =>               array("Affectation"=>"control", 
                                  "Action"=>"control", 
                                  "Activity"=>"control",
                                  "Attachement"=>"control",
                                  "Issue"=>"control",
                                  "Message"=>"cascade",
                                  "Milestone"=>"control",
                                  "Note"=>"control",
                                  "Parameter"=>"cascade", 
                                  "Project"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Decision"=>"control",
                                  "Meeting"=>"control",
                                  "Question"=>"control",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Version" =>            array("VersionProject"=>"cascade",
                                  "Requirement"=>"control",
                                  "TestCase"=>"control",
                                  "TestSession"=>"control"),
    "Workflow" =>            array("WorkflowStatus"=>"cascade", 
                                  "TicketType"=>"control", 
                                  "ActivityType"=>"control", 
                                  "MilestoneType"=>"control", 
                                  "RiskType"=>"control", 
                                  "ActionType"=>"control", 
                                  "IssueType"=>"control")
	);
	private static $_closeRelationShip=array(
    "AccessScopeRead" =>    array("AccessProfile"=>"control"),
    "AccessScopeCreate" =>  array("AccessProfile"=>"control"),
    "AccessScopeUpdate" =>  array("AccessProfile"=>"control"),
    "AccessScopeDelete" =>  array("AccessProfile"=>"control"),
    "Activity" =>           array("Milestone"=>"control", 
                                  "Activity"=>"control", 
                                  "Ticket"=>"control",
                                  "Assignment"=>"cascade"),
    "Document" =>           array("DocumentVersion"=>"cascade"),
    "DocumentDirectory" =>  array("Document"=>"control",
                                  "DocumentDirectory"=>"control"),
    "Product" =>            array("Version"=>"control",
                                  "Requirement"=>"cascade",
                                  "TestCase"=>"cascade",
                                  "TestSession"=>"control"),
    "Project" =>            array("Action"=>"control",
                                  "Activity"=>"control",
                                  "Affectation"=>"cascade",
                                  "Document"=>"cascade",
                                  "Issue"=>"control",
                                  "IndividualExpense"=>"cascade",
                                  "ProjectExpense"=>"cascade",
                                  "Term"=>"control",
                                  "Bill"=>"control",
                                  "Milestone"=>"control",
                                  "Project"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Decision"=>"cascade",
                                  "Meeting"=>"cascade",
                                  "VersionProject"=>"cascade",
                                  "Question"=>"cascade",
                                  "Requirement"=>"cascade",
                                  "TestCase"=>"cascade",
                                  "TestSession"=>"control"),
    "Requirement" =>        array("Requirement"=>"control"),
    "Resource" =>           array("Action"=>"control", 
                                  "Activity"=>"control",
                                  "Affectation"=>"cascade",
                                  "Assignment"=>"cascade",
                                  "Issue"=>"control",
                                  "Milestone"=>"control", 
                                  "Risk"=>"control", 
                                  "Ticket"=>"control",
                                  "Decision"=>"cascade",
                                  "Meeting"=>"cascade",
                                  "Question"=>"cascade",
                                  "Requirement"=>"cascade",
                                  "TestCase"=>"cascade",
                                  "TestSession"=>"control"),
    "TestCase" =>           array("TestCase"=>"cascade",
                                  "TestCaseRun"=>"cascade" ),
    "TestSession" =>        array("TestCaseRun"=>"cascade" ),
    "User" =>               array("Affectation"=>"cascade"),
    "Version" =>            array("VersionProject"=>"cascade",
                                  "TestSession"=>"control")
	);

	/** =========================================================================
	 * Constructor. Protected because this class must be extended.
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	protected function __construct($id = NULL, $withoutDependentObjects=false) {
		if (trim($id) and ! is_numeric($id)) {
			$class=get_class($this);
			traceHack("SqlElement->_construct : id '$id' is not numeric for class $class");
			return;
		} 
		$this->id=$id;
		if ($this->id=='') {
			$this->id=null;
		}
		$this->getSqlElement($withoutDependentObjects);
	}

	/** =========================================================================
	 * Destructor
	 * @return void
	 */
	protected function __destruct() {
	}

	// ============================================================================**********
	// UPDATE FUNCTIONS
	// ============================================================================**********

	/** =========================================================================
	 * Give public visibility to the saveSqlElement action
	 * @param force to avoid controls and force saving even if controls are false
	 * @return message including definition of html hiddenfields to be used
	 */
	public function save() {
		if (isset($this->_onlyCallSpecificSaveFunction) and $this->_onlyCallSpecificSaveFunction==true) return;
		return $this->saveSqlElement();
	}

	public function insert() { // Specific function to force insert with a defined id - Reserved to Import fonction
		$this->_onlyCallSpecificSaveFunction=true;
		$this->save(); // To force the update of fields calculated in the save function ...
		$this->_onlyCallSpecificSaveFunction=false;
		return $this->saveSqlElement(false, false, true);
	}

	public function saveForced($withoutDependencies=false) {
		return $this->saveSqlElement(true,$withoutDependencies);
	}

	/** =========================================================================
	 * Give public visibility to the purgeSqlElement action
	 * @return message including definition of html hiddenfields to be used
	 */
	public function purge($clause) {
		return $this->purgeSqlElement($clause);
	}

	/** =========================================================================
	 * Give public visibility to the closeSqlElement action
	 * @return message including definition of html hiddenfields to be used
	 */
	public function close($clause) {
		return $this->closeSqlElement($clause);
	}

	/** =========================================================================
	 * Give public visibility to the deleteSqlElement action
	 * @return message including definition of html hiddenfields to be used
	 */
	public function delete() {
		return $this->deleteSqlElement();
	}

	/** =========================================================================
	 * Give public visibility to the copySqlElement action
	 * @return the new object
	 */
	public function copy() {
		return $this->copySqlElement();
	}

	public function copyTo ($newClass, $newType, $newName, $setOrigin, $withNotes, $withAttachments,$withLinks) {
		return $this->copySqlElementTo($newClass, $newType, $newName, $setOrigin, $withNotes, $withAttachments,$withLinks);
	}
	/** =========================================================================
	 * Save an object to the database
	 * @return void
	 */
	private function saveSqlElement($force=false,$withoutDependencies=false,$forceInsert=false) {
		//traceLog("saveSqlElement(" . get_class($this) . "#$this->id)");
		// #305
		$this->recalculateCheckboxes();
		// select operation to be executed
		if ($force) {
			$control="OK";
		} else {
			$control=$this->control();
		}
		if ($control=="OK") {
			//$old=new Project();
			if (property_exists($this, 'idStatus') or property_exists($this,'reference') or property_exists($this,'idResource')
			or property_exists($this, 'description') or property_exists($this, 'result')) {
				$class=get_class($this);
				$old=new $class($this->id);
			}
			$statusChanged=false;
			$responsibleChanged=false;
			$descriptionChange=false;
			$resultChange=false;
			if (property_exists($this,'reference') and isset($old)) {
				$this->setReference(false, $old);
			}
			if (property_exists($this, 'idResource') and ! trim($this->idResource)) {
				$this->setDefaultResponsible();
			}
			if (! $this->id and $this instanceof PlanningElement) { // For planning element , check that not existing yet
				$critPe=array('refType'=>$this->refType, 'refId'=>$this->refId);
				$pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $critPe);
				if ($pe->id) {$this->id=$pe->id;}
			}
			if ($this->id != null  and !$forceInsert) {
				if (property_exists($this, 'idStatus')) {
					if ($this->idStatus and isset($old)) {
						if ($old->idStatus!=$this->idStatus) {
							$statusChanged=true;
						}
					}
				}
				$newItem=false;
				$returnValue=$this->updateSqlElement($force,$withoutDependencies);
			} else {
				if (property_exists($this, 'idStatus')) {
					$statusChanged=true;
				}
				$newItem=true;
				$returnValue=$this->insertSqlElement($forceInsert);
			}
			if (property_exists($this,'idResource') and ! $newItem and isset($old)) {
				if (trim($this->idResource) and trim($this->idResource)!=trim($old->idResource)) {
					$responsibleChanged=true;
				}
			}
			if (property_exists($this,'description') and ! $newItem and isset($old)) {
				if ($this->description!=$old->description) {
					$descriptionChange=true;
				}
			}
			if (property_exists($this,'result') and ! $newItem and isset($old)) {
				if ($this->result!=$old->result) {
					$resultChange=true;
				}
			}
			//if (($statusChanged or $responsibleChanged) and stripos($returnValue,'id="lastOperationStatus" value="OK"')>0 ) {
			if ( stripos($returnValue,'id="lastOperationStatus" value="OK"')>0 ) {
				$mailResult=$this->sendMailIfMailable($newItem, $statusChanged, false, $responsibleChanged,false,false,false,$descriptionChange, $resultChange,false,false,true);
				if ($mailResult) {
					$returnValue=str_replace('${mailMsg}',' - ' . i18n('mailSent'),$returnValue);
				} else {
					$returnValue=str_replace('${mailMsg}','',$returnValue);
				}
			} else {
				$returnValue=str_replace('${mailMsg}','',$returnValue);
			}
			// indicators
			if (SqlList::getIdFromTranslatableName('Indicatorable',get_class($this))) {
				$indDef=new IndicatorDefinition();
				$crit=array('nameIndicatorable'=>get_class($this),'idle'=>'0');
				$lstInd=$indDef->getSqlElementsFromCriteria($crit, false);
				foreach ($lstInd as $ind) {
					$fldType='id'.((get_class($this)=='TicketSimple')?'Ticket':get_class($this)).'Type';
					if (! $ind->idType or $ind->idType==$this->$fldType) {
						IndicatorValue::addIndicatorValue($ind,$this);
					}
				}
			}
			return $returnValue;
		} else {
			// errors on control => don't save, display error message
			$returnValue='<b>' . i18n('messageInvalidControls') . '</b><br/>' . $control;
			$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
			$returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
			$returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
			return $returnValue;
		}
	}

	/** =========================================================================
	 * Save an object to the database : new object
	 * @return void
	 */
	private function insertSqlElement($forceInsert=false) {
		if (get_class($this)=='Origin') {
			if (! $this->originId or ! $this->originType) {
				return;
			}
		}
		$depedantObjects=array();
		$returnStatus="OK";
		$objectClass = get_class($this);
		$query="insert into " . $this->getDatabaseTableName();
		$queryColumns="";
		$queryValues="";
		// initialize object definition criteria
		$databaseCriteriaList=$this->getDatabaseCriteria();
		foreach ($databaseCriteriaList as $col_name => $col_value) {
			$dataType = $this->getDataType($col_name);
			$dataLength = $this->getDataLength($col_name);
			$attribute= $this->getFieldAttributes($col_name);
			if (strpos($attribute,'calculated')===false) {
				if ($dataType=='int' and $dataLength==1) {
					if ($col_value==NULL or $col_value=="") {
						$col_value='0';
					}
				}
				if ($col_value != NULL and $col_value != '' and $col_value != ' ' and ($col_name != 'id' or $forceInsert)) {
					if ($queryColumns != "") {
						$queryColumns.=", ";
						$queryValues.=", ";
					}
					$queryColumns .= $this->getDatabaseColumnName($col_name);
					$queryValues .= Sql::str($col_value, $objectClass);
				}
			}
		}
		if (Sql::isPgsql()) {$queryColumns=strtolower($queryColumns);}
		// get all data
		foreach($this as $col_name => $col_value) {
			$attribute= $this->getFieldAttributes($col_name);
			if (strpos($attribute,'calculated')===false) {
				if (substr($col_name,0,1)=="_") {
					// not a fiels, just for presentation purpose
				} else if (ucfirst($col_name) == $col_name) {
					// if property is an object, store it to save it at the end of script
					$depedantObjects[$col_name]=($this->$col_name);
				} else if (array_key_exists($col_name, $databaseCriteriaList) ) {
					// Do not overwrite the default value from databaseCriteria, and do not double-set in insert clause
				} else {
					$dataType = $this->getDataType($col_name);
					$dataLength = $this->getDataLength($col_name);
					if ($dataType=='int' and $dataLength==1) {
						if ($col_value==NULL or $col_value=="") {
							$col_value='0';
						}
					}
					if ($col_value != NULL and $col_value != '' and $col_value != ' '
					and ($col_name != 'id' or $forceInsert)
					and strpos($queryColumns, ' '. $this->getDatabaseColumnName($col_name) . ' ')===false ) {
						if ($queryColumns != "") {
							$queryColumns.=",";
							$queryValues.=", ";
						}
						$queryColumns .= ' ' . $this->getDatabaseColumnName($col_name) . ' ';
						$queryValues .=  Sql::str($col_value, $objectClass);
					}
				}
			}
		}
		$query.=" ($queryColumns) values ($queryValues)";
		// execute request
		$result = Sql::query($query);
		if (!$result) {
			$returnStatus="ERROR";
		}
		// save history
		$newId= Sql::$lastQueryNewid;
		$this->id=$newId;
		if ($returnStatus!="ERROR" and ! property_exists($this,'_noHistory') ) {
			$result = History::store($this, $objectClass,$newId,'insert');
			if (!$result) {$returnStatus="ERROR";}
		}
		// save depedant elements (properties that are objects)
		if ($returnStatus!="ERROR") {
			$returnStatus=$this->saveDependantObjects($depedantObjects,$returnStatus);
		}
		// Prepare return data
		if ($returnStatus!="ERROR") {
			$returnValue=i18n(get_class($this)) . ' #' . $this->id . ' ' . i18n('resultInserted');
		} else {
			$returnValue=Sql::$lastQueryErrorMessage;
		}
		if ($returnStatus=="OK") {
			$returnValue .= '${mailMsg}';
		}
		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
		$returnValue .= '<input type="hidden" id="lastOperation" value="insert" />';
		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
		return $returnValue;
	}

	/** =========================================================================
	 * save an object to the database : existing object
	 * @return void
	 */
	private function updateSqlElement($force=false,$withoutDependencies=false) {
		//traceLog('updateSqlElement (for ' . get_class($this) . ' #' . $this->id . ')');
		$returnValue = i18n('messageNoChange') . ' ' . i18n(get_class($this)) . ' #' . $this->id;
		$returnStatus = 'NO_CHANGE';
		$depedantObjects=array();
		$objectClass = get_class($this);
		$arrayCols=array();
		$idleChange=false;
		// Get old values (stored) to : 1) build the smallest query 2) save change history
		$oldObject = null;
		if ( isset($_REQUEST['directAccessIndex'])
		and isset($_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']]) ) {
			$testObject=$_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']];
			if ($testObject and ! $force) {
				if (get_class($testObject)==$objectClass) {
					$oldObject=$testObject;
				}
			}
		} else if (array_key_exists('currentObject',$_SESSION)) {
			$testObject = $_SESSION['currentObject'];
			if ($testObject and ! $force) {
				if (get_class($testObject)==$objectClass) {
					$oldObject=$testObject;
				}
			}
		}
		if (! $oldObject) {
			$oldObject = new $objectClass($this->id);
		}
		// Specific treatment for other versions
		$versionTypes=array('Version', 'OriginalVersion', 'TargetVersion');
		foreach ($versionTypes as $versType) {
			$otherFld='_Other'.$versType;
			$versFld='id'.$versType;
			if ( property_exists($this, $versFld) and property_exists($this, $otherFld)) {
				usort($oldObject->$otherFld,"OtherVersion::sort");
				foreach ($oldObject->$otherFld as $otherVers) {
					if (! trim($this->$versFld)) {
						$this->$versFld=$otherVers->idVersion;
					}
					if ($otherVers->idVersion==$this->$versFld) {
						$otherVers->delete();
					}
				}
			}
		}
		$nbChanged=0;
		$query="update " . $this->getDatabaseTableName();
		// get all data, and identify if changes
		foreach($this as $col_name => $col_new_value) {
			$attribute= $this->getFieldAttributes($col_name);
			if (strpos($attribute,'calculated')!==false) {
				// calculated field, not to be save
			} else if (substr($col_name,0,1)=="_") {
				// not a fiels, just for presentation purpose
			} else if (ucfirst($col_name) == $col_name) {
				$depedantObjects[$col_name]=($this->$col_name);
			} else {
				$dataType = $this->getDataType($col_name);
				$dataLength = $this->getDataLength($col_name);
				if ($dataType=='int' and $dataLength==1) {
					if ($col_new_value==NULL or $col_new_value=="") {
						$col_new_value='0';
					}
				}
				$col_old_value=$oldObject->$col_name;
				// special null treatment (new value)
				//$col_new_value=Sql::str(trim($col_new_value));
				$col_new_value=trim($col_new_value);
				if ($col_new_value=='') {$col_new_value=NULL;};
				// special null treatment (old value)
				//$col_old_value=SQL::str(trim($col_old_value));
				if ($col_old_value=='') {$col_old_value=NULL;};
				// if changed
				if ($col_new_value != $col_old_value) {
					if ($col_name=='idle') {$idleChange=true;}
					$insertableColName= $this->getDatabaseColumnName($col_name);
					if (Sql::isPgsql()) {$insertableColName=strtolower($insertableColName);}
					if (!array_key_exists($insertableColName, $arrayCols)) {
						$arrayCols[$insertableColName]=$col_name;
						$query .= ($nbChanged==0)?" set ":", ";
						if ($col_new_value==NULL or $col_new_value=='' or $col_new_value=="''") {
							$query .= $insertableColName . " = NULL";
						} else {
							$query .= $insertableColName . '=' . Sql::str($col_new_value) .' ';
						}
						$nbChanged+=1;
						// Save change history
						if ($objectClass!='History' and ! property_exists($this,'_noHistory') and $col_name!='id') {
							$result = History::store($this, $objectClass,$this->id,'update', $col_name, $col_old_value, $col_new_value);
							if (!$result) {
								$returnStatus="ERROR";
								$returnValue=Sql::$lastQueryErrorMessage;
							}
						}
					}
				}
			}
		}
		$query .= ' where id=' . $this->id;
		// If changed, execute the query
		if ($nbChanged > 0 and $returnStatus!="ERROR") {
			// Catch errors, and return error message
			$result = Sql::query($query);
			if ($result) {
				if (Sql::$lastQueryNbRows==0) {
					$test=new $objectClass($this->id);
					if ($this->id!=$test->id) {
						$returnValue = i18n('messageItemDelete', array(i18n(get_class($this)), $this->id));
						$returnStatus='ERROR';
					} else {
						$returnValue = i18n('messageNoChange') . ' ' . i18n(get_class($this)) . ' #' . $this->id;
						$returnStatus = 'NO_CHANGE';
					}
				} else {
					$returnValue=i18n(get_class($this)) . ' #' . $this->id . ' ' . i18n('resultUpdated');
					$returnStatus='OK';
				}
			} else {
				$returnValue=Sql::$lastQueryErrorMessage;
				$returnStatus="ERROR";
			}
		}

		// if object is Asignable, update assignments on idle change
		// TODO : add constrain 'is assignable'
		if ($idleChange and $returnStatus!="ERROR") {
			$ass=new Assignment();
			$query="update " . $ass->getDatabaseTableName();
			$query.=" set idle='" . $this->idle . "'";
			$query.=" where refType='" . get_class($this) . "' ";
			$query.=" and refId=" . $this->id;
			$result = Sql::query($query);
			if ($returnStatus=="ERROR") {
				$returnValue=Sql::$lastQueryErrorMessage;
				$returnStatus='ERROR';
			}
		}

		// save depedant elements (properties that are objects)
		if ($returnStatus!="ERROR" and ! $withoutDependencies) {
			$returnStatus=$this->saveDependantObjects($depedantObjects,$returnStatus);
			if ($returnStatus=="ERROR") {
				$returnValue=Sql::$lastQueryErrorMessage;
			}
			if ($returnStatus=="OK") {
				$returnValue=i18n(get_class($this)) . ' #' . $this->id . ' ' . i18n('resultUpdated');
			}
		}
		if ($returnStatus=="OK") {
			$returnValue .= '${mailMsg}';
		}
		// Prepare return data
		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
		$returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
		return $returnValue;
	}

	/** =========================================================================
	 * Save the dependant objects stored in a list (may be single objects or list
	 * @param $depedantObjects list (array) of objects to store
	 * @return void
	 */
	private function saveDependantObjects($depedantObjects,$returnStatus) {
		$returnStatusDep=$returnStatus;
		foreach ($depedantObjects as $class => $depObj) {
			if (is_array($depObj) and $returnStatusDep!="ERROR" ) {
				foreach ($depObj as $depClass => $depObjOccurence) {
					if ($depObjOccurence instanceof SqlElement and $returnStatusDep!="ERROR") {
						$depObjOccurence->refId=$this->id;
						$depObjOccurence->refType=get_class($this);
						$ret=$depObjOccurence->saveSqlElement();
						if (stripos($ret,'id="lastOperationStatus" value="ERROR"')) {
							$returnStatusDep="ERROR";
						} else if (stripos($ret,'id="lastOperationStatus" value="OK"')) {
							$returnStatusDep='OK';
						}
					}
				}
			} else if ($depObj instanceof SqlElement and $returnStatusDep!="ERROR") {
				$depObj->refId=$this->id;
				$depObj->refType=get_class($this);
				$ret=$depObj->save();
				if (stripos($ret,'id="lastOperationStatus" value="ERROR"')) {
					$returnStatusDep="ERROR";
				} else if (stripos($ret,'id="lastOperationStatus" value="OK"')) {
					$returnStatusDep='OK';
				}
			}
		}
		return $returnStatusDep;
	}
	/** =========================================================================
	 * Delete an object from the database
	 * @return void
	 */
	private function deleteSqlElement() {
		$class = get_class($this);
		$control=$this->deleteControl();
		if ($control!="OK") {
			// errors on control => don't save, display error message
			$returnValue='<b>' . i18n('messageInvalidControls') . '</b><br/>' . $control;
			$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
			$returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
			$returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
			return $returnValue;
		}
		foreach($this as $col_name => $col_value) {
			// if property is an array containing objects, delete each
			if (is_array($this->$col_name)) {
				foreach ($this->$col_name as $obj) {
					if ($obj instanceof SqlElement) {
						if ($obj->id and $obj->id!='') { // object may be a "new" element, so try to delete only if id exists
							$obj->delete();
						}
					}
				}
			} else if (ucfirst($col_name) == $col_name) {
				// if property is an object, delete it
				if ($this->$col_name instanceof SqlElement) {
					if ($this->$col_name->id and $this->$col_name->id!='') { // object may be a "new" element, so try to delete only if id exists
						$this->$col_name->delete();
					}
				}
			}
		}
		// check relartionship : if "cascade", then auto delete
		$relationShip=self::$_relationShip;
		if ($class=='TicketSimple') {$class='Ticket';}
		if (array_key_exists($class,$relationShip)) {
			$relations=$relationShip[$class];
			foreach ($relations as $object=>$mode) {
				if ($mode=="cascade") {
					$where=null;
					$obj=new $object();
					$crit=array('id' . $class => $this->id);
					if (! property_exists($obj, 'id' . $class) and property_exists($obj, 'refType') and property_exists($obj,'refId')) {
						$crit=array("refType"=>$class, "refId"=>$this->id);
					}
					if ($object=="Dependency") {
						$crit=null;
						$where="(predecessorRefType='" . $class . "' and predecessorRefId=" . Sql::fmtId($this->id) .")"
						. " or (successorRefType='" . $class . "' and successorRefId=" . Sql::fmtId($this->id) .")";
					}
					if ($object=="Link") {
						$crit=null;
						$where="(ref1Type='" . $class . "' and ref1Id=" . Sql::fmtId($this->id) .")"
						. " or (ref2Type='" . $class . "' and ref2Id=" . Sql::fmtId($this->id) .")";
					}
					$list=$obj->getSqlElementsFromCriteria($crit,false,$where);
					foreach ($list as $subObj) {
						$subObjDel=new $object($subObj->id);
						$subObjDel->delete();
					}
				}
			}
		}
		$query="delete from " .  $this->getDatabaseTableName() . " where id=" . Sql::fmtId($this->id) . "";
		// execute request
		$returnStatus="OK";
		$result = Sql::query($query);
		if (!$result) {
			$returnStatus="ERROR";
		}
		// save history
		if ($returnStatus!="ERROR" and ! property_exists($this,'_noHistory') ) {
			$result = History::store($this, $class,$this->id,'delete');
			if (!$result) {$returnStatus="ERROR";}
		}
		if ($returnStatus!="ERROR") {
			$returnValue=i18n($class) . ' #' . $this->id . ' ' . i18n('resultDeleted');
		} else {
			$returnValue=Sql::$lastQueryErrorMessage;
		}
		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
		$returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus .'" />';
		$returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage(get_class($this)) . '" />';
		return $returnValue;
	}


	/** =========================================================================
	 * Purge objects from the database : delete all objects corresponding
	 * to clause $ clause
	 * Important :
	 *   => does not automatically purges included elements ...
	 *   => does not include history insertion
	 * @return void
	 */
	private function purgeSqlElement($clause) {
		$objectClass = get_class($this);
		// purge depending Planning Element if any
		if (property_exists($this, $objectClass.'PlanningElement')) {
			$query="select id from " .  $this->getDatabaseTableName() . " where " . $clause;
			$resultId = Sql::query($query);
			if (Sql::$lastQueryNbRows > 0) {
				$line = Sql::fetchLine($resultId);
				$peCrit='(0';
				while ($line) {
					$peCrit.=','.$line['id'];
					$line = Sql::fetchLine($resultId);
				}
				$peCrit.=')';
				$pe=new PlanningElement();
				$query="delete from " .  $pe->getDatabaseTableName() . " where refType='$objectClass' and refId in $peCrit";
				Sql::query($query);
			}
		}
		// get all data, and identify if changes
		$query="delete from " .  $this->getDatabaseTableName() . " where " . $clause;
		// execute request
		$returnStatus="OK";
		$result = Sql::query($query);
		if (!$result) {
			$returnStatus="ERROR";
		}
		if ($returnStatus!="ERROR") {
			$returnValue=Sql::$lastQueryNbRows . " " . i18n(get_class($this)) . '(s) ' . i18n('doneoperationdelete');
		} else {
			$returnValue=Sql::$lastQueryErrorMessage;
		}
		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
		$returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus .'" />';
		$returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage(get_class($this)) . '" />';
		return $returnValue;
	}

	/** =========================================================================
	 * Close objects from the database : delete all objects corresponding
	 * to clause $ clause
	 * Important :
	 *   => does not automatically purges included elements ...
	 *   => does not include history insertion
	 * @return void
	 */
	private function closeSqlElement($clause) {
		$objectClass = get_class($this);
		// get all data, and identify if changes
		$query="update " .  $this->getDatabaseTableName() . " set idle='1' where " . $clause;
		// execute request
		$returnStatus="OK";
		$result = Sql::query($query);
		if (!$result) {
			$returnStatus="ERROR";
		}
		if ($returnStatus!="ERROR") {
			$returnValue=Sql::$lastQueryNbRows . " " . i18n(get_class($this)) . '(s) ' . i18n('doneoperationclose');
		} else {
			$returnValue=Sql::$lastQueryErrorMessage;
		}
		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $this->id . '" />';
		$returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus .'" />';
		$returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage(get_class($this)) . '" />';
		return $returnValue;
	}


	/** =========================================================================
	 * Copy the curent object as a new one of the same class
	 * @return the new object
	 */
	private function copySqlElement() {
		$newObj=clone $this;
		$newObj->id=null;
		if (property_exists($newObj,"wbs")) {
			$newObj->wbs=null;
		}
		if (property_exists($newObj,"topId")) {
			$newObj->topId=null;
		}
		if (property_exists($newObj,"idStatus")) {
			if (get_class($newObj)=='TestSession') {
				$list=SqlList::getList('Status');
				$revert=array_keys($list);
				$newObj->idStatus=$revert[0];
			} else {
				$st=SqlElement::getSingleSqlElementFromCriteria('Status', array('isCopyStatus'=>'1'));
				if (! $st or ! $st->id) {
					errorLog("Error : several on no status exist with isCopyStatus=1");
				}
				$newObj->idStatus=$st->id;
			}
			// TODO : define a new status "copied"
			//$newObj->idStatus='1';
			//$st=SqlElement::getSingleSqlElementFromCriteria('Status', array('name'=>'copied'));
			//if ($st->id) $newObj->idStatus=$st->id;

		}
		if (property_exists($newObj,"idUser") and get_class($newObj)!='Affectation' and get_class($newObj)!='Message') {
			$newObj->idUser=$_SESSION['user']->id;
		}
		if (property_exists($newObj,"creationDate")) {
			$newObj->creationDate=date('Y-m-d');
		}
		if (property_exists($newObj,"creationDateTime")) {
			$newObj->creationDateTime=date('Y-m-d H:i');
		}
		if (property_exists($newObj,"done")) {
			$newObj->done=0;
		}
		if (property_exists($newObj,"idle")) {
			$newObj->idle=0;
		}
		if (property_exists($newObj,"idleDate")) {
			$newObj->idleDate=null;
		}
		if (property_exists($newObj,"doneDate")) {
			$newObj->doneDate=null;
		}
		if (property_exists($newObj,"idleDateTime")) {
			$newObj->idleDateTime=null;
		}
		if (property_exists($newObj,"doneDateTime")) {
			$newObj->doneDateTime=null;
		}
		if (property_exists($newObj,"reference")) {
			$newObj->reference=null;
		}
		foreach($newObj as $col_name => $col_value) {
			if (ucfirst($col_name) == $col_name) {
				// if property is an object, delete it
				if ($newObj->$col_name instanceof SqlElement) {
					$newObj->$col_name->id=null;
					if (property_exists($newObj->$col_name,"wbs")) {
						$newObj->$col_name->wbs=null;
					}
					if (property_exists($newObj->$col_name,"topId")) {
						$newObj->$col_name->topId=null;
					}
					if ($newObj->$col_name instanceof PlanningElement) {
						$newObj->$col_name->plannedStartDate="";
						$newObj->$col_name->realStartDate="";
						$newObj->$col_name->plannedEndDate="";
						$newObj->$col_name->realEndDate="";
						$newObj->$col_name->plannedDuration="";
						$newObj->$col_name->realDuration="";
						$newObj->$col_name->assignedWork=0;
						$newObj->$col_name->plannedWork=0;
						$newObj->$col_name->leftWork=0;
						$newObj->$col_name->realWork=0;
						$newObj->$col_name->idle=0;
						$newObj->$col_name->done=0;
					}
				}
			}
		}
		if (get_class($this)=='User') {
			$newObj->name=i18n('copiedFrom') . ' ' . $newObj->name;
		}
		if (property_exists($newObj,"isCopyStatus")) {
			$newObj->isCopyStatus=0;
		}
		$result=$newObj->saveSqlElement();
		if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
			$returnValue=i18n(get_class($this)) . ' #' . $this->id . ' ' . i18n('resultCopied') . ' #' . $newObj->id;
			$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $newObj->id . '" />';
			$returnValue .= '<input type="hidden" id="lastOperation" value="copy" />';
			$returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
		} else {
			$returnValue=$result;
		}
		$newObj->_copyResult=$returnValue;
		return $newObj;
	}

	private function copySqlElementTo($newClass, $newType, $newName, $setOrigin, $withNotes, $withAttachments,$withLinks) {
		$newObj=new $newClass();
		$newObj->id=null;
		$typeName='id' . $newClass . 'Type';
		$newObj->$typeName=$newType;
		if ($setOrigin and property_exists($newObj, 'Origin')) {
			$newObj->Origin->originType=get_class($this);
			$newObj->Origin->originId=$this->id;
			$newObj->Origin->refType=$newClass;
		}
		foreach($newObj as $col_name => $col_value) {
			if (ucfirst($col_name) == $col_name) {
				if ($newObj->$col_name instanceof PlanningElement) {
					$sub=substr($col_name, 0,strlen($col_name)-15    );
					$plMode='id' . $sub . 'PlanningMode';
					if ($newClass=="Activity") {
						$newObj->$col_name->$plMode="1";
					} else if ($newClass=="Milestone") {
						$newObj->$col_name->$plMode="5";
					}
					if (get_class($this)==$newClass and $newClass!='Project') {
						$newObj->$col_name->$plMode=$this->$col_name->$plMode;
					}
					$newObj->$col_name->refName=$newName;
				}
			}
		}
		foreach($this as $col_name => $col_value) {
			if (ucfirst($col_name) == $col_name) {
				if ($this->$col_name instanceof SqlElement) {
					//$newObj->$col_name->id=null;
					if ($this->$col_name instanceof PlanningElement) {
						$pe=$newClass . 'PlanningElement';
						if (property_exists($newObj, $pe)) {
							if (get_class($this)==$newClass) {
								$plMode='id' . $newClass . 'PlanningMode';
								if (property_exists($this->$col_name,$plMode)) {
									$newObj->$col_name->$plMode=$this->$col_name->$plMode;
								}
							}
							$newObj->$pe->initialStartDate=$this->$col_name->initialStartDate;
							$newObj->$pe->initialEndDate=$this->$col_name->initialEndDate;
							$newObj->$pe->initialDuration=$this->$col_name->initialDuration;
							$newObj->$pe->validatedStartDate=$this->$col_name->validatedStartDate;
							$newObj->$pe->validatedEndDate=$this->$col_name->validatedEndDate;
							$newObj->$pe->validatedDuration=$this->$col_name->validatedDuration;
							$newObj->$pe->validatedWork=$this->$col_name->validatedWork;
							$newObj->$pe->validatedCost=$this->$col_name->validatedCost;
							$newObj->$pe->priority=$this->$col_name->priority;
							//$newObj->$pe->topId=$this->$col_name->topId;
							$newObj->$pe->topRefType=$this->$col_name->topRefType;
							$newObj->$pe->topRefId=$this->$col_name->topRefId;
						}
					}
				}
			} else if (property_exists($newObj,$col_name)) {
				if ($col_name!='id' and $col_name!="wbs" and $col_name!='name' and $col_name != $typeName
				and $col_name!="handled" and $col_name!="handledDate" and $col_name!="handledDateTime"
				and $col_name!="done" and $col_name!="doneDate" and $col_name!="doneDateTime"
				and $col_name!="idle" and $col_name!="idleDate" and $col_name!="idelDateTime"
				and $col_name!="idStatus" and $col_name!="reference"){ //topId ?
					$newObj->$col_name=$this->$col_name;
				}
			}
		}
		if (property_exists($newObj,"idStatus")) {
			$st=SqlElement::getSingleSqlElementFromCriteria('Status', array('isCopyStatus'=>'1'));
			if (! $st or ! $st->id) {
				errorLog("Error : several on no status exist with isCopyStatus=1");
			}
			$newObj->idStatus=$st->id;
		}
		if (property_exists($newObj,"idUser") and get_class($newObj)!='Affectation' and get_class($newObj)!='Message') {
			$newObj->idUser=$_SESSION['user']->id;
		}
		if (property_exists($newObj,"creationDate")) {
			$newObj->creationDate=date('Y-m-d');
		}
		if (property_exists($newObj,"creationDateTime")) {
			$newObj->creationDateTime=date('Y-m-d H:i');
		}
		if (property_exists($newObj,"meetingDate")) {
			$newObj->meetingDate=date('Y-m-d');
		}
		if (property_exists($newObj,"reference")) {
			$newObj->reference=null;
		}
		$newObj->name=$newName;
		// check description
		if (property_exists($newObj,'description') and ! $newObj->description ) {
			$idType='id'.$newClass.'Type';
			if (property_exists($newObj, $idType)) {
				$type=$newClass.'Type';
				$objType=new $type($newObj->$idType);
				if (property_exists($objType, 'mandatoryDescription') and $objType->mandatoryDescription) {
					$newObj->description=$newObj->name;
				}
			}
		}
		$result=$newObj->save();
		if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
			$returnValue=i18n(get_class($this)) . ' #' . $this->id . ' ' . i18n('resultCopied') . ' #' . $newObj->id;
			$returnValue .= '<input type="hidden" id="lastSaveId" value="' . $newObj->id . '" />';
			$returnValue .= '<input type="hidden" id="lastOperation" value="copy" />';
			$returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
		} else {
			$returnValue=$result;
		}
		if ($withNotes) {
			$crit=array('refType'=>get_class($this),'refId'=>$this->id);
			$note=new Note();
			$notes=$note->getSqlElementsFromCriteria($crit);
			foreach ($notes as $note) {
				$note->id=null;
				$note->refType=get_class($newObj);
				$note->refId=$newObj->id;
				$note->save();
			}
		}
		if ($withLinks) {
			$crit=array('ref1Type'=>get_class($this),'ref1Id'=>$this->id);
			$link=new Link();
			$links=$link->getSqlElementsFromCriteria($crit);
			foreach ($links as $link) {
				$link->id=null;
				$link->ref1Type=get_class($newObj);
				$link->ref1Id=$newObj->id;
				$link->save();
			}
			$crit=array('ref2Type'=>get_class($this),'ref2Id'=>$this->id);
			$link=new Link();
			$links=$link->getSqlElementsFromCriteria($crit);
			foreach ($links as $link) {
				$link->id=null;
				$link->ref2Type=get_class($newObj);
				$link->ref2Id=$newObj->id;
				$link->save();
			}
		}
		if ($withAttachments) {
			$crit=array('refType'=>get_class($this),'refId'=>$this->id);
			$attachement=new Attachement();
			$attachements=$attachement->getSqlElementsFromCriteria($crit);
			$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
			$attachementDirectory=Parameter::getGlobalParameter('paramAttachementDirectory');
			foreach ($attachements as $attachement) {
				$fromdir = $attachementDirectory . $pathSeparator . "attachement_" . $attachement->id . $pathSeparator;
				if (file_exists($fromdir.$attachement->fileName)) {
					$attachement->id=null;
					$attachement->refType=get_class($newObj);
					$attachement->refId=$newObj->id;
					$attachement->save();
					$todir = $attachementDirectory . $pathSeparator . "attachement_" . $attachement->id . $pathSeparator;
					if (! file_exists($todir)) {
						mkdir($todir, 0777 , true);
					}
					copy($fromdir.$attachement->fileName, $todir.$attachement->fileName);
					$attachement->subDirectory=str_replace($attachementDirectory,'${attachementDirectory}',$todir);
					$attachement->save();
				}
			}
		}
		$newObj->_copyResult=$returnValue;
		return $newObj;
	}

	// ============================================================================**********
	// GET AND FETCH OBJECTS FUNCTIONS
	// ============================================================================**********

	/** =========================================================================
	 * Retrieve an object from the Request (modified Form) - Public method
	 * @return void (operate directly on the object)
	 */
	public function fillFromRequest($ext=null) {
		$this->fillSqlElementFromRequest(null,$ext);
	}

	/**  ========================================================================
	 * Retrieve a list of objects from the Database
	 * Called from an empty object of the expected class
	 * @param array $critArray the critera as an array
	 * @param boolean $initializeIfEmpty indicating if no result returns an
	 * initialised element or not
	 * @param string $clauseWhere Sql Where clause (alternative way to define criteria)
	 *        => $critArray must not be set
	 * @param string $clauseOrderBy Sql Order By clause
	 * @param boolean $getIdInKey
	 * @return SqlElement[] an array of objects
	 */
	public function getSqlElementsFromCriteria($critArray, $initializeIfEmpty=false,
	$clauseWhere=null, $clauseOrderBy=null, $getIdInKey=false, $withoutDependentObjects=false, $maxElements=null ) {
		//scriptLog("getSqlElementsFromCriteria(implode('|',$critArray), $initializeIfEmpty,$clauseWhere, $clauseOrderBy, $getIdInKey)");
		// Build where clause from criteria
		$whereClause='';
		$objects=array();
		$className=get_class($this);
		$defaultObj = new $className();
		if ($critArray) {
			foreach ($critArray as $colCrit => $valCrit) {
				$whereClause.=($whereClause=='')?' where ':' and ';
				if ($valCrit==null or $valCrit==' ') {
					$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . ' is null';
				} else {
					if ($this->getDataType($colCrit)=='int' and is_numeric($valCrit)) {
						$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . '='.$valCrit;
					} else {
						$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . '='.Sql::str($valCrit);
					}
				}
				$defaultObj->$colCrit=$valCrit;
			}
		} else if ($clauseWhere) {
			$whereClause = ' where ' . $clauseWhere;
		}
		$objectCrit=$this->getDatabaseCriteria();
		if (count($objectCrit)>0) {
			foreach ($objectCrit as $colCrit => $valCrit) {
				$whereClause.=($whereClause=='')?' where ':' and ';
				$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . " = " . Sql::str($valCrit) . " ";
			}
		}
		if (array_key_exists($className,self::$_cachedQuery)) {
			if (array_key_exists($whereClause,self::$_cachedQuery[$className])) {
				return self::$_cachedQuery[$className][$whereClause];
			}
		}
		// If $whereClause is set, get the element from Database
		$query = 'select * from ' . $this->getDatabaseTableName() . $whereClause;
		if ($clauseOrderBy) {
			$query .= ' order by ' . $clauseOrderBy;
		} else if (isset($this->sortOrder)) {
			$query .= ' order by ' . $this->getDatabaseTableName() . '.sortOrder';
		}
		if ($maxElements) {
			$query.=' LIMIT '.$maxElements;
		}
		$result = Sql::query($query);

		if (Sql::$lastQueryNbRows > 0) {
			$line = Sql::fetchLine($result);
			while ($line) {
				$obj=clone($this);
				// get all data fetched
				$keyId=null;
				foreach ($obj as $col_name => $col_value) {
					if (substr($col_name,0,1)=="_") {
						// not a fiels, just for presentation purpose
					} else if (strpos($this->getFieldAttributes($col_name),'calculated')!==false) {
						// calculated field : not to be fetched
					} else if (ucfirst($col_name) == $col_name) {
						if (! $withoutDependentObjects) {
							$obj->getDependantSqlElement($col_name);
						}
					} else {
						$dbColName=$obj->getDatabaseColumnName($col_name);
						if (array_key_exists($dbColName,$line)) {
							$obj->{$col_name}=$line[$dbColName];
						} else if (array_key_exists(strtolower($dbColName),$line)) {
							$obj->{$col_name}=$line[strtolower($dbColName)];
						} else {
							errorLog("Error on SqlElement to get '" . $col_name . "' for Class '".get_class($obj) . "' "
							. " : field '" . $dbColName . "' not found in Database.");
						}
						if ($col_name=='id' and $getIdInKey) {$keyId='#' . $obj->{$col_name};}
					}
				}
				if ($getIdInKey) {
					$objects[$keyId]=$obj;
				} else {
					$objects[]=$obj;
				}

				$line = Sql::fetchLine($result);
			}
		} else {
			if ($initializeIfEmpty) {
				$objects[]=$defaultObj; // return at least 1 element, initialized with criteria
			}
		}
		if (array_key_exists($className,self::$_cachedQuery)) {
			self::$_cachedQuery[$className][$whereClause]=$objects;
		}
		return $objects;
	}

	/**  ========================================================================
	 * Retrieve the count of a list of objects from the Database
	 * Called from an empty object of the expected class
	 * @param $critArray the critera asd an array
	 * @param $clauseWhere Sql Where clause (alternative way to define criteria)
	 *        => $critArray must not be set
	 * @param $clauseOrderBy Sql Order By clause
	 * @return an array of objects
	 */
	public function countSqlElementsFromCriteria($critArray, $clauseWhere=null) {
		// Build where clause from criteria
		$whereClause='';
		$objects=array();
		$className=get_class($this);
		$defaultObj = new $className();
		if ($critArray) {
			foreach ($critArray as $colCrit => $valCrit) {
				$whereClause.=($whereClause=='')?' where ':' and ';
				if ($valCrit==null) {
					$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . ' is null';
				} else {
					$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . '= ' . Sql::str($valCrit);
				}
				$defaultObj->$colCrit=$valCrit;
			}
		} else if ($clauseWhere) {
			$whereClause = ' where ' . $clauseWhere;
		}
		// If $whereClause is set, get the element from Database
		$query = "select count(*) as cpt from " . $this->getDatabaseTableName() . $whereClause;

		$result = Sql::query($query);
		if (Sql::$lastQueryNbRows > 0) {
			$line = Sql::fetchLine($result);
			return $line['cpt'];
		}
		return 0;
	}

	public function countGroupedSqlElementsFromCriteria($critArray, $critGroup, $critwhere) {
		// Build where clause from criteria
		$whereClause='';
		$className=get_class($this);
		if ($critArray) {
			foreach ($critArray as $colCrit => $valCrit) {
				$whereClause.=($whereClause=='')?' where ':' and ';
				if ($valCrit==null) {
					$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . ' is null';
				} else {
					$whereClause.=$this->getDatabaseTableName() . '.' . $this->getDatabaseColumnName($colCrit) . '= ' . Sql::str($valCrit);
				}
			}
		} else {
			$whereClause=$critwhere;
		}
		$groupList='';
		$critGroup=array_map('strtolower',$critGroup);
		foreach ($critGroup as $group) {
			$groupList.=($groupList=='')?'':', ';
			$groupList.=$group;
		}
		$query = "select $groupList, count(*) as cpt from " . $this->getDatabaseTableName() . ' where ' . $whereClause . " group by $groupList";
		$result = Sql::query($query);
		$groupRes=array();
		if (Sql::$lastQueryNbRows > 0) {
			while ($line = Sql::fetchLine($result)) {
				$grp='';
				foreach ($critGroup as $group) {
					$grp.=(($grp=='')?'':'|').$line[$group];
				}
				$groupRes[$grp]=$line['cpt'];
			}
		}
		return $groupRes;
	}

	/**  ==========================================================================
	 * Retrieve a single object from the Database
	 * Called from an empty object of the expected class
	 * @param $critArray the critera asd an array
	 * @param $initializeIfEmpty boolean indicating if no result returns en initialised element or not
	 * @return an array of objects
	 */
	public static function getSingleSqlElementFromCriteria($class, $critArray) {
		$obj=new $class();
		if ($class=='Attachement') {
			if (array_key_exists('refType',$critArray) ) {
				if ($critArray['refType']=='User' or $critArray['refType']=='Contact') {
					$critArray['refType']='Resource';
				}
			}
		}
		$objList=$obj->getSqlElementsFromCriteria($critArray, true);
		if (count($objList)==1) {
			return $objList[0];
		} else {
			$obj->_singleElementNotFound=true;
			if (count($objList)>1) {
				//traceLog("getSingleSqlElementFromCriteria for object '" . $class . "' returned more than 1 element");
				$obj->_tooManyRows=true;
			}
			return $obj;
		}
	}

	/**  ==========================================================================
	 * Retrieve an object from the Request (modified Form)
	 * @return void (operate directly on the object)
	 */
	private function fillSqlElementFromRequest($included=false,$ext=null) {
		foreach($this as $key => $value) {
			// If property is an object, recusively fill it
			if (ucfirst($key) == $key and substr($key,0,1)<> "_") {
				if (is_object($key)) {
					$subObjectClass = get_class($key);
					$subObject = $key;
				} else {
					$subObjectClass = $key;
					$subObject= new $subObjectClass;
				}
				$subObject->fillSqlElementFromRequest(true,$ext);
				$this->$key = $subObject;
			} else {
				if (substr($key,0,1)== "_") {
					// not a real field
				} else {
					$dataType = $this->getDataType($key);
					$dataLength = $this->getDataLength($key);
					$formField = $key . $ext;
					if ($included) { // if included, then object is called recursively, name is prefixed by className
						$formField = get_class($this) . '_' . $key . $ext;
					}
					if ($dataType=='int' and $dataLength==1) {
						if (array_key_exists($formField,$_REQUEST)) {
							//if filed is hidden, must check value, otherwise just check existence
							if (strpos($this->getFieldAttributes($key), 'hidden')!==false) {
								$this->$key = $_REQUEST[$formField];
							} else {
								$this->$key = 1;
							}
						} else {
							//echo "val=False<br/>";
							$this->$key = 0;
						}
					} else if ($dataType=='datetime') {
						$formFieldBis = $key . "Bis" . $ext;
						if ($included) {
							$formFieldBis = get_class($this) . '_' . $key . "Bis" . $ext;
						}
						if (isset($_REQUEST[$formFieldBis])) {
							$this->$key = $_REQUEST[$formField] . " " . substr($_REQUEST[$formFieldBis],1);
						} else {
							//hidden field
							if (isset($_REQUEST[$formField])) {
								$this->$key = $_REQUEST[$formField];
							}
						}
					} else if ($dataType=='decimal' and (substr($key, -4,4)=='Work')) {
						if (array_key_exists($formField,$_REQUEST)) {
							$this->$key=Work::convertWork($_REQUEST[$formField]);
						}
					} else if ($dataType=='time') {
						if (array_key_exists($formField,$_REQUEST)) {
							$this->$key=substr($_REQUEST[$formField],1);
						}

					} else {
						if (array_key_exists($formField,$_REQUEST)) {
							$this->$key = $_REQUEST[$formField];
						}
					}
				}
			}
		}
	}

	/**  ==========================================================================
	 * Retrieve an object from the Database
	 * @return void
	 */
	private function getSqlElement($withoutDependentObjects=false) {
		$curId=$this->id;
		if (! trim($curId)) {$curId=null;}
		if ($curId and array_key_exists(get_class($this),self::$_cachedQuery)) {
			$whereClause='#id=' . $curId;
			$class=get_class($this);
			if (array_key_exists($whereClause,self::$_cachedQuery[$class])) {
				$obj=self::$_cachedQuery[$class][$whereClause];
				foreach($obj as $fld=>$val) {
					$this->$fld=$obj->$fld;
				}
				return;
			}
		}
		$empty=true;
		// If id is set, get the element from Database
		if ($curId != NULL) {
			$query = "select * from " . $this->getDatabaseTableName() . ' where id=' . $curId ;
			foreach ($this->getDatabaseCriteria() as $critFld=>$critVal) {
				$query .= ' and ' . $critFld . ' = ' . Sql::str($critVal);
			}
			$result = Sql::query($query);
			if (Sql::$lastQueryNbRows > 0) {
				$empty=false;
				$line = Sql::fetchLine($result);
				// get all data fetched
				foreach ($this as $col_name => $col_value) {
					if (substr($col_name,0,1)=="_") {
						$colName=substr($col_name,1);
						if (is_array($this->{$col_name}) and ucfirst($colName) == $colName ) {
							if (substr($colName,0,4)=="Link") {
								$linkClass=null;
								if (strlen($colName)>4) {
									$linkClass=substr($colName,5);
								}
								$this->{$col_name}=Link::getLinksForObject($this,$linkClass);
							} else if ($colName=="ResourceCost") {
								$this->{$col_name}=$this->getResourceCost();
							}  else if ($colName=="VersionProject") {
								if (get_class($this)!='OriginalVersion' and get_class($this)!='TargetVersion') {
									$vp=new VersionProject();
									$crit=array('id'.get_class($this)=>$this->id);
									$this->{$col_name}=$vp->getSqlElementsFromCriteria($crit,false);
								}
							}  else if ($colName=="DocumentVersion") {
								$dv=new DocumentVersion();
								$crit=array('idDocument'=>$this->id);
								$this->{$col_name}=$dv->getSqlElementsFromCriteria($crit,false);
							} else if ($colName=="ExpenseDetail") {
								$this->{$col_name}=$this->getExpenseDetail();
							} else if (substr($colName,0,10)=="Dependency") {
								$depType=null;
								$crit=Array();
								if (strlen($colName)>10) {
									$depType=substr($colName,11);
									if ($depType=="Successor") {
										$crit=Array("PredecessorRefType"=>get_class($this),
                                "PredecessorRefId"=>$this->id );
									} else {
										$crit=Array("SuccessorRefType"=>get_class($this),
                                "SuccessorRefId"=>$this->id );
									}
								}
								$dep=new Dependency();
								$this->{$col_name}=$dep->getSqlElementsFromCriteria($crit, false);
							} else {
								$this->{$col_name}=$this->getDependantSqlElements($colName);
							}
						}
					} else if (ucfirst($col_name) == $col_name and ! $withoutDependentObjects) {
						$this->{$col_name}=$this->getDependantSqlElement($col_name);
					} else if (strpos($this->getFieldAttributes($col_name),'calculated')!==false) {
						 
					} else {
						//$test=$line[$this->getDatabaseColumnName($col_name)];
						$dbColName=$this->getDatabaseColumnName($col_name);
						if (array_key_exists($dbColName,$line)) {
							$this->{$col_name}=$line[$dbColName];
						} else if (array_key_exists(strtolower($dbColName),$line)) {
							$dbColName=strtolower($dbColName);
							$this->{$col_name}=$line[$dbColName];
						} else {
							errorLog("Error on SqlElement to get '" . $col_name . "' for Class '".get_class($this) . "' "
							. " : field '" . $dbColName . "' not found in Database.");
						}
					}
				}
			} else {
				$this->id=null;
			}
		}
		if ($empty and ! $withoutDependentObjects) {
			// Get all the elements that are objects (first letter is uppercase in object properties)
			foreach($this as $key => $value) {
				//echo substr($key,0,1) . "<br/>";
				if (ucfirst($key) == $key and substr($key,0,1)<> "_") {
					$this->{$key}=$this->getDependantSqlElement($key);
				}
			}
		}
		// set default idUser if exists
		if ($empty and property_exists($this, 'idUser') and get_class($this)!='Affectation' and get_class($this)!='Message') {
			if (array_key_exists('user', $_SESSION)) {
				$this->idUser=$_SESSION['user']->id;
			}
		}
		if ($curId and array_key_exists(get_class($this),self::$_cachedQuery)) {
			$whereClause='#id=' . $curId;
			$class=get_class($this);
			self::$_cachedQuery[get_class($this)][$whereClause]=clone($this);
		}

	}

	/** ==========================================================================
	 * retrieve single object included in an object from the Database
	 * @param $objClass the name of the class of the included object
	 * @return an object
	 */
	private function getDependantSqlElement($objClass) {
		$curId=$this->id;
		if (! trim($curId)) {$curId=null;}
		$obj = new $objClass;
		$obj->refId=$this->id;
		$obj->refType=get_class($this);
		// If id is set, get the elements from Database
		if ( ($curId != NULL) and ($obj instanceof SqlElement) ) {
			// set the reference data
			// build query
			$query = "select id from " . $obj->getDatabaseTableName()
			. ' where refId =' . $curId.
       " and refType ='" . get_class($this) . "'" ;      
			$result = Sql::query($query);
			// if no element in database, will return empty object
			if (Sql::$lastQueryNbRows > 0) {
				$line = Sql::fetchLine($result);
				// get all data fetched for the dependant element
				$obj->id=$line['id'];
				$obj->getSqlElement();
			}
		}
		// set the dependant element
		return $obj;
	}

	/** ==========================================================================
	 * retrieve objects included in an object from the Database
	 * @param $objClass the name of the class of the included object
	 * @return an array ob objects
	 */
	private function getDependantSqlElements($objClass) {
		$curId=$this->id;
		$obj = new $objClass;
		$list=array();
		//$obj->refId=$this->id;
		//$obj->refType=get_class($this);
		// If id is set, get the elements from Database
		if ( ($curId != NULL) and ($obj instanceof SqlElement) ) {
			// set the reference data
			// build query
			$query = "select id from " . $obj->getDatabaseTableName();
			if (property_exists($objClass, 'id'.get_class($this))) {
				$query .= " where " . $obj->getDatabaseColumnName('id' . get_class($this)) . "= " . Sql::str($curId) . " ";
			} else {
				$refType=get_class($this);
				if ($refType=='TicketSimple') {
					$refType='Ticket';
				}
				$query .= " where refId =" . Sql::str($curId) . " "
				. " and refType ='" . $refType . "'";
			}
			$query .= " order by id desc ";
			$result = Sql::query($query);
			// if no element in database, will return empty array
			if (Sql::$lastQueryNbRows > 0) {
				while ($line = Sql::fetchLine($result)) {
					$newObj = new $objClass;
					$newObj->id=$line['id'];
					$newObj->getSqlElement();
					$list[]=$newObj;
				}
			}
		}
		// set the dependant element
		return $list;
	}

	// ============================================================================**********
	// GET STATIC DATA FUNCTIONS
	// ============================================================================**********

	/** ========================================================================
	 * return the type of a column depending on its name
	 * @param $colName the name of the column
	 * @return the type of the data
	 */
	public function getDataType($colName) {
		$colName=strtolower($colName);
		$formatList=self::getFormatList(get_class($this));
		if ( ! array_key_exists($colName, $formatList) ) {
			return 'undefined';
		}
		$fmt=$formatList[$colName];
		$split=preg_split('/[()\s]+/',$fmt,2);
		return $split[0];
	}

	/** ========================================================================
	 * return the length (max) of a column depending on its name
	 * @param $colName the name of the column
	 * @return the type of the data
	 */
	public function getDataLength($colName) {
		$colName=strtolower($colName);
		$formatList=self::getFormatList(get_class($this));
		if ( ! array_key_exists($colName, $formatList) ) {
			return '';
		}
		$fmt=$formatList[$colName];
		$split=preg_split('/[()\s]+/',$fmt,3);
		$type = $split[0];
		if ($type=='date') {
			return '10';
		} else if ($type=='time') {
			return '5';
		} else if ($type=='timestamp' or $type=='datetime') {
			return 19;
		} else if ($type=='double') {
			return 2;
		} else {
			if (count($split)>=2) {
				return $split[1];
			} else {
				return 0;
			}
		}
	}

	/** ========================================================================
	 * return the generic layout for grit list
	 * @return the layout from static data
	 */
	public function getLayout() {
		$result="";
		$columns=ColumnSelector::getColumnsList(get_class($this));
		$totWidth=0;
		foreach ($columns as $col) {
			if ($col->hidden) {
				continue;
			}
			if ( ! self::isVisibleField($col->attribute) ) {
				continue;
			}
			$result.='<th';
			$result.=' field="'.$col->field.'"';
			$result.=' width="'.(($col->field=='name')?'auto':$col->widthPct.'%').'"';
			$result.=($col->formatter)?' formatter="'.$col->formatter.'"':'';
			$result.=($col->_from)?' from="'.$col->_from.'"':'';
			$result.=($col->hidden)?' hidden="true"':'';
			$result.='>'.$col->_displayName.'</th>'."\n";
			$totWidth+=($col->field=='name')?0:$col->widthPct;
		}
		if ($totWidth<90) {
			$autoWidth=100-$totWidth;
		} else {
			$autoWidth=10;
		}
		$result=str_replace('width="auto"', 'width="'.$autoWidth.'%"', $result);
		return $result;
	}

	/** ========================================================================
	 * return the generic attributes (required, disabled, ...) for a given field
	 * @return an array of fields  with specific attributes
	 */
	public function getFieldAttributes($fieldName) {
		$fieldsAttributes=$this->getStaticFieldsAttributes();
		if (array_key_exists($fieldName,$fieldsAttributes)) {
			return $fieldsAttributes[$fieldName];
		} else {
			return '';
		}
	}
	public function isAttributeSetToField($fieldName, $attribute) {
		if (strpos($this->getFieldAttributes($fieldName), $attribute)!==false) {
			return true;
		} else {
			return false;
		}
	}

	/** ========================================================================
	 * Return the name of the table in the database
	 * Default is the name of the class (lowercase)
	 * May be overloaded for some classes, who reference a table different
	 * from class name
	 * @return string the name of the data table
	 */
	public function getDatabaseTableName() {
		return $this->getStaticDatabaseTableName();
	}

	/** ========================================================================
	 * Return the name of the column name in the table in the database
	 * Default is the name of the field
	 * May be overloaded for some fields of some classes
	 * @return string the name of the data column
	 */
	public function getDatabaseColumnName($field) {
		$colName=$field;
		$databaseColumnName=$this->getStaticDatabaseColumnName();
		if (array_key_exists($field,$databaseColumnName)) {
			$colName=$databaseColumnName[$field];
		} //else {
		//return Sql::str($field); // Must not be quoted : would return 'name' (with quotes)
		//return $field;
		//}
		//if (Sql::isPgsql() ) {
		//	$colName=strtolower($colName);
		//}
		return $colName;
	}

	/** ========================================================================
	 * Return the name of the field in the object from the column name in the
	 * table in the database
	 * (it is the reversed method from getDatabaseColumnName()
	 * Default is the name of the field
	 * May be overloaded for some fields of some classes
	 * @return string the name of the data column
	 */
	public function getDatabaseColumnNameReversed($field) {
		$databaseColumnName=$this->getStaticDatabaseColumnName();
		//if (1 or Sql::isPgsql()) {
		$databaseColumnNameReversed=array_flip(array_map('strtolower',$databaseColumnName));
		$field=strtolower($field);
		//} else {
		//	$databaseColumnNameReversed=array_flip($databaseColumnName);
		//}
		//I deleted Sql::str because it's add ' '
		if (array_key_exists(strtolower($field),$databaseColumnNameReversed)) {
			return $databaseColumnNameReversed[$field];
			//return Sql::str($databaseColumnNameReversed[$field]);
		} else {
			//return Sql::str($field);
			return $field;
		}
	}

	/** ========================================================================
	 * Return the additional criteria to select class elements in the database
	 * Default is empty string
	 * May be overloaded for some classes, which reference a table different
	 * from class name
	 * @return array listing criteria
	 */
	public function getDatabaseCriteria() {
		return $this->getStaticDatabaseCriteria();
	}

	/** ============================================================================
	 * Return the caption of a field using i18n translation
	 * @param $fld the name of the field
	 * @return the translated colXxxxxx value
	 */
	function getColCaption($fld) {
		if (! $fld or $fld=='') {
			return '';
		}
		$colCaptionTransposition=$this->getStaticColCaptionTransposition($fld);
		if (array_key_exists($fld,$colCaptionTransposition)) {
			$fldName=$colCaptionTransposition[$fld];
		} else {
			$fldName=$fld;
		}
		return i18n('col' . ucfirst($fldName));
	}

	public function getLowercaseFieldsArray() {
		$arrayFields=array();
		foreach ($this as $fld=>$fldVal) {
			if (is_object($this->$fld)) {
				$arrayFields=array_merge($arrayFields,$this->$fld->getLowercaseFieldsArray());
			} else {
				$arrayFields[strtolower($fld)]=$fld;
			}
		}
		return $arrayFields;
	}

	public function getFieldsArray() {
		$arrayFields=array();
		foreach ($this as $fld=>$fldVal) {
			if (is_object($this->$fld)) {
				$arrayFields=array_merge($arrayFields,$this->$fld->getFieldsArray());
			} else {
				$arrayFields[$fld]=$fld;
			}
		}
		return $arrayFields;
	}
	/** =========================================================================
	 * Return the list of fields format and store it in static array of formats
	 * to be able to fetch it again without requesting it from database
	 * @param $class the class of the object
	 * @return the format list
	 */
	private static function getFormatList($class) {
		if (array_key_exists($class, self::$_tablesFormatList)) {
			return self::$_tablesFormatList[$class];
		}
		$obj=new $class();
		$formatList= array();
		$query="desc " . $obj->getDatabaseTableName();
		if (Sql::isPgsql()) {
			$query="SELECT a.attname as field, pg_catalog.format_type(a.atttypid, a.atttypmod) as type"
			. " FROM pg_catalog.pg_attribute a "
			. " WHERE a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='".$obj->getDatabaseTableName()."')"
			. " AND a.attnum > 0 AND NOT a.attisdropped"
			. " ORDER BY a.attnum";
		}
		$result=Sql::query($query);
		while ( $line = Sql::fetchLine($result) ) {
			$fieldName=(isset($line['Field']))?$line['Field']:$line['field'];
			$fieldName=$obj->getDatabaseColumnNameReversed($fieldName);
			$type=(isset($line['Type']))?$line['Type']:$line['type'];
			if (Sql::isPgsql()) {
				$from=array();                               $to=array();
				$from[]='integer';                           $to[]='int(12)';
				$from[]='numeric(12,0)';                     $to[]='int(12)';
				$from[]='numeric(5,0)';                      $to[]='int(5)';
				$from[]='numeric(3,0)';                      $to[]='int(3)';
				$from[]='numeric(1,0)';                      $to[]='int(1)';
				$from[]=' without time zone';                $to[]='';
				$from[]='character varying';                 $to[]='varchar';
				$from[]='numeric';                           $to[]='decimal';
				$from[]='timestamp';                         $to[]='datetime';
				$type=str_ireplace($from, $to, $type);
			}
			$formatList[strtolower($fieldName)] = $type;
		}
		self::$_tablesFormatList[$class]=$formatList;
		return $formatList;
	}

	/** ========================================================================
	 * return the generic layout
	 * @return the layout from static data
	 */
	protected function getStaticLayout() {
		return self::$_layout;
	}

	/** ==========================================================================
	 * Return the generic fieldsAttributes
	 * @return the layout
	 */
	protected function getStaticFieldsAttributes() {
		return self::$_fieldsAttributes;
	}

	/** ==========================================================================
	 * Return the generic databaseTableName
	 * @return the layout
	 */
	protected function getStaticDatabaseTableName() {
		$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
		return strtolower($paramDbPrefix . get_class($this));
	}

	/** ========================================================================
	 * Return the generic databaseTableName
	 * @return the databaseTableName
	 */
	protected function getStaticDatabaseColumnName() {
		return array();
	}

	/** ========================================================================
	 * Return the generic database criteria
	 * @return the databaseTableName
	 */
	protected function getStaticDatabaseCriteria() {
		return array();
	}

	/** ============================================================================
	 * Return the specific colCaptionTransposition
	 * @return the colCaptionTransposition
	 */
	protected function getStaticColCaptionTransposition($fld) {
		return array();
	}

	// ============================================================================**********
	// GET VALIDATION SCRIPT
	// ============================================================================**********

	/** ========================================================================
	 * return generic javascript to be executed on validation of field
	 * @param $colName the name of the column
	 * @return the javascript code
	 */
	public function getValidationScript($colName) {
		$colScript = '';
		$posDate=strlen($colName)-4;
		if (substr($colName,0,2)=='id' and strlen($colName)>2 ) {  // SELECT => onChange
			$colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
			$colScript .= '  if (this.value!=null && this.value!="") { ';
			$colScript .= '    formChanged();';
			$colScript .= '  }';
			//if ( get_class($this)=='Activity' or get_class($this)=='Ticket' or get_class($this)=='Milestone' ) {
			if ( get_class($this)!='Project' and get_class($this)!='Affectation' ) {
				if ($colName=='idProject' and property_exists($this,'idActivity')) {
					$colScript .= '   refreshList("idActivity","idProject", this.value);';
				}
				if ($colName=='idProject' and property_exists($this,'idResource')) {
					$colScript .= '   refreshList("idResource","idProject", this.value, "' . $this->idResource. '");';
				}
				$arrVers=array('idVersion','idOriginalVersion','idTargetVersion','idTestCase','idRequirement');
				$versionExists=false;
				foreach ($arrVers as $vers) {
					if (property_exists($this,$vers)) {
						$versionExists=true;
					}
				}
				if ($colName=='idProject' and $versionExists) {
					if (property_exists($this,'idProduct')) {
						$colScript .="    var idProduct=trim(dijit.byId('idProduct').get('value'));";
						$colScript .= '   if (idProduct) {';
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProduct", idProduct);':'';}
						$colScript .= '   } else {';
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProject", this.value);':'';}
						$colScript .= '   }';
					} else {
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProject", this.value);':'';}
					}
				}
				if ($colName=='idProduct' and $versionExists) {
					if (property_exists($this,'idProject')) {
						$colScript .= '   if (trim(this.value)) {';
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProduct", this.value);':'';}
						$colScript .= '   } else {';
						$colScript .="      var idProject=trim(dijit.byId('idProject').get('value'));";
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProject", idProject);':'';}
						$colScript .= '   }';
					} else {
						foreach ($arrVers as $vers) {$colScript.=(property_exists($this,$vers))?'refreshList("'.$vers.'","idProject", idProject);':'';}
					}
				}
				if (($colName=='idVersion' or $colName=='idOriginalVersion' or $colName=='idTargetVersion')
				and property_exists($this,'idProduct')) {
					$colScript .= 'if (! trim(dijit.byId("idProduct").get("value")) ) {';
					$colScript .= '   setProductValueFromVersion("idProduct",this.value);';
					$colScript .= '}';
				}
				if ($colName=='idProject' and property_exists($this,'idContact')) {
					$colScript .= '   refreshList("idContact","idProject", this.value);';
				}
				if ($colName=='idProject' and property_exists($this,'idTicket')) {
					$colScript .= '   refreshList("idTicket","idProject", this.value);';
				}
				if ($colName=='idProject' and property_exists($this,'idUser')) {
					$colScript .= '   refreshList("idUser","idProject", this.value);';
				}
			}
			$colScript .= '</script>';
		}
		if (substr($colName,$posDate,4)=='Date') {  // Date => onChange
			$colScript .= '<script type="dojo/connect" event="onChange">';
			$colScript .= '  if (this.value!=null && this.value!="") { ';
			$colScript .= '    formChanged();';
			$colScript .= '  }';
			$colScript .= '</script>';
		}
		if ( ! (substr($colName,0,2)=='id' and strlen($colName)>2 ) ) { // OTHER => onKeyPress
			$colScript .= '<script type="dojo/method" event="onKeyPress" args="event">';
			$colScript .= '  if (isUpdatableKey(event.keyCode)) {';
			$colScript .= '    formChanged();';
			$colScript .= '  }';
			$colScript .= '</script>';
		}
		if ($colName=="idStatus") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			if (property_exists($this, 'idle') and get_class($this)!='StatusMail') {
				$colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
				$colScript .= '  var setIdle=0;';
				$colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
				$colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
				$colScript .= '  if (setIdle==1) {';
				$colScript .= '    dijit.byId("idle").set("checked", true);';
				$colScript .= '  } else {';
				$colScript .= '    dijit.byId("idle").set("checked", false);';
				$colScript .= '  }';
			}
			if (property_exists($this, 'done')) {
				$colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
				$colScript .= '  var setDone=0;';
				$colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
				$colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
				$colScript .= '  if (setDone==1) {';
				$colScript .= '    dijit.byId("done").set("checked", true);';
				$colScript .= '  } else {';
				$colScript .= '    dijit.byId("done").set("checked", false);';
				$colScript .= '  }';
			}
			if (property_exists($this, 'handled')) {
				$colScript .= htmlGetJsTable('Status', 'setHandledStatus', 'tabStatusHandled');
				$colScript .= '  var setHandled=0;';
				$colScript .= '  var filterStatusHandled=dojo.filter(tabStatusHandled, function(item){return item.id==dijit.byId("idStatus").value;});';
				$colScript .= '  dojo.forEach(filterStatusHandled, function(item, i) {setHandled=item.setHandledStatus;});';
				$colScript .= '  if (setHandled==1) {';
				$colScript .= '    dijit.byId("handled").set("checked", true);';
				$colScript .= '  } else {';
				$colScript .= '    dijit.byId("handled").set("checked", false);';
				$colScript .= '  }';
			}
		  if (property_exists($this, 'cancelled')) {
        $colScript .= htmlGetJsTable('Status', 'setCancelledStatus', 'tabStatusCancelled');
        $colScript .= '  var setCancelled=0;';
        $colScript .= '  var filterStatusCancelled=dojo.filter(tabStatusCancelled, function(item){return item.id==dijit.byId("idStatus").value;});';
        $colScript .= '  dojo.forEach(filterStatusCancelled, function(item, i) {setCancelled=item.setCancelledStatus;});';
        $colScript .= '  if (setCancelled==1) {';
        $colScript .= '    dijit.byId("cancelled").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("cancelled").set("checked", false);';
        $colScript .= '  }';
      }
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
		} else if ($colName=="idle") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			$colScript .= '  if (this.checked) { ';
			if (property_exists($this, 'idleDateTime')) {
				$colScript .= '    if (! dijit.byId("idleDateTime").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("idleDateTime").set("value", curDate); ';
				$colScript .= '      dijit.byId("idleDateTimeBis").set("value", curDate); ';
				$colScript .= '    }';
			}
			if (property_exists($this, 'idleDate')) {
				$colScript .= '    if (! dijit.byId("idleDate").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
				$colScript .= '    }';
			}
			if (property_exists($this, 'done')) {
				$colScript .= '    if (! dijit.byId("done").get("checked")) {';
				$colScript .= '      dijit.byId("done").set("checked", true);';
				$colScript .= '    }';
			}
			if (property_exists($this, 'handled')) {
				$colScript .= '    if (! dijit.byId("handled").get("checked")) {';
				$colScript .= '      dijit.byId("handled").set("checked", true);';
				$colScript .= '    }';
			}
			$colScript .= '  } else {';
			if (property_exists($this, 'idleDateTime')) {
				$colScript .= '    dijit.byId("idleDateTime").set("value", null); ';
				$colScript .= '    dijit.byId("idleDateTimeBis").set("value", null); ';
			}
			if (property_exists($this, 'idleDate')) {
				$colScript .= '    dijit.byId("idleDate").set("value", null); ';
			}
			$colScript .= '  } ';
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
		} else if ($colName=="done") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			$colScript .= '  if (this.checked) { ';
			if (property_exists($this, 'doneDateTime')) {
				$colScript .= '    if (! dijit.byId("doneDateTime").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("doneDateTime").set("value", curDate); ';
				$colScript .= '      dijit.byId("doneDateTimeBis").set("value", curDate); ';
				$colScript .= '    }';
			}
			if (property_exists($this, 'doneDate')) {
				$colScript .= '    if (! dijit.byId("doneDate").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
				$colScript .= '    }';
			}
			if (property_exists($this, 'handled')) {
				$colScript .= '    if (! dijit.byId("handled").get("checked")) {';
				$colScript .= '      dijit.byId("handled").set("checked", true);';
				$colScript .= '    }';
			}
			$colScript .= '  } else {';
			if (property_exists($this, 'doneDateTime')) {
				$colScript .= '    dijit.byId("doneDateTime").set("value", null); ';
				$colScript .= '    dijit.byId("doneDateTimeBis").set("value", null); ';
			}
			if (property_exists($this, 'doneDate')) {
				$colScript .= '    dijit.byId("doneDate").set("value", null); ';
			}
			if (property_exists($this, 'idle')) {
				$colScript .= '    if (dijit.byId("idle").get("checked")) {';
				$colScript .= '      dijit.byId("idle").set("checked", false);';
				$colScript .= '    }';
			}
			$colScript .= '  } ';
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
		} else if ($colName=="handled") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			$colScript .= '  if (this.checked) { ';
			if (property_exists($this, 'handledDateTime')) {
				$colScript .= '    if ( ! dijit.byId("handledDateTime").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("handledDateTime").set("value", curDate); ';
				$colScript .= '      dijit.byId("handledDateTimeBis").set("value", curDate); ';
				$colScript .= '    }';
			}
			if (property_exists($this, 'handledDate')) {
				$colScript .= '    if (! dijit.byId("handledDate").get("value")) {';
				$colScript .= '      var curDate = new Date();';
				$colScript .= '      dijit.byId("handledDate").set("value", curDate); ';
				$colScript .= '    }';
			}
			$colScript .= '  } else {';
			if (property_exists($this, 'handledDateTime')) {
				$colScript .= '    dijit.byId("handledDateTime").set("value", null); ';
				$colScript .= '    dijit.byId("handledDateTimeBis").set("value", null); ';
			}
			if (property_exists($this, 'handledDate')) {
				$colScript .= '    dijit.byId("handledDate").set("value", null); ';
			}
			if (property_exists($this, 'done')) {
				$colScript .= '    if (dijit.byId("done").get("checked")) {';
				$colScript .= '      dijit.byId("done").set("checked", false);';
				$colScript .= '    }';
			}
			if (property_exists($this, 'idle')) {
				$colScript .= '    if (dijit.byId("idle").get("checked")) {';
				$colScript .= '      dijit.byId("idle").set("checked", false);';
				$colScript .= '    }';
			}
			$colScript .= '  } ';
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
		}
		return $colScript;
	}

	// ============================================================================**********
	// MISCELLANOUS FUNCTIONS
	// ============================================================================**********

	/** =========================================================================
	 * Draw a specific item for a given class.
	 * Should always be implemented in the corresponding class.
	 * Here is alway an error.
	 * @param $item the item
	 * @return a message to draw (to echo) : always an error in this class,
	 *  must be redefined in the inherited class
	 */
	public function drawSpecificItem($item){
		return "No specific item " . $item . " for object " . get_class($this);
	}

	public function drawCalculatedItem($item){
		return "No calculated item " . $item . " for object " . get_class($this);
	}
	/** =========================================================================
	 * Indicate if a property of is translatable
	 * @param $col the nale of the property
	 * @return a boolean
	 */
	public function isFieldTranslatable($col) {
		$testField='_is' . ucfirst($col) . 'Translatable';
		if (isset($this->{$testField})) {
			if ($this->{$testField}) {
				return true;
			} else {
				return false;
			}
		}
	}

	/** =========================================================================
	 * control data corresponding to Model constraints, before saving an object
	 * @param void
	 * @return "OK" if controls are good or an error message
	 *  must be redefined in the inherited class
	 */
	public function control(){
		//traceLog('control (for ' . get_class($this) . ' #' . $this->id . ')');
		global $cronnedScript, $loginSave;
		$result="";
		//
		$right="";
	  // Manage Exceptions
		if (get_class($this)=='Alert' or get_class($this)=='Mail' 
		 or get_class($this)=='Audit' or get_class($this)=='AuditSummary'
		 or get_class($this)=='ColumnSelector') {
			$right='YES';
		} else if (isset($cronnedScript) and $cronnedScript==true) { // Cronned script can do everything
			$right='YES';
	  } else if (isset($loginSave) and $loginSave==true) { // User->save during autenticate can do everything
        $right='YES';
		} else if (get_class($this)=='User') { // User can change his own data (to be able to change password)
			$usr=$_SESSION['user'];
			if ($this->id==$usr->id) {
				$right='YES';
			}
		}
		if ($right!='YES') {
		  $right=securityGetAccessRightYesNo('menu' . get_class($this), (($this->id)?'update':'create'), $this);
		}
		if ($right!='YES') {
			$result.='<br/>' . i18n('error'.(($this->id)?'Update':'Create').'Rights');
			return $result;
		}
		foreach ($this as $col => $val) {
			$dataType=$this->getDataType($col);
			$dataLength=$this->getDataLength($col);
			if (substr($col,0,1)!='_') {
				if (ucfirst($col) == $col and is_object($val)) {
					$subResult=$val->control();
					if ($subResult!='OK') {
						$result.= $subResult;
					}
				} else {
					// check if required
					if (strpos($this->getFieldAttributes($col), 'required')!==false) {
						if (!$val) {
							$result.='<br/>' . i18n('messageMandatory',array($this->getColCaption($col)));
						} else if (trim($val)==''){
							$result.='<br/>' . i18n('messageMandatory',array($this->getColCaption($col)));
						}
					}
					if ($dataType=='datetime') {
						if (strlen($val)==9) {
							$result.='<br/>' . i18n('messageDateMandatoryWithTime',array(i18n('col' . ucfirst($col))));
						}
					}
					if ($dataType=='date' and $val!='') {
						if (strlen($val)!=10 or substr($val,4,1)!='-' or substr($val,7,1)!='-') {
							$result.='<br/>' . i18n('messageInvalidDateNamed',array(i18n('col' . ucfirst($col))));
						}
					}
				}
			}
			/** TODO impement format control */
			if ($val and $col!='colRefName') {
				if ($dataType=='varchar') {
					if (strlen($val)>$dataLength) {
						$result.='<br/>' . i18n('messageTextTooLong',array(i18n('col' . ucfirst($col)),$dataLength));
					}
				} else if ($dataType=="int" or $dataType=="decimal") {
					if (trim($val) and ! is_numeric($val)) {
						$result.='<br/>' . i18n('messageInvalidNumeric',array(i18n('col' . ucfirst($col))));
					}
				}
			}
		}
		$idType='id'.((get_class($this)=='TicketSimple')?'Ticket':get_class($this)).'Type';
		if (property_exists($this, $idType)) {
			$type=((get_class($this)=='TicketSimple')?'Ticket':get_class($this)).'Type';
			$objType=new $type($this->$idType);
			if (property_exists($objType, 'mandatoryDescription') and $objType->mandatoryDescription
			and property_exists($this, 'description')) {
				if (! $this->description) {
					$result.='<br/>' . i18n('messageMandatory',array($this->getColCaption('description')));
				}
			}
			if (property_exists($objType, 'mandatoryResourceOnHandled') and $objType->mandatoryResourceOnHandled
			and property_exists($this, 'idResource')
			and property_exists($this, 'handled')) {
				if ($this->handled and ! trim($this->idResource)) {
					$user=$_SESSION['user'];
					if ($user->isResource and Parameter::getGlobalParameter('setResponsibleIfNeeded')!='NO') {
						$this->idResource=$user->id;
					} else {
						$result.='<br/>' . i18n('messageMandatory',array($this->getColCaption('idResource')));
					}
				}
			}
			if (property_exists($objType, 'mandatoryResultOnDone') and $objType->mandatoryResultOnDone
			and property_exists($this, 'result')
			and property_exists($this, 'done')) {
				if ($this->done and ! $this->result) {
					$result.='<br/>' . i18n('messageMandatory',array($this->getColCaption('result')));
				}
			}
		}
		// Control for Closed item that all items are closed
		if (property_exists($this,'idle') and $this->idle) {
			$relationShip=self::$_closeRelationShip;
			if (array_key_exists(get_class($this),$relationShip)) {
				$objects='';
				foreach ( $relationShip[get_class($this)] as $object=>$mode) {
					if ($mode=='control' and property_exists($object,'idle')) {
						$where=null;
						$obj=new $object();
						$crit=array('id' . get_class($this) => $this->id, 'idle'=>'0');
						if (property_exists($obj, 'refType') and property_exists($obj,'refId')) {
							$crit=array("refType"=>get_class($this), "refId"=>$this->id);
						}
						if ($object=="Dependency") {
							$crit=null;
							$where="idle=0 and ((predecessorRefType='" . get_class($this) . "' and predecessorRefId=" . $this->id .")"
							. " or (successorRefType='" . get_class($this) . "' and successorRefId=" . $this->id ."))";
						}
						if ($object=="Link") {
							$crit=null;
							$where="idle=0 and ((ref1Type='" . get_class($this) . "' and ref1Id=" . Sql::fmtId($this->id) .")"
							. " or (ref2Type='" . get_class($this) . "' and ref2Id=" . Sql::fmtId($this->id) ."))";
						}
						$nb=$obj->countSqlElementsFromCriteria($crit,$where);
						if ($nb>0) {
							$objects.="<br/>&nbsp;-&nbsp;" . i18n($object) . " (" . $nb . ")";
						}
					}
				}
				if ($objects!="") {
					$result.="<br/>" . i18n("errorControlClose") . $objects;
				}
			}
		}
		// control Workflow
		$class=get_class($this);
		$old=new $class($this->id);
		$fldType='id'.$class.'Type';

		if ( property_exists($class, 'idStatus') and property_exists($class, $fldType)
		and trim($old->idStatus) and trim($old->$fldType)
		and (trim($old->idStatus)!=trim($this->idStatus) or trim($old->$fldType)!=trim($this->$fldType) )
		and $old->id and $class!='Document') {
			$oldStat=new Status($old->idStatus);
			$statList=SqlList::getList('Status');
			$firstStat=key($statList);
			if (! $oldStat->isCopyStatus and ($this->idStatus!=$old->idStatus or $this->idStatus!=$firstStat) ) {
				$type=new Type($this->$fldType);
				$crit=array('idWorkflow'=>$type->idWorkflow,
	    	            'idStatusTo'=>$this->idStatus,
	    	            'idProfile'=>$_SESSION['user']->idProfile);
				if (trim($old->idStatus)!=trim($this->idStatus)) {
					$crit['idStatusFrom']=$old->idStatus;
				}
				$ws=new WorkflowStatus();
				$wsList=$ws->getSqlElementsFromCriteria($crit);
				$allowed=false;
				foreach ($wsList as $ws) {
					if ($ws->allowed) {
						$allowed=true;
						break;
					}
				}
				if (! $allowed) {
					$result.="<br/>" . i18n("errorWorflow");
				}
			}
		}
		if ($result=="") {
			$result='OK';
		}
		return $result;
	}

	/** =========================================================================
	 * control data corresponding to Model constraints, before deleting an object
	 * @param void
	 * @return "OK" if controls are good or an error message
	 *  must be redefined in the inherited class
	 */
	public function deleteControl(){
		$result="";
		$objects="";
		$right=securityGetAccessRightYesNo('menu' . get_class($this), 'delete', $this);
		if ($right!='YES') {
			$result.='<br/>' . i18n('errorDeleteRights');
			return $result;
		}
		$relationShip=self::$_relationShip;
		if (array_key_exists(get_class($this),$relationShip)) {
			$relations=$relationShip[get_class($this)];
			foreach ($relations as $object=>$mode) {
				if ($mode=="control") {
					$where=null;
					$obj=new $object();
					$crit=array('id' . get_class($this) => $this->id);
					if (property_exists($obj, 'refType') and property_exists($obj,'refId')) {
						//if (($object=="Assignment" and get_class($this)=="Activity") or $object=="Note" or $object=="Attachement") {
						$crit=array("refType"=>get_class($this), "refId"=>$this->id);
					}
					if ($object=="Dependency") {
						$crit=null;
						$where="(predecessorRefType='" . get_class($this) . "' and predecessorRefId=" . $this->id .")"
						. " or (successorRefType='" . get_class($this) . "' and successorRefId=" . $this->id .")";
					}
					if ($object=="Link") {
						$crit=null;
						$where="(ref1Type='" . get_class($this) . "' and ref1Id=" . Sql::fmtId($this->id) .")"
						. " or (ref2Type='" . get_class($this) . "' and ref2Id=" . Sql::fmtId($this->id) .")";
					}

					$nb=$obj->countSqlElementsFromCriteria($crit,$where);
					if ($nb>0) {
						$objects.="<br/>&nbsp;-&nbsp;" . i18n($object) . " (" . $nb . ")";
					}
				}
			}
			if ($objects!="") {
				$result.="<br/>" . i18n("errorControlDelete") . $objects;
			}
		}
		if ($result=="") {
			$result='OK';
		}
		return $result;
	}

	/** =========================================================================
	 * Return the menu string for the object (from its class)
	 * @param void
	 * @return a string
	 */
	public function getMenuClass() {
		return "menu" . get_class($this);
	}

	/** =========================================================================
	 * Send a mail on status change (if object is "mailable")
	 * @param void
	 * @return status of mail, if sent
	 */
	public function sendMailIfMailable($newItem=false, $statusChange=false, $directStatusMail=null,
	$responsibleChange=false, $noteAdd=false, $attachmentAdd=false,
	$noteChange=false, $descriptionChange=false, $resultChange=false, $assignmentAdd=false, $assignmentChange=false,
	$anyChange=false) {
		$objectClass=get_class($this);
		if ($objectClass=='TicketSimple') {$objectClass='Ticket';}
		if ($objectClass=='History') {
			return false; // exit : not for History
		}
		$mailable=SqlElement::getSingleSqlElementFromCriteria('Mailable', array('name'=>$objectClass));
		if (! $mailable or ! $mailable->id) {
			return false; // exit if not mailable object
		}
		if (! property_exists($this, 'idStatus')) {
			return false; // exit if object has not idStatus
		}
		if (! $this->idStatus) {
			return false; // exit if status not set
		}
		$crit=array();
		$crit['idStatus']=$this->idStatus;
		$crit="idle='0' and idMailable='" . $mailable->id . "' and ( false ";
		if ($statusChange) {
			$crit.="  or idStatus='" . $this->idStatus . "' ";
		}
		if ($responsibleChange) {
			$crit.=" or idEvent='1' ";
		}
		if ($noteAdd) {
			$crit.=" or idEvent='2' ";
		}
		if ($attachmentAdd) {
			$crit.=" or idEvent='3' ";
		}
		if ($noteChange) {
			$crit.=" or idEvent='4' ";
		}
		if ($descriptionChange) {
			$crit.=" or idEvent='5' ";
		}
		if ($resultChange) {
			$crit.=" or idEvent='6' ";
		}
		if ($assignmentAdd) {
			$crit.=" or idEvent='7' ";
		}
		if ($assignmentChange) {
			$crit.=" or idEvent='8' ";
		}
		if ($anyChange) {
			$crit.=" or idEvent='9' ";
		}
		$crit.=")";
		$statusMail=new StatusMail();
		$statusMailList=$statusMail->getSqlElementsFromCriteria(null,false, $crit);
		if ($directStatusMail) { // Direct Send Mail
			$statusMailList=array($directStatusMail->id => $directStatusMail);
		}
		if (count($statusMailList)==0) {
			return false; // exit not a status for mail sending (or disabled)
		}

		$dest="";
		foreach ($statusMailList as $statusMail) {
			if ($statusMail->idType){
				if (property_exists($this, 'idType') and $this->idType!=$statusMail->idType) {
					continue; // exist : not corresponding type
				}
				$typeName='id'.$objectClass.'Type';
				if (property_exists($this, $typeName) and $this->$typeName!=$statusMail->idType) {
					continue; // exist : not corresponding type
				}
			}
			if ($statusMail->mailToUser==0 and $statusMail->mailToResource==0 and $statusMail->mailToProject==0
			and $statusMail->mailToLeader==0  and $statusMail->mailToContact==0  and $statusMail->mailToOther==0
			and $statusMail->mailToManager==0 and $statusMail->mailToAssigned==0) {
				continue; // exit not a status for mail sending (or disabled)
			}
			if ($statusMail->mailToUser) {
				if (property_exists($this,'idUser')) {
					$user=new User($this->idUser);
					$newDest = "###" . $user->email . "###";
					if ($user->email and strpos($dest,$newDest)===false) {
						$dest.=($dest)?', ':'';
						$dest.= $newDest;
					}
				}
			}
			if ($statusMail->mailToResource) {
				if (property_exists($this, 'idResource')) {
					$resource=new Resource($this->idResource);
					$newDest = "###" . $resource->email . "###";
					if ($resource->email and strpos($dest,$newDest)===false) {
						$dest.=($dest)?', ':'';
						$dest.= $newDest;
					}
				}
			}
			if ($statusMail->mailToProject or $statusMail->mailToLeader) {
				$aff=new Affectation();
				$crit=array('idProject'=>$this->idProject, 'idle'=>'0');
				$affList=$aff->getSqlElementsFromCriteria($crit, false);
				if ($affList and count($affList)>0) {
					foreach ($affList as $aff) {
						$resource=new Resource($aff->idResource);
						if ($statusMail->mailToProject) {
							$newDest = "###" . $resource->email . "###";
							if ($resource->email and strpos($dest,$newDest)===false) {
								$dest.=($dest)?', ':'';
								$dest.= $newDest;
							}
						}
						if ($statusMail->mailToLeader and $resource->idProfile) {
							$prf=new Profile($resource->idProfile);
							if ($prf->profileCode=='PL') {
								$newDest = "###" . $resource->email . "###";
								if ($resource->email and strpos($dest,$newDest)===false) {
									$dest.=($dest)?', ':'';
									$dest.= $newDest;
								}
							}
						}
					}
				}
			}
			if ($statusMail->mailToManager) {
				if (property_exists($this,'idProject')) {
					$project=new Project($this->idProject);
					$manager=new Affectable($project->idUser);
					$newDest = "###" . $manager->email . "###";
					if ($manager->email and strpos($dest,$newDest)===false) {
						$dest.=($dest)?', ':'';
						$dest.= $newDest;
					}
				}
			}
			if ($statusMail->mailToAssigned) {
				$ass=new Assignment();
				$crit=array('refType'=>$objectClass,'refId'=>$this->id);
				$assList=$ass->getSqlElementsFromCriteria($crit);
				foreach ($assList as $ass) {
					$res=new Resource($ass->idResource);
					$newDest = "###" . $res->email . "###";
					if ($res->email and strpos($dest,$newDest)===false) {
						$dest.=($dest)?', ':'';
						$dest.= $newDest;
					}
				}
			}
			if ($statusMail->mailToContact) {
				if (property_exists($this,'idContact')) {
					$contact=new Contact($this->idContact);
					$newDest = "###" . $contact->email . "###";
					if ($contact->email and strpos($dest,$newDest)===false) {
						$dest.=($dest)?', ':'';
						$dest.= $newDest;
					}
				}
			}
			if ($statusMail->mailToOther) {
				if ($statusMail->otherMail) {
					$otherMail=str_replace(';',',', $statusMail->otherMail);
					$otherMail=str_replace(' ',',', $otherMail);
					$split=explode(',',$otherMail);
					foreach ($split as $adr) {
						if ($adr and $adr!='') {
							$newDest = "###" . $adr . "###";
							if (strpos($dest,$newDest)===false) {
								$dest.=($dest)?', ':'';
								$dest.= $newDest;
							}
						}
					}
				}
			}
		}
		if ($dest=="") {
			return false; // exit no addressees
		}
		$dest=str_replace('###','',$dest);
		if ($newItem) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleNew');
		} else if ($noteAdd) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleNote');
		} else if ($noteChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleNoteChange');
		} else if ($assignmentAdd) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleAssignment');
		} else if ($assignmentChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleAssignmentChange');
		} else if ($attachmentAdd) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleAttachment');
		} else if ($statusChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleStatus');
		} else if ($responsibleChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleResponsible');
		} else if ($descriptionChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleDescription');
		} else if ($resultChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleResult');
		} else if ($directStatusMail) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleDirect');
		} else if ($anyChange) {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitleAnyChange');
		} else {
			$paramMailTitle=Parameter::getGlobalParameter('paramMailTitle'); // default
		}
		$arrayFrom=array();
		$arrayTo=array();
		// Class of item
		$arrayFrom[]='${item}';
		$item=i18n($objectClass);
		$arrayTo[]=$item;
		// id
		$arrayFrom[]='${id}';
		$arrayTo[]=$this->id;
		// name
		$arrayFrom[]='${name}';
		$arrayTo[]=(property_exists($this, 'name'))?$this->name:'';
		// status
		$arrayFrom[]='${status}';
		$arrayTo[]=(property_exists($this, 'idStatus'))?SqlList::getNameFromId('Status', $this->idStatus):'';
		// project
		$arrayFrom[]='${project}';
		$arrayTo[]=(property_exists($this, 'idProject'))?SqlList::getNameFromId('Project', $this->idProject):'';
		// type
		$typeName='id' . $objectClass . 'Type';
		$arrayFrom[]='${type}';
		$arrayTo[]=(property_exists($this, $typeName))?SqlList::getNameFromId($objectClass . 'Type', $this->$typeName):'';
		// reference
		$arrayFrom[]='${reference}';
		$arrayTo[]=(property_exists($this, 'reference'))?$this->reference:'';
		// externalReference
		$arrayFrom[]='${externalReference}';
		$arrayTo[]=(property_exists($this, 'externalReference'))?$this->externalReference:'';
		// issuer
		$arrayFrom[]='${issuer}';
		$arrayTo[]=(property_exists($this, 'idUser'))?SqlList::getNameFromId('User', $this->idUser):'';
		// responsible
		$arrayFrom[]='${responsible}';
		$arrayTo[]=(property_exists($this, 'idResource'))?SqlList::getNameFromId('Resource', $this->idResource):'';
		// db display name
		$arrayFrom[]='${dbName}';
		$arrayTo[]=Parameter::getGlobalParameter('paramDbDisplayName');
		// sender
		$arrayFrom[]='${sender}';
		$user=$_SESSION['user'];
		$arrayTo[]=($user->resourceName)?$user->resourceName:$user->name;
		// Format title
		$title=str_replace($arrayFrom, $arrayTo, $paramMailTitle);

		$message=$this->getMailDetail();
		if ($directStatusMail and isset($directStatusMail->message)) {
			$message=$directStatusMail->message.'<br/><br/>'.$message;
		}

		$message='<html>' .
      '<head>'  . 
      '<title>' . $title . '</title>' .
      '</head>' . 
      '<body>' . 
		$message .
      '</body>' . 
      '</html>';
		$message = wordwrap($message, 70); // wrapt text so that line do not exceed 70 cars per line
		$resultMail=sendMail($dest, $title, $message, $this);
		if ($directStatusMail) {
			if ($resultMail) {
				return array('result'=>'OK', 'dest'=>$dest);
			} else {
				return array('result'=>'', 'dest'=>$dest);
			}
		}
		return $resultMail;
	}

	/**
	 *
	 * Get the detail of object, to be send by mail
	 * This is a simplified copy of objectDetail.php, in print mode
	 */
	public function getMailDetail () {
		$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
		$currency=Parameter::getGlobalParameter('currency');
		$msg="";
		$rowStart='<tr>';
		$rowEnd='</tr>';
		$labelStart='<td style="background:#DDDDDD;font-weight:bold;text-align: right;width:25%;vertical-align: middle;">&nbsp;&nbsp;';
		$labelEnd='&nbsp;</td>';
		$fieldStart='<td style="width:2px;">&nbsp;</td><td style="background:#FFFFFF;text-align: left;">';
		$fieldEnd='</td>';
		$sectionStart='<td colspan="3" style="background:#555555;color: #FFFFFF; text-align: center;font-size:10pt;font-weight:bold;">';
		$sectionEnd='</td>';
		$tableStart='<table style="font-size:9pt; width: 95%">';
		$tableEnd='</table>';
		$msg=$tableStart;
		$ref=$this->getReferenceUrl();
		$msg.='<tr><td colspan="3" style="font-size:18pt;color:#AAAAAA"><a href="' . $ref . '">'.i18n(get_class($this)).' #'.$this->id.'</a></td></tr>';
		$nobr=false;
		foreach ($this as $col => $val) {
			$hide=false;
			$nobr_before=$nobr;
			$nobr=false;
			if (substr($col,0,4)=='_tab') {
				// Nothing
			} else if (substr($col,0,5)=='_col_') {
				if (strlen($col)>8) {
					$section=substr($col,9);
					if ($section=='description' or $section=='treatment') {
						$msg.=$rowStart.$sectionStart.i18n('section' . ucfirst($section)).$sectionEnd.$rowEnd;
					}
				} else {
					$section='';
				}
			} else if (substr($col,0,5)=='_sec_') {
				if (strlen($col)>8) {
					$section=substr($col,5);
					if ($section=='description' or $section=='treatment') {
						$msg.=$rowStart.$sectionStart.i18n('section' . ucfirst($section)).$sectionEnd.$rowEnd;
					}
				} else {
					$section='';
				}
			} else if (substr($col,0,5)=='_spe_') {
				// Nothing
			} else if (substr($col,0,6)=='_calc_') {
				$item=substr($col,6);
				$msg.= $this->drawCalculatedItem($item);
			} else if (substr($col,0,5)=='_lib_') {
				$item=substr($col,5);
				if (strpos($this->getFieldAttributes($col), 'nobr')!==false) {$nobr=true;}
				if ($this->getFieldAttributes($col)!='hidden') { $msg.= (($nobr)?'&nbsp;':'').i18n($item).'&nbsp;'; }
				if (!$nobr) { $msg.=$fieldEnd.$rowEnd; }
			} else if (substr($col,0,5)=='_Link') {
				// Nothing
			} else if (substr($col,0,11)=='_Assignment') {
				// Nothing
			} else if (substr($col,0,11)=='_Approver') {
				// Nothing
			} else if (substr($col,0,15)=='_VersionProject') {
				// Nothing
			} else if (substr($col,0,11)=='_Dependency') {
				// Nothing
			} else if ($col=='_ResourceCost') {
				// Nothing
			} else if ($col=='_DocumentVersion') {
				// Nothing
			} else if ($col=='_ExpenseDetail') {
				// Nothing
			} else if (substr($col,0,12)=='_TestCaseRun') {
				// Nothing
			} else if (substr($col,0,1)=='_' and substr($col,0,6)!='_void_' and substr($col,0,7)!='_label_') {
				// Nothing
			} else {
				$attributes=''; $isRequired=false; $readOnly=false;$specificStyle='';
				$dataType = $this->getDataType($col); $dataLength = $this->getDataLength($col);
				if ($dataType=='decimal' and substr($col, -4,4)=='Work') { $hide=true; }
				if (strpos($this->getFieldAttributes($col), 'hidden')!==false) { $hide=true; }
				if (strpos($this->getFieldAttributes($col), 'nobr')!==false) { $nobr=true; }
				if (strpos($this->getFieldAttributes($col), 'invisible')!==false) { $specificStyle.=' visibility:hidden'; }
				if (is_object($val)) {
					if (get_class($val)=='Origin') {
						if ($val->originType and $val->originId) {
							$val=i18n($val->originType) . ' #'.$val->originId.' : '. SqlList::getNameFromId($val->originType, $val->originId);
						} else {
							$val="";
						}
						$dataType='varchar';$dataLength=4000;
					} else {
						$hide=true;
					}
				}
				if ($hide) { continue; }
				if (! $nobr_before) {
					$msg.=$rowStart.$labelStart.$this->getColCaption($col).$labelEnd.$fieldStart;
				} else {
					$msg.="&nbsp;&nbsp;&nbsp;";
				}
				if (is_array($val)) {
					// Nothing
				} else if (substr($col,0,6)=='_void_') {
					// Nothing
				} else if (substr($col,0,7)=='_label_') {
					//$captionName=substr($col,7);
					//$msg.='<label class="label shortlabel">' . i18n('col' . ucfirst($captionName)) . '&nbsp;:&nbsp;</label>';
				} else if ($hide) {
					// Nothing
				} else  if (strpos($this->getFieldAttributes($col), 'displayHtml')!==false) {
					$msg.=  $val;
				} else if ($col=='id') { // id
					$msg.= '<span style="color:grey;">#</span>' . $val;
				} else if ($col=='password') {
					$msg.=  "..."; // nothing
				} else if ($dataType=='date' and $val!=null and $val != '') {
					$msg.= htmlFormatDate($val);
				} else if ($dataType=='datetime' and $val!=null and $val != '') {
					$msg.= htmlFormatDateTime($val,false);
				} else if ($dataType=='time' and $val!=null and $val != '') {
					$msg.= htmlFormatTime($val,false);
				} else if ($col=='color' and $dataLength == 7 ) { // color
					/*echo '<table><tr><td style="width: 100px;">';
					 echo '<div class="colorDisplay" readonly tabindex="-1" ';
					 echo '  value="' . htmlEncode($val) . '" ';
					 echo '  style="width: ' . $smallWidth / 2 . 'px; ';
					 echo ' color: ' . $val . '; ';
					 echo ' background-color: ' . $val . ';"';
					 echo ' >';
					 echo '</div>';
					 echo '</td>';
					 if ($val!=null and $val!='') {
					 //echo '<td  class="detail">&nbsp;(' . htmlEncode($val) . ')</td>';
					 }
					 echo '</tr></table>';*/
				} else if ($dataType=='int' and $dataLength==1) { // boolean
					$msg.='<input type="checkbox" disabled="disabled" ';
					if ($val!='0' and ! $val==null) {
						$msg.=' checked />';
					} else {
						$msg.=' />';
					}
				} else if (substr($col,0,2)=='id' and $dataType=='int' and strlen($col)>2
				and substr($col,2,1)==strtoupper(substr($col,2,1)) ) { // Idxxx
					$msg.= htmlEncode(SqlList::getNameFromId(substr($col,2),$val),'print');
				} else  if ($dataLength > 100) { // Text Area (must reproduce BR, spaces, ...
					$msg.= htmlEncode($val,'print');
				} else if ($dataType=='decimal' and (substr($col, -4,4)=='Cost' or substr($col,-6,6)=='Amount' or $col=='amount') ) {
					if ($currencyPosition=='after') {
						$msg.=  htmlEncode($val,'print') . ' ' . $currency;
					} else {
						$msg.=  $currency . ' ' . htmlEncode($val,'print');
					}
				} else if ($dataType=='decimal' and substr($col, -4,4)=='Work') {
					//$msg.=  Work::displayWork($val) . ' ' . Work::displayShortWorkUnit();
				} else {
					if ($this->isFieldTranslatable($col))  {
						$val=i18n($val);
					}
					if (strpos($this->getFieldAttributes($col), 'html')!==false) {
						$msg.=  $val;
					} else {
						$msg.=  htmlEncode($val,'print');
					}
				}
				if (! $nobr) {
					$msg.=$fieldEnd.$rowEnd;
				}
			}
		}
		if (isset($this->_Note) and is_array($this->_Note)) {
			$msg.=$rowStart.$sectionStart.i18n('sectionNotes').$sectionEnd.$rowEnd;
			$note = new Note();
			$notes=$note->getSqlElementsFromCriteria(array('refType'=>get_class($this),'refId'=>$this->id));
			foreach ($notes as $note) {
				if ($note->idPrivacy==1) {
					$userId=$note->idUser;
					$userName=SqlList::getNameFromId('User',$userId);
					$creationDate=$note->creationDate;
					$updateDate=$note->updateDate;
					if ($updateDate==null) {$updateDate='';}
					$msg.=$rowStart.$labelStart;
					$msg.=$userName;
					$msg.= '<br/>';
					if ($updateDate) {
						$msg.= '<i>' . htmlFormatDateTime($updateDate) . '</i>';
					} else {
						$msg.= htmlFormatDateTime($creationDate);
					}
					$msg.=$labelEnd.$fieldStart;
					$msg.=htmlEncode($note->note,'print');
					$msg.=$fieldEnd.$rowEnd;
				}
			}
		}
		$msg.=$tableEnd;
		return $msg;
	}
	
	public function getReferenceUrl() {
		$url=(((isset($_SERVER['HTTPS']) and strtolower($_SERVER['HTTPS'])=='on') or $_SERVER['SERVER_PORT']=='443')?'https://':'http://')
    .$_SERVER['SERVER_NAME']
    .(($_SERVER['SERVER_PORT']!='80' and $_SERVER['SERVER_PORT']!='443')?':'.$_SERVER['SERVER_PORT']:'')
    .$_SERVER['REQUEST_URI'];
    $ref="";
    if (strpos($url,'/tool/')) {
       $ref.=substr($url,0,strpos($url,'/tool/'));
    } else if (strpos($url,'/view/')) {
    	$ref.=substr($url,0,strpos($url,'/view/'));
    } else if (strpos($url,'/report/')) {
      $ref.=substr($url,0,strpos($url,'/report/'));
    }   
    $ref.='/view/main.php?directAccess=true&objectClass='.get_class($this).'&objectId='.$this->id;
    return $ref;
	}
	 
	/** =========================================================================
	 * Specific function added to setup a workaround for bug #305
	 * waiting for Dojo fixing (Dojo V1.6 ?)
	 * @todo : deactivate this function if Dojo fixed.
	 */
	public function recalculateCheckboxes($force=false) {
		// if no status => nothing to do
		if (! property_exists($this, 'idStatus')) {
			return;
		}
		$status=new Status($this->idStatus);
		// if no type => nothong to do
		$fldType = 'id' . get_class($this) . 'Type';
		$typeClass=get_class($this) . 'Type';
		if (! property_exists($this, $fldType)) {
			return;
		}
		$type=new $typeClass($this->$fldType);
		if ( ( (property_exists($type,'lockHandled') and $type->lockHandled) or $force)
		and property_exists($this,'handled')
		) {
			$this->handled=($status->setHandledStatus)?1:0;
		}
		if ( ( (property_exists($type,'lockDone') and $type->lockDone) or $force)
		and property_exists($this,'done') ) {
			$this->done=($status->setDoneStatus)?1:0;
		}
		if ( ( (property_exists($type,'lockIdle') and $type->lockIdle) or $force)
		and property_exists($this,'idle') ) {
			$this->idle=($status->setIdleStatus)?1:0;
		}
		if ( ( (property_exists($type,'lockCancelled') and $type->lockCancelled) or $force)
    and property_exists($this,'cancelled') ) {
			$this->cancelled=($status->setCancelledStatus)?1:0;
		}
	}

	public function getAlertLevel($withIndicator=false) {
		$crit=array('refType'=>get_class($this),'refId'=>$this->id);
		$indVal=new IndicatorValue();
		$lst=$indVal->getSqlElementsFromCriteria($crit, false);
		$level="NONE";
		$desc='';
		foreach($lst as $indVal) {
			if ($indVal->warningSent and $level!="ALERT") {
				$level="WARNING";
			}
			if ($indVal->alertSent) {
				$level="ALERT";
			}
			if ($withIndicator) {
				$color=($indVal->alertSent)?"#FFCCCC":"#FFFFCC";
				$desc.='<div style="font-size:80%;background-color:'.$color.'">'.$indVal->getShortDescription().'</div>';
				//$indDesc=$indVal->getShortDescriptionArray();
				//$desc.=$indDesc['indicator'];
				//$desc.=$indDesc['target'];
			}
		}
		return array('level'=>$level,'description'=>$desc);
	}

	public function buildSelectClause($included=false,$hidden=array()){	
		$table=$this->getDatabaseTableName();
		$select="";
		$from="";
		if (is_subclass_of($this,'PlanningElement')) {
			$this->setVisibility();
		}
		foreach ($this as $col=>$val) {		
			$firstCar=substr($col,0,1);
			$threeCars=substr($col,0,3);
			if ( ($included and ($col=='id' or $threeCars=='ref' or $threeCars=='top' or $col=='idle') )
			or ($firstCar=='_')
			or ( strpos($this->getFieldAttributes($col), 'hidden')!==false and strpos($this->getFieldAttributes($col), 'forceExport')===false )
			or ($col=='password')
			or (isset($hidden[$col]))
			or (strpos($this->getFieldAttributes($col), 'noExport')!==false)
			or (strpos($this->getFieldAttributes($col), 'calculated')!==false)
			//or ($costVisibility!="ALL" and (substr($col, -4,4)=='Cost' or substr($col,-6,6)=='Amount') )
			//or ($workVisibility!="ALL" and (substr($col, -4,4)=='Work') )
			// or calculated field : not to be fetched
			) {
				// Here are all cases of not dispalyed fields
			} else if ($firstCar==ucfirst($firstCar)) {
				$ext=new $col();
				$from.=' left join ' . $ext->getDatabaseTableName() .
              ' on ' . $table . ".id" .  
              ' = ' . $ext->getDatabaseTableName() . '.refId' .
  				    ' and ' . $ext->getDatabaseTableName() . ".refType='" . get_class($this) . "'";
				$extClause=$ext->buildSelectClause(true,$hidden);
				if (trim($extClause['select'])) {
				  $select.=', '.$extClause['select'];
				}
			} else {
				$select .= ($select=='')?'':', ';
				$select .= $table . '.' . $this->getDatabaseColumnName($col) . ' as ' . $col;
			}
		}
		return array('select'=>$select,'from'=>$from);
	}

	public function setReference($force=false, $old=null) {
		scriptLog('SqlElement::setReference');
		if (! property_exists($this,'reference')) {
			return;
		}
		$class=get_class($this);
		if ($class=='TicketSimple') $class='Ticket';
		$fmtPrefix=Parameter::getGlobalParameter('referenceFormatPrefix');
		$fmtNumber=Parameter::getGlobalParameter('referenceFormatNumber');
		$change=Parameter::getGlobalParameter('changeReferenceOnTypeChange');
		$type='id' . $class . 'Type';
		if ($this->reference and ! $force) {
			if ($change!='YES') {
				return;
			}
			if (! property_exists($this,$type)) {
				return;
			}
			if (! $old) {
				$old=new $class($this->id);
			}
			if ($this->$type==$old->$type) {
				return;
			}
		}
		if (isset($this->idProject)) {
			$projObj=new Project($this->idProject);
		} else {
			$projObj=new Project();
		}
		if (isset($this->$type)) {
			$typeObj=new Type($this->$type);
		} else {
			$typeObj=new Type();
		}
		$prefix=str_replace(array('{PROJ}', '{TYPE}'), array($projObj->projectCode,$typeObj->code),$fmtPrefix);
		$query="select max(reference) as ref from " . $this->getDatabaseTableName();
		$query.=" where reference like '" . $prefix . "%'";
		$query.=" and length(reference)=( select max(length(reference)) from " . $this->getDatabaseTableName();
		$query.=" where reference like '" . $prefix . "%')";
		$ref=$prefix;
		$mutex = new Mutex($prefix);
		$mutex->reserve();
		$result=Sql::query($query);
		$numMax='0';
		if (count($result)>0) {
			$line=Sql::fetchLine($result);
			$refMax=$line['ref'];
			$numMax=substr($refMax,strlen($prefix));
		}
		$numMax+=1;
		if ($fmtNumber and  $fmtNumber-strlen($numMax)>0) {
			$num=substr('0000000000', 0, $fmtNumber-strlen($numMax)) . $numMax;
		} else {
			$num=$numMax;
		}
		$this->reference=$prefix.$num;
		if (get_class($this)=='Document' and property_exists($this, 'documentReference')) {
			$fmtDocument=Parameter::getGlobalParameter('documentReferenceFormat');
			$docRef=str_replace(array('{PROJ}',              '{TYPE}',      '{NUM}', '{NAME}'),
			array($projObj->projectCode, $typeObj->code, $num,   $this->name),
			$fmtDocument);
			$this->documentReference=$docRef;
		}
		if ($force) {
			$this->updateSqlElement();
		}
		$mutex->release();

	}


	public function setDefaultResponsible() {
		if (get_class($this)!='Project' and property_exists($this,'idResource') and property_exists($this,'idProject')
		and ! trim($this->idResource) and trim($this->idProject)) {
			if (Parameter::getGlobalParameter('setResponsibleIfSingle')=="YES") {
				$aff=new Affectation();
				$crit=array('idProject'=>$this->idProject);
				$cpt=$aff->countSqlElementsFromCriteria($crit);
				if ($cpt==1) {
					$aff=SqlElement::getSingleSqlElementFromCriteria('Affectation', $crit);
					$res=new Resource($aff->idResource);
					if ($res and $res->id) {
						$this->idResource=$res->id;
					}
				}
			}
		}
	}

	public function getTitle($col) {
		return i18n('col'.$col);
	}

	public static function unsetRelationShip($rel1, $rel2) {
		unset(self::$_relationShip[$rel1][$rel2]);
	}

	public function getOld() {
		$class=get_class($this);
		return new $class($this->id);
	}

	public function splitLongFields() {
		$maxLenth=500;
		foreach ($this as $fld=>$val) {
			if ($this->getDataLength($fld)>100 and strlen($val)>$maxLenth) {
				//$secFull="_col_1_1_".$fld;
				//$this->$secFull=$val;
				$fldFull="_".$fld."_full";
				$this->$fldFull=$val;
				$this->$fld=substr($val,0,$maxLenth).' (...)';
			}

		}
	}

	public static function isVisibleField($col) {
		// Check if cost and work field is visible for profile
		$cost=(substr($col,-4)=='Cost' or substr($col,-6)=="Amount")?true:false;
		$work=(substr($col,-4)=='Work')?true:false;
		if (!$cost and !$work) {return true;}
		if (! self::$staticCostVisibility or ! self::$staticWorkVisibility) {
			$pe=new PlanningElement();
			$pe->setVisibility();
			self::$staticCostVisibility=$pe->_costVisibility;
			self::$staticWorkVisibility=$pe->_workVisibility;
		}
		$costVisibility=self::$staticCostVisibility ;
		$workVisibility=self::$staticWorkVisibility;
		$validated=(substr($col,0,9)=='validated')?true:false;
		if ($cost) {
			if ($costVisibility=='ALL') {
				return true;
			} else if ($costVisibility=='NO') {
				return false;
			} else if ($costVisibility=='VAL') {
				if ($validated) {
					return true;
				} else {
					return false;
				}
			} else {
				errorLog("ERROR : costVisibility='$costVisibility' is not 'ALL', 'NO' or 'VAL'");
			}
		} else if ($work) {
			if ($workVisibility=='ALL') {
				return true;
			} else if ($workVisibility=='NO') {
				return false;
			} else if ($workVisibility=='VAL') {
				if ($validated) {
					return true;
				} else {
					return false;
				}
			} else {
				errorLog("ERROR : workVisibility='$workVisibility' is not 'ALL', 'NO' or 'VAL'");
			}
		}
		return true;
	}
}
?>