<?php 
/** ============================================================================
 * creation of the description of the content for a bill.
 */ 
class Bill extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $idBillType;
  public $name;
  public $date;
  public $idProject;
  public $idClient;
  public $idContact;
  public $idRecipient;
  public $_spe_billingType;
  public $_col_2_2_treatment;  
  public $billId;
  public $idStatus;
  public $done;
  public $idle;
  public $cancelled;
  public $_lib_cancelled;
  public $untaxedAmount;
  public $tax;
  public $fullAmount;
  public $description;
  public $billingType;
  public $_BillLine=array();
  public $_Note=array();


  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameClient" width="14%" >${idClient}</th>
    <th field="nameProject" width="14%" >${idProject}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="date" formatter="dateFormatter" width="10%" >${date}</th>  
    <th field="nameRecipient" width="14%" >${idRecipient}</th>
    <th field="fullAmount" width="8%" >${fullAmount}</th>
    <th field="billId" width="5%" >${billId}</th>
    <th field="done" formatter="booleanFormatter" width="5%" >${done}</th>
    <th field="idle" formatter="booleanFormatter" width="5%" >${idle}</th>
    ';
  
  private static $_fieldsAttributes=array('name'=>'required',
  										'idStatus'=>'required',
                      'idBillType'=>'required',
                      'idProject'=>'required',
  										'billId'=>'readonly',
  										'idPrec'=>'required',
                      'billingType'=>'hidden',
                      'fullAmount'=>'readonly',
                      'untaxedAmount'=>'readonly',
                      "idle"=>"nobr",
                      "cancelled"=>"nobr"
                      );  
  
  private static $_colCaptionTransposition = array('description'=>'comment',
                                                   'idContact'=>'billContact');
  
  private static $_databaseColumnName = array();
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    
    if ($this->done) {
    	self::$_fieldsAttributes['idClient']='readonly';
    	self::$_fieldsAttributes['idBillType']='readonly';
    	self::$_fieldsAttributes['date']='readonly';
    	self::$_fieldsAttributes['idProject']='readonly';
    	self::$_fieldsAttributes['idRecipient']='readonly';
    	self::$_fieldsAttributes['idContact']='readonly';
    	self::$_fieldsAttributes['tax']='readonly';
    }
    if (count($this->_BillLine)) {
    	self::$_fieldsAttributes['idProject']='readonly';
    }
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
  

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
  	
  	$result="";
    
    // When bill is done
    if ( $this->done ) {
    	// some data is mandatory
      if ( ! $this->date ){
    	  $result.="<br/>" . i18n('messageMandatory',array(i18n('colDate')));
      }
      if ( ! trim($this->idClient) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdClient')));
      }
      if ( ! trim($this->idContact) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdContact')));
      }
      if ( ! trim($this->idRecipient) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdRecipient')));
      }
      // Lines must exist when bill is done
    	if(!$this->id) {
    		$result.="<br/>" . i18n('errorEmptyBill');
    	} else {   	
    		$line = new BillLine();
    		$crit = array("refId"=>$this->id);
    		$lineList = $line->getSqlElementsFromCriteria($crit,false);
    		if (count($lineList)==0) {
    			$result.="<br/>" . i18n('errorEmptyBill');
    		}
    	}
    }
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  

  /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl()
  {
  	$result="";
  	
  	// Cannot delete done bill
  	$status=new Status($this->idStatus);
  	if ($status->setDoneStatus)	{
  		$result .= "<br/>" . i18n("errorDeleteDoneBill");
  	}
  	
  	// Cannot delete bill with lines
    $line = new BillLine();
    $crit = array("refId"=>$this->id);
    $lineList = $line->getSqlElementsFromCriteria($crit,false);
    if (count($lineList)>0) {
      $result.="<br/>" . i18n('errorControlDelete') . "<br/>&nbsp;-&nbsp;" . i18n('BillLine') . " (" . count($lineList) . ")";; ;
    }
  	
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
  /** =========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */  
  public function delete()
  {
  	$result = parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }	
	  return $result;
  }
    

  /** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  

	public function save() {
		$oldBill = $this->getOld();
	
		// billingType
		$proj=new Project($this->idProject);
		$type=new ProjectType($proj->idProjectType);
		$this->billingType=$type->internalData;

		// Calclate bill id
		if ($this->done and ! $this->billId) {
			$numStart=Parameter::getGlobalParameter('billNumStart');
			$bill = new Bill();
			$crit = array("done"=> "1");
			$billList = $bill->getSqlElementsFromCriteria($crit,false);
			$num=count($billList)+$numStart;
			$this->billId = $num;
		}

		// Get Client
		if (! trim($this->idClient)) {
			$this->idClient=$proj->idClient;
		}
		// get Contact
	  if (! trim($this->idContact)) {
      $this->idContact=$proj->idContact;
    }

		// Get the tax from Client / Contact / Recipient 
		if (trim($this->idClient)) {
			$client=new Client($this->idClient);
			if ($client->tax!='') {
		  	$this->tax=$client->tax;
			}
		}
	  if (trim($this->idRecipient)) {
      $recipient=new Recipient($this->idRecipient);
      if ($recipient->taxFree) {
      	$this->tax=0;
      }
    }
		
		
		// calculate amounts for bill lines
		$billLine=new BillLine();
		$crit = array("refType"=> "Bill", "refId"=>$this->id);
    $billLineList = $billLine->getSqlElementsFromCriteria($crit,false);
    $amount=0;
    foreach ($billLineList as $line) {
    	$amount+=$line->amount;
    }
    $this->untaxedAmount=$amount;
    $this->fullAmount=$amount*(1+$this->tax/100);
      
		$result= parent::save();
		return $result;
	}  

  public function drawSpecificItem($item){
  	global $print,$displayWidth;
  	$labelWidth=175; // To be changed if changes in css file (label and .label)
  	$largeWidth=( ($displayWidth+30) / 2) - $labelWidth;
    $result="";
    if ($item=='billingType') {
    	$result .="<table><tr><td class='label' valign='top'><label>" . i18n('colBillingType') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($print) {
      	$result.=i18n('billingType'.$this->billingType);
      } else {
	      $result .='<input dojoType="dijit.form.TextBox" class="input" ';
	      if ($this->billingType) {
	        $result .=' value="' .  i18n('billingType'.$this->billingType) . '"';
	      } 
	      $result.=' style="width:' . $largeWidth . 'px;"';
	      $result.=' readonly="readonly"';
	      $result .='/>';
      }
	    $result .= '</td></tr></table>';
      return $result;     
    }
  }
  
  public function simpleSave() {
     return parent::save();
  }
}
?>