<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class DocumentDirectory extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idDocumentDirectory;
  public $name;
  public $location;
  public $idProject;
  public $idProduct;
  public $idDocumentType;
  //public $sortOrder=0;
  public $idle;
  public $_col_2_2;
  
  public $_noCopy;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="location" width="45%">${location}</th>
    <th field="name" width="15%">${name}</th>
    <th field="nameProject" width="15%">${idProject}</th>
    <th field="nameProduct" width="15%">${idProduct}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

   private static $_colCaptionTransposition = array('idDocumentDirectory' => 'parentDirectory',
                                                    'idDocumentType'=>'defaultType'
                                                    );
   
   private static $_fieldsAttributes=array("name"=>"required",
                                           "location"=>"readonly",
                                           "idDocumentDirectory"=>"noList");  
  
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
  
  public function control() {
  	$result="";
    $pattern = '/^[a-zA-Z0-9][a-zA-Z0-9\-\_\ ]*\Z/';
    if (! preg_match($pattern, $this->name) ) {
      $result.="<br/>" . i18n('invalidDirectoryName',null);
    }
    $crit="location='" . $this->location . "' and id<>'" . Sql::fmtId($this->id) . "'";
    $dirList=$this->getSqlElementsFromCriteria(null, false, $crit);
    if (count($dirList)>0) {
    	$result.="<br/>" . i18n('existingDirectoryName',null);
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
  
  public function delete () {
    $result=parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    //delete directory if empty
    $dir=$this->getLocation();
    if (file_exists($dir)) {
    	if  (($files = @scandir($dir)) && count($files) <= 2) {
    	  rmdir($dir);
    	}
    }
    return $result;  
  }
  
  public function save() {
  	//$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$paramPathSeparator="/"; // Save with Linux format (windows interprets it correctly)
  	$old=$this->getOld();
  	$this->location="";
  	if ($this->idDocumentDirectory) {
  		$dir=new DocumentDirectory($this->idDocumentDirectory);
  		$this->location=$dir->location;
  	}
  	$this->location.=$paramPathSeparator . $this->name;
  	$result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    if (! $old->id) {
      $this->createDirectory();
    } else {
    	$newLocation=$this->getLocation();
    	$oldLocation=$old->getLocation();
    	if (! file_exists($oldLocation)) {
    		 $this->createDirectory();
    	} else {
    		$dir=new DocumentDirectory($this->idDocumentDirectory);
    		$dir->createDirectory();
        rename($oldLocation,$newLocation);    	
    	}
    } 
  	return $result;
  }
  
  function createDirectory() {
  	$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$split=explode($paramPathSeparator,$this->getLocation());
  	$rep="";
  	foreach ($split as $sp) {
  	  $rep.= $sp . $paramPathSeparator;
  		if (! file_exists($rep)) {
  			mkdir($rep,0777,true);
  		}	
  	}
  	
  }
  
  public function getLocation() {
  	$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$root=Parameter::getGlobalParameter('documentRoot');
  	if (substr($root,-1,1)!=$paramPathSeparator) {
  		$root.=$paramPathSeparator;
  	}
  	return $root . $this->location ;
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
    if ($colName=="name"){
      $colScript .= '<script type="dojo/connect" event="onKeyPress" >';
      $colScript .= '  dijit.byId("location").set("value","...");';
      $colScript .= '  formChanged();';      
      $colScript .= '</script>';     
    } else if ($colName=="idDocumentDirectory") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dijit.byId("location").set("value","...");';
      $colScript .= '  formChanged();';      
      $colScript .= '</script>';      
    } 
    return $colScript;
  }
  
  
}
?>