<?php
$maintenance=true;
Sql::$maintenanceMode=true;
// Version History : starts at 0.3.0 with clean database (before scripts are empty)
$versionHistory = array(
  "V0.3.0",
  "V0.4.0",
  "V0.5.0",
  "V0.6.0",
  "V0.7.0",
  "V0.8.0",
  "V0.9.0",
  "V1.0.0",
  "V1.1.0",
  "V1.2.0",
  "V1.3.0",
  "V1.4.0",
  "V1.5.0",
  "V1.6.0",
  "V1.7.0",
  "V1.8.0",
  "V1.9.0",
  "V2.0.0",
  "V2.0.1",
  "V2.1.0",
  "V2.1.1",
  "V2.2.0",
  "V2.3.0",
  "V2.4.0",
  "V2.4.1",
  "V2.4.2",
  "V2.5.0",
  "V2.6.0",
  "V3.0.0",
  "V3.0.1",
  "V3.1.0",
  "V3.2.0",
  "V3.3.0",
  "V3.3.1",
  "V3.4.0",
  "V3.4.1",
  "V4.0.0",
  "V4.0.1");
$versionParameters =array(
  'V1.2.0'=>array('paramMailSmtpServer'=>'localhost',
                 'paramMailSmtpPort'=>'25',
                 'paramMailSendmailPath'=>null,
                 'paramMailTitle'=>'[Project\'Or RIA] ${item} #${id} moved to status ${status}',
                 'paramMailMessage'=>'The status of ${item} #${id} [${name}] has changed to ${status}',
                 'paramMailShowDetail'=>'true' ),
  'V1.3.0'=>array('defaultTheme'=>'blue'),
  'V1.4.0'=>array('paramReportTempDirectory'=>'../files/report/'),
  'V1.5.0'=>array('currency'=>'€', 
                  'currencyPosition'=>'after'),
  'V1.8.0'=>array('paramLdap_allow_login'=>'false',
					'paramLdap_base_dn'=>'dc=mydomain,dc=com',
					'paramLdap_host'=>'localhost',
					'paramLdap_port'=>'389',
					'paramLdap_version'=>'3',
					'paramLdap_search_user'=>'cn=Manager,dc=mydomain,dc=com',
					'paramLdap_search_pass'=>'secret',
					'paramLdap_user_filter'=>'uid=%USERNAME%')
);
$SqlEndOfCommand=";";
$SqlComment="--";
   
require_once (dirname(__FILE__) . '/../tool/projeqtor.php');

$nbErrors=0;
$currVersion=Sql::getDbVersion();
traceLog("");
traceLog("=====================================");
traceLog("");
traceLog("DataBase actual Version = " . $currVersion );
traceLog("ProjeQtOr actual Version = " . $version );
traceLog("");
if ($currVersion=="") {
  $currVersion='V0.0.0';
  // if no current version, parameters are set through config.php
  $versionParameters=array(); // Clear $versionParameter to avoid dupplication of parameters
}
$arrVers=explode('.',substr($currVersion,1));
$currVer=$arrVers[0];
$currMaj=$arrVers[1];
$currRel=$arrVers[2];

if ($currVersion!='V0.0.0' and $currVersion<'V3.0.0') {
	$nbErrors+=runScript('V3.0.-');
}

foreach ($versionHistory as $vers) {
  $arrVers=explode('.',substr($vers,1));
  $histVer=$arrVers[0];
  $histMaj=$arrVers[1];
  $histRel=$arrVers[2];
  if ( $histVer > $currVer 
  or ( $histVer == $currVer and $histMaj > $currMaj)
  or ( $histVer == $currVer and $histMaj == $currMaj and $histRel > $currRel) ) {
    $nbErrors+=runScript($vers);
  }
}

if ($currVersion=='V0.0.0') {
  traceLog ("create default project");
  $type=new ProjectType();
  $lst=$type->getSqlElementsFromCriteria(array('name'=>'Fixed Price'));
  $type=(count($lst)>0)?$lst[0]:null;
  $proj=new Project();
  $proj->color='#0000FF';
  $proj->description='Default project' . "\n" .
                     'For example use only.' . "\n" .
                     'Remove or rename this project when initializing your own data.';
  $proj->name='Default project';
  if ($type) {
    $proj->idProjectType=$type->id;
  }
  $result=$proj->save();
  $split=explode("<", $result);
  traceLog($split[0]);
}

//echo "for V1.6.1<br/>";
// For V1.6.1
$tst=new ExpenseDetailType('1');
if (! $tst->id) {
	$nbErrors+=runScript('V1.6.1');
}

$memoryLimitForPDF=Parameter::getGlobalParameter('paramMemoryLimitForPDF');
// For V1.7.0
if (! isset($memoryLimitForPDF) ) {
	writeFile('$memoryLimitForPDF = \'512\';',$parametersLocation);
  writeFile("\n",$parametersLocation);
  traceLog('Parameter $paramMemoryLimitForPDF added');
}

// For V1.9.0
if ($currVersion<"V1.9.0" and $currVersion!='V0.0.0') {
	$adminFunctionality='updateReference';
	include('../tool/adminFunctionalities.php');
	echo "<br/>";
}

// For V1.9.1
if ($currVersion<"V1.9.1") {
  // update affectations
  $aff=new Affectation();
  $affList=$aff->getSqlElementsFromCriteria(null, false);
  foreach ($affList as $aff) {
    $aff->save();
  }
}

// For V2.1.0
if ($currVersion<"V2.1.0") {
  // update PlanningElements (progress)
  $pe=new PlanningElement();
  $peList=$pe->getSqlElementsFromCriteria(null, false);
  foreach ($peList as $pe) {
    $pe->save();
  }
}
// For V2.1.1
if ($currVersion<"V2.1.1") {
  // update PlanningElements (progress)
  $ass=new Assignment();
  $assList=$ass->getSqlElementsFromCriteria(null, false);
  foreach ($assList as $ass) {
    $ass->saveWithRefresh();
  }
}

// For V2.4.1 & V2.4.2
if ($currVersion<"V2.4.2") {
  $req=new Requirement();
  $reqList=$req->getSqlElementsFromCriteria(null, false);
  foreach ($reqList as $req) {
  	$rq=new Requirement($req->id);
    $rq->updateDependencies();
  }
  $ses=new TestSession();
  $sesList=$ses->getSqlElementsFromCriteria(null, false);
  foreach ($sesList as $ses) {
  	$ss=new TestSession($ses->id);
    $ss->updateDependencies();
  }
  $tst=new TestCase();
  $tstList=$tst->getSqlElementsFromCriteria(null, false);
  foreach ($tstList as $tst) {
    $tc=new TestCase($tst->id);
    $tc->updateDependencies();
  }
}

// For V2.6.0 : migration of parameters to database
if ($currVersion<"V2.6.0") {
  $arrayParamsToMigrate=array('paramDbDisplayName',
                              'paramMailTitle','paramMailMessage','paramMailSender','paramMailReplyTo','paramAdminMail',
                              'paramMailSmtpServer','paramMailSmtpPort','paramMailSendmailPath','paramMailShowDetail');
  migrateParameters($arrayParamsToMigrate); 
}
if ($currVersion<"V3.0.0") {
  $arrayParamsToMigrate=array('paramLdap_allow_login', 'paramLdap_base_dn', 'paramLdap_host', 'paramLdap_port',
    'paramLdap_version', 'paramLdap_search_user', 'paramLdap_search_pass', 'paramLdap_user_filter',
    'paramDefaultPassword','paramPasswordMinLength', 'lockPassword',
    'paramDefaultLocale', 'paramDefaultTimezone', 'currency', 'currencyPosition',
    'paramFadeLoadingMode', 'paramRowPerPage', 'paramIconSize',
    'defaultTheme', 'paramPathSeparator', 'paramAttachementDirectory', 'paramAttachementMaxSize',
    'paramReportTempDirectory', 'paramMemoryLimitForPDF',
    'defaultBillCode','paramMailEol' 
    //'logFile', 'logLevel', 'paramDebugMode',
    );
  migrateParameters($arrayParamsToMigrate); 
}
if ($currVersion>="V3.0.0" and $version<"V3.1.3" 
and ! strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' and $paramDbType=='mysql') { 
	$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
	$query="RENAME TABLE `".$paramDbPrefix."workPeriod` TO `".$paramDbPrefix."workperiod`;";
	$query=trim(formatForDbType($query));
  //Sql::beginTransaction();
  $result=Sql::query($query);
  //Sql::commitTransaction();
}

if ($currVersion<"V3.3.0" and $currVersion!='V0.0.0') {
  $ses=new TestSession();
  $sesList=$ses->getSqlElementsFromCriteria(null, false);
  foreach ($sesList as $ses) {
    $ss=new TestSession($ses->id);
    $ss->TestSessionPlanningElement->validatedStartDate=$ss->startDate;
    $ss->TestSessionPlanningElement->validatedEndDate=$ss->endDate;
    $ss->save();
  }
}

$tstTable=new TodayParameter();
$tst=Sql::query("select count(*) from ". $tstTable->getDatabaseTableName()) ;
if (! $tst or count($tst)==0) {
  $nbErrors+=runScript('V3.3.1.linux');
}

if ($currVersion<"V3.4.0") {
	$defProf=Parameter::getGlobalParameter('defaultProfile');
	if (! $defProf) {
		$prf=new Profile('5');
		if ($prf->profileCode=='G') {
			$param=New Parameter();
			$param->parameterCode='defaultProfile';
			$param->parameterValue=5;
			$param->idUser=null;
			$param->idProject=null;
			$param->save();
		}
	}
}

if ($currVersion<"V4.0.0") {
	// Deleting old files referencing projector or projectorria : these files have been renamed
  $root=$_SERVER['SCRIPT_FILENAME'];
	$root=substr($root,0,strpos($root, '/tool/'));
  $files = glob($root.'/db/Projector_*.sql'); // get all file names
  error_reporting(0);
  disableCatchErrors();
  if ($files) {
	  foreach($files as $file){ // iterate files
	    if(is_file($file))
	      $perms = fileperms($file);
	      if ($perms & 0x0080) {
	        $do=@unlink($file); // delete file
	      } else {
	      	errorLog("Cannot delete file : ".$file);
	      } 
	  }
  }  
  $arrayFiles=array('/tool/projector.php',
    '/view/js/projector.js',
    '/view/js/projectorDialog.js',
    '/view/js/projectorFormatter.js',
    '/view/js/projectorWork.js',
    '/view/css/projector.css',
    '/view/css/projectorIcons.css',
    '/view/css/projectorPrint.css');
  foreach ($arrayFiles as $file) {
  	if (file_exists($root.$file)) {
  		$perms = fileperms($root.$file);
  		if ($perms & 0x0080) {
  		  $do=@unlink($root.$file);
  		} else {
        errorLog("Cannot delete file : ".$root.$file);
      } 
  	}
  }
  error_reporting(E_ALL);
  enableCatchErrors();
}
$tstTable=new OverallProgress();
$tst=Sql::query("select count(*) from ". $tstTable->getDatabaseTableName()) ;
if (! $tst or count($tst)==0) {
  $nbErrors+=runScript('V4.0.1.linux');
}

// To be sure, after habilitations updates ...
Habilitation::correctUpdates();
Habilitation::correctUpdates();
Habilitation::correctUpdates();
deleteDuplicate();
Sql::saveDbVersion($version);
traceLog('=====================================');
traceLog("");
echo "____________________________________________";
echo "<br/><br/>";
if ($nbErrors==0) {
  traceLog("DATABASE UPDATE COMPLETED TO VERSION " . $version);
  echo "DATABASE UPDATE COMPLETED TO VERSION " . $version;
} else {
  traceLog($nbErrors . " ERRORS DURING UPDATE TO VERSION " . $version );
  echo $nbErrors . " ERRORS DURING UPDATE TO VERSION " . $version . "<br/>";
  echo "DETAILS CAN BE FOUND IN LOG FILE.";
}
traceLog("");
traceLog("=====================================");
traceLog("");
echo "<br/>____________________________________________";


function runScript($vers) {
  global $versionParameters, $parametersLocation;
  $paramDbName=Parameter::getGlobalParameter('paramDbName');
  $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
  $dbType=Parameter::getGlobalParameter('paramDbType');
  set_time_limit(300);
  traceLog("=====================================");
  traceLog("");
  traceLog("VERSION " . $vers);
  traceLog("");
  $handle = @fopen("../db/projeqtor_" . $vers . ".sql", "r");
  $query="";
  $nbError=0;
  if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle);
        $buffer=trim($buffer);
        $buffer=str_replace('${database}', $paramDbName, $buffer);
        $buffer=str_replace('${prefix}', $paramDbPrefix, $buffer);
        if ( substr($buffer,0,2)=='--' ) {
          $buffer=''; // remove comments
        }
        if ($buffer!='') {
          $query.=$buffer . "\n";
        }
        if ( substr($buffer,strlen($buffer)-1,1)==';' ) {
        	$query=trim(formatForDbType($query));
        	if ($query) {
        		Sql::beginTransaction();
	          $result=Sql::query($query);
	          if ( ! $result or !$result->queryString ) {
	          	Sql::rollbackTransaction();
	          	$nbError++;
	          	traceLog("");
	            traceLog( "Error # $nbError => SQL error while executing maintenance query for version $vers (see above message)");
	            traceLog("");
	            //traceLog(Sql::$lastQueryErrorMessage);
	            //traceLog("");
	            traceLog("*************************************************");
	            traceLog("");
	            $query="";
	          } else {
	          	Sql::commitTransaction();
	            $action="";
	            if (substr($query,0,12)=='CREATE TABLE') {
	              $action="CREATE TABLE";
	            }
	            if (substr($query,0,12)=='RENAME TABLE') {
	              $action="RENAME TABLE";
	            }
	            if (substr($query,0,11)=='INSERT INTO') {
	              $action="INSERT INTO";
	            }
	            if (substr($query,0,6)=='UPDATE') {
	              $action="UPDATE";
	            }
	            if (substr($query,0,11)=='ALTER TABLE') {
	              $action="ALTER TABLE";
	            }
	            if (substr($query,0,10)=='DROP TABLE') {
	              $action="DROP TABLE";
	            }
	            if (substr($query,0,11)=='DELETE FROM') {
	              $action="DELETE FROM";
	            }
	            if (substr($query,0,14)=='TRUNCATE TABLE') {
	              $action="TRUNCATE TABLE";
	            }
	            if (substr($query,0,12)=='CREATE INDEX') {
                $action="CREATE INDEX";
              }
	            $deb=strlen($action);
	            $end=strpos($query,' ', $deb+1);
	            $len=$end-$deb;
	            $tableName=substr($query, $deb, $len );
	            if ($action=="DROP TABLE") {            
                $q=trim($query,"\n");
                $q=trim($q,"\r");
	            	$q=trim($q,' ;');
                $q=trim($q,' ');
	            	$tableName=substr($q,strrpos($q,' ',-2)+1);
	            }
	            $tableName=trim($tableName);
	            $tableName=trim($tableName,'`');
	            $tableName=trim($tableName,'"');
	            $tableName=trim($tableName,';');
	            switch ($action) {
	              case "CREATE TABLE" :
	                traceLog(" Table \"" . $tableName . "\" created."); 
	                break;
	              case "DROP TABLE" :
	                traceLog(" Table \"" . $tableName . "\" dropped."); 
	                break;
	              case "ALTER TABLE" :
	                traceLog(" Table \"" . $tableName . "\" altered."); 
	                break;
	              case "RENAME TABLE" :
	                traceLog(" Table \"" . $tableName . "\" renamed."); 
	                break;
	              case "TRUNCATE TABLE" :
	                traceLog(" Table \"" . $tableName . "\" truncated.");
	                if ($dbType=='pgsql') {Sql::updatePgSeq($tableName);} 
	                break;                
	              case "INSERT INTO":         	
	              	traceLog(" " . Sql::$lastQueryNbRows . " lines inserted into table \"" . $tableName . "\".");
	                if ($dbType=='pgsql') {Sql::updatePgSeq($tableName);} 
	                break;
	              case "UPDATE":
	                traceLog(" " . Sql::$lastQueryNbRows . " lines updated into table \"" . $tableName . "\"."); 
	                break;
	              case "DELETE FROM":
	                traceLog(" " . Sql::$lastQueryNbRows . " lines deleted from table \"" . $tableName . "\".");
	                if ($dbType=='pgsql') {Sql::updatePgSeq($tableName);} 
	                break;              
	              case "CREATE INDEX" :
                  traceLog(" Index \"" . $tableName . "\" created."); 
                  break;
                default:
	                traceLog("ACTION '$action' NOT EXPECTED FOR QUERY : " . $query);
	            }
	          }
        	}
          $query="";
        }
    }
    if (array_key_exists($vers,$versionParameters)) {
      $nbParam=0;
      writeFile('// New parameters ' . $vers . "\n", $parametersLocation);
      foreach($versionParameters[$vers] as $id=>$val) {
      	$param=Parameter::getGlobalParameter($id);
      	if (! $param) {
          $nbParam++;
          writeFile('$' . $id . ' = \'' . addslashes($val) . '\';',$parametersLocation);
          writeFile("\n",$parametersLocation);
          traceLog('Parameter $' . $id . ' added');
      	}
      }
      echo i18n('newParameters', array($nbParam, $vers));
      echo '<br/>' . "\n";
    }
    fclose($handle);
    traceLog("");
    traceLog("DATABASE UPDATED");
    if ($nbError==0) {
      traceLog(" WITH NO ERROR");
    } else {
      traceLog(" WITH " . $nbError . " ERROR" . (($nbError>1)?"S":""));
    }
  }
  traceLog("");
  return $nbError;
}

/*
 * Delete duplicate if new version has been installed twice :
 *  - habilitation
 * 
 */
function deleteDuplicate() {
  // HABILITATION
  $hab=new Habilitation();
  $habList=$hab->getSqlElementsFromCriteria(null, false, null, 'idMenu, idProfile, id ');
  $idMenu='';
  $idProfile='';
  foreach ($habList as $hab) {
    if ($hab->idMenu==$idMenu and $hab->idProfile==$idProfile) {
      $hab->delete();
    } else {
      $idMenu=$hab->idMenu;
      $idProfile=$hab->idProfile;
    }
  }
  // HABILITATIONREPORT
  $hab=new HabilitationReport();
  $habList=$hab->getSqlElementsFromCriteria(array(), false, null, 'idReport, idProfile, id ');
  $idReport='';
  $idProfile='';
  foreach ($habList as $hab) {
    if ($hab->idReport==$idReport and $hab->idProfile==$idProfile) {
      $hab->delete();
    } else {
      $idReport=$hab->idReport;
      $idProfile=$hab->idProfile;
    }
  }
// HABILITATIONOTHER
  $hab=new HabilitationOther();
  $habList=$hab->getSqlElementsFromCriteria(array(), false, null, 'scope, idProfile, id ');
  $scope='';
  $idProfile='';
  foreach ($habList as $hab) {
    if ($hab->scope==$scope and $hab->idProfile==$idProfile) {
      $hab->delete();
    } else {
      $scope=$hab->scope;
      $idProfile=$hab->idProfile;
    }
  }
  // ACCESSRIGHT
  $acc=new AccessRight();
  $accList=$acc->getSqlElementsFromCriteria(array(), false, null, 'idProfile, idMenu, id ');
  $idMenu='';
  $idProfile='';
  foreach ($accList as $acc) {
    if ($acc->idMenu==$idMenu and $acc->idProfile==$idProfile) {
      $acc->delete();
    } else {
      $idMenu=$acc->idMenu;
      $idProfile=$acc->idProfile;
    }
  }
  
// PARAMETER
  $par=new Parameter();
  $parList=$par->getSqlElementsFromCriteria(array(), false, null, 'idUser, idProject, parameterCode, id');
  $idUser='';
  $idProject='';
  $parameterCode='';
  foreach ($parList as $par) {
    if ($par->idUser==$idUser and $par->idProject==$idProject and $par->parameterCode==$parameterCode) {
      $par->delete();
    } else {
      $idUser=$par->idUser;
      $idProject=$par->idProject;
      $parameterCode=$par->parameterCode;
    }
  }
// REPORT PARAMETER
  $par=new ReportParameter();
  $parList=$par->getSqlElementsFromCriteria(array(), false, null, 'idReport, name');
  $idReport='';
  $name='';
  foreach ($parList as $par) {
    if ($par->idReport==$idReport and $par->name==$name) {
      $par->delete();
    } else {
      $idReport=$par->idReport;
      $name=$par->name;
    }
  }
}

function formatForDbType($query) {
  $dbType=Parameter::getGlobalParameter('paramDbType');
  if ($dbType=='mysql') {
    return $query;
  }
  $from=array();
  $to=array();
  if ($dbType=='pgsql') {
  	if (stripos($query,'ADD INDEX')) {
  		return '';
  	}
  	$from[]='  ';                                         $to[]=' ';
    $from[]='`';                                          $to[]='';
    $from[]=' int(12) unsigned NOT NULL AUTO_INCREMENT';  $to[]=' serial';
    $from[]='int(';                                       $to[]=' numeric(';
    $from[]=' datetime';                                  $to[]=' timestamp';
    $from[]=' double';                                    $to[]=' double precision';
    $from[]=' unsigned';                                  $to[]='';
    $from[]='\\\'';                                       $to[]='\'\'';
    $from[]='ENGINE=InnoDB';                              $to[]='';
    $from[]='DEFAULT CHARSET=utf8';                       $to[]='';
    $res=str_ireplace($from, $to, $query);
    // ALTER TABLE : very different from MySql !!!
    if (substr($res,0,11)=='ALTER TABLE') {
    	$posChange=strpos($res,'CHANGE');
    	while ($posChange) {
    		$colPos1=strpos($res,' ',$posChange+1);
    		$colPos2=strpos($res,' ',$colPos1+1);
    		$colPos3=strpos($res,' ',$colPos2+1);
    		if (!$colPos3) {$colPos3=strlen($res)-1;}
    		$col1=substr($res,$colPos1+1,$colPos2-$colPos1-1);
    		$col2=substr($res,$colPos2+1,$colPos3-$colPos2-1);
        if ($col1==$col2) {
          $res=substr($res,0,$posChange-1). ' ALTER '.$col2.' TYPE '.substr($res,$colPos3+1);
        } else {
        	$res=substr($res,0,$posChange-1). ' RENAME '.$col1.' TO '.$col2.';';
        }
    		$posChange=strpos($res,'CHANGE', $posChange+5);
    	}
    } else if (substr($res,0,12)=='RENAME TABLE') {
    	 $res=str_replace('RENAME TABLE','ALTER TABLE',$res);
    	 $res=str_replace(' TO ',' RENAME TO ',$res);
    }
  } else {
  	// not mysql, not pgsql, so WHAT ?
    echo "unknown database type '$dbType'";
    return '';
  }
  
  return $res;
}

function migrateParameters($arrayParamsToMigrate) {
	global $parametersLocation;
	include $parametersLocation;
	foreach ($arrayParamsToMigrate as $param) {
    //$crit=array('idUser'=>null, 'idProject'=>null, 'parameterCode'=>$param);
    //$parameter=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
    //if (!$parameter or !$parameter->id) { 
    $parameter=new Parameter();
    //}
    $parameter->idUser=null;
    $parameter->idProject=null;
    $parameter->parameterCode=$param;  
    $parameter->parameterValue=Parameter::getGlobalParameter($param);
    if ($param=='paramMailEol') {
    	if ($parameter->parameterValue=='\n') {
    		$parameter->parameterValue='LF';
    	} else  {
    		$parameter->parameterValue='CRLF';
    	}
    }
    $parameter->save();
  }
  Parameter::regenerateParamFile();
}

?>