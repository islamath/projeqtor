<?php 
/** ============================================================================
 * abstract class to define a view as a direct Sql resource
 */ 
abstract class SqlDirectElement {

  public $request;

  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct() {
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
  }

    /** ==========================================================================
     * Returns an array (lines) of corresponing objects
     * @param string $query Query to get Lines
     * @return SqlElement[]
     */
  public function getLines($query) {
    $result = Sql::query($query); 
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine($result);
      while ($line) {
        $obj=clone($this);
        // get all data fetched
        foreach ($obj as $col_name => $col_value) {
          if (substr($col_name,0,1)=="_") {
            // not a fiels, just for presentation purpose
          } else if (ucfirst($col_name) == $col_name) {
            $obj->getDependantSqlElement($col_name);
          } else {
            $obj->{$col_name}=$line[$obj->getDatabaseColumnName($col_name)];
          }
        }
        $objects[]=$obj;
        $line = Sql::fetchLine($result);
      }
    } else {
      if ($initializeIfEmpty) {
        $objects[]=$defaultObj; // return at least 1 element, initialized with criteria
      }
    }
    return $objects;
  }
  
  static public function execute($query) {
    $result = Sql::query($query); 
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
}
?>