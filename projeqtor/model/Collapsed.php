<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class Collapsed extends SqlElement {

  // Define the layout that will be used for lists
   public $scope;
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

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  

  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */

  public static function collapse($scope) {
  	$userId=self::getUserId();
  	$crit=array('scope'=>$scope, 'idUser'=>$userId);
    $col=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
    if (!$col or !$col->id) {
      $col=new Collapsed();
      $col->scope=$scope;
      $col->idUser=$userId;
      $col->save();
    }
    $list=self::getCollaspedList();
    $list[$scope]=true;
    self::setCollaspedList($list);
  }
  
  public static function expand($scope) {
  	$userId=self::getUserId();
  	$crit=array('scope'=>$scope, 'idUser'=>$userId);
    $col=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
  	if ($col and $col->id) {
  		$col->delete();
  	}
    $list=self::getCollaspedList();
    if (array_key_exists($scope, $list)) {
      unset($list[$scope]);
    }
    self::setCollaspedList($list);
  }
  
  private static function getUserId() {
  	if (array_key_exists('user', $_SESSION)) {
  		$user=$_SESSION['user'];
  		return $user->id;
  	} else {
  		return null;
  	}
  }
  
  public static function getCollaspedList() {
    if (! array_key_exists('collapsed', $_SESSION) ) {
      self::initialiseCollapsedList();
    }
    return $_SESSION['collapsed'];
  }
  
  private static function setCollaspedList($list) { 
  	$_SESSION['collapsed']=$list;
  }
  
  private static function initialiseCollapsedList() {
  	$list=array();
  	$crit=array('idUser'=>self::getUserId());
  	$col=new Collapsed();
  	$listCol=$col->getSqlElementsFromCriteria($crit, false);
  	foreach($listCol as $col) {
  		$list[$col->scope]=true;
  	}
  	self::setCollaspedList($list);
  }
}  
?>