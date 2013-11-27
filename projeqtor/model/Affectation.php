<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class Affectation extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idResourceSelect;
  public $idResource;
  public $idContact;
  public $idUser;
  public $idProject;
  public $rate;
  public $idle;
  public $description;
  public $_col_2_2;
  
public $_noCopy;

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameResourceSelect" width="20%" >${resourceName}</th>
    <th field="nameContact" width="20%" >${contactName}</th>
    <th field="nameUser" width="20%" >${userName}</th>
    <th field="nameProject" width="20%" >${projectName}</th>
    <th field="rate" width="10%" formatter="percentFormatter">${rate}</th>  
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array('idUser'=>'orUser', 
                                                   'idContact'=>'orContact',
                                                   'idResourceSelect'=>'idResource');
  
   private static $_fieldsAttributes=array("idResourceSelect"=>"hidden, forceExport", "idResource"=>"noExport,noList"); 
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    /*if ($this->id) {
    	if ($this->idResource) {
    		if (!$this->idContact) {
    			$this->idContact=$this->idResource;
    		}
    	  if (!$this->idUser) {
          $this->idUser=$this->idResource;
        }
    	}
    }*/
    if (SqlList::getNameFromId('Resource', $this->idResource)==$this->idResource) {
    	$this->idResource=null;
    }
    
    if (! $this->id) {
    	$this->rate=100;
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
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

     if ($colName=="idResource") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (testAllowedChange(this.value)) {';
      $colScript .= '  dijit.byId("idContact").set("value",this.value);';
      $colScript .= '  if (! dijit.byId("idContact").get("value")) { dijit.byId("idContact").set("value",null); }'; 
      $colScript .= '  dijit.byId("idUser").set("value",this.value);'; 
      $colScript .= '  if (! dijit.byId("idUser").get("value")) { dijit.byId("idUser").set("value",null); }'; 
      $colScript .= '  terminateChange();';
      $colScript .= '  formChanged();';
      $colScript .= '};';
      $colScript .= '</script>';
    }
    if ($colName=="idContact") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (testAllowedChange(this.value)) {';
      $colScript .= '  dijit.byId("idResource").set("value",this.value);';
      $colScript .= '  if (! dijit.byId("idResource").get("value")) { dijit.byId("idResource").set("value",null); }'; 
      $colScript .= '  dijit.byId("idUser").set("value",this.value);'; 
      $colScript .= '  if (! dijit.byId("idUser").get("value")) { dijit.byId("idUser").set("value",null); }'; 
      $colScript .= '  terminateChange();';
      $colScript .= '  formChanged();';
      $colScript .= '}';
      $colScript .= '</script>';
    }
    if ($colName=="idUser") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (testAllowedChange(this.value)) {';;
      $colScript .= '  dijit.byId("idContact").set("value",this.value);';
      $colScript .= '  if (! dijit.byId("idContact").get("value")) { dijit.byId("idContact").set("value",null); }'; 
      $colScript .= '  dijit.byId("idResource").set("value",this.value);'; 
      $colScript .= '  if (! dijit.byId("idResource").get("value")) { dijit.byId("idResource").set("value",null); }'; 
      $colScript .= '  terminateChange();';
      $colScript .= '  formChanged();';
      $colScript .= '}';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  public function drawAffectationList($critArray, $nameDisp) {
    $result="<table>";
    $affList=$this->getSqlElementsFromCriteria($critArray, false);
// TEST - New - Start
//drawAffectationsFromObject($affList, $$obj, $nameDisp, false);
//return;    
// TEST - New - Stop
    foreach ($affList as $aff) {
    	if ($nameDisp=='Resource' and ! $aff->idResource) continue;
    	if ($nameDisp=='Resource' and SqlList::getNameFromId('Resource', $aff->idResource)==$aff->idResource) continue;
    	if ($nameDisp=='Contact' and ! $aff->idContact) continue;
    	if ($nameDisp=='User' and ! $aff->idUser) continue; 
      $result.= '<tr>';
      $result.= '<td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td>';
      $result.= '<td>';
      $disp=''; 
      if ($nameDisp=='Resource') {
        $disp.=SqlList::getNameFromId('Resource', $aff->idResource);
      } else if ($nameDisp=='Contact') {
        $disp.=SqlList::getNameFromId('Contact', $aff->idContact);
      } else if ($nameDisp=='User') {
        $disp.=SqlList::getNameFromId('User', $aff->idUser);
      } else if ($nameDisp=='Project') {
        $disp.=SqlList::getNameFromId('Project', $aff->idProject);      
      } else{
        $disp.=SqlList::getNameFromId('Resource', $aff->idResource);
        $disp.=' - ';
        $disp.=SqlList::getNameFromId('Project', $aff->idProject);
      }
      if ($aff->rate ) {
        $disp.=' (' . $aff->rate . '%)';
      }
      $result.=htmlDrawLink($aff,$disp);
      $result.= '</td></tr>';
    }
    $result .="</table>";
    return $result; 
  }
  
  public function control(){
    $result="";
    $this->idResource=trim($this->idResource);
    $this->idResourceSelect=trim($this->idResourceSelect);
    $this->idContact=trim($this->idContact);
    $this->idUser=trim($this->idUser);
    $this->idProject=trim($this->idProject);
    if (!$this->idResource) {
      if ($this->idContact) {
      	$this->idResource=$this->idContact;
      } else if ($this->idResourceSelect) {
      	$this->idResource=$this->idResourceSelect;
      } else {
      	$this->idResource=$this->idUser;
      }
    }
    //echo " ress=".$this->idResourceSelect." cont=".$this->idContact." user=".$this->idUser;
    //echo " id=".$this->idResource;
    $affectable=new Affectable($this->idResource);
    if ($affectable->isResource) {
      $this->idResourceSelect=$this->idResource;
    } else {
      $this->idResourceSelect=null;
    }
    if ($affectable->isUser) {
      $this->idUser=$this->idResource;
    } else {
      $this->idUser=null;
    }
    if ($affectable->isContact) {
      $this->idContact=$this->idResource;
    } else {
      $this->idContact=null;
    }
    
    if (! $this->idResource) {
    	$result.='<br/>' . i18n('messageMandatory',array(i18n('colIdResource') 
    	                                         . ' ' . i18n('colOrContact') 
    	                                         . ' ' . i18n('colOrUser')));
    }
    if (! $this->idProject) {
    	$result.='<br/>' . i18n('messageMandatory',array(i18n('colIdProject')));
    }
    if ($result=='') {
      $clauseWhere=" idResource=".Sql::fmtId($this->idResource)
         ." and idProject=".Sql::fmtId($this->idProject)
         ." and id<>".Sql::fmtId($this->id);
      $search=$this->getSqlElementsFromCriteria(null, false, $clauseWhere);
      if (count($search)>0) { 
      	$result.='<br/>' . i18n('errorDuplicateAffectation');
      }
    } else {
    
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
  	$old=$this->getOld();
  	$result = parent::save();
    if (! $old->id or $this->idle!=$old->idle) {
      User::resetAllVisibleProjects(null,$this->idUser);
    }
    return $result;
  }
  
  public function delete() {
    $result = parent::delete();
    User::resetAllVisibleProjects(null,$this->idUser);
    return $result;
  }
  
  public static function updateAffectations($resource) {
  	$crit=array('idResource'=>$resource);
  	$aff=new Affectation();
  	$affList=$aff->getSqlElementsFromCriteria($crit, false);
  	foreach ($affList as $aff) {
  		$aff->save();
  	}
  }
  
  public static function updateIdle($idProject,$idResource) {
    $aff=new Affectation();
    $crit=array("idle"=>'0');
    if ($idProject) {$crit['idProject']=$idProject;}
    if ($idResource) {$crit['idResource']=$idResource;}
    $affList=$aff->getSqlElementsFromCriteria($crit, false);
    foreach ($affList as $aff) {
      $aff->idle=1;
      $aff->save();
    }
  }
  
}
?>