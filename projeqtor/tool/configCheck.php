<?php
/** =========================================================================== 
 * Chek login/password entered in connection screen
 */
  include_once("../tool/file.php");
  restore_error_handler();
  error_reporting(0);
  $param=$_REQUEST["param"];
  $pname=$_REQUEST["pname"];
  $label=$_REQUEST["label"];
  $value=$_REQUEST["value"];
  $ctrl=$_REQUEST["ctrls"];
  
  // Controls
  $error=false;
  foreach ($param as $id=>$val) {
    $ct=$ctrl[$id];
    if (substr($ct,0,1)=="=") {
      if ( strpos($ct, '=' . $val . '=')===false) {
        showError("incorrect value for '" . $label[$id] . "', valid values are : " . str_replace("="," ",$ct));
      }
    } else if ($ct=="mandatory") {
      if ( ! $val) {
        showError("incorrect value for '" . $label[$id] . "', field is mandatory");
      }
    } else if ($ct=="email") {
      if ($val and !filter_var($val, FILTER_VALIDATE_EMAIL)) {
        showError("incorrect value for '" . $label[$id] . "', invalid email address");  
      }
    } else if ($ct=="integer") {
      if (! is_numeric($val) or !is_int($val*1)) {
        showError("incorrect value for '" . $label[$id] . "', field must be an integer");  
      }
    }
  }
  // Check that PDO is enabled
  if (! extension_loaded('pdo')) {
    showError('PDO module is not available - check your php configuration (php.ini)');
    exit;
  }
  if ( ! extension_loaded('pdo_'.$param['DbType']) ) {
  	showError('Module PDO for ' . strtoupper($param['DbType']).' is not available - check your php configuration (php.ini)');
  	exit; 	
  }
  
  // check database connexion
  //error_reporting();
  $dbType=$param['DbType'];
  if ($dbType=='mysql') {
    ini_set('mysql.connect_timeout', 10);
  }
  // dsn without database
  $dsn = $param['DbType'].':host='.$param['DbHost'].';port='.$param['DbPort'];
  try {
    $connexion = new PDO($dsn, $param['DbUser'], $param['DbPassword']);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
  	if ($dbType=='pgsql' and strpos($e->getMessage(), $param['DbUser'])>0 
  	    and (strpos($e->getMessage(), "does not exist")>0 or strpos($e->getMessage(), "n'existe pas")>0)) {
  	   //FATAL: database "pj_integ" does not exist
  	   //FATAL: la base de données « pj_integ » n'existe pas
  	   // => not an error, pgsql expect an existing database with user name
  	   $pgError="User  '" . $param['DbUser'] . "' is valid but no database named '". $param['DbUser'] ."' exists."
  	     . "<br/>You have to create database '".$param['DbName']."' on your own "
  	     . "<br/>or create default database '".$param['DbUser']."' in order to allow connection of user '".$param['DbUser']."'";
  	} else {
      showError(utf8_encode($e->getMessage()));
      showError('dsn = '.$dsn);
      if ($dbType=='mysql') {
        exit;
      }
  	}
  }
  $baseExists=false;
  $dsn = $param['DbType'].':host='.$param['DbHost'].';port='.$param['DbPort'].';dbname='.$param['DbName'];
  try {
  	$cnxDb = new PDO($dsn, $param['DbUser'], $param['DbPassword']);
    $cnxDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $baseExists=true;
  } catch (PDOException $e) {
    $baseExists=false;
  }
  if ( ! $baseExists and $dbType=='pgsql' and isset($pgError)) {
    showError($pgError);
    exit;
  }
  if ( ! $baseExists ) {
  	try {
      $query='CREATE DATABASE ' . $param['DbName'];
      if ($dbType=='mysql') {
        $query.=' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';
      } else if ($dbType=='pgsql') {
      	$query.=' ENCODING \'UNICODE\';';
      }
      $result=$connexion->exec($query);
  	} catch (PDOException $e) {
      showError($e->getMessage());
      showError('dsn = '.$dsn);
      exit;
    }  
    showMsg('Database \'' . $param['DbName'] . '\' created.');
  }
  
  // Check attachement directory (may be empty)
  if ($param['AttachementDirectory']) {
    if (! file_exists ($param['AttachementDirectory'])) {
      if (! mkdir($param['AttachementDirectory'],0777,true)) {
        showError("incorrect value for '" . $label['AttachementDirectory'] . "', this is not a valid directory name");
      }  
    }
  }
  // Check log file location : write possible
  if ($param['logFile']) {
    $rep=dirname($param['logFile']);
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for '" . $label['logFile'] . "', does not include a valid directory name");
      } 
    }
    if (! $error) {
      $logFile=str_replace('${date}',date('Ymd'),$param['logFile']);
      if (! writeFile ( 'CONFIGURATION CONTROLS ARE OK', $logFile )) {
        showError("incorrect value for '" . $label['logFile'] . "', cannot write to such a file");
      } else {
        //echo "Write in $logFile OK<br/>";
        kill($logFile);
      }
    }
  }  
  
  // Check parameter file location : write possible
  $paramFile=$_REQUEST['location'];
  if ($paramFile) {
    $rep=dirname($paramFile);
    if (! $rep or $rep=='.') {
      $paramFile='../tool/' . $paramFile;
      $rep=dirname($paramFile);
    }
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for 'Parameter file name', does not include a valid directory name ($rep)");
      } 
    }
    if (! $error) {
      if (! writeFile ( 'TEST' , $paramFile)) {
        showError("incorrect value for 'Parameter file name', cannot write to such a file");
      } else {
        kill($paramFile);
      }
    }
  } else {
    showError("incorrect value for 'Parameter file name', field is mandatory");
  } 
  
  if ($error) {exit;}

  kill($paramFile);
  writeFile('<?php ' . "\n", $paramFile);
  writeFile('// =======================================================================================' . "\n", $paramFile);
  writeFile('// Automatically generated parameter file' . "\n", $paramFile);
  writeFile('// =======================================================================================' . "\n", $paramFile);
  foreach ($param as $id=>$val) {
    writeFile('$' . $pname[$id] . ' = \'' . addslashes($val) . '\';', $paramFile);
    writeFile("\n", $paramFile);
  }
  if ($error) {exit;}
  
  $paramLocation="../tool/parametersLocation.php";
  kill($paramLocation);
  if (! writeFile(' ',$paramLocation)) {
    showError("impossible to write \'$paramLocation\' file, cannot write to such a file");
  }
  kill($paramLocation);
  writeFile('<?php ' . "\n", $paramLocation);
  writeFile('$parametersLocation = \'' . $paramFile . '\';', $paramLocation);
  
  //rename ('../tool/config.php','../tool/config.php.old');
  showMsg("Parameters are saved.");
  
  echo '<br/><button id="continueButton" dojoType="dijit.form.Button" showlabel="true">continue';
  echo '<script type="dojo/connect" event="onClick" args="evt">';
  echo '  window.location = ".";';
  echo '</script>';
  echo '</button>';
  
  function showError($msg) {
    global $error;
    $error=true;
    echo "<div class='messageERROR'>" . $msg . "</div>";
  }

  function showMsg($msg) {
    echo "<div class='messageOK'>" . $msg . "</div>";
  }

?>