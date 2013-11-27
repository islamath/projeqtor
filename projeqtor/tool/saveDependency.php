<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

// Get the link info
if (! array_key_exists('dependencyRefType',$_REQUEST)) {
  throwError('dependencyRefType parameter not found in REQUEST');
}
$dependencyRefType=$_REQUEST['dependencyRefType'];

if (! array_key_exists('dependencyRefId',$_REQUEST)) {
  throwError('dependencyRefId parameter not found in REQUEST');
}
$dependencyRefId=$_REQUEST['dependencyRefId'];

if (! array_key_exists('dependencyType',$_REQUEST)) {
  throwError('dependencyType parameter not found in REQUEST');
}
$dependencyType=$_REQUEST['dependencyType'];

if (! array_key_exists('dependencyRefTypeDep',$_REQUEST)) {
  throwError('dependencyRefTypeDep parameter not found in REQUEST');
}
//$dependencyRefTypeDep=SqlList::getNameFromId('Dependable', $_REQUEST['dependencyRefTypeDep']);
$dependencyRefTypeDepObj=New Dependable($_REQUEST['dependencyRefTypeDep']);
$dependencyRefTypeDep=$dependencyRefTypeDepObj->name;

if (! array_key_exists('dependencyRefIdDep',$_REQUEST)) {
  throwError('dependencyRefIdDep parameter not found in REQUEST');
}
$dependencyRefIdDep=$_REQUEST['dependencyRefIdDep'];

$dependencyDelay=0;
if (array_key_exists('dependencyDelay',$_REQUEST)) {
  $dependencyDelay=$_REQUEST['dependencyDelay'];
}
$dependencyId=null;
if (array_key_exists('dependencyId',$_REQUEST)) {
  $dependencyId=$_REQUEST['dependencyId'];
}

$arrayDependencyRefIdDep=array();
if (is_array($dependencyRefIdDep)) {
  $arrayDependencyRefIdDep=$dependencyRefIdDep;
} else {
  $arrayDependencyRefIdDep[]=$dependencyRefIdDep;
}
Sql::beginTransaction();
if ($dependencyId) { // Edit Mode
	$dep=new Dependency($dependencyId);
	$dep->dependencyDelay=$dependencyDelay;
	$result=$dep->save();
} else { // Add Mode
	$result="";
	
	foreach ($arrayDependencyRefIdDep as $dependencyRefIdDep) {
		if ($dependencyType=="Successor") {
		  $critPredecessor=array("refType"=>$dependencyRefType,"refId"=>$dependencyRefId);
		  $critSuccessor=array("refType"=>$dependencyRefTypeDep,"refId"=>$dependencyRefIdDep);
		} else if ($dependencyType=="Predecessor") {  
		  $critSuccessor=array("refType"=>$dependencyRefType,"refId"=>$dependencyRefId);
		  $critPredecessor=array("refType"=>$dependencyRefTypeDep,"refId"=>$dependencyRefIdDep);  
		} else {
		  throwError('unknown dependency type : \'' . $dependencyType . '\'');
		}
	  $successor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critSuccessor);
	  $predecessor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critPredecessor);;
		
		$dep=new Dependency($dependencyId);
		$dep->successorId=$successor->id;
		$dep->successorRefType=$successor->refType;
		$dep->successorRefId=$successor->refId;
		$dep->predecessorId=$predecessor->id;
		$dep->predecessorRefType=$predecessor->refType;
		$dep->predecessorRefId=$predecessor->refId;
		$dep->dependencyType='E-S';
		//$dep->dependencyDelay=0;
		$dep->dependencyDelay=$dependencyDelay;
	  $res=$dep->save();
	  if (!$result) {
	    $result=$res;
	  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
	    if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	      $deb=stripos($res,'#');
	      $fin=stripos($res,' ',$deb);
	      $resId=substr($res,$deb, $fin-$deb);
	      $deb=stripos($result,'#');
	      $fin=stripos($result,' ',$deb);
	      $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
	    } else {
	      $result=$res;
	    } 
	  }
	}
}
// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>