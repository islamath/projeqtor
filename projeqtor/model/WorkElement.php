<?php

/* ============================================================================
 * Client is the owner of a project.
 */

class WorkElement extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place
  public $refType;
  public $refId;
  public $idActivity;
  public $refName;
  public $plannedWork;
  public $realWork;
  public $_spe_run;
  public $idUser;
  public $ongoing;
  public $ongoingStartDateTime;
  public $leftWork;
  public $done;
  public $idle;
  private static $_fieldsAttributes = array("refType" => "hidden", "refId" => "hidden", "refName" => "hidden",
        "realWork" => "", "ongoing" => "hidden", "ongoingStartDateTime" => "hidden",
        "idUser" => "hidden", "idActivity" => "hidden",
        "leftWork" => "readonly", "done" => "hidden", "idle" => "hidden");
  private static $_colCaptionTransposition = array('plannedWork' => 'estimatedWork');

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
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    if (! $this->id) {
      self::lockRealWork();
    }
    return self::$_fieldsAttributes;
  }

  public static function lockRealWork() {
    self::$_fieldsAttributes['realWork']='readonly';
  }
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }

  public function save() {
    $old=$this->getOld();
    if (!array_key_exists('user', $_SESSION)) return parent::save();
    $user = $_SESSION['user'];
    // Update left work
    $this->leftWork = $this->plannedWork - $this->realWork;
    if ($this->leftWork < 0 or $this->done) {
      $this->leftWork = 0;
    }
    
    if ($this->refType) {
      $top = new $this->refType($this->refId);
      $topProject=$top->idProject;
      if (isset($top->idActivity)) {
        $this->idActivity=$top->idActivity;
      }
    } else {
      //$top = new Project();
      $topProject=$this->idProject;
    }
    if ($top and isset($top->done) and $top->done == 1) {
      $this->leftWork = 0;
      $this->done=1;
    }
    if ($top and property_exists($top, 'idActivity')) {
      $this->idActivity = $top->idActivity;
      // Check if changed Planning Activity
      if (! trim($old->idActivity) and $old->idActivity != $this->idActivity) {
        $crit = array('refType' => $this->refType, 'refId' => $this->refId);
        $work = new Work();
        $workList = $work->getSqlElementsFromCriteria($crit);
        foreach ($workList as $work) {
          $work->refType = 'Activity';
          $work->refId = $this->idActivity;
        	$work->idAssignment=$this->updateAssignment($work,$work->work);
        	$work->save();
        }
      }
    }
    $result = parent::save();
    $diff = $this->realWork - $old->realWork;
    if ($diff != 0) {
      // Set work to Ticket
      $idx=-1;
      if ($this->idActivity) {
      	$crit=array('refType'=>'Activity', 'refId'=>$this->idActivity, 'idResource'=>$user->id, 'idProject'=>$topProject);
      } else {
        $crit=array('refType'=>$this->refType, 'refId'=>$this->refId, 'idResource'=>$user->id, 'idProject'=>$topProject);
      }
      if ($diff>0) {
        $crit['workDate']=date('Y-m-d');
      }
      $work=new Work();
      $workList=$work->getSqlElementsFromCriteria($crit,true,null,'day asc');
      if (count($workList)>0) {
      	$idx=count($workList)-1;
        $work=$workList[$idx];       
      } else {
        $work=new Work();
        $work->refType=$this->refType;
        $work->refId=$this->refId;
        $work->idResource=$user->id;
        $work->idProject=$topProject;
        $work->dailyCost=0;
        $work->cost=0;
      }
      if ($diff>0) {
	      $work->work+=$diff;
	      $work->setDates(date('Y-m-d'));
	      if ($work->work<0) {$work->work=0;}
        if (! $work->refType) {
          $work->refType=$this->refType;
          $work->refId=$this->refId;
        }
        $work->idProject=$topProject;
        $work->idAssignment=$this->updateAssignment($work,$diff);
        $work->save();
      } else {
      	while ($diff<0 and $idx>=0) {
      		$valDiff=0;
      		if ($work->work + $diff >= 0) {
      			$valDiff=$diff;
      			$work->work += $diff;
      			$diff=0;
      		} else {
      			$valDiff=(-1)*$work->work;
       			$diff+=$work->work;
       			$work->work=0;
       		}
       		$work->idAssignment=$this->updateAssignment($work,$valDiff);
       	  if ($work->work==0) {
            if ($work->id) {
            	$work->delete();
            }
          } else {
            $work->save();
          }       
       		$idx--;
       		if ($idx>=0) { 
       			$work=$workList[$idx];
       		} else if (array_key_exists('idResource', $crit)) {
       			unset($crit['idResource']);
       			$work=new Work();
            $workList=$work->getSqlElementsFromCriteria($crit,true,null,'day asc');
            if (count($workList)>0) {
              $idx=count($workList)-1;
              $work=$workList[$idx]; 
            } 
       	  }
       	}
      }
    }
    return $result;
  }

  private function updateAssignment($work, $diff) {
  	if ($work->refType!='Activity') {
  		return null;
  	}
  	$ass =new Assignment();
    $crit = array('refType' => $work->refType, 'refId' => $work->refId, 'idResource' => $work->idResource);
    $lstAss = $ass->getSqlElementsFromCriteria($crit);
    if (count($lstAss) > 0) {
      $ass = $lstAss[count($lstAss) - 1];
    } else {
      $ass = new Assignment();
      $ass->refType = $work->refType;
      $ass->refId = $work->refId;
      $ass->idResource = $work->idResource;
    }
    $ass->leftWork-=$diff;
    $ass->realWork+=$diff;
    if ($ass->leftWork<0 or $ass->leftWork==null) {$ass->leftWork=0;}
    if ($ass->realWork<0) {$ass->realWork=0;}
    $ass->save();
    return $ass->id;
  }
  
  public function start() {
    // First, stop all ongoing work
    $_SESSION['user']->stopAllWork();
    // Then start current work
    $this->idUser = $_SESSION['user']->id;
    $this->ongoing = 1;
    $this->ongoingStartDateTime = date('Y-m-d H:i');
    $this->save();
    // save to database
  }

  /**
   *
   */
  public function stop() {
    $start = $this->ongoingStartDateTime;
    $stop = date('Y-m-d H:i');
    $work = workTimeDiffDateTime($start, $stop);
    $this->realWork+=$work;
    $this->idUser = null;
    $this->ongoing = 0;
    $this->ongoingStartDateTime = null;
    $this->save();
  }

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are :
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item) {
    global $print, $comboDetail;
    $result = "";
    $refObj = new $this->refType($this->refId);
    if ($item == 'run' and !$comboDetail and !$this->idle) {
      if ($print) {
        return "";
      }
      $user = $_SESSION['user'];
      $title = i18n('startWork');
      if ($this->ongoing) {
        $title = i18n('stopWork');
      }
      $canUpdate = (securityGetAccessRightYesNo('menu' . $this->refType, 'update', $refObj) == 'YES');
      if ($user->isResource and $canUpdate) {
        $result .= '<div style="position: absolute; right: 12px; top : 175px;
                     border: 0px solid #FFFFFF; -moz-border-radius: 15px; border-radius: 15px; text-align: right;">';
        $result .= '<button id="startStopWork" dojoType="dijit.form.Button" showlabel="true"';
        if (($this->ongoing and $this->idUser != $user->id) or !$user->isResource) {
          $result .= ' disabled="disabled" ';
        }
        $result .= ' title="' . $title . '" style="vertical-align: middle;">';
        $result .= '<span>' . $title . '</span>';
        $result .= '<script type="dojo/connect" event="onClick" args="evt">';
        $result .= '    loadContent("../tool/startStopWork.php?action=' . (($this->ongoing) ? 'stop' : 'start') . '","resultDiv","objectForm",true);';
        $result .= '</script>';
        $result .= '</button><br/>';
      }
      if ($this->ongoing) {
        if ($this->idUser == $user->id) {
          //$days = workDayDiffDates($this->ongoingStartDateTime, date('Y-m-d H:i'));
          if (substr($this->ongoingStartDateTime,0,10) != date('Y-m-d') ) {
            //$result .= i18n('workStartedSince', array($days));
            $result .= i18n('workStartedAt', array(substr($this->ongoingStartDateTime, 11, 5).' ('.htmlFormatDate(substr($this->ongoingStartDateTime,0,10)).')'));
          } else {
            $result .= i18n('workStartedAt', array(substr($this->ongoingStartDateTime, 11, 5)));
          }
        } else {
          $result .= i18n('workStartedBy', array(SqlList::getNameFromId('Resource', $this->idUser)));
        }
      }
      $result .= '</div>';
      return $result;
      return $result;
    }
    return $result;
  }
}

?>
