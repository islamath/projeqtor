<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class TestSession extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idProduct;
  public $idVersion;
  public $idTestSessionType;
  public $name;
  public $externalReference;
  public $creationDateTime;
  public $idUser;
  public $description;
  public $_col_2_2_treatment;
  public $idActivity;
  public $idTestSession;
  public $idStatus;
  public $idResource;
  public $startDate;
  public $endDate;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_Assignment;
  public $_Assignment=array();
  public $_col_1_1_Progress;
  public $TestSessionPlanningElement;
  public $_spe_separator_progress;
  public $_tab_7_2 = array('testSummary','countTotal', 'countPlanned', 'countPassed', 'countBlocked', 'countFailed', 'countIssues', 'countTests','');
  public $runStatusName;
  public $countTotal;
  public $countPlanned;
  public $countPassed;
  public $countBlocked;
  public $countFailed;
  public $countIssues;
  public $runStatusIcon;
  public $noDisplay1;
  public $pctPlanned;
  public $pctPassed;
  public $pctBlocked;
  public $pctFailed;
  public $noDisplay3;
  public $idRunStatus;
  public $_col_1_2_predecessor;
  public $_Dependency_Predecessor=array();
  public $_col_2_2_successor;
  public $_Dependency_Successor=array();
  public $_col_1_1_TestCaseRun;
  public $_TestCaseRun=array();
  public $_col_1_1_Link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="8%" >${idProject}</th>
    <th field="nameProduct" width="8%" >${idProduct}</th>
    <th field="nameVersion" width="8%" >${idVersion}</th>
    <th field="nameTestSessionType" width="10%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameRunStatus" width="6%" formatter="colorNameFormatter">${testSummary}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" width="10%" >${responsible}</th>
    <th field="handled" width="5%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idTestSessionType"=>"required",
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idUser"=>"hidden",
                                  "countTotal"=>"display",
                                  "countPlanned"=>"display",
                                  "countPassed"=>"display",
                                  "countFailed"=>"display",
                                  "countBlocked"=>"display",
                                  "countIssues"=>"display",
                                  "noDisplay1"=>"calculated,hidden",
                                  "pctPlanned"=>"calculated,display,html",
                                  "pctPassed"=>"calculated,display,html",
                                  "pctBlocked"=>"calculated,display,html",
                                  "pctFailed"=>"calculated,display,html",
                                  "noDisplay3"=>"calculated,hidden",
                                  "idRunStatus"=>"display,html,hidden",
                                  "runStatusIcon"=>"calculated,display,html",
                                  "runStatusName"=>"calculated,display,html",
                                  "startDate"=>"hidden", 
                                  "endDate"=>"hidden",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                   'idVersion'=>'productVersion',
                                                   'idActivity'=>'parentActivity',
                                                   'idTestSession'=>'parentTestSession'
                                                   );
  
  private static $_databaseColumnName = array();
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    $this->getCalculatedItem();
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
    if ($colName=="idProject" ) {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
     if ($colName=="idActivity") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  if (trim(this.value)) dijit.byId("idTestSession").set("value",null); ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
     if ($colName=="idTestSession" ) {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  if (trim(this.value)) dijit.byId("idActivity").set("value",null); ';
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
    
    if (!trim($this->idProject) and !trim($this->idProduct)) {
      $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdProject') . " " . i18n('colOrProduct')));
    }
    if ($this->id and $this->id==$this->idTestSession) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->TestSessionPlanningElement and $this->TestSessionPlanningElement->id){
      $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'TestSession','refId'=>$this->idTestSession));
      $parentList=$parent->getParentItemsArray();
      if (array_key_exists('#' . $this->TestSessionPlanningElement->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    if (trim($this->idActivity)) {
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
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
  
  public function save() {

  	$old=$this->getOld();
  	if (! trim($this->idRunStatus)) $this->idRunStatus=5;
  	
  	$this->recalculateCheckboxes();
    $this->TestSessionPlanningElement->refName=$this->name;
    $this->TestSessionPlanningElement->idProject=$this->idProject;
    $this->TestSessionPlanningElement->idle=$this->idle;
    $this->TestSessionPlanningElement->done=$this->done;
    $this->TestSessionPlanningElement->cancelled=$this->cancelled;
    if ($this->idActivity and trim($this->idActivity)!='') {
      $this->TestSessionPlanningElement->topRefType='Activity';
      $this->TestSessionPlanningElement->topRefId=$this->idActivity;
      $this->TestSessionPlanningElement->topId=null;
    } else if ($this->idTestSession and trim($this->idTestSession)!=''){
    	$this->TestSessionPlanningElement->topRefType='TestSession';
      $this->TestSessionPlanningElement->topRefId=$this->idTestSession;
      $this->TestSessionPlanningElement->topId=null;
    } else  if ($this->idProject and trim($this->idProject)!=''){
      $this->TestSessionPlanningElement->topRefType='Project';
      $this->TestSessionPlanningElement->topRefId=$this->idProject;
      $this->TestSessionPlanningElement->topId=null;
    } else {
    	$this->TestSessionPlanningElement->topRefType=null;
      $this->TestSessionPlanningElement->topRefId=null;
      $this->TestSessionPlanningElement->topId=null;
    }
    if (trim($this->idProject)!=trim($old->idProject) or trim($this->idActivity)!=trim($old->idActivity)) {
      $this->TestSessionPlanningElement->wbs=null;
      $this->TestSessionPlanningElement->wbsSortable=null;
    }
  	$result=parent::save();
    return $result;
  }
  
  public function copy() {

    $newObj=parent::copy();
    $copyResult=$newObj->_copyResult;
    // Copy TestCaseRun for session
    $newId=$newObj->id;
    $crit=array('idTestSession'=>$this->id);
    $tcr=new TestCaseRun();
    $list=$tcr->getSqlElementsFromCriteria($crit);
    foreach ($list as $tcr) {
    	$new=new TestCaseRun();
    	$new->idTestSession=$newId;
    	$new->idTestCase=$tcr->idTestCase;
    	$new->idRunStatus='1';
    	$new->save();
    }  
    $new=new TestSession($newId);
    $new->_noHistory=true;
    $new->save();
    $new->updateDependencies();
    $new->_copyResult=$copyResult;
    unset($new->_noHistory);
    return $new;
  
  }
  
  
  public function updateDependencies() {
  	$this->_noHistory=true;
  	$this->countBlocked=0;
  	$this->countFailed=0;
  	$this->countIssues=0;
  	$this->countPassed=0;
  	$this->countPlanned=0;
  	$this->countTotal=0;
  	foreach($this->_TestCaseRun as $tcr) {
  		$this->countTotal+=1;
      if ($tcr->idRunStatus==1) {
        $this->countPlanned+=1;
      }
  		if ($tcr->idRunStatus==2) {
  			$this->countPassed+=1;
  		}
  	  if ($tcr->idRunStatus==3) {
        $this->countFailed+=1;
      }
  	  if ($tcr->idRunStatus==4) {
        $this->countBlocked+=1;
      }
  	}
  	foreach($this->_Link as $link) {
  		if ($link->ref2Type=='Ticket') {
  			$this->countIssues+=1;
  		}
  	}
  	if ($this->countFailed>0) {
      $this->idRunStatus=3; // failed
    } else if ($this->countBlocked>0) {
      $this->idRunStatus=4; // blocked
    } else if ($this->countPlanned>0) {
      $this->idRunStatus=1; // planned
    } else if ($this->countTotal==0) {
      $this->idRunStatus=5; // empty
    } else {
      $this->idRunStatus=2; // passed
    }  
  	$this->save();
  	
  }
  
   public function getCalculatedItem(){
   	 if ($this->countTotal!=0) {
       $this->pctPlanned='<i>('.htmlDisplayPct(round($this->countPlanned/$this->countTotal*100)).')</i>';
       $this->pctPassed='<i>('.htmlDisplayPct(round($this->countPassed/$this->countTotal*100)).')</i>';
       $this->pctFailed='<i>('.htmlDisplayPct(round($this->countFailed/$this->countTotal*100)).')</i>';
       $this->pctBlocked='<i>('.htmlDisplayPct(round($this->countBlocked/$this->countTotal*100)).')</i>';
     }
     if ($this->id) {
       $name=SqlList::getNameFromId('RunStatus', $this->idRunStatus,false);
       $this->runStatusName=i18n($name);
       $this->runStatusIcon='<img src="../view/css/images/icon'.ucfirst($name).'22.png" />';
     }
  }
  
  public function drawSpecificItem($item){
//scriptLog("Project($this->id)->drawSpecificItem($item)");   
    $result="";
    if ($item=='separator_progress') {
    	$result .='<div style="height:5px;">&nbsp;</div>';
      $result .='<div class="section" style="height:14px;">';
      $result .="&nbsp;".i18n('menuTestCase')."&nbsp;";
      $result .='</div>';
      return $result;
    }
  }  
}
?>