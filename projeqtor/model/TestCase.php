<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class TestCase extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idProduct;
  public $idVersion;
  public $idTestCaseType;
  public $name;
  public $externalReference;
  public $creationDateTime;
  public $idContext1;
  public $idContext2;
  public $idContext3;
  public $idUser;
  public $description;
  public $_col_2_2_treatment;
  public $idTestCase;
  public $idStatus;
  public $idResource;
  public $idPriority;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $prerequisite;
  public $result;
  public $_calc_runStatus;
  public $idRunStatus;
  public $_col_1_1_TestCaseRun;
  public $_TestCaseRun=array();
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
    <th field="nameVersion" width="8%" >${idVersion}</th>
    <th field="nameTestCaseType" width="10%" >${type}</th>
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
                                  "idTestCaseType"=>"required",
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idUser"=>"hidden",
                                  "idContext1"=>"nobr,size1/3,title",
                                  "idContext2"=>"nobr,title", 
                                  "idContext3"=>"title",
                                  "idRunStatus"=>"display,html,hidden",
                                  "runStatusIcon"=>"calculated,display,html",
                                  "runStatusName"=>"calculated,display",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                   'result'=>'expectedResult',
                                                   'idVersion'=>'productVersion',
                                                   'runStatusName'=>'testSummary',
                                                   'runStatusIcon'=>'testSummary',
                                                   );
  
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
    
    if ($this->id and $this->id==$this->idTestCase) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if (trim($this->idTestCase)){
      $parentList=array();
      $parent=new TestCase($this->idTestCase);
      while ($parent->idTestCase) {
        $parentList[$parent->idTestCase]=$parent->idTestCase;
        $parent=new TestCase($parent->idTestCase);
      }
      if (array_key_exists($this->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    if (trim($this->idTestCase)) {
      $parent=new TestCase($this->idTestCase);
      if ( trim($this->idProduct)) {
        if (trim($parent->idProduct)!=trim($this->idProduct)) {
      	  $result.='<br/>' . i18n('msgParentTestCaseInSameProjectProduct');
        }
      } else {
      	if (trim($parent->idProject)!=trim($this->idProject)) {
          $result.='<br/>' . i18n('msgParentTestCaseInSameProjectProduct');
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
  	if (! $this->prerequisite and $this->idTestCase) {
  		$parent=new TestCase($this->idTestCase);
  		$this->prerequisite=$parent->prerequisite;
  	}
  	$result=parent::save();
    return $result;
  }
  
  public function getCalculatedItem(){
     if ($this->id) {
       $name=SqlList::getNameFromId('RunStatus', $this->idRunStatus,false);
       $this->runStatusName=i18n($name);
       $this->runStatusIcon='<img src="../view/css/images/icon'.ucfirst($name).'22.png" />';
     }
  }
  
  public function drawCalculatedItem($item){
    $result="&nbsp;";
    if ($item=='runStatus') {
    	 $name=SqlList::getNameFromId('RunStatus', $this->idRunStatus,false);
    	 $result='<tr>';
    	 $result.='<td class="label" style="display:table-cell; vertical-align:middle">' . i18n('colTestSummary') . '&nbsp;:&nbsp;</td>';
    	 $result.='<td>';
    	 if ($this->idRunStatus) {
	    	 $result.='<table><tr>';
	    	 $result.='<td style="width:5px;">&nbsp;</td>';
	    	 $result.='<td><img src="../view/css/images/icon'.ucfirst($name).'22.png" /></td>';
	    	 $result.='<td style="width:5px;">&nbsp;</td>';
	    	 $result.='<td style="vertical-align:top">'.(i18n($name)).'</td>';
	    	 $result.='</tr></table>';
    	 }
    	 $result.='</td>';
    	 $result.='</tr>';
    	 return $result;
       
     } else {
      return "&nbsp;"; 
     }
     return $result;
   }
   
  public function updateDependencies() {
    $this->_noHistory=true;
    $tcr=new TestCaseRun();
    $listTcr=$tcr->getSqlElementsFromCriteria(array('idTestCase'=>$this->id), false);
    $countBlocked=0;
    $countFailed=0;
    $countIssues=0;
    $countPassed=0;
    $countPlanned=0;
    $countTotal=0;
    foreach($listTcr as $tcr) {
      $countTotal+=1;
      if ($tcr->idRunStatus==1) {
        $countPlanned+=1;
      }
      if ($tcr->idRunStatus==2) {
        $countPassed+=1;
      }
      if ($tcr->idRunStatus==3) {
        $countFailed+=1;
      }
      if ($tcr->idRunStatus==4) {
        $countBlocked+=1;
      }
    }
    if ($countFailed>0) {
      $this->idRunStatus=3; // failed
    } else if ($countBlocked>0) {
      $this->idRunStatus=4; // blocked
    } else if ($countPlanned>0) {
      $this->idRunStatus=1; // planned
    } else if ($countTotal==0) {
      $this->idRunStatus=5; // empty
    } else {
      $this->idRunStatus=2; // passed
    }  
    $this->save();
  }
  
}
?>