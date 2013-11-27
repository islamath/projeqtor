<?php
/** ===========================================================================
 * Static class to retrieves data to build a list for reference needs
 * (to be able to build a select html list)  
 */
class SqlList {

  static private $list=array();


  /** ==========================================================================
   * Constructor : private to lock instanciantion (static class)
   */
  private function __construct() {
  }

  /** ==========================================================================
   * Public method to get the list : either retrieve it from a static array
   * or fetch if from database (and store it in the static array)
   * @param $listType the name of the table containing the data
   * *@param $displayCol the name of the value column (defaut is name)
   * @return an array containing the list of references
   */
  public static function cleanAllLists() {
  	self::$list=array();
  }
  
  public static function getList($listType, $displayCol='name', $selectedValue=null, $showIdle=false) {
    $listName=$listType . "_" . $displayCol;
    if ($showIdle) { $listName .= '_all'; }
    if (array_key_exists($listName, self::$list)) {
      return self::$list[$listName];
    } else {
      return self::fetchList($listType, $displayCol, $selectedValue, $showIdle, true);
    }
  }
  
  public static function getListNotTranslated($listType, $displayCol='name', $selectedValue=null, $showIdle=false) {
    $listName='no_tr_' . $listType . "_" . $displayCol;
    if ($showIdle) { $listName .= '_all'; }
    if (array_key_exists($listName, self::$list)) {
      return self::$list[$listName];
    } else {
      return self::fetchList($listType, $displayCol, $selectedValue, $showIdle, false);
    }
  }

   public static function getListWithCrit($listType, $crit, $displayCol='name', $selectedValue=null, $showIdle=false) {
//scriptLog("       =>getListWithCrit($listType, implode('|',$crit), $displayCol, $selectedValue)");
     return self::fetchListWithCrit($listType, $crit, $displayCol, $selectedValue,$showIdle);
   }
  /** ==========================================================================
   * Private method to get fetch the list from database and store it in a static array
   * for further needs
   * @param $listType the name of the table containing the data
   * @return an array containing the list of references
   */
  private static function fetchList($listType,$displayCol, $selectedValue, $showIdle=false, $translate=true) {
scriptLog("fetchList($listType,$displayCol, $selectedValue, $showIdle, $translate)");
    $res=array();
    $obj=new $listType();
    $calculated=false;
    $field=$obj->getDatabaseColumnName($displayCol);
    if (property_exists($obj, '_calculateForColumn') and isset($obj->_calculateForColumn[$displayCol])) {
    	$field=$obj->_calculateForColumn[$displayCol];
    	$calculated=true;
    }
    $query="select " . $obj->getDatabaseColumnName('id') . " as id, " . $field . " as name from " . $obj->getDatabaseTableName() ;
    if ($showIdle) {
      $query.= " where (1=1 ";
    } else {
      $query.= " where (idle=0 ";
    }
    $crit=$obj->getDatabaseCriteria();
    foreach ($crit as $col => $val) {
    	if ($obj->getDatabaseColumnName($col)=='idProject' and ($val=='*' or !$val)) {$val=0;}
      $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . '=' . Sql::str($val);
    }
    $query .=')';
    if ($selectedValue) {
    	if ($selectedValue!='*') {
        $query .= " or " . $obj->getDatabaseColumnName('id') .'= ' . Sql::str($selectedValue) ;
    	}
    }
    if (property_exists($obj,'sortOrder')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.sortOrder, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    } else if (property_exists($obj,'order')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.order, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    } else {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    }
    $result=Sql::query($query);
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        $name=$line['name'];
        if ($obj->isFieldTranslatable($displayCol) and $translate){
        	if ($listType=='Linkable' and substr($name,0,7)=='Context') {
        		$name=SqlList::getNameFromId('ContextType', substr($name,7,1));
        	} else {
            $name=i18n($name);
        	}
        }
        if ($displayCol=='name' and property_exists($obj,'_constructForName') and !$calculated) {
        	$nameObj=new $listType($line['id']);
        	$name=$nameObj->name;
        }
        $res[($line['id'])]=$name;
      }
    }
    if ($translate) {
      self::$list[$listType . "_" . $displayCol .(($showIdle)?'_all':'')]=$res;
    } else {
    	self::$list['no_tr_' . $listType . "_" . $displayCol .(($showIdle)?'_all':'')]=$res;
    } 
    return $res;
  }
 
  private static function fetchListWithCrit($listType,$criteria, $displayCol, $selectedValue, $showIdle) {
scriptLog("fetchListWithCrit(listType=$listType,criteria=".implode('|',$criteria).",displayCol=$displayCol, selectedValue=$selectedValue, showIdle=$showIdle)");

    $res=array();
    $obj=new $listType();
    $query="select " . $obj->getDatabaseColumnName('id') . " as id, " . $obj->getDatabaseColumnName($displayCol) . " as name from " . $obj->getDatabaseTableName() . " where (1=1 ";
    $query.=(! $showIdle)?' and idle=0 ':'';
    $crit=array_merge($obj->getDatabaseCriteria(),$criteria);
    foreach ($crit as $col => $val) {
      if ( (strtolower($listType)=='resource' or strtolower($listType)=='contact' or strtolower($listType)=='user') and $col=='idProject') {
        $aff=new Affectation();
        $user=new Resource();
        if ($val=='*' or ! $val) {$val=0;}
        $query .= " and exists (select 'x' from " . $aff->getDatabaseTableName() . " a where a.idProject=" . Sql::fmtId($val) . " and a.idResource=" . $user->getDatabaseTableName() . ".id)";
      } else if ((strtolower($listType)=='version' or strtolower($listType)=='originalversion' or strtolower($listType)=='targetversion') and $col=='idProject') {
      	$vp=new VersionProject();
        $ver=new Version();
        $proj=new Project($val);
        $lst=$proj->getTopProjectList(true);
        $inClause='(0';
        foreach ($lst as $prj) {
        	if ($prj) {
        	  $inClause.=',';
        	  $inClause.=$prj;
        	}
        }
        $inClause.=')';
        $query .= " and exists (select 'x' from " . $vp->getDatabaseTableName() . " vp where vp.idProject in " . $inClause . " and vp.idVersion=" . $ver->getDatabaseTableName() . ".id)";
      } else if (strtolower($listType)=='indicator' and $col=='idIndicatorable' ) {
      	$ii=new IndicatorableIndicator();
      	$i=new Indicator();
      	$query.=" and exists ( select 'x' from " . $ii->getDatabaseTableName() . " ii " 
      	      . " where ii.idIndicatorable='" . Sql::fmtId($val) . "' and ii.idIndicator=" . $i->getDatabaseTableName() . ".id)"; 
      } else if ( (strtolower($listType)=='warningdelayunit' or strtolower($listType)=='alertdelayunit') and $col=='idIndicator' ) {
        $ind=new Indicator($val);
        $query .= " and " . $obj->getDatabaseTableName() . '.type='. Sql::str($ind->type);
      } else {
        if ($val==null or $val=='') {
          $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . " is null";
        } else {
          if ($col=='idProject' and ($val=='*' or ! $val)) {$val=0;}
          $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . '=' . Sql::str($val);
        }
      }
    }
    $query .=')';
    if ($listType=='Report') {
      $hr=new HabilitationReport();
      $user=$_SESSION['user'];
      $lstIn="";
      $lst=$hr->getSqlElementsFromCriteria(array('idProfile'=>$user->idProfile, 'allowAccess'=>'1'), false);
      foreach ($lst as $h) {
        $lstIn.=(($lstIn=='')?'':', ') . $h->idReport;
      }
      $query .= ' and id in (' . $lstIn . ')' ;
    } 
    if ($selectedValue) {
      $query .= " or " . $obj->getDatabaseColumnName('id') . '='. $selectedValue;
    }
    if (property_exists($obj,'sortOrder')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.sortOrder';
    } else if (property_exists($obj,'order')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.order';
    } else{
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol); 
    }
    $result=Sql::query($query);
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        $name=$line['name'];
        if ($obj->isFieldTranslatable($displayCol)){
          $name=i18n($name);
        }
        if (property_exists($obj,'_constructForName') ) {
        	if ($listType=='TargetVersion') $listType='OriginalVersion';
          $nameObj=new $listType($line['id']);
          if ($nameObj->id) {
            $name=$nameObj->name;
          }
        }
        $res[($line['id'])]=$name;
      }
    }
    // In fetchListWithCrit, never store the list : results may always depend on criteria => must fetch every time.
    //self::$list[$listType . "_" . $displayCol]=$res;
    return $res;
  }
  
  public static function getNameFromId($listType, $id, $translate=true) {
    return self::getFieldFromId($listType, $id, 'name', $translate);
  }
  
  public static function getFieldFromId($listType, $id, $field, $translate=true) {
    if ($id==null or $id=='') {
      return '';
    }
    $name=$id;
    $list=self::getListNotTranslated($listType,$field, null, true);
    if (array_key_exists($id,$list)) {
      $name=$list[$id];
      $obj=new $listType();
      if ($translate and $obj->isFieldTranslatable('name')) {
      	$trans=i18n(strtolower($listType) . ucfirst($name));
      	if ($trans=='['.strtolower($listType) . ucfirst($name).']') {
      		$trans=i18n($name);
      	}
        $name=$trans;
      }
    }
    return $name;
  }
 
  public static function getIdFromName($listType, $name) {
    if ($name==null or $name=='') {
      return '';
    } 
    $list=self::getList($listType);      
    $id=array_search($name,$list);
    return $id;
  }
  
  public static function getIdFromTranslatableName($listType, $name) {
    return self::getIdFromName($listType, i18n($name));
  }
}

?>