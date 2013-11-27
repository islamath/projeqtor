<?php
/* ============================================================================
 * Client is the owner of a project.
 */
class ColumnSelector extends SqlElement {

	// extends SqlElement, so has $id
	public $id;    // redefine $id to specify its visiblez place
	public $scope;
	public $objectClass;
	public $idUser;
	public $field;
	public $attribute;
	public $hidden;
	public $sortOrder;
	public $widthPct;
	public $name;
	public $formatter;
	public $subItem;
	public $_from;
	public $_displayName;

	private static $cachedLists=array();
	private static $allFields=true; // Keep it to false as long as addAllFields() is not fiabilized
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
	public static function getColumnsList($classObj) {
		// scope = "list"
		if (isset(self::$cachedLists['list#'.$classObj])) {
			return self::$cachedLists['list#'.$classObj];
		}
		// retrieve from database, in correct order
		$user=$_SESSION['user'];
		$obj=new $classObj();
		$cs=new ColumnSelector();
		$crit=array('scope'=>'list', 'objectClass'=>$classObj, 'idUser'=>$user->id);
		$csList=$cs->getSqlElementsFromCriteria($crit, false, null, 'sortOrder asc');
		$result=array();
		foreach ($csList as $cs) {
		  if (! SqlElement::isVisibleField($cs->attribute)) {
        continue;
      }
      $cs->_name=$cs->attribute;
      $dispObj=$obj;
      if ($cs->subItem) {
      	$fromObj='obj'.$cs->subItem;
      	if (! isset($$fromObj)) {
      		$$fromObj=new $cs->subItem();      		
      	}
      	$dispObj=$$fromObj;
      }
      $cs->_displayName=$dispObj->getColCaption($cs->_name);
		  if (substr($cs->attribute,0,9)=='idContext' and strlen($cs->attribute)==10) {
		  	$ctx=new ContextType(substr($cs->attribute,-1));
        $cs->_displayName=$ctx->name;
      }		
      $cs->_from=$cs->subItem;
			$result[$cs->attribute]=$cs;
		}

		// retrieve (complete) from layout
		$cpt=count($result);
		$layout=$obj->getStaticLayout();
		$dom = new DOMDocument();
		$dom->loadHTML($layout);
		$domx = new DOMXPath($dom);
		$entries = $domx->evaluate("//th");
		Sql::beginTransaction();
		foreach ($entries as $entry) {
			$field=$entry->getAttribute("field");
			$attribute=$field;
      if (substr($attribute,0,4)=="name" and $attribute!="name") {$attribute='id'.substr($attribute,4);}
      if (substr($attribute,0,9)=="colorName") {$attribute='id'.substr($attribute,9);}     
      if (substr($attribute,-8)=="Sortable") {$attribute=substr($attribute,0,strlen($attribute)-8);}  
			$cpt++;
			if (array_key_exists($attribute, $result)) {
				$cs=$result[$attribute];
			} else {
				$cs=new ColumnSelector();
				$cs->scope="list";
				$cs->objectClass=$classObj;
				$cs->idUser=$user->id;
				$cs->field=$field;
				$cs->attribute=$attribute;
				$cs->hidden=(strtolower($entry->getAttribute("hidden"))=="true")?1:0;
				$cs->sortOrder=$cpt;
				$cs->widthPct=str_replace('%','',$entry->getAttribute("width"));
			}
			$cs->name=str_replace(array('# ','${','}'), array('','',''), $entry->nodeValue);
			$cs->_displayName=i18n('col'.ucfirst($cs->name));
			$cs->formatter=$entry->getAttribute("formatter");
			$cs->_from=$entry->getAttribute("from");
			$cs->subItem=$cs->_from;
			if (!$cs->id) { $cs->save(); }
			$result[$attribute]=$cs;
		}
		if (self::$allFields) {
			$result=self::addAllFields($result,$obj);
		}
		if (! self::$allFields) {
			foreach ($result as $id=>$cs) {
				if (! $cs->name) {
					if ($cs->id) {
						$res=$cs->delete();
					}
					unset($result[$cs->attribute]);
				}
			}
	  }
		Sql::commitTransaction();
		self::$cachedLists['list#'.$classObj]=$result;
		return $result;
	}

	private static function addAllFields($result, $obj, $included=false, $sourceClass=null) {
		// TODO : fiabilize this function
		$fieldsArray=$obj->getFieldsArray();
		$user=$_SESSION['user'];
		$cpt=count($result);
		foreach($obj as $col => $val) {
		  if (array_key_exists($col,$result)) {
        continue;
      }
			if ( $included and ($col=='id' or $col=='refId' or $col=='refType' or $col=='refName') ) {
				continue;
			}
			if (substr($col,0,1)=='_') {
				continue;
			}
			if ($obj->isAttributeSetToField($col,'hidden') or $obj->isAttributeSetToField($col,'noList') 
			 or $obj->isAttributeSetToField($col,'calculated')) {
				continue;
			}
			if ($col=="password" or $col=="Origin") {
				continue;
			}
			if (! SqlElement::isVisibleField($col)) {
				continue;
			}
			if (is_object($val)) {
				$result=self::addAllFields($result, $val, true, get_class($obj));
				continue;
			}

			$dataType = $obj->getDataType($col);
			$dataLength = $obj->getDataLength($col);
			if ($dataLength>100 or $dataType=='text') {
				continue;
			}
			$cpt++;
			$cs=new ColumnSelector();
			$cs->scope="list";
			if ($included) {
				$cs->_from=get_class($obj);
				$cs->subItem=$cs->_from;
				$cs->objectClass=$sourceClass;
			} else {
				$cs->objectClass=get_class($obj);
			}
			$cs->idUser=$user->id;
			$cs->field=$col;
			if (substr($cs->field,0,2)=='id' and strlen($cs->field)>2 and substr($col,2,1)==strtoupper(substr($col,2,1)) ) {
				$cs->field='name'.substr($cs->field,2);
			}
			$cs->attribute=$col;
			$cs->sortOrder=$cpt;
			$cs->widthPct=5;
			$cs->name=$col;
			$cs->_displayName=$obj->getColCaption($col);
			if (substr($cs->attribute,0,9)=='idContext') {
        $ctx=new ContextType(substr($cs->attribute,-1));
        $cs->_displayName=$ctx->name;
      } 
			$cs->formatter='';
			$cs->hidden=1;
			if ($col=='id') {
				$cs->formatter="numericFormatter";
			} else if ($dataType=='date') {
				$cs->formatter="dateFormatter";
				$cs->widthPct=10;
			} else if ($dataType=='datetime') {
        $cs->formatter="dateTimeFormatter";
        $cs->widthPct=10;
      } else if ($dataType=='time') {
        $cs->formatter="timeFormatter";
        $cs->widthPct=10;
			} else if ($col=='color' and $dataLength == 7 ) {
				$cs->formatter="colorFormatter";
			} else if ($dataType=='int' and $dataLength==1) {
				$cs->formatter="booleanFormatter";
			} else if (substr($col,0,2)=='id' and $dataType=='int' and strlen($col)>2 and substr($col,2,1)==strtoupper(substr($col,2,1)) ) {
				$idClass=substr($col,2);
				if (class_exists($idClass)) {
					//$idObj=new $IdClass;
					if(property_exists($idClass, 'color')) {
						$cs->field='color'.ucfirst($cs->field);
	          $cs->formatter="colorNameFormatter";
	        } else if(property_exists($idClass, '_isNameTranslatable')) {
	          $cs->formatter="translateFormatter";
	        } else {
	          //$cs->formatter="";
	        }
	        $cs->widthPct=10;
				}
			} else if ($dataType=='int' or $dataType=='decimal') {
				if (strtolower(substr($col,-8))=='progress' or strpos($col,'Pct')!=false) {
					 $cs->formatter="percentFormatter";
				} else if (strtolower(substr($col,-4))=='work') {
					 $cs->formatter="workFormatter";
				} else if (strtolower(substr($col,-4))=='cost' or strtolower(substr($col,-6))=='amount') {
           $cs->formatter="costFormatter";	 
				} else {
				  $cs->formatter="numericFormatter";
				}
			}
			$res=$cs->save();
			$result[$cs->attribute]=$cs;
		}
		return $result;
	}
}
?>