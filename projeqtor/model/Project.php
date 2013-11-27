<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class Project extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idProjectType;
  public $codeType;
  public $idClient;
  public $idContact;
  //public $idRecipient;
  public $projectCode;
  public $contractCode;
  public $clientCode;
  public $idProject;
  public $idUser;
  public $color;
  //public $paymentDelay;
  public $idStatus;
  public $idHealth;
  public $idOverallProgress;
  public $fixPlanning;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $longitude;
  public $latitude;
  public $description;
  public $_col_2_2_Subprojects;
  public $_spe_subprojects;
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_sec_Versionproject_versions;
  public $_VersionProject=array();
  public $_col_1_1_Progress;
  public $ProjectPlanningElement; // is an object
  public $_col_1_2_predecessor;
  public $_Dependency_Predecessor=array();
  public $_col_2_2_successor;
  public $_Dependency_Successor=array();
  public $sortOrder;
  public $_col_1_1_Link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="wbsSortable" from="ProjectPlanningElement" formatter="sortableFormatter" width="5%" >${wbs}</th>
    <th field="name" width="15%" >${projectName}</th>
    <th field="nameProjectType" width="10%" >${type}</th>
    <th field="color" width="4%" formatter="colorFormatter">${color}</th>
    <th field="projectCode" width="6%" >${projectCode}</th>
    <th field="nameClient" width="8%" >${clientName}</th>
    <th field="colorNameStatus" width="8%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="colorNameHealth" width="8%" formatter="colorNameFormatter">${idHealth}</th>
    <th field="progress" from="ProjectPlanningElement" width="5%" formatter="percentFormatter">${progress}</th>
    <th field="validatedEndDate" from="ProjectPlanningElement" width="8%" formatter="dateFormatter">${validatedEnd}</th>
    <th field="plannedEndDate" from="ProjectPlanningElement" width="8%" formatter="dateFormatter">${plannedEnd}</th>  
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
// Removed in 1.2.0 
//     <th field="wbs" from="ProjectPlanningElement" width="5%" >${wbs}</th>
// Removed in 2.0.1
//  <th field="nameRecipient" width="10%" >${idRecipient}</th>
  

  private static $_fieldsAttributes=array("name"=>"required",                                   
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "sortOrder"=>"hidden",
                                  "codeType"=>"hidden",
                                  "idProjectType"=>"required",
                                  "longitude"=>"hidden", "latitude"=>"hidden",
                                  "idStatus"=>"required",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );   
 
  private static $_colCaptionTransposition = array('idUser'=>'manager',
   'idProject'=> 'isSubProject',
   'idProjectType'=>'type',
   'idContact'=>'billContact');

  private static $_subProjectList=array();
  private static $_subProjectFlatList=array();
  private static $_drawSubProjectsDone=array();
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    if ($id=='*') {$id='';}
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
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
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("idleDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '    if (! dijit.byId("done").get("checked")) {';
      $colScript .= '      dijit.byId("done").set("checked", true);';
      $colScript .= '    }';  
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="done") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("doneDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("doneDate").set("value", null); ';
      $colScript .= '    if (dijit.byId("idle").get("checked")) {';
      $colScript .= '      dijit.byId("idle").set("checked", false);';
      $colScript .= '    }'; 
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idProject") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("ProjectPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Retrieves the hierarchic sub-projects of the current project
   * @return an array of Projects as sub-projects
   */
  public function getSubProjects($limitToActiveProjects=false) {
//scriptLog("Project($this->id)->getSubProjects($limitToActiveProjects)");  	
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    if ($this->id=='*') {
      $crit['idProject']='';
    } else {
      $crit['idProject']=$this->id;
    }

    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    $sorted=SqlList::getListWithCrit('Project',$crit,'name');
    //$subProjects=$this->getSqlElementsFromCriteria($crit, false);
    //uasort($subProjects,'wbsProjectSort');
    $subProjects=array();
    foreach($sorted as $projId=>$projName) {
      $subProjects[$projId]=new Project($projId);
    }
    return $subProjects;
  }

  /** ==========================================================================
   * Retrieves the hierarchic sub-projects of the current project
   * @return an array of Projects as sub-projects
   */
  public function getSubProjectsList($limitToActiveProjects=false) {
//scriptLog("Project($this->id)->getSubProjectsList(limitToActiveProjects=$limitToActiveProjects)");
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    if ($this->id=='*') {
      $crit['idProject']='';
    } else {
      $crit['idProject']=$this->id;
    }
    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    $sorted=SqlList::getListWithCrit('Project',$crit,'name', null, ! $limitToActiveProjects);
    return $sorted;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the hierarchic sub-projects of the current project
   * @return an array containing id, name, subprojects (recursive array)
   */
  public function getRecursiveSubProjects($limitToActiveProjects=false) {
//scriptLog("Project($this->id)->getRecursiveSubProjects($limitToActiveProjects)");
    if (isset(self::$_subProjectList[$this->id])) {
    	//return self::$_subProjectList[$this->id];
    }    	
    $crit=array('idProject'=>$this->id);
    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    //$obj=new Project();
    $subProjects=$this->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subProjectList=null;
    foreach ($subProjects as $subProj) {
      $recursiveList=null;
      $recursiveList=$subProj->getRecursiveSubProjects($limitToActiveProjects);
      $arrayProj=array('id'=>$subProj->id, 'name'=>$subProj->name, 'subItems'=>$recursiveList);
      $subProjectList[]=$arrayProj;
    }
    self::$_subProjectList[$this->id]=$subProjectList;
    return $subProjectList;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the sub-projects of the current project
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of subprojects as id=>name
   * 
   */
  public function getRecursiveSubProjectsFlatList($limitToActiveProjects=false, $includeSelf=false) {
//scriptLog("Project($this->id)->getRecursiveSubProjectsFlatList($limitToActiveProjects,$includeSelf)");   	
    if (isset(self::$_subProjectFlatList[$this->id])) {
      //return self::$_subProjectFlatList[$this->id];
    }
    $tab=$this->getRecursiveSubProjects($limitToActiveProjects);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=$this->name;
    }
    if ($tab) {
      foreach($tab as $subTab) {
        $id=$subTab['id'];
        $name=$subTab['name'];
        $list[$id]=$name;
        $subobj=new Project();
        $subobj->id=$id;
        $sublist=$subobj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    self::$_subProjectFlatList[$this->id]=$list;
    return $list;
  }

  
  public function getTopProjectList($includeSelf=false) {
//scriptLog("Project($this->id)->getTopProjectList($includeSelf)");    	
    if ($includeSelf) {
      return array_merge(array($this->id),$this->getTopProjectList(false));
    }
    if (! $this->idProject) {
      return array();
    } else {
      $topProj=new Project($this->idProject);
      $topList=$topProj->getTopProjectList();
      $result=array_merge(array($this->idProject),$topList);
      return $result;
    }
  }
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
//scriptLog("Project($this->id)->drawSpecificItem($item)");  	
    $result="";
    if ($item=='subprojects') {
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('subProjects') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $result .= $this->drawSubProjects();
      }
      $result .="</td></tr></table>";
      return $result;
    /*} else if ($item=='affectations') {
      $aff=new Affectation();
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('resources') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $result .= $aff->drawAffectationList(array('idProject'=>$this->id,'idle'=>'0'),'Resource');
      }
      $result .="</td></tr></table>";
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('contacts') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $result .= $aff->drawAffectationList(array('idProject'=>$this->id,'idle'=>'0'),'Contact');
      }
      $result .="</td></tr></table>";
      return $result;*/
    } else if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idProject'=>(($this->id)?$this->id:'0'));
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'Resource', false);  
      drawAffectationsFromObject($affList, $this, 'Contact', false); 
      return $result;
    }
  }
  

  /** =========================================================================
   * Specific function to draw a recursive tree for subprojects
   * @return string the html table for the given level of subprojects
   *  must be redefined in the inherited class
   */  
  public function drawSubProjects($selectField=null, $recursiveCall=false, $limitToUserProjects=false, $limitToActiveProjects=false) {
scriptLog("Project($this->id)->drawSubProjects(selectField=$selectField, recursiveCall=$recursiveCall, limitToUserProjects=$limitToUserProjects, limitToActiveProjects=$limitToActiveProjects)");
  	self::$_drawSubProjectsDone[$this->id]=$this->name;
    if ($limitToUserProjects) {
      $user=$_SESSION['user'];
      if (! $user->_accessControlVisibility) {
        $user->getAccessControlRights(); // Force setup of accessControlVisibility
      }
      if ($user->_accessControlVisibility != 'ALL') {      
        $visibleProjectsList=$user->getHierarchicalViewOfVisibleProjects($limitToActiveProjects);
      } else {
      	$visibleProjectsList=array();
      }
      $reachableProjectsList=$user->getVisibleProjects($limitToActiveProjects);
    } else {  
      $visibleProjectsList=array();
      $reachableProjectsList=array();
    }  
    $result="";
    $clickEvent=' onClick=""';
    if ($limitToUserProjects and $user->_accessControlVisibility != 'ALL' and ! $recursiveCall) {
    	$subList=array();
    	foreach($visibleProjectsList as $idP=>$nameP) {
    		$split=explode('#',$nameP);
    		if (strpos($split[0],'.')==0) {
    			$subList[substr($idP,1)]=$split[1];
    		}
    	}
    } else {
  	  $subList=$this->getSubProjectsList($limitToActiveProjects);
    }
    if ($selectField!=null and ! $recursiveCall) { 
      $result .= '<table ><tr><td>';
      $clickEvent=' onClick=\'setSelectedProject("*", "<i>' . i18n('allProjects') . '</i>", "' . $selectField . '");\' ';
      $result .= '<div ' . $clickEvent . ' class="menuTree" style="width:100%;">';
      $result .= '<i>' . i18n('allProjects') . '</i>';
      $result .= '</div></td></tr></table>';
    }
    $result .='<table style="width: 100%;" >';
    if (count($subList)>0) {
      foreach ($subList as $idPrj=>$namePrj) {
        $showLine=true;
        $reachLine=true;
        if (array_key_exists($idPrj,self::$_drawSubProjectsDone)) {
        	$showLine=false;
        }
        if ($limitToUserProjects) {
          if ($user->_accessControlVisibility != 'ALL') {
            if (! array_key_exists('#' . $idPrj,$visibleProjectsList)) {
              $showLine=false;
            }
            if (! array_key_exists($idPrj,$reachableProjectsList)) {
              $reachLine=false;
            }
          }  
        }
        if ($showLine) {
        	$prj=new Project($idPrj);
          $result .='<tr><td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td>';
          if ($selectField==null) {
            $result .= '<td class="display"  NOWRAP>' . htmlDrawLink($prj);
          } else if (! $reachLine) {
            $result .= '<td style="#AAAAAA;" NOWRAP><div class="display" style="width: 100%;">' . $prj->name . '</div>';
          } else {
            $clickEvent=' onClick=\'setSelectedProject("' . $prj->id . '", "' . htmlEncode($prj->name) . '", "' . $selectField . '");\' ';
            $result .= '<td><div ' . $clickEvent . ' class="menuTree" style="width:100%;">';
            $result .= htmlEncode($prj->name);
            $result .= '</div>';
          }
          $result .= $prj->drawSubProjects($selectField,true,$limitToUserProjects,$limitToActiveProjects);
          $result .= '</td></tr>';
        }
      }
    }
    $result .='</table>';
    return $result;
  }

  public function drawProjectsList($critArray) {
//scriptLog("Project($this->id)->drawProjectsList(implode('|',$critArray))");  	
    $result="<table>";
    $prjList=$this->getSqlElementsFromCriteria($critArray, false);
    foreach ($prjList as $prj) {
      $result.= '<tr><td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td><td>';
      $result.=htmlDrawLink($prj);
      $result.= '</td></tr>';
    }
    $result .="</table>";
    return $result; 
  }
  
   /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {	
    // #305 : need to recalculate before dispatching to PE
    $old=$this->getOld();
    $this->recalculateCheckboxes();
    //$old=$this->getOld();
    //$oldtype=new ProjectType($old->idProjectType);
    $type=new ProjectType($this->idProjectType);
    
    $noMoreAdministrative=false;
    if ($this->codeType=='ADM' and $type->code!='ADM') {
    	$noMoreAdministrative=true;
    }
    $this->codeType=$type->code;
    
    $this->ProjectPlanningElement->refName=$this->name;
    $this->ProjectPlanningElement->idProject=$this->id;
    $this->ProjectPlanningElement->idle=$this->idle;
    $this->ProjectPlanningElement->done=$this->done;
    $this->ProjectPlanningElement->cancelled=$this->cancelled;
    if ($this->idProject and trim($this->idProject)!='') {
      $this->ProjectPlanningElement->topRefType='Project';
      $this->ProjectPlanningElement->topRefId=$this->idProject;
      $this->ProjectPlanningElement->topId=null;
    } else {
      $this->ProjectPlanningElement->topId=null;
      $this->ProjectPlanningElement->topRefType=null;
      $this->ProjectPlanningElement->topRefId=null;
    }
    if (trim($this->idProject)!=trim($old->idProject)) {    	
      $this->ProjectPlanningElement->wbs=null;
      $this->ProjectPlanningElement->wbsSortable=null;
    }
    // Initialize user->_visibleProjects, to force recalculate
    $result = parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    } 
    if (! $old->id or $this->idle!=$old->idle) {
      User::resetAllVisibleProjects($this->id, null);
    }
    if ($this->idle) {
      $crit=array('idProject'=>$this->id, 'idle'=>'0');
      $vp=new VersionProject();
      $vpLst=$vp->getSqlElementsFromCriteria($crit, false);
      foreach ($vpLst as $vp) {
        $vp->idle=$this->idle;
        $vp->save();
      }
    }
    // Create affectation for Manager.
    if ($this->idUser) {
      if (securityGetAccessRight('menuProject', 'update', $this)=="PRO"){
        $id=($this->id)?$this->id:Sql::$lastQueryNewid;
        $crit=array('idProject'=>$id, 'idResource'=>$this->idUser);
        $aff=SqlElement::getSingleSqlElementFromCriteria('Affectation', $crit);
        if ( ! $aff or ! $aff->id) {
        	$aff=new Affectation();
        	$aff->idResource=$this->idUser;
        	$aff->idProject=$id;
        	$aff->save();
        } else if (! $this->idle and $aff->idle) {
        	$aff->idle=0;
        	$resAff=$aff->save();
        }
      }
    }

    if ($this->idle) {
      Affectation::updateIdle($this->id, null);
    }
    if ($noMoreAdministrative) {
    	 $ass=new Assignment();
    	 $lstAss=$ass->getSqlElementsFromCriteria(array('idProject'=>$this->id));
    	 foreach ($lstAss as $ass) {
    	 	 if ($ass->realWork==0 and $ass->leftWork==0) {
    	 	 	 $ass->delete();
    	 	 }
    	 }
    }

    return $result; 

  }
  public function delete() {
    User::resetAllVisibleProjects($this->id,null);
  	$result = parent::delete();
    return $result;
  }
  
  // Ticket #1175
  public function updateValidatedWork() {
  	if (! $this->id) return;
	  $lst=null;
  	$sumValidatedWork=0;
  	$sumValidatedCost=0;
  	$order=new Command();
  	$lst=$order->getSqlElementsFromCriteria(array('idProject'=>$this->id, 'cancelled'=>'0'));
  	foreach ($lst as $item) { 		
  		$sumValidatedWork+=$item->validatedWork;
  		$sumValidatedCost+=$item->validatedAmount;
  	}
  	
  	$lst=null;
  	$prj=new Project();
  	$queryWhere='refType="Project" and refId in (SELECT id FROM ' . $prj->getDatabaseTableName() . ' WHERE idProject=' . $this->id . ' and cancelled=0)';
  	
  	$prj=new ProjectPlanningElement();
  	$lst=$prj->getSqlElementsFromCriteria(array(), false, $queryWhere);
  	foreach ($lst as $item) {
  		$sumValidatedWork+=$item->validatedWork;
  		$sumValidatedCost+=$item->validatedCost;
  	}

  	$this->ProjectPlanningElement->validatedWork=$sumValidatedWork;
  	$this->ProjectPlanningElement->validatedCost=$sumValidatedCost;
  	$this->save();
  	
  	if (trim($this->idProject)!='') {
  		$prj=new Project($this->idProject);
  		$prj->updateValidatedWork();
  	}
  }
  // Ticket END
  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if ($this->id and $this->id==$this->idProject) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->ProjectPlanningElement and $this->ProjectPlanningElement->id){
      $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'Project','refId'=>$this->idProject));
      $parentList=$parent->getParentItemsArray();
      if (array_key_exists('#' . $this->ProjectPlanningElement->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    
    if ($this->longitude!=null and $this->latitude!=null) {
      if ($this->longitude > 180 || $this->longitude < -180 || $this->latitude < -90 || $this->latitude > 90) {
        $result = i18n('invalidGpsData');
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
  
  public static function getAdminitrativeProjectList() {
  	$arrayProj=array();
  	$arrayProj[]=0;
  	$type=new ProjectType();
  	$critType=array('code'=>'ADM');
  	$listType=$type->getSqlElementsFromCriteria($critType, false);
  	foreach ($listType as $type) {
  	  $proj=new Project(); 
  		$critProj=array('idProjectType'=>$type->id);
      $listProj=$proj->getSqlElementsFromCriteria($critProj, false);
      foreach ($listProj as $proj) {
      	$arrayProj[]=$proj->id;
      }
  	}
  	return '(' . implode(', ',$arrayProj) . ')';
  }

  public static function getFixedProjectList() {
    $arrayProj=array();
    $arrayProj[]=0;
    $proj=new Project(); 
    $critProj=array('fixPlanning'=>'1', 'idle'=>'0');
    $listProj=$proj->getSqlElementsFromCriteria($critProj, false);
    foreach ($listProj as $proj) {
      $arrayProj[]=$proj->id;
      $sublist=$proj->getRecursiveSubProjectsFlatList(true);
      if ($sublist and count($sublist)>0) {
        foreach($sublist as $subId=>$subName) {
          $arrayProj[]=$subId;
        }
      }
    }
    return '(' . implode(', ',$arrayProj) . ')';
  }
  
  public function getColor() {
    $color=null;
    if ($this->color) {
      $color=$this->color;
    } else if ($this->idProject) {
      $top=new Project($this->idProject);
      $color=$top->getColor();
    }
    return $color;
  }

  
}
?>