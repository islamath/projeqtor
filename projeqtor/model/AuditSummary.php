<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class AuditSummary extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $auditDay;
  public $firstConnection;
  public $lastConnection;
  public $numberSessions;
  public $minDuration;
  public $maxDuration;
  public $meanDuration;
  
  public $_noHistory;
  public $_readOnly=true;
  
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
  
  static function updateAuditSummary($day) {
  	AuditSummary::finishOldSessions($day);
  	$audit=new Audit();
  	$crit=array('auditDay'=>$day);
  	$summary=SqlElement::getSingleSqlElementFromCriteria('AuditSummary', $crit);
  	$summary->numberSessions=0;
  	$summary->auditDay=$day;
  	$summary->firstConnection=null;
  	$summary->minDuration=null;
  	$totDuration=0;
  	$list=$audit->getSqlElementsFromCriteria($crit);
  	foreach($list as $audit) {
      if (! $summary->firstConnection or $audit->connection<$summary->firstConnection) {
  		  $summary->firstConnection=$audit->connection;
      }  
      if ($audit->disconnection>$summary->lastConnection) {
        $summary->lastConnection=$audit->disconnection;
      }
      $summary->numberSessions++;
      if (! $summary->minDuration or $audit->duration<$summary->minDuration) {
      	$summary->minDuration=$audit->duration;
      } 
      if ($audit->duration>$summary->maxDuration) {
        $summary->maxDuration=$audit->duration;
      }
      $totDuration+=strtotime($audit->lastAccess)-strtotime($audit->connection);
  	}
    if ($summary->numberSessions>0) {
  	  $meanDuration=round($totDuration/$summary->numberSessions,0);   
	    $hh=floor($meanDuration/3600);
	    $meanDuration-=$hh*3600;
	    $mm=floor($meanDuration/60);
	    $meanDuration-=$mm*60;  
	    $ss=$meanDuration;
	    $summary->meanDuration=$hh.':'.$mm.':'.$ss;   
    } else {
    	$summary->meanDuration='00:00:00';
    }
  	$result=$summary->save();
  	return $result;
  }
    
   static function finishOldSessions($day) {
   	 $crit="auditDay < '" . $day . "' and idle=0";
   	 $audit=new Audit();
   	 $list=$audit->getSqlElementsFromCriteria(null, false, $crit);
   	 $delay=Parameter::getGlobalParameter('alertCheckTime');
   	 if (! $delay or $delay < 30) { $delay==30 ;}
   	 foreach ($list as $audit) {
   	 	 $duration=strtotime(date('Y-m-d'))-strtotime($audit->lastAccess);
       if ($duration>5*$delay) { // Very old connection, idle now, must be closed
    	 	 //$audit->requestDisconnection=1;
         $audit->idle=1;
   	 	   $audit->disconnection=$audit->lastAccess;
         $res=$audit->save();
   	   } 
   	 }    	 
   }    
}
?>