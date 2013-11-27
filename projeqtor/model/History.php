<?php 
/* ============================================================================
 * History reflects all changes to any object.
 */ 
class History extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $refType;
  public $refId;
  public $operation;
  public $colName; 
  public $oldValue;
  public $newValue;
  public $operationDate;
  public $idUser;
  
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

  /** ===========================================================================
   * Store a new History trace (will call ->save)
   * @param $refType type of object updated
   * @param $refId id of object updated
   * @param $operation 
   * @param $colName name of column updated
   * @param $oldValue old value of column (before update)
   * @param $newValue new value of column (after update)
   * @return boolean true if save is OK, false either
   */
  public static function store ($obj, $refType, $refId, $operation, $colName=null, $oldValue=null, $newValue=null) {
  	$user=(array_key_exists('user',$_SESSION))?$_SESSION['user']:new User();
    $hist=new History();
    // Attention : History fields are not to be escaped by Sql::str because $olValue and $newValue have already been escaped
    // So other fiels (names) must be manually "quoted"
    $hist->refType=$refType;
    if ($refType=='TicketSimple') {
      $hist->refType='Ticket';
    }
    $hist->refId=$refId;
    $hist->operation=$operation;
    $hist->colName=$colName;
    $hist->oldValue=$oldValue;
    $hist->newValue=$newValue;
    $hist->idUser=$user->id;
    $returnValue=$hist->save();
    // For TestCaseRun : store history for TestSession 
    if ($refType=='TestCaseRun') {
    	self::store ($obj, 'TestSession', $obj->idTestSession, $operation , $colName. '|' . 'TestCase' . '|' .$obj->idTestCase, $oldValue, $newValue);
    } else if ($refType=='Link') {       
    // For link : store History for both referenced items
      self::store ($obj, $obj->ref1Type, $obj->ref1Id, $operation , 'Link' . '|' . $colName. '|' . $obj->ref2Type . '|' . $obj->ref2Id, $oldValue, $newValue);
      self::store ($obj, $obj->ref2Type, $obj->ref2Id, $operation , 'Link' . '|' . $colName. '|' . $obj->ref1Type . '|' . $obj->ref1Id, $oldValue, $newValue);
    } else if ($refType=='Note') {
    	if ($operation=='insert') {
    		$newValue=$obj->note;
    	} else if ($operation=='delete') {
        $oldValue=$obj->note;
      }
    	if ($colName!="updateDate") {    
        self::store ($obj, $obj->refType, $obj->refId, $operation , $colName. '|' . $refType . '|' . $obj->id, $oldValue, $newValue);
    	}
    } else if ($refType=='Attachement') {
      if ($operation=='insert') {
        $newValue=$obj->fileName;
      } else if ($operation=='delete') {
        $oldValue=$obj->fileName;
      }
      if ($colName!="updateDate") {    
        self::store ($obj, $obj->refType, $obj->refId, $operation , $colName. '|' . $refType . '|' . $obj->id, $oldValue, $newValue);
      }
    } 
    if (strpos($returnValue,'<input type="hidden" id="lastOperationStatus" value="OK"')) {
      return true;
    } else {
      return false;
    }
  }
}
?>