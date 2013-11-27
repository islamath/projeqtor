<?php 
/* ============================================================================
 * User is a resource that can connect to the application.
 */ 
class Contact extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idClient;
  public $_spe_image;
  //public $idRecipient;
  public $isResource;
  public $initials;
  public $isUser;
  public $idProfile;
  public $userName;
  
  public $email;
  public $phone;
  public $mobile;
  public $fax;
  public $idle;
  public $description;
  public $_col_2_2_Address;
  public $designation;
  public $street;
  public $complement;
  public $zip;
  public $city;
  public $state;
  public $country;  
  public $_sec_Affectations;
  public $_spe_affectations;
  public $password;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${name}</th>
    <th field="photo" formatter="thumb16" width="5%">${photo}</th>
    <th field="initials" width="5%">${initials}</th>  
    <th field="nameClient" width="15%">${client}</th>
    <th field="nameRecipient" width="10%">${recipient}</th> 
    <th field="nameProfile" width="10%" formatter="translateFormatter">${idProfile}</th>
    <th field="userName" width="15%">${userName}</th>
    <th field="isUser" width="5%" formatter="booleanFormatter">${isUser}</th>
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>    
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required", 
                                          "idProfile"=>"readonly",
                                          "isUser"=>"readonly",
                                          "isResource"=>"readonly",
                                          "password"=>"hidden" 
  );    
  
  private static $_databaseTableName = 'resource';

  private static $_databaseColumnName = array('name'=>'fullName',
                                              'userName'=>'name');

  private static $_databaseCriteria = array('isContact'=>'1');
  
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
        
    $crit=array("name"=>"menuUser");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }     
    if (securityCheckDisplayMenu($menu->id)) {
      self::$_fieldsAttributes["isUser"]="";
      self::$_fieldsAttributes["idProfile"]="";
    } 
    
    $crit=array("name"=>"menuResource");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }     
    if (securityCheckDisplayMenu($menu->id)) {
      self::$_fieldsAttributes["isResource"]="";
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
 
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
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
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="isUser") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    dijit.byId("userName").set("required", "true");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("userName").set("required", null);';
      //$colScript .= '    dijit.byId("userName").set("value", "");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;

  } 


  public function getWork($startDate, $withProjectRepartition=false) {
    $result=array();
    $startDay=str_replace('-','',$startDate);
    $where="day >= '" . $startDay . "'";
    $where.=" and idResource='" . Sql::fmtId($this->id) . "'"; 
    $pw=new PlannedWork();
    $pwList=$pw->getSqlElementsFromCriteria(null,false,$where);
    $listTopProjectsArray=array();
    foreach ($pwList as $work) {
      $date=$work->workDate;
      if (array_key_exists($date,$result)) {
        $val=$result[$date];
      } else {
        $val=0;
      }
      $val+=$work->work;
      $result[$date]=$val;
      if ($withProjectRepartition) {
        $projectKey='Project#'. $work->idProject;
        if (array_key_exists($projectKey,$listTopProjectsArray)) {
          $listTopProjects=$listTopProjectsArray[$projectKey];
        } else {
          $proj = new Project($work->idProject);
          $listTopProjects=$proj->getTopProjectList(true);
          $listTopProjectsArray[$projectKey]=$listTopProjects;
        }
      // store Data on a project level view
        foreach ($listTopProjects as $idProject) {
          $projectKey='Project#'. $idProject;
          $week=weekFormat($date);
          if (array_key_exists($projectKey,$result)) {
            if (array_key_exists($week,$result[$projectKey])) {
              $valProj=$result[$projectKey][$week];
            } else {
              $valProj=0;
            }
          } else {
            $result[$projectKey]=array();
            $result[$projectKey]['rate']=$this->getAffectationRate($idProject);
            $valProj=0;
          }
          $valProj+=$work->work; 
          $result[$projectKey][$week]=$valProj;
        }
      }
    }
    $w=new Work();
    $wList=$w->getSqlElementsFromCriteria(null,false,$where);
    foreach ($wList as $work) {
      $date=$work->workDate;
      if (array_key_exists($date,$result)) {
        $val=$result[$date];
      } else {
        $val=0;
      }
      $val+=$work->work;
      $result[$date]=$val;
// ProjectRepartition - start
      if ($withProjectRepartition) {
        $projectKey='Project#'. $work->idProject;
        if (array_key_exists($projectKey,$listTopProjectsArray)) {
          $listTopProjects=$listTopProjectsArray[$projectKey];
        } else {
          $proj = new Project($work->idProject);
          $listTopProjects=$proj->getTopProjectList(true);
          $listTopProjectsArray[$projectKey]=$listTopProjects;
        }
        // store Data on a project level view
        foreach ($listTopProjects as $idProject) {
          $projectKey='Project#' . $idProject;
          $week=weekFormat($date);
          if (array_key_exists($projectKey,$result)) {
            if (array_key_exists($week,$result[$projectKey])) {
              $valProj=$result[$projectKey][$week];
            } else {
              $valProj=0;
            }
          } else {
            $result[$projectKey]=array();
            $result[$projectKey]['rate']=$this->getAffectationRate($idProject);
            $valProj=0;
          }
          $valProj+=$work->work; 
          $result[$projectKey][$week]=$valProj;
        }
      }
// ProjectRepartition - end
    }
    return $result;
  }
  
  public function getAffectationRate($idProject) {
    $result="";
    $crit=array('idResource'=>$this->id, 'idProject'=>$idProject);
    $aff=SqlElement::getSingleSqlElementFromCriteria('Affectation',$crit);
    if ($aff->rate) {
      $result=$aff->rate;
    } else {
      $prj=new Project($idProject);
      if ($prj->idProject) {
        $result=$this->getAffectationRate($prj->idProject);
      } else {
        $result='100';
      }
    }
    return $result;
  }
  
 
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if ($this->isUser and (! $this->userName or $this->userName=="")) {
      $result.='<br/>' . i18n('messageMandatory',array(i18n('colUserName')));
    } 
    $old=$this->getOld();
    // if uncheck isResource must check resource for deletion
    if ($old->isResource and ! $this->isResource and $this->id) {
        $obj=new Resource($this->id);
        $resultDelete=$obj->deleteControl(true);
        if ($resultDelete and $resultDelete!='OK') {
          $result.=$resultDelete;
        }
    }
    // if uncheck isUser must check user for deletion
    if ($old->isUser and ! $this->isUser and $this->id) {
        $obj=new User($this->id);
        $resultDelete=$obj->deleteControl(true);
        if ($resultDelete and $resultDelete!='OK') {
          $result.=$resultDelete;
        }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }

  public function save() {
    if ($this->isUser and !$this->password and Parameter::getGlobalParameter('initializePassword')=="YES") {
      $paramDefaultPassword=Parameter::getGlobalParameter('paramDefaultPassword');
      $this->password=md5($paramDefaultPassword);
    }
  	$result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    Affectation::updateAffectations($this->id);
    return $result;
  }
  
  public function deleteControl($nested=false) {
    
    $result="";
    if ($this->isUser) {    
      $crit=array("name"=>"menuUser");
      $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
      if (! $menu) {
        return;
      }     
      if (! securityCheckDisplayMenu($menu->id)) {
        $result="<br/>" . i18n("msgCannotDeleteContact");
      }             
    }
    /*$rec = new Recipient();
    $crit = array("id"=>$this->idRecipient);
    $recList = $rec->getSqlElementsFromCriteria($crit,false);
    if (count($recList)!=0) {
    	//$result = "Suppression impossible : contact li&eacute; a un contractant";
    	$result="<br/>" . i18n("msgCannotDeleteContact");
    }*/
    if (! $nested) {
	  // if uncheck isResource must check resource for deletion
	    if ($this->isResource) {
	        $obj=new Resource($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.=$resultDelete;
	        }
	    }
	  // if uncheck isUser must check user for deletion
	    if ($this->isUser) {
	        $obj=new User($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.=$resultDelete;
	        }
	    }
    }
    if ($nested) {
      SqlElement::unsetRelationShip('Contact','Affectation');
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  public function drawContactsList($critArray) {
    $result="<table>";
    $conList=$this->getSqlElementsFromCriteria($critArray, false);
    foreach ($conList as $con) {
      $result.= '<tr><td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td><td>';
      $result.=htmlDrawLink($con);
      $result.='</td></tr>';
    }
    $result .="</table>";
    return $result; 
  }
  
  public function drawSpecificItem($item){
  	global $print, $outMode;
    $result="";
    if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idContact'=>(($this->id)?$this->id:'0'));
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'Project', false);   
      return $result;
    }
    if ($item=='image' and $this->id){
      $result="";
      $image=SqlElement::getSingleSqlElementFromCriteria('Attachement', array('refType'=>'Resource', 'refId'=>$this->id));
      $left=250;
      $top=88;
      if ($image->id and $image->isThumbable()) {
        if (!$print) {
          $result.='<tr style="height:20px;">';
          $result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
          $result.='<td>&nbsp;&nbsp;';
          $result.='<img src="css/images/smallButtonRemove.png" onClick="removeAttachement('.$image->id.');" title="'.i18n('removePhoto').'" class="smallButton"/>';         
        } else {
          if ($outMode=='pdf') {
            $left=450;
            $top=90;
          } else {
            $left=400;
            $top=64;
          }
        }
        $result.='<div style="position: absolute; top:'.$top.'px;left:'.$left.'px; width:60px;height:60px;border: 1px solid grey;"><img src="'. getImageThumb($image->getFullPathFileName(),60).'" '
           . ' title="'.$image->fileName.'" style="cursor:pointer"'
           . ' onClick="showImage(\'Attachement\',\''.$image->id.'\',\''.$image->fileName.'\');" /></div>';
        if (!$print) {
          $result.='</td></tr>';
        }
      } else {
        if ($image->id) {
          $image->delete();
        }
        if (!$print) {
          $result.='<tr style="height:20px;">';
          $result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
          $result.='<td>&nbsp;&nbsp;';
          $result.='<img src="css/images/smallButtonAdd.png" onClick="addAttachement(\'file\');" title="'.i18n('addPhoto').'" class="smallButton"/> ';
          $result.='<div style="position: absolute; top:'.$top.'px;left:'.$left.'px; width:60px;height:60px;border: 1px solid grey;color: grey;font-size:80%; text-align:center;cursor: pointer;" '
              .' onClick="addAttachement(\'file\');" title="'.i18n('addPhoto').'">'
              . i18n('addPhoto').'</div>';
          $result.='</td>';
          $result.='</tr>';
        }
      }
      return $result;
    }
  }
  
  public function getPhotoThumb($size) {
    $result="";
    $image=SqlElement::getSingleSqlElementFromCriteria('Attachement', array('refType'=>'Resource', 'refId'=>$this->id));
    if ($image->id and $image->isThumbable()) {
      $result.='<img src="'. getImageThumb($image->getFullPathFileName(),$size).'" '
             . ' title="'.$image->fileName.'" style="cursor:pointer"'
             . ' onClick="showImage(\'Attachement\',\''.$image->id.'\',\''.$image->fileName.'\');" />';
    } else {
      $result='<div style="width:'.$size.';height:'.$size.';border:1px solide grey;">&nbsp;</span>';
    }
    return $result;
  }
}
?>