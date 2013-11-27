<?php 
/** ============================================================================
 * Light view of ticket, for simple definition.
 */ 
class TicketSimple extends Ticket {

	public $_noDisplayHistory=true;
	
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="name" width="50%" >${name}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="actualDueDateTime" width="10%" formatter="dateTimeFormatter">${dueDate}</th>
    <th field="handled" width="5%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array(
    "actualDueDateTime"=>"readonly",
    "creationDateTime"=>"readonly",
    "done"=>"hidden",
    "doneDateTime"=>"hidden",
    "externalReference"=>"hidden",
    "handled"=>"hidden",
    "handledDateTime"=>"hidden",
    "id"=>"nobr", 
    "idle"=>"hidden",  
    "idleDateTime"=>"hidden",                             
    "idActivity"=>"hidden",
    "idContact"=>"hidden",
    "idContext1"=>"nobr,size1/3,title",
    "idContext2"=>"nobr,title", 
    "idContext3"=>"title",
    "idCriticality"=>"hidden",
    "idPriority"=>"hidden",
    "idProject"=>"required",
    "idResource"=>"readonly",
    "idStatus"=>"required,readonly",
    "idTicket"=>"hidden",
    "idTicketType"=>"hidden",
    "idUser"=>"hidden",
    "initialDueDateTime"=>"hidden",
    "name"=>"required",                               
    "Origin"=>"hidden",
    "reference"=>"readonly",
    "result"=>"readonly",
    "idTargetVersion"=>"readonly",
    "WorkElement"=>"hidden", "_Link"=>"hidden"
  );  
    
  private static $_colCaptionTransposition = array('idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
                                                   'idActivity' => 'planningActivity',
                                                   'idContact' => 'requestor',
                                                   'idTargetVersion'=>'targetVersion',
                                                   'idOriginalVersion'=>'version',
                                                   'idTicket'=>'duplicateTicket',
                                                   'idContext1'=>'idContext',
                                                   'actualDueDateTime'=>'dueDate');
  
  private static $_databaseColumnName = array('idTargetVersion'=>'idVersion');

  private static $_databaseTableName = 'ticket';
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    unset($this->_Link);
    unset($this->WorkElement);
    unset($this->_col_1_1_Link);
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
  
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
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
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }

  public function save() {
  	//$old=new Ticket($this->id);
  	$user=$_SESSION['user'];
  	if (! $this->id) {
  	  if (! trim($this->idContact) and $user->isContact) {
  		  $this->idContact=$user->id;
  	  }
  	  $this->idUser=$user->id;
  	  $lst=SqlList::getList('TicketType');
  	  foreach ($lst as $id=>$val) {
  	    $this->idTicketType=$id;
  	    break;
  	  }
  	}
  	$result=parent::save();
  	return $result;
  }

  public function deleteControl() { 
    $result='';
    $crit=array('refType'=>'Ticket', 'refId'=>$this->id);
    $this->WorkElement=SqlElement::getSingleSqlElementFromCriteria('WorkElement', $crit);
    if ($this->WorkElement and $this->WorkElement->realWork>0) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  
  public function getTitle($col) {
  	if (substr($col,0,9)=='idContext') {
  	  return SqlList::getNameFromId('ContextType', substr($col, 9));
  	} else {
  		return parent::getTitle($col);
  	} 
  	
  }
  
}
?>