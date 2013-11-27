<?php
/** ===========================================================================
 * Save the current object : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 * The old values are fetched in $currentObject of $_SESSION
 * Only changed values are saved. 
 * This way, 2 users updating the same object don't mess.
 */

require_once "../tool/projeqtor.php";

// Get the object class from request
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('objectClass parameter not found in REQUEST');
}
$className=$_REQUEST['objectClass'];
if (! array_key_exists('selection',$_REQUEST)) {
  throwError('selection parameter not found in REQUEST');
}
$selection=trim($_REQUEST['selection']);
$selectList=explode(';',$selection);

if (!$selection or count($selectList)==0) {
	 echo '<span class="messageERROR" >'.i18n('messageNoData',array(i18n($className))).'</span>';
	 exit;
}

$description="";
if (array_key_exists('description',$_REQUEST)) {
	$description=trim($_REQUEST['description']);
}
$idStatus="";
if (array_key_exists('idStatus',$_REQUEST)) {
  $idStatus=trim($_REQUEST['idStatus']);
}
$idResource="";
if (array_key_exists('idResource',$_REQUEST)) {
  $idResource=trim($_REQUEST['idResource']);
}
$result="";
if (array_key_exists('result',$_REQUEST)) {
  $result=trim($_REQUEST['result']);
}
$note="";
if (array_key_exists('note',$_REQUEST)) {
  $note=trim($_REQUEST['note']);
}
$idProject="";
if (array_key_exists('idProject',$_REQUEST)) {
  $idProject=trim($_REQUEST['idProject']);
}
$idTargetVersion="";
if (array_key_exists('idTargetVersion',$_REQUEST)) {
  $idTargetVersion=trim($_REQUEST['idTargetVersion']);
}
//var_dump($_REQUEST);
$cptOk=0;
$cptError=0;
$cptWarning=0;
echo "<table>";
foreach ($selectList as $id) {
	if (!trim($id)) { continue;}
	Sql::beginTransaction();
	echo '<tr>';
	echo '<td valign="top"><b>#'.$id.'&nbsp:&nbsp;</b></td>';
	$item=new $className($id);
	if (property_exists($item, 'locked') and $item->locked) {
		Sql::rollbackTransaction();
    $cptWarning++;
    echo '<td><span class="messageWARNING" >' . i18n($className) . " #" . $item->id . ' '.i18n('colLocked'). '</span></td>';
		continue;
	}
	if ($description and property_exists($item,'description')) {
		$item->description.=(($item->description)?"\n":"").$description;
	}
  if ($idStatus and property_exists($item,'idStatus')) {
  	//$oldStatus=new Status($item->idStatus);
    $item->idStatus=$idStatus;
    $item->recalculateCheckboxes(true);
  }
  if ($idResource and property_exists($item,'idResource')) {
    $item->idResource=$idResource;
  }
  if ($result and property_exists($item,'result')) {
    $item->result.=(($item->result)?"\n":"").$result;
  }
  if ($idProject and property_exists($item,'idProject')) {
    $item->idProject=$idProject;
  }
  if ($idTargetVersion and property_exists($item,'idTargetVersion')) {
    $item->idTargetVersion=$idTargetVersion;
  } 
  $resultSave=$item->save();
  if ($note and property_exists($item,'_Note')) {
    $noteObj=new Note();
    $noteObj->refType=$className;
    $noteObj->refId=$id;
    $noteObj->creationDate=date('Y-m-d H:i:s');
    $noteObj->note=$note;
    $noteObj->idPrivacy=1;
    $res=new Resource($_SESSION['user']->id);
    $noteObj->idTeam=$res->idTeam;
    $resultSaveNote=$noteObj->save();
    if (! stripos($resultSave,'id="lastOperationStatus" value="OK"')>0) {
    	$resultSave=$resultSaveNote;
    }   
  }
	$resultSave=str_replace('<br/><br/>','<br/>',$resultSave);
	if (stripos($resultSave,'id="lastOperationStatus" value="ERROR"')>0 ) {
	  Sql::rollbackTransaction();
	  $cptError++;
	  echo '<td><span class="messageERROR" >' . $resultSave . '</span></td>';
	} else if (stripos($resultSave,'id="lastOperationStatus" value="OK"')>0 ) {
	  Sql::commitTransaction();
	  $cptOk++;
	  echo '<td><span class="messageOK" >' . $resultSave . '</span></td>';
	} else { 
	  Sql::rollbackTransaction();
	  $cptWarning++;
	  echo '<td><span class="messageWARNING" >' . $resultSave . '</span></td>';
  }
  echo '</tr>';
}
echo "</table>";
$summary="";
if ($cptError) {
  $summary.='<div class=\'messageERROR\' >' . $cptError." ".i18n('resultError') . '</div>';
}
if ($cptOk) {
  $summary.='<div class=\'messageOK\' >' . $cptOk." ".i18n('resultOk') . '</div>';
}
if ($cptWarning) {
  $summary.='<div class=\'messageWARNING\' >' . $cptWarning." ".i18n('resultWarning') . '</div>';
}
echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />';
?>