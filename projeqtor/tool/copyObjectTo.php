<?php
/** ===========================================================================
 * Copy an object as a new one (of the same class) : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

// Get the object from session(last status before change)
if (isset($_REQUEST['directAccessIndex'])) {
  if (! isset($_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']])) {
    throwError('currentObject parameter not found in SESSION');
  }
  $obj=$_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']];
} else {
  if (! array_key_exists('currentObject',$_SESSION)) {
    throwError('currentObject parameter not found in SESSION');
  }
  $obj=$_SESSION['currentObject'];
}
if (! is_object($obj)) {
  throwError('last saved object is not a real object');
}

// Get the object class from request
if (! array_key_exists('copyClass',$_REQUEST)) {
  throwError('copyClass parameter not found in REQUEST');
}
$className=$_REQUEST['copyClass'];

// compare expected class with object class
if ($className!=get_class($obj)) {
  throwError('last save object (' . get_class($obj) . ') is not of the expected class (' . $className . ').'); 
}
if (! array_key_exists('copyToClass',$_REQUEST)) {
  throwError('copyToClass parameter not found in REQUEST');
}
$toClassNameObj=new Copyable($_REQUEST['copyToClass']);
$toClassName=$toClassNameObj->name;
if (! array_key_exists('copyToName',$_REQUEST)) {
  throwError('copyToName parameter not found in REQUEST');
}
$toName=$_REQUEST['copyToName'];
$copyToOrigin=false;
if (array_key_exists('copyToOrigin',$_REQUEST)) {
  $copyToOrigin=true;
}
$copyToLinkOrigin=false;
if (array_key_exists('copyToLinkOrigin',$_REQUEST)) {
  $copyToLinkOrigin=true;
}
if (! array_key_exists('copyToType',$_REQUEST)) {
  throwError('copyToType parameter not found in REQUEST');
}
$toType=$_REQUEST['copyToType'];
$copyToWithNotes=false;
if (array_key_exists('copyToWithNotes',$_REQUEST)) {
  $copyToWithNotes=true;
}
$copyToWithAttachments=false;
if (array_key_exists('copyToWithAttachments',$_REQUEST)) {
  $copyToWithAttachments=true;
}
$copyToWithLinks=false;
if (array_key_exists('copyToWithLinks',$_REQUEST)) {
  $copyToWithLinks=true;
}
$copyWithStructure=false;
if (array_key_exists('copyWithStructure',$_REQUEST)) {
	if ($className=='Activity' && $toClassName=='Activity') {
    $copyWithStructure=true; 
	}
}

Sql::beginTransaction();
// copy from existing object
$newObj=$obj->copyTo($toClassName,$toType, $toName, $copyToOrigin, $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks);
// save the new object to session (modified status)
$result=$newObj->_copyResult;
unset($newObj->_copyResult);
$res="OK";
if ($copyWithStructure and get_class($obj)=='Activity' and get_class($newObj)=='Activity') {
	$res=copyStructure($obj, $newObj);
}
if ($copyToLinkOrigin) {
	$link=new Link();
  $link->ref1Id=$obj->id;
  $link->ref1Type=get_class($obj);
  $link->ref2Id=$newObj->id;
  $link->ref2Type=get_class($newObj);
  $link->comment=null;
  $user=$_SESSION['user'];
  $link->idUser=$user->id;
  $link->creationDate=date("Y-m-d H:i:s"); 
  $resLink=$link->save();
}


// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0) {
	if ($res=="OK") {
	  if (! array_key_exists('comboDetail', $_REQUEST)) {
      if (isset($_REQUEST['directAccessIndex'])) {
        $_SESSION['directAccessIndex'][$_REQUEST['directAccessIndex']]=$newObj;
      } else {
        $_SESSION['currentObject']=$newObj;
      }
    }
		Sql::commitTransaction();
	  echo '<span class="messageOK" >' . $result . '</span>';
  } else {
  	Sql::rollbackTransaction();
    echo '<span class="messageWARNING" >' . $res . '</span>';
  }
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}


function copyStructure($from, $to) {
	$nbErrors=0;
	$errorFullMessage="";
	$milArray=array();
  $milArrayObj=array();
  $actArray=array();
  $actArrayObj=array();
  $crit=array('idActivity'=>$from->id);
  $items=array();
  // Activities to be copied
  $activity=New Activity();
  $activities=$activity->getSqlElementsFromCriteria($crit, false, null, null, true);
  foreach ($activities as $activity) {
    $act=new Activity($activity->id);
    $items['Activity_'.$activity->id]=$act;
  }
  $mile=New Milestone();
  $miles=$mile->getSqlElementsFromCriteria($crit, false, null, null, true);
  foreach ($miles as $mile) {
    $mil=new Milestone($mile->id);
    $items['Milestone_'.$mile->id]=$mil;
  }
  // Sort by wbsSortable
  uasort($items, "customSortByWbsSortable");
  $itemArrayObj=array();
  $itemArray=array();
  foreach ($items as $id=>$item) {
    $new=$item->copy();
    $tmpRes=$new->_copyResult;
    if (! stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
      errorLog($tmpRes);
      $errorFullMessage.='<br/>'.i18n(get_class($item)).' #'.$item->id." : ".$tmpRes;
      $nbErrors++;
    } else {
      $itemArrayObj[get_class($new) . '_' . $new->id]=$new;
      $itemArray[$id]=get_class($new) . '_' . $new->id;
      if (get_class($item)=='Activity') {
        copyStructure($item, $new);
      }
    }
  }
  foreach ($itemArrayObj as $new) {
    //$new->idProject=$newProj->id;
    $new->idActivity=$to->id;
    $pe=get_class($new).'PlanningElement';
    $new->$pe->wbs=null;
    $tmpRes=$new->save();
    if (! stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
      errorLog($tmpRes);
      $errorFullMessage.='<br/>'.i18n(get_class($new)).' #'.$new->id." : ".$tmpRes;
      $nbErrors++;
    } 
  }
  // Copy dependencies 
  $critWhere="";
  foreach ($itemArray as $id=>$new) {
    $split=explode('_',$id);
    $critWhere.=($critWhere)?', ':'';
    $critWhere.="('" . $split[0] . "','" . Sql::fmtId($split[1]) . "')";
  }
  if ($critWhere) {
    $clauseWhere="(predecessorRefType,predecessorRefId) in (" . $critWhere . ")"
         . " or (successorRefType,successorRefId) in (" . $critWhere . ")";
  } else {
    $clauseWhere=" 1=0 ";
  }
  $dep=New dependency();
  $deps=$dep->getSqlElementsFromCriteria(null, false, $clauseWhere);
  foreach ($deps as $dep) {
    if (array_key_exists($dep->predecessorRefType . "_" . $dep->predecessorRefId, $itemArray) ) {
      $split=explode('_',$itemArray[$dep->predecessorRefType . "_" . $dep->predecessorRefId]);
      $dep->predecessorRefType=$split[0];
      $dep->predecessorRefId=$split[1];
      $crit=array('refType'=>$split[0], 'refId'=>$split[1]);
      $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
      $dep->predecessorId=$pe->id;
    }
    if (array_key_exists($dep->successorRefType . "_" . $dep->successorRefId, $itemArray) ) {
      $split=explode('_',$itemArray[$dep->successorRefType . "_" . $dep->successorRefId]);
      $dep->successorRefType=$split[0];
      $dep->successorRefId=$split[1];
      $crit=array('refType'=>$split[0], 'refId'=>$split[1]);
      $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
      $dep->successorId=$pe->id;
    }
    $dep->id=null;
    $tmpRes=$dep->save();
    if (! stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
      errorLog($tmpRes);
      $errorFullMessage.='<br/>'.i18n(get_class($dep)).' #'.$dep->id." : ".$tmpRes;
      $nbErrors++;
    } 
  }
  $result="OK";
  if ($nbErrors>0) {
    $result='<span class="messageERROR" >' 
           . i18n('errorMessageCopy',array($nbErrors))
           . '</span><br/>'
           . str_replace('<br/><br/>','<br/>',$errorFullMessage);
  }
  return $result;
}
function customSortByWbsSortable($a,$b) {
  $pe=get_class($a).'PlanningElement';
  $wbsA=$a->$pe->wbsSortable;
  $pe=get_class($b).'PlanningElement';
  $wbsB=$b->$pe->wbsSortable;
  return ($wbsA > $wbsB)?1:-1;
}
?>