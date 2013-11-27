<?php 
include_once '../tool/projeqtor.php';

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=1*trim($_REQUEST['idProject']);
}

$headerParameters="";
if ($idProject) {
  $headerParameters.= i18n("colId") . ' : ' . htmlEncode($idProject) . '<br/>';
	$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
} 

include "header.php";
  
$user=$_SESSION['user'];

$tab=array();

// gets projects
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$queryWhere=getAccesResctictionClause('Project',false);
  $queryWhere.=  "and cancelled=0 and id in " . getVisibleProjectsList(true, $idProject);
  
	$prj=new Project();
	$lstPrj=$prj->getSqlElementsFromCriteria(array(),false, $queryWhere, 'sortOrder');
	$item=null;
	$bMultiPrj=count($lstPrj)>1;
	$sqlPrj='in (0';
	
	foreach ($lstPrj as $item){
		$sqlPrj.= ', ' . $item->id;
		
		$tab[$item->id]=array('prj'=>array('id'=>'', 'name'=>'', 'manager'=>'', 'validated'=>0, 'real'=>0, 'left'=>0, 'assigned'=>0, 'planned'=>0),
				'bc'=>array(),
				'charge'=>array());
			
		$tab[$item->id]['prj']['id']=$item->id;
		$tab[$item->id]['prj']['name']=$item->name;
		$tab[$item->id]['prj']['manager']=SqlList::getNameFromId('Affectable', $item->idUser);
		$tab[$item->id]['prj']['color']=SqlList::getFieldFromId('Health', $item->idHealth, 'color');
		$pe=SqlElement::getSingleSqlElementFromCriteria('ProjectPlanningElement', array('refType'=>'Project', 'refId'=>$item->id));
		$tab[$item->id]['prj']['validated']=$pe->validatedWork;
		$tab[$item->id]['prj']['assigned']=$pe->assignedWork;
		$tab[$item->id]['prj']['real']=$pe->realWork;
		$tab[$item->id]['prj']['left']=$pe->leftWork;
		$tab[$item->id]['prj']['planned']=$pe->leftWork;
	
	}
	$sqlPrj.=')';
	
// gets orders of projects
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$queryWhere='cancelled=0 and idProject ' . $sqlPrj; // remove cancelled
 	$stt=new Status();
	$queryOrder='(SELECT sortOrder FROM ' . $stt->getDatabaseTableName() . ' WHERE id=idStatus) asc';
	$stt=null;
	$bc=new Command();
	$lstBC=$bc->getSqlElementsFromCriteria(array(), false, $queryWhere, $queryOrder);
	$item=null;
	
	foreach ($lstBC as $item){
		if (array_key_exists($item->idProject, $tab)) {
			
			$tab[$item->idProject]['bc'][]=array(
				'ref'=>$item->externalReference,
				'name'=>$item->name,
				'desc'=>$item->description,
				'idProject'=>$item->idProject,
				'work'=>$item->validatedWork,
				'tjm'=>$item->validatedPricePerDayAmount,
				'total'=>$item->validatedAmount,
				'setIdleStatus'=>SqlList::getFieldFromId('Status', $item->idStatus,'setIdleStatus'),
				'status'=>SqlList::getNameFromId('Status', $item->idStatus),
				'color'=>SqlList::getFieldFromId('Status', $item->idStatus,'color'));
	
			//$tab[$item->idProject]['prj']['validated']+=$item->validatedWork;
			//if ($item->idProject!=$idProject and $idProject) $tab[$idProject]['prj']['validated']+=$item->validatedWork;
		}
	}

// Get left by activity and resource
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  	$querySelect= 'select sum(realWork) as realWork, sum(leftWork) as leftWork, sum(assignedWork) as assignedWork, refId as idActivity, idProject';
  	$queryWhere=getAccesResctictionClause('Activity',false);
  	$queryWhere.=  " and reftype = 'Activity' and idProject " . $sqlPrj ;
  	$queryGroupBy = 'idActivity, idProject' ;
  	$queryHaving = ' realWork<>0 or leftWork<>0 or assignedWork<>0';
  
  	// constitute query and execute
  	$obj=new Assignment();
  	$query=$querySelect
  		. ' from ' . $obj->getDatabaseTableName()
  		. ' where ' . $queryWhere
  		. ' group by ' . $queryGroupBy 
  		. ' having ' . $queryHaving;
  	$result=Sql::query($query);
 
  	while ($line = Sql::fetchLine($result)) {
  	 
  		$line=array_change_key_case($line,CASE_LOWER);
  
  		$idAct=$line['idactivity'];
  		$idPrj=$line['idproject'];
	  	$realWork=round($line['realwork'],2);
  		$leftWork=round($line['leftwork'],2);
  		$assgWork=round($line['assignedwork'],2);
  	 
	  	$idActType=SqlList::getFieldFromId('Activity', $idAct, 'idActivityType');
	  	$nameActType=SqlList::getNameFromId('Type', $idActType);
  	 
	  	
	  	
  		//remplis le Tableau de valeur : Charge par phase et par Ressource 
  		if (! array_key_exists($idActType, $tab[$idPrj]['charge']) ) 
  			$tab[$idPrj]['charge'][$idActType]=array("real"=>0, "left"=>0, "assigned"=>0, "name"=>$nameActType);
	  		
  		
	  	$tab[$idPrj]['charge'][$idActType]['real']+=$realWork;
	  	$tab[$idPrj]['charge'][$idActType]['left']+=$leftWork;
	  	$tab[$idPrj]['charge'][$idActType]['assigned']+=$assgWork;
  	
	  	//$tab[$idPrj]['prj']['real']+=$realWork;
	  	//$tab[$idPrj]['prj']['left']+=$leftWork;
	  	//$tab[$idPrj]['prj']['assigned']+=$assgWork;
	  	
	  	//if ($idPrj!=$idProject and $idProject) {
		  //	$tab[$idProject]['prj']['real']+=$realWork;
		  //	$tab[$idProject]['prj']['left']+=$leftWork;
		  //	$tab[$idProject]['prj']['assigned']+=$assgWork;
	  	//}
  	}
  
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  
if (checkNoData($tab)) exit;

// Header
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';
 

	echo "<table width='95%' align='center'>";
	echo "<tr><td class='legend'>" . Work::displayWorkUnit() . "</td></tr>";
	echo "</table>";
	echo "<br/>";
	echo '<table width="95%" align="center">';
	echo '<tr>';
	echo '<td>';
	
	
	foreach ($tab as $tabPrj) {
		
		include 'subProjectDashboard.php';
		echo "<br/>";
	}
	
	echo '</td>';
	echo '</tr><tr><td colspan=3>';
	echo '<br/></td></tr>';
	echo '<tr><td width="45%" align="top">';
	

	echo '</td></tr>';
//////////////////////////////////////////////////////////////////////////
	echo '</table>';
	
?>
