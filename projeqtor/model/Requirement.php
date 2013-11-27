<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Requirement extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idProduct;
  //public $idVersion;
  public $idRequirementType;
  public $name;
  public $externalReference;
  public $creationDateTime;
  public $idUser;
  public $idContact;
  public $Origin;
  public $idUrgency;
  public $description;
  public $_col_2_2_treatment;
  public $idRequirement;
  public $idStatus;
  public $idResource;
  public $idCriticality;
  public $idFeasibility;
  public $idRiskLevel;
  public $plannedWork;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $idTargetVersion;
  public $result;
  //public $_sec_Lock;
  public $_spe_lockButton;
  public $locked;
  public $idLocker;
  public $lockedDate;
  public $_col_1_1_Progress;
  public $_tab_8_2 = array('testSummary','countLinked', 'countTotal', 'countPlanned', 'countPassed', 'countBlocked', 'countFailed', 'countIssues', 'countTests', '');
  public $runStatusName;
  public $countLinked;
  public $countTotal;
  public $countPlanned;
  public $countPassed;
  public $countBlocked;
  public $countFailed;
  public $countIssues; 
  public $runStatusIcon;
  public $noDisplay1;
  public $noDisplay2;
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
  public $_col_1_1_Link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="8%" >${idProject}</th>
    <th field="nameProduct" width="8%" >${idProduct}</th>
    <th field="nameRequirementType" width="8%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameRunStatus" width="6%" formatter="colorNameFormatter">${testSummary}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" width="10%" >${responsible}</th>
    <th field="nameTargetVersion" width="10%" >${idVersion}</th>
    <th field="handled" width="5%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idRequirementType"=>"required",
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idUser"=>"hidden",
                                  "countLinked"=>"display",
                                  "countTotal"=>"display",
                                  "countPlanned"=>"display",
                                  "countPassed"=>"display",
                                  "countFailed"=>"display",
                                  "countBlocked"=>"display",
                                  "countIssues"=>"display",
                                  "noDisplay1"=>"calculated,hidden",
                                  "noDisplay2"=>"calculated,hidden",
                                  "pctPlanned"=>"calculated,display,html",
                                  "pctPassed"=>"calculated,display,html",
                                  "pctBlocked"=>"calculated,display,html",
                                  "pctFailed"=>"calculated,display,html",
                                  "noDisplay3"=>"calculated,hidden",
                                  "noDisplay4"=>"calculated,hidden",
                                  "idRunStatus"=>"display,html,hidden",
                                  "runStatusIcon"=>"calculated,display,html",
                                  "runStatusName"=>"calculated,display,html",
                                  "locked"=>"readonly",
                                  "idLocker"=>"readonly",
                                  "lockedDate"=>"readonly",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                   'idTargetVersion'=>'targetVersion',
                                                   'idRiskLevel'=>'technicalRisk',
                                                   'plannedWork'=>'estimatedEffort',
                                                   'idContact' => 'requestor',
                                                   );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
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
    
    if ($this->id and $this->id==$this->idRequirement) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if (trim($this->idRequirement)){
      $parentList=array();
    	$parent=new Requirement($this->idRequirement);
    	while ($parent->idRequirement) {
    		$parentList[$parent->idRequirement]=$parent->idRequirement;
    		$parent=new Requirement($parent->idRequirement);
    	}
      if (array_key_exists($this->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    if (trim($this->idRequirement)) {
      $parentRequirement=new Requirement($this->idRequirement);
      if ( trim($this->idProduct)) {
        if (trim($parentRequirement->idProduct)!=trim($this->idProduct)) {
          $result.='<br/>' . i18n('msgParentRequirementInSameProjectProduct');
        }
      } else {
        if (trim($parentRequirement->idProject)!=trim($this->idProject)) {
          $result.='<br/>' . i18n('msgParentRequirementInSameProjectProduct');
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
  
  public function save() {

  	if (! trim($this->idRunStatus)) $this->idRunStatus=5;
  	$result=parent::save();
    return $result;
  }
  
  public function drawSpecificItem($item){
    global $print;
    $result="";
    if ($item=='lockButton' and !$print) {
      if ($this->locked) {
        $canUnlock=false;
        $user=$_SESSION['user'];
        if ($user->id==$this->idLocker) {
          $canUnlock=true;
        } else {
          $right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'requirement'));        
          if ($right) {
            $list=new ListYesNo($right->rightAccess);
            if ($list->code=='YES') {
              $canUnlock=true;
            }
          }  
        }
        if ($canUnlock) {
          $result .= '<tr><td></td><td>';
          $result .= '<button id="unlockRequirement" dojoType="dijit.form.Button" showlabel="true"'; 
          $result .= ' title="' . i18n('unlockRequirement') . '" >';
          $result .= '<span>' . i18n('unlockRequirement') . '</span>';
          $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
          $result .=  '  unlockRequirement();';
          $result .= '</script>';
          $result .= '</button>';
          $result .= '</td></tr>';
        }
      } else {
        $result .= '<tr><td></td><td>';
        $result .= '<button id="lockRequirement" dojoType="dijit.form.Button" showlabel="true"'; 
        $result .= ' title="' . i18n('lockRequirement') . '" >';
        $result .= '<span>' . i18n('lockRequirement') . '</span>';
        $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
        $result .=  '  lockRequirement();';
        $result .= '</script>';
        $result .= '</button>';
        $result .= '</td></tr>';
      }
      $result .= '<input type="hidden" id="idCurrentUser" name="idCurrentUser" value="' . $_SESSION['user']->id . '" />';
      return $result;
    }
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
  
  public function updateDependencies() {
  	$this->_noHistory=true;
  	$listCrit='idTestCase in (0';
  	$this->countLinked=0;
    foreach ($this->_Link as $link) {
      if ($link->ref2Type=='TestCase') {
        $listCrit.=','.Sql::fmtId($link->ref2Id);
        $this->countLinked+=1;
      }
      if ($link->ref2Type=='Ticket') {
        $this->countIssues+=1;
      }
    }
    $listCrit.=")";
    $tcr=new TestCaseRun();
    $listTcr=$tcr->getSqlElementsFromCriteria(null, false, $listCrit);
    $this->countBlocked=0;
    $this->countFailed=0;
    $this->countIssues=0;
    $this->countPassed=0;
    $this->countPlanned=0;
    $this->countTotal=0;
    foreach($listTcr as $tcr) {
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
}
?>