<?php
/** ===========================================================================
 * Static method defining all persistance methods
 */
class Sql {

  private static $connexion = NULL;
   
  // Database informations
  private static $dbType;
  private static $dbHost;
  private static $dbPort;
  private static $dbUser;
  private static $dbPassword;
  private static $dbName;
  private static $dbVersion=NULL;

  // Visible Information
  public static $lastQuery=NULL;           // the string of the last executed query
  public static $lastQueryType=NULL;       // the type of the last executed query : SELECT or UPDATE
  public static $lastQueryResult=NULL;     // the result of the last executed query
  public static $lastQueryNbRows=NULL;     // the number of rows returns of affected by the last executed query
  public static $lastQueryNewid=NULL;      // the new id of the last executed query, if it was an INSERT query
  public static $lastQueryNewObjectId=NULL;
  public static $lastQueryErrorMessage=NULL;
  public static $lastQueryErrorCode=NULL;
  public static $lastConnectError=NULL;  
  public static $maintenanceMode=false;

  /** ========================================================================
   * Constructor (private, because static access only) 
   * => no destructor for this class
   * @return void
   */
  private function __construct() {
  }
	
  /** =========================================================================
   * Execute a query on database and return the result
   * @param $sqlRequest the resquest to be executed. Can be SELECT, UPDATE, INSERT, DELETE or else
   * @return resource of result if query is SELECT, false either
   */
  static function query($sqlRequest=NULL) {
    global $debugQuery;
    if ($sqlRequest==NULL) {
      echo "SQL WARNING : empty query";
      traceLog("SQL WARNING : empty query");
      return FALSE;
    }
    // Execute query
    $cnx = self::getConnection();
    self::$lastQueryErrorMessage=NULL;
    self::$lastQueryErrorCode=NULL;
    enableCatchErrors();
    $result = new PDOStatement();
    $checkResult="OK";
    try { 
    	$startMicroTime=microtime(true);
      $result = $cnx->query($sqlRequest);  
      if (isset($debugQuery) and $debugQuery) {
      	// debugLog to keep
        debugLog(round((microtime(true) - $startMicroTime)*1000000)/1000000 . ";" . $sqlRequest);
      }
      //traceLog($sqlRequest);
      if (! $result) {
        self::$lastQueryErrorMessage=i18n('sqlError'). ' : ' .$cnx->errorCode() . "<br/><br/>" . $sqlRequest;
        self::$lastQueryErrorCode=$cnx->errorInfo(); 
        errorLog('Error-[' . self::$lastQueryErrorCode . '] ' .self::$lastQueryErrorMessage);
        $checkResult="ERROR";       
      }
    } catch (PDOException $e) {
    	if (self::$dbVersion!='0.0.0') { // we get the version, if not set, may be normal : initial configuration. Must not log error
        $checkResult="EXCEPTION";
	      self::$lastQueryErrorMessage=$e->getMessage();
	      self::$lastQueryErrorCode=$e->getCode();
	      errorLog('Exception-[' . self::$lastQueryErrorCode . '] ' .self::$lastQueryErrorMessage);
	      errorLog('   For query : '.$sqlRequest);
	      errorLog('   Strack trace :');
	      $traces = debug_backtrace();
	      foreach ($traces as $idTrace=>$arrayTrace) {
	      	errorLog("   #$idTrace "
	      	  . ((isset($arrayTrace['class']))?$arrayTrace['class'].'->':'')
	      	  . ((isset($arrayTrace['function']))?$arrayTrace['function'].' called at ':'')
	      	  . ((isset($arrayTrace['file']))?'['.$arrayTrace['file']:'')
	      	  . ((isset($arrayTrace['line']))?':'.$arrayTrace['line']:'')
	      	  . ((isset($arrayTrace['file']))?']':'')
	      	  );
	      }
	      return false;
    	}
    }
    disableCatchErrors();
    // store informations about last query
    self::$lastQuery=$sqlRequest;
    self::$lastQueryResult=$result;
    self::$lastQueryType= (is_resource($result)) ? "SELECT" : "UPDATE";
    self::$lastQueryNbRows = (self::$lastQueryType=="SELECT") ? $result->rowCount() : $result->rowCount();
    self::$lastQueryNewid=null;
    // Specific update of sequence in pgsql mode.
    if (self::$lastQueryType=="UPDATE") {
      if (self::isPgsql() and ! self::$maintenanceMode) {
      	if (strtolower(substr($sqlRequest,0,11))=='insert into') {
      		$table=substr($sqlRequest,12,strpos($sqlRequest,'(')-13);
      		$seq=trim(strtolower($table)).'_id_seq';
      		//try {
      		$lastId=$cnx->lastInsertId($seq);
      		//} catch (PDOException $e) {
      		//	$lastId=null;
      		//}
      		self::$lastQueryNewid =($lastId)?$lastId:NULL;
      	}
      } else {   	
        self::$lastQueryNewid = ($cnx->lastInsertId()) ? $cnx->lastInsertId() : NULL ;
      }
    }
    if ($checkResult!='OK') {
    	return false;
    }
    return $result;
  }

  /** =========================================================================
   * Fetch the next line in a result set
   * @param $result
   * @return array of data, or false if no more line
   */
  static function fetchLine($result) {
    if ($result) {
      return $result->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  }
  
  /** =========================================================================
   * Begin a transaction
   * @return void
   */
  public static function beginTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (!$cnx->beginTransaction()) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Begin Transaction");
        errorLog("SQL ERROR : Error on Begin Transaction");
        exit; 
      }
      error_reporting(E_ALL ^ E_WARNING);
    }    
  }

  
  /** =========================================================================
   * Commit a transaction (validate the changes)
   * @return void
   */
  public static function commitTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (! $cnx->commit()) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Commit Transaction");
        errorLog("SQL ERROR : Error on Commit Transaction");
        exit; 
      }
      error_reporting(E_ALL ^ E_WARNING);
    }
  }

  
  /** =========================================================================
   * RoolBack a transaction (cancel the changes)
   * @return void
   */
  public static function rollbackTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (! $cnx->rollBack() ) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Rollback Transaction");
        errorLog("SQL ERROR : Error on Rollback Transaction");
        exit; 
      }
    }
  }
  
  
  /** =========================================================================
   * Replace in the string all the special caracters to ensure a valid query syntax
   * @param $string the string to be protected
   * @return the string, protected to ensure a correct sql query
   */
  public static function str($string, $objectClass=null) {
    // OK, validated, values are not escaped any more on check, but just while writing the query 
    /*if ($objectClass and $objectClass=="History") {
    	return $string; // for history saving, value have just been escaped yet, don't do it twice !
    }*/
  	$str=$string;
    // To be kept : if magic_quote_gpc is on, it would insert \' instead of ' and so on
  	if (get_magic_quotes_gpc()) {
      $str=str_replace('\"','"',$str);
      $str=str_replace("\'","'",$str);
      $str=str_replace('\\\\','\\',$str);
    }   
    $cnx=self::getConnection();
    return $cnx->quote($str);
  }
   
  
  /** =========================================================================
   * Return the connexion. Private. Only for internal use.
   * @return resource connexion to database
   */
  private static function getConnection() {
    if (self::$connexion != NULL) {
      return self::$connexion;
    }
    if (!self::$dbType or !self::$dbHost or !self::$dbName or ! self::$dbPort) {
      self::$dbType=Parameter::getGlobalParameter('paramDbType');
      self::$dbHost=Parameter::getGlobalParameter('paramDbHost');
      self::$dbPort=Parameter::getGlobalParameter('paramDbPort');
      self::$dbUser=Parameter::getGlobalParameter('paramDbUser');
      self::$dbPassword=Parameter::getGlobalParameter('paramDbPassword');
      self::$dbName=Parameter::getGlobalParameter('paramDbName');     
    }
    if (self::$dbType != "mysql" and self::$dbType != "pgsql") {
    	$logLevel=Parameter::getGlobalParameter('logLevel');
      if ($logLevel>=3) {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown '" . self::$dbType . "' \n");
      } else {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown");
      }
      errorLog("SQL ERROR : Database type unknown '" . self::$dbType . "'");
      self::$lastConnectError="TYPE";
      exit;
    }

    //restore_error_handler();
    //error_reporting(0);
    enableCatchErrors();
    if (self::$dbType == "mysql") {
      ini_set('mysql.connect_timeout', 10);
    }
    try {
    	$dsn = self::$dbType.':host='.self::$dbHost.';port='.self::$dbPort.';dbname='.self::$dbName; 	  
    	self::$connexion = new PDO($dsn, self::$dbUser, self::$dbPassword);
    	self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
    	echo htmlGetErrorMessage($e->getMessage( )).'<br />';
    }
    if (self::$dbType == "mysql") {
      ini_set('mysql.connect_timeout', 60);
    }        
    disableCatchErrors();
    self::$lastConnectError=NULL;
    return self::$connexion;
  }

   /** =========================================================================
   * Return the version of the DataBase
   * @return the version of the DataBase, as String Vx.y.z or empty string if never initialized
   */
  public static function getDbVersion() {
    if (self::$dbVersion!=NULL) {
      return self::$dbVersion;
    }
    self::$dbVersion='0.0.0';
    $crit['idUser']=null;
    $crit['idProject']=null;
    $crit['parameterCode']='dbVersion';
    $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
    self::$dbVersion=NULL;
    if (! $obj or $obj->id==null) {
      return "";
    } else {
    	self::$dbVersion=$obj->parameterValue;
      return $obj->parameterValue;
    }
  }
  
   /** =========================================================================
   * Save the version of the DataBase
   * @return void
   */
  public static function saveDbVersion($vers) {
    $crit['idUser']=null;
    $crit['idProject']=null;
    $crit['parameterCode']='dbVersion';
    $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
    $obj->parameterValue=$vers;
    $obj->save();
  }
  
  // Retores the Sequence for PgSql
  public static function updatePgSeq($table) {
    $updateSeq=Sql::query("SELECT setval('".$table."_id_seq', (SELECT MAX(id) FROM $table));");
  }
  
  public static function isPgsql() {
  	if (! self::$dbType) {
  		self::$dbType=Parameter::getGlobalParameter('paramDbType');
  	}
  	if (self::$dbType=='pgsql') {
  		return true;
  	} else {
  		return false;
  	}
  } 

  public static function isMysql() {
    if (! self::$dbType) {
      self::$dbType=Parameter::getGlobalParameter('paramDbType');
    }
    if (self::$dbType=='mysql') {
      return true;
    } else {
      return false;
    }
  } 
  
  public static function fmtId($id) {
  	if ($id==null or $id=='*' or $id=='' or $id==' ') {
  		return -1;
  	} else {
  	  return $id;
    }
  }
  
}
?>