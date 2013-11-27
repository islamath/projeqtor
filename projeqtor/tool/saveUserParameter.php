<?php
require_once "../tool/projeqtor.php";

Sql::beginTransaction();
 $crit=array();
 $user=$_SESSION['user'];
 $crit['idUser']=$user->id;
 $crit['idProject']=null;
 $crit['parameterCode']=$_REQUEST['parameter'];
 $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
 $obj->parameterValue=$_REQUEST['value'];
 $result=$obj->save();
 if (!array_key_exists('userParamatersArray',$_SESSION)) {
   $_SESSION['userParamatersArray']=array();
 }
 $_SESSION['userParamatersArray'][$_REQUEST['parameter']]=$_REQUEST['value'];
Sql::commitTransaction();
 ?>