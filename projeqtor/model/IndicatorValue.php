<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class IndicatorValue extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;
  public $code;
  public $type;
  public $refType;
  public $refId;
  public $idIndicatorDefinition;
  public $targetDateTime;
  public $targetValue;
  public $warningTargetDateTime;
  public $warningTargetValue;
  public $warningSent;
  public $alertTargetDateTime;
  public $alertTargetValue;
  public $alertSent;
  public $handled;
  public $done;
  public $idle;
  public $status;
  
  public $_noHistory=true;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="refType" width="20%">${name}</th>
    <th field="refId" width="20">${code}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required");
    
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
    
  static public function addIndicatorValue($def, $obj) {
  	$class=get_class($obj);
  	if (property_exists($obj, 'idStatus')) {
  	  $stat=new Status($obj->idStatus);
  	  if ($stat->isCopyStatus) { // Status "copied" : do not generate indicator alerts
  	  	return;
  	  }
  	}
  	if ($def->nameIndicatorable!=$class) {
  		errorLog("ERROR in IndicatorValue::addIndicatorValue() => incoherent class between def ($def->nameIndicatorable) and obj ($class) ");
  		return;
  	}
  	$crit=array('idIndicatorDefinition'=>$def->id, 'refType'=>$class, 'refId'=>$obj->id);
  	$indVal=new IndicatorValue();
  	$lst=$indVal->getSqlElementsFromCriteria($crit, true);
  	if (count($lst)==1) {
  		$indVal=$lst[0];
  	} else if (count($lst)==0) {
  		$indVal=new IndicatorValue();
  		$indVal->idIndicatorDefinition=$def->id;
  		$indVal->refType=$class;
  		$indVal->refId=$obj->id; 		
  		$indVal->warningSent='0';
  		$indVal->alertSent='0';
  	} else {
  		$cpt=count($lst);
      errorLog("ERROR in IndicatorValue::addIndicatorValue() => more than 1 (exactely $cpt) line of IndicatorValue for refType=$class, refId=$obj->id, idIndicatorDefinition=$def->id");
      return;  		
  	}
  	$fld="";
  	$fldVal;
  	$sub="";
    $indVal->idle=$obj->idle;
    if (property_exists($obj, 'handled')) {
      $indVal->handled=$obj->handled;
    }
    if (property_exists($obj, 'done')) {
      $indVal->done=$obj->done;
    }
    $indVal->code=$def->codeIndicator;
    $indVal->type=$def->typeIndicator;
  	$ind=new Indicator($def->idIndicator);
  	if ($ind->type=="delay") {
  		$fld=$ind->name;
  		if ($class=='Risk' or $class=='Issue') {
  			$fld=str_replace('Due','End',$fld);
  		}
  		$sub=$class . "PlanningElement";
  		if ( (substr($fld,-7)=='EndDate' or substr($fld,-9)=='StartDate') and property_exists($obj, $sub) ) {
  		  $indVal->targetDateTime=$obj->$sub->$fld;
  		  $indVal->targetDateTime.=(strlen($indVal->targetDateTime)=='10')?" 00:00:00":"";
  	  } else {
  	    $indVal->targetDateTime=$obj->$fld;
  	    $indVal->targetDateTime.=(strlen($indVal->targetDateTime)=='10')?" 00:00:00":"";
  	  }
  	  if (! trim($indVal->targetDateTime)) {
  	  	if ($indVal->id) {
  	  		$indVal->delete();
  	  	}
  	  	return;
  	  }
  	  if (trim($indVal->targetDateTime)=="00:00:00") $indVal->targetDateTime==null;
  	  $indVal->targetValue=null;
  	  $indVal->warningTargetValue=null;
  	  $indVal->alertTargetValue=null;
  	  if (trim($indVal->targetDateTime)) {
  	  	$indVal->warningTargetDateTime=addDelayToDatetime($indVal->targetDateTime, (-1)*$def->warningValue, $def->codeWarningDelayUnit);
  	    $indVal->alertTargetDateTime=addDelayToDatetime($indVal->targetDateTime, (-1)*$def->alertValue, $def->codeAlertDelayUnit);
  	  }
  	  $indVal->checkDates($obj);  	  
  	} else if ($ind->type=="percent") {
  		$indVal->checkPercent($obj,$def);
    } else {
      errorLog("ERROR in IndicatorValue::addIndicatorValue() => unknown indicator type = $ind->type");
    }
    $indVal->save();
  	
  }
  
  public function checkPercent($obj,$def) {
  	$pe=get_class($obj).'PlanningElement';
  	switch ($this->code) {
      case 'PCOVC' :   //PlannedCostOverValidatedCost
      	$this->targetValue=$obj->$pe->validatedCost;
      	$value=$obj->$pe->plannedCost;
      	break;
      case 'PCOAC' :   //PlannedCostOverAssignedCost
      	$this->targetValue=$obj->$pe->assignedCost;
      	$value=$obj->$pe->plannedCost;
      	break;
      case 'PWOVW' :   //PlannedWorkOverValidatedWork
      	$this->targetValue=$obj->$pe->validatedWork;
      	$value=$obj->$pe->plannedWork;
        break;
      case 'PWOAW' :   //PlannedWorkOverAssignedWork
      	$this->targetValue=$obj->$pe->assignedWork;
      	$value=$obj->$pe->plannedWork;
        break;
      case 'RWOVW' :   //RealWorkOverValidatedWork
        $this->targetValue=$obj->$pe->validatedWork;
        $value=$obj->$pe->realWork;
        break;
      case 'RWOAW' :   //RealWorkOverAssignedWork
        $this->targetValue=$obj->$pe->assignedWork;
        $value=$obj->$pe->realWork;
        break;        
  	}
  	$this->warningTargetValue=$this->targetValue*$def->warningValue/100;
  	$this->alertTargetValue=$this->targetValue*$def->alertValue/100;
  	if ($value>$this->warningTargetValue) {
  		if (! $this->warningSent) {
        $this->sendWarning();
        $this->warningSent='1';  
  		}		
  	} else {
  		$this->warningSent='0';  
  	}
    if ($value>$this->alertTargetValue) {
    	if (! $this->alertSent) {
        $this->sendAlert();
        $this->alertSent='1';
      }      
    } else {
    	$this->alertSent='0';
    }
    if ($obj->done) {
      if ($value>$this->targetValue) {
      	$this->status="KO";
      } else {
      	$this->status="OK";
      }   	
    }
  }
  
  public function checkDates($obj=null) {
    if ($this->type!='delay') {
  		return;
  	}
  	if (!$obj and ($this->idle or $this->done)) {
  		return;
  	} 
  	switch ($this->code) {
  		case 'IDDT' :   //InitialDueDateTime
  		case 'ADDT' :   //ActualDueDateTime
      case 'IDD' :    //InitialDueDate
      case 'ADD' :    //ActualDueDate
      	if (substr($this->code,-3)=='DDT'){
          $date=date('Y-m-d H:i:s');
      	} else {
      		$date=date('Y-m-d H:i:s');
      	}
        if ($obj and $obj->done) {
          if (substr($this->code,-3)=='DDT'){
          	$date=$obj->doneDateTime;
          } else {
          	$date=$obj->doneDate . " 00:00:00";
          }
          $this->status=($date>$this->targetDateTime)?'KO':'OK';
        }
      	break;
      case 'IED' :    //InitialEndDate
      case 'VED' :    //ValidatedEndDate
      case 'PED' :    //PlannedEndDate
      	$date=date('Y-m-d');
        if ($obj and $obj->done) {
        	$date=$obj->doneDate . " 00:00:00";
        	$this->status=($date>$this->targetDateTime)?'KO':'OK';
        }        
      	break;
      case 'ISD' :    //InitialStartDate
      case 'VSD' :    //ValidatedStartDate
      case 'PSD' :    //PlannedStartDate
      	$date=date('Y-m-d');
        if ($obj and property_exists($obj,'handledDate') and $obj->handled) {
          $date=$obj->handledDate . " 00:00:00";
          $this->status=($date>$this->targetDateTime)?'KO':'OK';
        }
        $pe=get_class($obj).'PlanningElement';
        if ($obj and property_exists($obj,$pe) and property_exists($obj->$pe,'realStartDate') and $obj->$pe->realStartDate and $obj->$pe->realStartDate<$date) {
        	$date=$obj->$pe->realStartDate . " 00:00:00";
        	$this->status=($date>$this->targetDateTime)?'KO':'OK';
        }        
        break;
  	}
    if (trim($this->warningTargetDateTime) and $date>=$this->warningTargetDateTime) {
      if (! $this->warningSent and !$this->done) {
        $this->sendWarning();
        $this->warningSent='1';
      }
    } else {
      $this->warningSent='0';
    }
    if (trim($this->alertTargetDateTime) and $date>=$this->alertTargetDateTime) {
      if (! $this->alertSent and !$this->done) {
        $this->sendAlert();
        $this->alertSent='1';
      }
    } else {
      $this->alertSent='0';
    }        
  	if (!$obj) $this->save();
  }
  
  public function sendAlert() {
  	$this->send('ALERT');
  	$this->alertSent='1';
  }
  
  public function sendWarning() {
  	$this->send('WARNING');  	
  	$this->warningSent='1';
  }
  
  public function send($type) {
  	$currency=Parameter::getGlobalParameter('currency');
  	$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
    $def=new IndicatorDefinition($this->idIndicatorDefinition);
    $obj=new $this->refType($this->refId);
    $arrayAlertDest=array();
  	if ($def->mailToUser==0 and $def->mailToResource==0 and $def->mailToProject==0
    and $def->mailToLeader==0  and $def->mailToContact==0 and $def->mailToAssigned==0
    and $def->mailToManager==0 and $def->mailToOther==0
    and $def->alertToUser==0 and $def->alertToResource==0 and $def->alertToProject==0
    and $def->alertToLeader==0  and $def->alertToContact==0 and $def->alertToAssigned==0
    and $def->alertToManager==0 ) {
      return false; // exit not a status for mail sending (or disabled) 
    }
    $dest="";
    if ($def->mailToUser or $def->alertToUser) {
      if (property_exists($obj,'idUser')) {
        $user=new User($obj->idUser);
        if ($def->alertToUser) {
        	$arrayAlertDest[$user->id]=$user->name;
        }
        $newDest = "###" . $user->email . "###";
        if ($def->mailToUser and $user->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToResource or $def->alertToResource) {
      if (property_exists($obj, 'idResource')) {
        $resource=new Resource($obj->idResource);
        if ($def->alertToResource and $resource->isUser) {
          $arrayAlertDest[$resource->id]=$resource->name;
        }
        $newDest = "###" . $resource->email . "###";
        if ($def->mailToResource and $resource->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }    
    }
    if ($def->mailToProject or $def->mailToLeader or $def->alertToProject or $def->alertToLeader) {
      $aff=new Affectation();
      $crit=array('idProject'=>$obj->idProject, 'idle'=>'0');
      $affList=$aff->getSqlElementsFromCriteria($crit, false);
      if ($affList and count($affList)>0) {
        foreach ($affList as $aff) {
          $resource=new Resource($aff->idResource);
          if ($def->alertToProject and $resource->isUser) {
          	$arrayAlertDest[$resource->id]=$resource->name;
          }
          if ($def->mailToProject) {
            $newDest = "###" . $resource->email . "###";
            if ($resource->email and strpos($dest,$newDest)===false) {
              $dest.=($dest)?', ':'';
              $dest.= $newDest;
            }
          }
          if (($def->mailToLeader or $def->alertToLeader) and $resource->idProfile) {
            $prf=new Profile($resource->idProfile);
            if ($prf->profileCode=='PL') {
            	if ($def->alertToLeader) {
            		$arrayAlertDest[$resource->id]=$resource->name;
            	}
              $newDest = "###" . $resource->email . "###";
              if ($def->mailToLeader and $resource->email and strpos($dest,$newDest)===false) {
                $dest.=($dest)?', ':'';
                $dest.= $newDest;
              }
            }
          }
        }
      }
    }
    if ($def->mailToManager or $def->alertToManager) {
      if (property_exists($obj,'idProject')) {
        $project=new Project($obj->idProject);
        $manager=new Affectable($project->idUser);
        if ($def->alertToManager) {
          $arrayAlertDest[$manager->id]=$manager->name;
        }
        $newDest = "###" . $manager->email . "###";
        if ($manager->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToAssigned or $def->alertToAssigned) {
      $ass=new Assignment();
      $crit=array('refType'=>get_class($obj),'refId'=>$obj->id);
      $assList=$ass->getSqlElementsFromCriteria($crit);
      foreach ($assList as $ass) {
        $res=new Resource($ass->idResource);
        if ($def->alertToAssigned) {
          $arrayAlertDest[$res->id]=$res->name;
        }
        $newDest = "###" . $res->email . "###";
        if ($res->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToContact or $def->alertToContact) {
      if (property_exists($obj,'idContact')) {
        $contact=new Contact($obj->idContact);
        if ($def->alertToContact and $contact->isUser) {
        	$arrayAlertDest[$contact->id]=$contact->name;
        }
        $newDest = "###" . $contact->email . "###";
        if ($def->mailToContact and $contact->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToOther) {
      if ($def->otherMail) {
        $otherMail=str_replace(';',',', $def->otherMail);
        $otherMail=str_replace(' ',',', $otherMail);
        $split=explode(',',$otherMail);
        foreach ($split as $adr) {
          if ($adr and $adr!='') {
            $newDest = "###" . $adr . "###";
            if (strpos($dest,$newDest)===false) {
              $dest.=($dest)?', ':'';
              $dest.= $newDest;
            }
          }
        }
      }
    }
    if ($dest=="" and count($arrayAlertDest)==0) {
      return false; // exit no addressees 
    }
    $dest=str_replace('###','',$dest);
    
    $paramMailMessage='${type} - ${item} #${id} - ${name}';
    $paramMailMessage.='<BR/>' . i18n('indicator') . ' : ${indicator}'; 
    
    // substituable items
    $item=i18n(get_class($obj));
    $id=$obj->id;
    $name=$obj->name;
    $status=(property_exists($obj, 'idStatus'))?SqlList::getNameFromId('Status', $obj->idStatus):"";
    $indicator=SqlList::getNameFromId('Indicator',$def->idIndicator);
    $target="";
    $warningTarget="";
    $alertTarget="";
    $value="";
    if ($this->type=="delay") {
    	$target=htmlFormatDateTime(trim($this->targetDateTime),false,true);
    	$warningTarget=htmlFormatDateTime(trim($this->warningTargetDateTime),false, true);
    	$alertTarget=htmlFormatDateTime(trim($this->alertTargetDateTime),false, true);
    } else if ($this->type=="percent") {
    	if (substr($this->code,-1)=='W') {
    	  $target=Work::displayWork($this->targetValue) . ' ' . Work::displayShortWorkUnit();
    	  $warningTarget=Work::displayWork($this->warningTargetValue) . ' ' . Work::displayShortWorkUnit();
    	  $alertTarget=Work::displayWork($this->alertTargetValue) . ' ' . Work::displayShortWorkUnit();
    	} else {
    		if ($currencyPosition=='before') {
    			$befCur=$currency;
    			$aftCur='';
    		} else {
    			$befCur='';
    			$aftCur=$currency;
    		}
    		$target=$befCur . ' ' . $this->targetValue . ' ' . $aftCur;
        $warningTarget=$befCur . ' ' . $this->warningTargetValue . ' ' . $aftCur;
        $alertTarget=$befCur . ' ' . $this->alertTargetValue . ' ' . $aftCur;
    	}
    }
    $arrayFrom=array('${type}','${item}','${id}','${name}','${status}','${indicator}');
    $arrayTo=array($type, $item, $id, $name, $status, $indicator);
    
    $title=ucfirst(i18n($type)) .' - '. $item . ' #' . $id; 
    $message='<table>';
    $message.='<tr><td colspan="3" style="border:1px solid grey">' . htmlEncode($name) . '</td></tr>';
    $message.='<tr><td width="35%" align="right" valign="top">' . i18n('colIdIndicator') . '</td><td valign="top">&nbsp;:&nbsp;</td><td valign="top">' . $indicator . '</td>';
    $message.='<tr><td width="35%" align="right">' . i18n('targetValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $target . '</td>';
    $message.=($warningTarget and $type=="WARNING")?'<tr><td width="35%" align="right">' . i18n('warningValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $warningTarget . '</td>':'';
    $message.=($alertTarget and $type=="ALERT")?'<tr><td width="35%" align="right">' . i18n('alertValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $alertTarget . '</td>':'';
    $message.=($value)?'<tr><td width="30%">' . i18n('value') . '</td><td>&nbsp;:&nbsp;</td><td>' . $value . '</td>':'';
    $message.='</table>';
    $messageMail='<html>' . "\n" .
      '<head>'  . "\n" .
      '<title>' . $title . '</title>' . "\n" .
      '</head>' . "\n" .
      '<body>' . "\n" .
      '<b>' . $title . '</b><br/>' . "\n" .
      $message . "\n" .
      '</body>' . "\n" .
      '</html>';
    $messageAlert=$message;
    $messageMail = wordwrap($messageMail, 70); // wrapt text so that line do not exceed 70 cars per line
    if ($dest!="") {     
      $resultMail=sendMail($dest, $title, $messageMail, $obj);
    }
    if (count($arrayAlertDest)>0) {
      foreach ($arrayAlertDest as $id=>$name) {     	
      	// Create alert
      	$alert=new Alert();
      	$alert->idProject=$obj->idProject;
      	$alert->refType=get_class($obj);
      	$alert->refId=$obj->id;
      	$alert->idIndicatorValue=$this->id;
      	$alert->idUser=$id;
      	$alert->alertType=$type;
      	$alert->message=$messageAlert;
      	$alert->title=$title;
      	$alert->readFlag=0;
      	$alert->alertInitialDateTime=date('Y-m-d H:i:s');
      	$alert->alertDateTime=date('Y-m-d H:i');
      	$alert->idle=0;
      	$alert->save();
      } 
    }
  	
  }
  
  public function getShortDescription() {
  	$result=SqlList::getNameFromId('IndicatorDefinition', $this->idIndicatorDefinition);
  	return $result;
  }
  
  public function getShortDescriptionArray() {
    $result=array('indicator'=>'','target'=>'');
  	$result['indicator']=SqlList::getNameFromId('IndicatorDefinition', $this->idIndicatorDefinition);
    if ($this->type=='delay') {
      $result['target']=$this->targetDateTime;
    }
    return $result;
  }
  
  public function save() {
  	$this->targetDateTime=trim($this->alertTargetDateTime);
  	if ($this->targetDateTime=='00:00' or $this->targetDateTime=='00:00:00') $this->targetDateTime='';
  	$this->warningTargetDateTime=trim($this->warningTargetDateTime);
  	if ($this->warningTargetDateTime=='00:00' or $this->warningTargetDateTime=='00:00:00') $this->warningTargetDateTime='';
  	$this->alertTargetDateTime=trim($this->warningTargetDateTime);
  	if ($this->alertTargetDateTime=='00:00' or $this->alertTargetDateTime=='00:00:00') $this->alertTargetDateTime='';
  	return parent::save();
  }
  
}
?>