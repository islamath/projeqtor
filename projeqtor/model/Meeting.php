<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Meeting extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idMeetingType;
  public $idPeriodicMeeting;
  public $isPeriodic;
  public $periodicOccurence;
  public $meetingDate;
  public $_lib_from;
  public $meetingStartTime;
  public $_lib_to;
  public $meetingEndTime;
  public $name;
  public $location;
  public $_sec_Assignment;
  public $_Assignment=array();
  public $attendees;
  public $_spe_buttonSendMail;
  public $idUser;
  public $description;
  public $_col_2_2_treatment;
  public $MeetingPlanningElement;
  public $idActivity;
  public $idStatus;
  public $idResource;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  //public $_sec_linkDecision;
  //public $_Link_Decision=array();
  //public $_sec_linkQuestion;
  //public $_Link_Question=array();
  public $_col_1_1_link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();


  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameMeetingType" width="15%" >${idMeetingType}</th>
    <th field="meetingDate" formatter="dateFormatter" width="15%" >${meetingDate}</th>
    <th field="name" width="25%" >${name}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="handled" width="5%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "idProject"=>"required",
                                  "idMeetingType"=>"required",
                                  "meetingDate"=>"required, nobr",
                                  "_lib_from"=>'nobr',
                                  "_lib_to"=>'nobr',
                                  "meetingStartTime"=>'nobr',
                                  "idUser"=>"hidden",
                                  //"idResource"=>"idden",
                                  "idStatus"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
																  "idPeriodicMeeting"=>"hidden",
																  "isPeriodic"=>"readonly",
																  "periodicOccurence"=>"hidden",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('result'=>'minutes', 
  'idResource'=>'responsible', 
  'idActivity'=>'parentActivity',
  'attendees'=>'otherAttendees');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
    
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

  public function setAttributes() {
    if ($this->isPeriodic) {
      $this->idActivity=null;
      self::$_fieldsAttributes['idActivity']='hidden';
      self::$_fieldsAttributes['isPeriodic']='readonly';
      self::$_fieldsAttributes['periodicOccurence']='display';
    } else {
    	self::$_fieldsAttributes['isPeriodic']="readonly";
    	unset($this->isPeriodic);
    }  	
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="initialDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    } else     if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("idleDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '    if (! dijit.byId("done").get("checked")) {';
      $colScript .= '      dijit.byId("done").set("checked", true);';
      $colScript .= '    }';  
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="done") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("doneDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("doneDate").set("value", null); ';
      $colScript .= '    if (dijit.byId("idle").get("checked")) {';
      $colScript .= '      dijit.byId("idle").set("checked", false);';
      $colScript .= '    }'; 
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }

  public function drawSpecificItem($item){
    global $print;
    $result="";
    if ($item=='buttonSendMail') {
      if ($print) {
        return "";
      }
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="sendMailToAttendees" dojoType="dijit.form.Button" showlabel="true"';
      $result .= ' title="' . i18n('sendMailToAttendees') . '" >';
      $result .= '<span>' . i18n('sendMailToAttendees') . '</span>';
      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
      $result .= '   if (checkFormChangeInProgress()) {return false;}';
      $result .=  '  loadContent("../tool/sendMail.php","resultDiv","objectForm",true);';
      $result .= '</script>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    }
  }
  
  public function deleteControl() { 
    $result='';
    if ($this->MeetingPlanningElement and $this->MeetingPlanningElement->realWork>0) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }

  public function control(){
    $result="";
    if (trim($this->idActivity)) {
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
  	$old=$this->getOld();
  	if (! $this->name) {
      $this->name=SqlList::getNameFromId('MeetingType',$this->idMeetingType) . " " . $this->meetingDate;
  	}
    $listTeam=array_map('strtolower',SqlList::getList('Team','name'));
    $listName=array_map('strtolower',SqlList::getList('Affectable'));
    $listUserName=array_map('strtolower',SqlList::getList('Affectable','userName'));
    $listInitials=array_map('strtolower',SqlList::getList('Affectable','initials'));
    $this->MeetingPlanningElement->idle=$this->idle;
    $this->MeetingPlanningElement->done=$this->done;
    $this->MeetingPlanningElement->cancelled=$this->cancelled;
    if ($this->attendees) {
      $listAttendees=explode(',',str_replace(';',',',$this->attendees));
      $this->attendees="";
      foreach ($listAttendees as $attendee) {
      	$stockAttendee=$attendee;
        $attendee=strtolower(trim($attendee));
        if (in_array($attendee,$listName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listName));
          $this->attendees.='"' . $aff->name . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listUserName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listUserName));
          $this->attendees.='"' . (($aff->name)?$aff->name:$stockAttendee) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listInitials)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listInitials));         
          $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listTeam)) {
          $this->attendees.=($this->attendees)?', ':'';
          $id=array_search($attendee,$listTeam);
          $aff=new Affectable();
          $lst=$aff->getSqlElementsFromCriteria(array('idTeam'=>$id));
          foreach ($lst as $aff) {
            $this->attendees.=($this->attendees)?', ':'';
            $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
            if ($aff->email) {
              $this->attendees.=' <' . $aff->email . '>';
            }
          }
        } else {
          $this->attendees.=($this->attendees)?', ':'';
          $this->attendees.=$stockAttendee;
        }
      }
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
    }
    $this->MeetingPlanningElement->validatedStartDate=$this->meetingDate;
    $this->MeetingPlanningElement->validatedEndDate=$this->meetingDate;
    if (! $this->MeetingPlanningElement->assignedWork) {
    	$this->MeetingPlanningElement->plannedStartDate=$this->meetingDate;
      $this->MeetingPlanningElement->plannedEndDate=$this->meetingDate;
    }
    if (trim($this->idProject)!=trim($old->idProject) or trim($this->idActivity)!=trim($old->idActivity) 
    or trim($this->idPeriodicMeeting)!=trim($old->idPeriodicMeeting)) {
      $this->MeetingPlanningElement->wbs=null;
      $this->MeetingPlanningElement->wbsSortable=null;
    }
    return parent::save();
  }

  function sendMail() {
  	$paramMailSender=Parameter::getGlobalParameter('paramMailSender');
    $paramMailReplyTo=Parameter::getGlobalParameter('paramMailReplyTo');
    $paramTimezone=Parameter::getGlobalParameter('paramDefaultTimezone');
    $lstDest=explode(',',$this->attendees);
    if (count($this->_Assignment)>0) {
    	foreach ($this->_Assignment as $ass) {
    		$res=new Affectable($ass->idResource);
    		$resMail=(($res->name)?$res->name:$res->userName);
    		$resMail.=(($res->email)?' <'.$res->email.'>':'');
    		$lstDest[]=$resMail;
    	}
    }
    $lstMail=array();
    foreach ($lstDest as $dest) {
      $to="";
      $name="";
      $dest=trim($dest);
      $start=strpos($dest,'<');
      if ($start>0) {
        $end=strpos($dest,'>');
        $to=trim(substr( $dest, $start+1, $end-$start-1));
        $name=trim(substr($dest,0,$start));
      } else if (strpos($dest,'@')>0){
        $to=$dest;
        $name=$to;
      }
      if ($to) {
        if (!$name) {
          $name=$to;
        }
        $lstMail[$name]=$to;
      }
    }   
    $sent=0;
    $vcal = "BEGIN:VCALENDAR\r\n";
    //$vcal .= "PRODID:-//ProjeQtOr//Meeting//EN\r\n";
    $vcal .= "PRODID:-//Microsoft Corporation//Outlook 12.0 MIMEDIR//EN\r\n";
    $vcal .= "VERSION:2.0\r\n";
    //$vcal .= "METHOD:REQUEST\r\n";
    $vcal .= "METHOD:REQUEST\r\n";
    $vcal .= "BEGIN:VEVENT\r\n";
    $user=$_SESSION['user'];
    $vcal .= "ORGANIZER;CN=" . (($user->resourceName)?$user->resourceName:$user->name). ":MAILTO:$user->email\r\n";
    foreach($lstMail as $name=>$to) {
      //$vcal .= "ATTENDEE;CN=\"$name\";ROLE=REQ-PARTICIPANT;RSVP=FALSE:MAILTO:$to\r\n";
      //$vcal .= "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=\"$name\":MAILTO:$to\r\n";
      $vcal .= "ATTENDEE;ROLE=REQ-PARTICIPANT";
      $vcal .= ';CN='.str_replace(array("\r\n","\n","\r"," "),array("","","","_"),$name);
      $vcal .= ":MAILTO:".str_replace(array("\r\n","\n"," "),array("","",""),$to)."\r\n";
    }
    $vcal .= "UID:".date('Ymd').'T'.date('His')."-".rand()."-projeqtor.org\r\n";
    //$vcal .= "DTSTAMP:".date('Ymd').'T'.date('His')."\r\n";
    date_default_timezone_set($paramTimezone);
    $dtStart=strtotime($this->meetingDate.' '.$this->meetingStartTime);
    $dtEnd=strtotime($this->meetingDate.' '.$this->meetingEndTime);
    $vcal .= "DTSTART:".gmdate('Ymd',$dtStart).'T'.gmdate('Hi',$dtStart)."00Z\r\n";
    $vcal .= "DTEND:".gmdate('Ymd',$dtEnd).'T'.gmdate('Hi',$dtEnd)."00Z\r\n";
    if (trim($this->location) != "") $vcal .= "LOCATION:$this->location\r\n";
    $vcal .= "CATEGORIES:ProjeQtOr\r\n"; 
    $vcal .= "SUMMARY:$this->name\r\n";
    $vcal .= "PRIORITY:5\r\n";
    if (trim($this->description) != "") $vcal .= "DESCRIPTION:".str_replace(array("\r\n","\n"),array("\\n","\\n"),$this->description)."\r\n";
    /*$vcal .= "BEGIN:VALARM\r\n";
    $vcal .= "TRIGGER:-PT15M\r\n";
    $vcal .= "ACTION:DISPLAY\r\n";
    $vcal .= "DESCRIPTION:Reminder\r\n";
    $vcal .= "END:VALARM\r\n";*/
    $vcal .= "END:VEVENT\r\n";
    $vcal .= "END:VCALENDAR\r\n";
    $sender=($user->email)?$user->email:$paramMailSender;
    $replyTo=($user->email)?$user->email:$paramMailReplyTo;
    $headers = "From: $sender\r\n";
    $headers .= "Reply-To: $replyTo\r\n";
    $headers .= "MIME-version: 1.0\r\n";
    $headers .= "Content-Type: text/calendar\r\n";
    //$headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "X-Mailer: Microsoft Office Outlook 12.0";
    //mail($to, $this->description, $vcal, $headers);
    $destList="";
    foreach($lstMail as $name=>$to) {
      $destList.=($destList)?',':'';
      $destList.=$to;
      $sent++;
    }

    $result=sendMail($destList, $this->name, $vcal, $this, $headers,$sender);
    if (! $result) {
    	$sent=0;
    	$destList="";
    } 
    return str_replace(',', ', ', $destList);
  }
}
?>