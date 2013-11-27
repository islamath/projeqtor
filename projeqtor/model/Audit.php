<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Audit extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $sessionId;
  public $auditDay;
  public $idUser;
  public $userName;
  public $platform;
  public $browser;
  public $browserVersion;
  public $userAgent;
  public $_col_2_2_connectionStatus;
  public $connection;
  public $lastAccess;
  public $disconnection;
  public $duration;
  public $idle;
  public $_spe_disconnectButton;
  public $requestRefreshParam;
  public $requestRefreshProject;
  public $requestDisconnection;
  
  public $_noHistory;
  public $_readOnly=true;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="sessionId" width="15%" ># ${sessionId}</th>
    <th field="userName" width="15%" >${idUser}</th>
    <th field="connection" formatter="dateFormatter" width="12%" >${connection}</th>
    <th field="lastAccess" formatter="dateFormatter" width="12%"  >${lastAccess}</th>
    <th field="duration" formatter="timeFormatter" width="10%"  >${duration}</th>
    <th field="platform" width="10%" >${platform}</th>
    <th field="browser" formatter="timeFormatter" width="10%" >${browser}</th>
    <th field="requestDisconnection" width="6%" formatter="booleanFormatter" >${requestDisconnection}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("auditDay"=>"hidden", 
     "disconnection"=>"hidden",
     "idUser"=>"hidden",
     "requestRefreshParam"=>"hidden",
     "requestRefreshProject"=>"hidden" );
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
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  static function updateAudit() {
  	// $source can be "main" (from projeqtor.php), "login" (from loginCheck.php) or "alert" (from checkAlertToDisplay.php)
  	if (! isset($_SESSION['user'])) return;
  	$audit=SqlElement::getSingleSqlElementFromCriteria('Audit', array('sessionId'=>session_id()));
    if (! $audit->id) {
      $audit->sessionId=session_id();
      $audit->auditDay=date('Ymd');
      $audit->connection=date('Y-m-d H:i:s');
      $user=$_SESSION['user'];
      $audit->idUser=$user->id;
      $audit->userName=$user->name;
      $audit->userAgent=$_SERVER['HTTP_USER_AGENT'];
      $browser = self::getBrowser(null, true);
      $audit->platform=$browser['platform'];
      $audit->browser=$browser['browser'];
      $audit->browserVersion=$browser['version'];
      $audit->disconnection=null;
    } else if ($audit->requestDisconnection) {
    	$script=basename($_SERVER['SCRIPT_NAME']); 
    	if ($script=='checkAlertToDisplay.php') {
	    	echo '<b>' .  i18n('disconnect') . '</b>';
	      echo '<br/>'.'<br/>';
	      echo  i18n('disconnected');
	      echo '<input type="hidden" id="idAlert" name="idAlert" value="" ./>';
	      echo '<input type="hidden" id="alertType" name="alertType" value="INFO" ./>';
	      Audit::finishSession();
	      exit;
    	}
    } else { 
    	if ($audit->requestRefreshParam) {
	    	$audit->requestRefreshParam=0;
	    	Parameter::refreshParameters();
    	}
    	if ($audit->requestRefreshProject and basename($_SERVER['SCRIPT_NAME'])=='checkAlertToDisplay.php') {
    		$audit->requestRefreshProject=0;
    		echo '<input type="hidden" id="requestRefreshProject" name="requestRefreshProject" value="true" ./>';
    	}
    }
    $audit->lastAccess=date('Y-m-d H:i:s');
    // date_diff is only supported from PHP 5.3
    $audit->duration=date('H:i:s',strtotime($audit->lastAccess)-strtotime($audit->connection)-3600);
    //$duration=date_diff(date_create($audit->connection), date_create($audit->lastAccess)) ;
    //$audit->duration=$duration->format('%H%I%S');
    $audit->requestDisconnection=0;
    $audit->idle=0;
    $audit->auditDay=date('Ymd');
  	$result=$audit->save();
  }
  
   static function finishSession() {
     $audit=SqlElement::getSingleSqlElementFromCriteria('Audit', array('sessionId'=>session_id()));
     if ($audit->id) {
     	 $audit->lastAccess=date('Y-m-d H:i:s');
     	 $audit->requestRefreshParam=0;
     	 $audit->disconnection=$audit->lastAccess;
     	 // date_diff is only supported from PHP 5.3
     	 $audit->duration=date('H:i:s',strtotime($audit->lastAccess)-strtotime($audit->connection)-3600);
     	 //$duration=date_diff(date_create($audit->connection), date_create($audit->lastAccess)) ;
       //$audit->duration=$duration->format('%H%I%S');
       $audit->idle=1;
    	 $audit->save();
     }
     AuditSummary::updateAuditSummary($audit->auditDay);
     $user=$_SESSION['user'];
     $user->disconnect();
     // terminate the session
     if (ini_get("session.use_cookies")) {
	     $params = session_get_cookie_params();
	     setcookie(session_name(), '', time() - 42000,
	        $params["path"], $params["domain"],
	        $params["secure"], $params["httponly"]);
     }
     try { @session_destroy(); }
     catch (Exception $e) {
     	 // tried twice : OK let's give up.
     }
   }  
   
  static function getBrowser() { 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $ub = 'Unknown';
    $version= "";
  
    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?P<browser>' . join('|', $known) .
    ')[/ ]+(?P<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}    
    return array(
        'userAgent' => $u_agent,
        'browser'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
  } 
  
   public function drawSpecificItem($item){
     global $print, $comboDetail;
     $result="";
     if ($item=='disconnectButton') {
     	 $result .="<table><tr><td class='label' valign='top'><label>&nbsp;</label>";
       $result .="</td><td>";
     	 $result .= '<button id="disconnectSession" dojoType="dijit.form.Button" showlabel="true"';
       if ( $this->sessionId==session_id()) {
         $result .= ' disabled="disabled" ';
       }
       $result .= ' title="' . i18n('disconnectSession') . '" style="vertical-align: middle;">';
       $result .= '<span>' . i18n('disconnect') . '</span>';
       $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
       $result .=  '    loadContent("../tool/disconnectSession.php?idAudit=' . $this->id .'","resultDiv","objectForm",true);';
       $result .= '</script>';
       $result .= '</button>';
       $result .="</td></tr></table>";
     }
     return $result;
     
   }
   
}
?>