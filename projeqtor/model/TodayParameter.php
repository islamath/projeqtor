<?php 
/* ============================================================================
 * Parameter is a global kind of object for parametring.
 * It may be on user level, on project level or on global level.
 */ 
class TodayParameter extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place 
  public $idUser;
  public $idReport;
  public $idToday;
  public $parameterName;
  public $parameterValue;
  
  public static $staticList=array('Projects','AssignedTasks','ResponsibleTasks','IssuerRequestorTasks','ProjectsTasks');
  public $_noHistory=true; // Will never save history for this object
  
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
// GET VALIDATION SCRIPT
// ============================================================================**********

  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  
  static public function returnReportParameters($report, $includeAllBooleans=false) {
    $result=array();
    $currentWeek=weekNumber(date('Y-m-d'));
    if (strlen($currentWeek)==1) {
      $currentWeek='0' . $currentWeek;
    }
    $currentYear=strftime("%Y") ;
    $currentMonth=strftime("%m") ;
    $param=new ReportParameter();
    $crit=array('idReport'=>$report->id);
    $listParam=$param->getSqlElementsFromCriteria($crit,false,null,'sortOrder');
    foreach ($listParam as $param) {
      if ($param->paramType=='week') {
        $result['periodType']='week';
        $result['periodValue']=($param->defaultValue=='currentWeek')?$currentYear . $currentWeek:$param->defaultValue;
        $result['yearSpinner']=substr($result['periodValue'],0,4);
        $result['weekSpinner']=substr($result['periodValue'],4,2);
      } else if ($param->paramType=='month') {
        $result['periodType']='month';
        $result['periodValue']=($param->defaultValue=='currentMonth')?$currentYear . $currentMonth:$param->defaultValue;
        $result['yearSpinner']=substr($result['periodValue'],0,4);
        $result['monthSpinner']=substr($result['periodValue'],4,2);
      } else if ($param->paramType=='year') {
        $result['periodType']='year';
        $result['periodValue']=($param->defaultValue=='currentYear')?$currentYear:$param->defaultValue;
        $result['yearSpinner']=$result['periodValue'];
      } else if ($param->paramType=='date') {
        $result[$param->name]=($param->defaultValue=='today')?date('Y-m-d'):$param->defaultValue;
      } else if ($param->paramType=='periodScale') {
        $result[$param->name]=$param->defaultValue;
      } else if ($param->paramType=='boolean') {
        if ($param->defaultValue=='true') {
        	$result[$param->name]=true;
        } else if ($includeAllBooleans) {
        	$result[$param->name]=$param->defaultValue;
        }
      } else if ($param->paramType=='projectList') {
        $defaultValue='';
        if ($param->defaultValue=='currentProject') {       
          if (array_key_exists('project',$_SESSION)) {
            if ($_SESSION['project']!='*') {
              $defaultValue=$_SESSION['project'];
            }
          }
        } else if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='productList') {
        $defaultValue='';
        if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='userList') {
        $defaultValue='';
        if ($param->defaultValue=='currentUser') {
          if (array_key_exists('user',$_SESSION)) {
            $user=$_SESSION['user'];
            $defaultValue=$user->id;
          }
        } else if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='versionList') {
        $defaultValue=$param->defaultValue;
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='testSessionList') {
        $defaultValue=$param->defaultValue;
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='resourceList') {
        $defaultValue='';
        if ($param->defaultValue=='currentResource') {
          if (array_key_exists('project',$_SESSION)) {
            $user=$_SESSION['user'];
            $defaultValue=$user->id;
          }
        } else if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='requestorList') {
        $defaultValue='';
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='showDetail') {
        $defaultValue='';
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='ticketType') {
        $defaultValue='';
        if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='objectList') {
        $defaultValue='';
        if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else if ($param->paramType=='id') {
        $defaultValue='';
        if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      } else {
        $defaultValue='';
        if ($param->defaultValue) {
          $defaultValue=$param->defaultValue; 
        }
        $result[$param->name]=$defaultValue;
      }
    }
    return $result;
  }
  
  static public function returnTodayReportParameters($today) {
  	$tp=new TodayParameter();
    $tpList=$tp->getSqlElementsFromCriteria(array('idToday'=>$today->id));
    $result=array();
    foreach ($tpList as $tp) {
    	$result[$tp->parameterName]=$tp->parameterValue;
    }
    return $result;
  }
}
?>