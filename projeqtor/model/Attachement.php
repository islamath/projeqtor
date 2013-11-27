<?php
/* ============================================================================
 * Attachement is an object that can be included in all objects, 
 * to trace file uploads and link it to objects.
 */ 
class Attachement extends SqlElement {

  public $id;
  public $refType;
  public $refId;
  public $idUser;
  public $creationDate;
  public $fileName;
  public $description;
  public $subDirectory;
  public $mimeType; 
  public $fileSize;
  public $type;
  public $link;
  public $idPrivacy;
  public $idTeam;
  
  //public $_noHistory=true; // Will never save history for this object
  
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
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
  }
  
  public function delete() {
  	$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$paramAttachementDirectory=Parameter::getGlobalParameter('paramAttachementDirectory');
  	return parent::delete();
  	$subDirectory=str_replace('${attachementDirectory}', $paramAttachementDirectory, $this->subDirectory);
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
  	enableCatchErrors();
  	if (file_exists($subDirectory . $paramPathSeparator . $this->fileName)) {
  	  unlink($subDirectory . $paramPathSeparator . $this->fileName);
  	}
  	if (file_exists($subDirectory)) {
  		purgeFiles($subDirectory, null);
  	  rmdir($subDirectory);
  	}
  	disableCatchErrors();
  }
   
  public function getFullPathFileName() {
  	$path = str_replace('${attachementDirectory}', Parameter::getGlobalParameter('paramAttachementDirectory'), $this->subDirectory);
  	$name = $this->fileName;
  	$file = $path . $name;
  	return $file;
  }
  
  public function isThumbable() {
    return isThumbable($this->fileName);
  }
}
?>