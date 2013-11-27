<?php
/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */
class Importable extends SqlElement {

	// extends SqlElement, so has $id
	public $id;    // redefine $id to specify its visible place
	public $name;

	public $_isNameTranslatable = true;

	public static $importResult;
	public static $cptTotal;
	public static $cptDone;
	public static $cptUnchanged;
	public static $cptCreated;
	public static $cptModified;
	public static $cptRejected;
	public static $cptInvalid;
	public static $cptError;
	//
	public static $cptOK;
	public static $cptWarning;

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
	// MISCELLANOUS FUNCTIONS
	// ============================================================================**********
	public static function import($fileName, $class){
		require_once '../external/XLSXReader/XLSXReader.php';
		$extension=substr(strrchr($fileName,'.'),1) ;
		if (isset($_REQUEST['fileType'])) {
		  $fileType=$_REQUEST['fileType'];
		} else {
			$fileType=$extension;
		}
		
		if($extension!=$fileType){
			errorLog("ERROR - Type : File Type and Type selected are not consistent");
			errorLog("File Name : ".$fileName);
			errorLog("Type Selected : ".$fileType);
			$msg=i18n('errorImportFormat');
			self::$importResult=$msg;
			return $msg;
		}
		switch($extension){
			case "csv":
				traceLog( "Importation : File type CSV");
				traceLog("File Name : ".$fileName);
				break;
			case "xlsx":
				traceLog( "Importation : File type XSLX");
				traceLog("File Name : ".$fileName);
				break;
			default:
				errorLog("ERROR - File Type not recognized");
				errorLog("File Name : ".$fileName);
				$msg='<b>ERROR - File Type not recognized</b><br/>Import aborted<br/>Contact your administrator';
				self::$importResult=$msg;
				return $msg;
				break;
		}
		// Control that mbsting is available
		if (! function_exists('mb_detect_encoding')) {
			errorLog("ERROR - mbstring not enabled - Import cancelled");
			$msg='<b>Error - mbstring is not enabled</b><br/>Import aborted<br/>Contact your administrator';
			self::$importResult=$msg;
			return $msg;
		}
		SqlList::cleanAllLists(); // Added for Cron mode : as Cron is never stopped, Static Lists must be freshened
		set_time_limit(3600); // 60mn
		self::$cptTotal=0;
		self::$cptDone=0;
		self::$cptUnchanged=0;
		self::$cptCreated=0;
		self::$cptModified=0;
		self::$cptRejected=0;
		self::$cptInvalid=0;
		self::$cptError=0;
		self::$cptOK=0;
		self::$cptWarning=0;
		if (! class_exists($class)) {
			self::$importResult="Cron error : class '$class' is unknown";
			self::$cptError=1;
			self::$cptRejected=1;
			return "ERROR";
		}
		$obj=new $class();
		$captionArray=array();
		$captionObjectArray=array();
		$objectArray=array();
		$titleObject=array();
		$noImport=array();
		$idArray=array();
		foreach ($obj as $fld=>$val) {
			if (is_object($val)) {
				$objectArray[$fld]=$val;
				foreach ($val as $subfld=>$subval){
					$capt=$val->getColCaption($subfld);
					if ($subfld!='id' and substr($capt,0,1)!='[' and ! isset($captionArray[$capt]) ) {
						$captionArray[$capt]=$subfld;
						$captionObjectArray[$capt]=$fld;
					}
				}
			} else {
				$capt=$obj->getColCaption($fld);
				if (substr($fld,0,9)=='idContext' and strlen($fld)==10) {
          $ctx=new ContextType(substr($fld,-1));
          $val=$ctx->name;
          $captionArray[$val]=$fld;
        } else if (substr($capt,0,1)!='[' ) {
					$captionArray[$capt]=$fld;
				}
			}
		}
		switch($extension){
			case "csv":
				$data=Importable::importCSV($fileName);
				break;
			case "xlsx":
				$data=Importable::importXLSX($fileName);
				break;
			default:
				errorLog("ERROR - File Type not recognized");
				errorLog("File Name : ".$fileName);
				$msg='<b>ERROR - File Type not recognized</b><br/>Import aborted<br/>Contact your administrator';
				self::$importResult=$msg;
				return $msg;
				break;
		}

		$title=null;
		$idxId=-1;
		$htmlResult="";
		$htmlResult.='<TABLE WIDTH="100%" style="border: 1px solid black; border-collapse:collapse;">';
		foreach($data as $nbl=>$fields){
			if($nbl==0){
				$htmlResult.= "<TR>";
				$obj=new $class();
				foreach ($fields as $idx=>$caption) {
					$title[$idx]=trim($caption);
					$title[$idx]=str_replace(chr(13),'',$title[$idx]);
					$title[$idx]=str_replace(chr(10),'',$title[$idx]);
					$color="#A0A0A0";
					$colorNoImport="#A0A0FF";
					$colCaption=$title[$idx];
					$testTitle=str_replace(' ', '', $title[$idx]);
					$testIdTitle='id'.ucfirst($testTitle);
					$testCaption=$title[$idx];
					$testIdClassTitle='id'.$class.ucfirst($testTitle);
					if (property_exists($obj,$testTitle)) { // Title is directly field id
						$title[$idx]=$testTitle;
						$color="#000000";
						$colCaption=$obj->getColCaption($title[$idx]);
						if ($title[$idx]=='id') {
							$idxId=$idx;
						}
					} else if (property_exists($obj,$testIdTitle)) { // Title is field id withoud the 'id' (for external reference)
						$title[$idx]=$testIdTitle;
						$idArray[$idx]=true;
						$color="#000000";
						$colCaption=$obj->getColCaption($title[$idx]);
					} else if (array_key_exists($testCaption,$captionArray) or array_key_exists(strtolower($testCaption),$captionArray)) {
						$color="#000000";
						$colCaption=$testCaption;
						if (array_key_exists(strtolower($testCaption),$captionArray)) {$testCaption=strtolower($testCaption);}
						$title[$idx]=$captionArray[$testCaption];
						if (isset($captionObjectArray[$testCaption])) {
							$titleObject[$idx]=$captionObjectArray[$testCaption];
						}
					} else {
						foreach ($objectArray as $fld=>$subObj) {
							if (property_exists($subObj,$testTitle)) { // Title is directly field id
								$title[$idx]=$testTitle;
								$color="#000000";
								$titleObject[$idx]=$fld;
								$colCaption=$subObj->getColCaption($title[$idx]);							
							} else if (property_exists($subObj,$testIdTitle)) { // Title is field id withoud the 'id' (for external reference)
								$title[$idx]=$testIdTitle;
								$idArray[$idx]=true;
								$color="#000000";
								$titleObject[$idx]=$fld;
								$colCaption=$subObj->getColCaption($title[$idx]);
							} else if (array_key_exists($testCaption,$captionArray) or array_key_exists(strtolower($testCaption),$captionArray)) {
								$color="#000000";
								$colCaption=$testCaption;
								if (array_key_exists(strtolower($testCaption),$captionArray)) {
									$testCaption=strtolower($testCaption);
								}
								$title[$idx]=$captionArray[$testCaption];
								if (isset($captionObjectArray[$testCaption])) {
									$titleObject[$idx]=$captionObjectArray[$testCaption];
								}
							}
						}
					}

					if (isset($titleObject[$idx]) and class_exists($titleObject[$idx]) ) {
						$subObj=new $titleObject[$idx];
						if ($subObj->isAttributeSetToField($title[$idx], 'noImport')) {
							$color=$colorNoImport;
							$noImport[$idx]=true;
						}
					} else {
						if ($obj->isAttributeSetToField($title[$idx], 'noImport')) {
							$color=$colorNoImport;
							$noImport[$idx]=true;
						}
					}
					$htmlResult.= '<TH class="messageHeader" style="color:' . $color . ';border:1px solid black;background-color: #DDDDDD">' . $colCaption . "</TH>";
				}
				$htmlResult.= '<th class="messageHeader" style="color:#208020;border:1px solid black;;background-color: #DDDDDD">' . i18n('colResultImport') . '</th></TR>';
			} else {
				$htmlResult.= '<TR>';
				if (count($fields) > count($title)) {
					$line="";
					foreach($fields as $field){
						$line.=$field." ;; ";
					}
					self::$cptError+=1;
					$htmlResult.= '<td colspan="' . count($title) . '" class="messageData" style="border:1px solid black;">';
					$htmlResult.= $line;
					$htmlResult.= '</td>';
					$htmlResult.= '<td class="messageData" style="border:1px solid black;">';
					$htmlResult.= '<div class="messageERROR" >ERROR : column count is incorrect</div>';
					$htmlResult.= '</td>';
					continue;
				}
				$id = ($idxId >= 0) ? trim($fields[$idxId]) : null;
				if ($id and ! is_numeric($id)) {
				  $line="";
          foreach($fields as $field){
            $line.=$field." ;; ";
          }
					self::$cptError+=1;
          $htmlResult.= '<td colspan="' . count($title) . '" class="messageData" style="border:1px solid black;">';
          $htmlResult.= $line;
          $htmlResult.= '</td>';
          $htmlResult.= '<td class="messageData" style="border:1px solid black;">';
          $htmlResult.= '<div class="messageERROR" >ERROR : id provided is not a number</div>';
          $htmlResult.= '</td>';
          continue;
				}
				$obj = new $class($id);
				$forceInsert = (!$obj->id and $id and !Sql::isPgsql()) ? true : false;
				self::$cptTotal+=1;
				foreach ($fields as $idx => $field) {
					if (isset($noImport[$idx])) {
						$htmlResult.= '<td class="messageData" style="color:'.$colorNoImport.';border:1px solid black;">' . htmlEncode($field) . '</td>';
						continue;
					}
					if (isset($titleObject[$idx])) {
						$subClass = $titleObject[$idx];
						$subobj = new $subClass();
						$dataType = $subobj->getDataType($title[$idx]);
						$dataLength = $subobj->getDataLength($title[$idx]);
					} else {
						$dataType = $obj->getDataType($title[$idx]);
						$dataLength = $obj->getDataLength($title[$idx]);
					}
					if ($dataType == 'varchar') {
						if (strlen($field) > $dataLength) {
							$field = substr($field, 0, $dataLength);
						}
					}
					// 4.1.0 : Adaptation of date formats
					else if ($dataType == 'date') {
						if (!$field == '') {
							if ($extension=="xlsx") {								
							  $field=date('Y-m-d',XLSXReader::toUnixTimeStamp($field));
							} else {
							  $field=formatBrowserDateToDate($field); // Detect if format is correct
							}
						}
					}
					// 4.1.0 : Adaptation of date formats
					else if ($dataType=='datetime') {
						if (!$field == '') {
							if ($extension=="xlsx") {								
								$field=gmdate ('Y-m-d H:i:s',XLSXReader::toUnixTimeStamp($field));
							} else {
								$field=formatBrowserDateToDate($field); // Detect if format is correct
							}
						}
					}
					// --------------------------------------
					else if (($dataType == 'int' and substr($title[$idx], 0, 2) != 'id') or $dataType == 'decimal') {
						$field = str_replace(' ', '', $field);
					}
					if ($field == '') {
						$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
						continue;
					}
					if (strtolower($field) == 'null') {
						$field = null;
					}
					if (substr(trim($field), 0, 1) == '"' and substr(trim($field), -1, 1) == '"') {
						$field = substr(trim($field), 1, strlen(trim($field)) - 2);
					}
					if ($idx == count($fields) - 1) {
						$field = trim($field, "\r");
						$field = trim($field, "\r\n");
					}
					$field = str_replace('""', '"', $field);
					if (property_exists($obj, $title[$idx])) {
						if (substr($title[$idx], 0, 2) == 'id' and substr($title[$idx], 0, 4) != 'idle' and strlen($title[$idx]) > 2 and !is_numeric($field)) {
							$obj->$title[$idx] = SqlList::getIdFromName(substr($title[$idx], 2), $field);
						} else {
							$obj->$title[$idx] = $field;
						}
						$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
						continue;
					}
					if (isset($titleObject[$idx])) {
						$subClass = $titleObject[$idx];
						if (!is_object($obj->$subClass)) {
							$obj->$subClass = new $subClass();
						}
						$sub = $obj->$subClass;
						if (property_exists($subClass, $title[$idx])) {
							if (substr($title[$idx], 0, 2) == 'id' and substr($title[$idx], 0, 4) != 'idle' and strlen($title[$idx]) > 2 and !is_numeric($field)) {
								$obj->$subClass->$title[$idx] = SqlList::getIdFromName(substr($title[$idx], 2), $field);
							} else {
								$obj->$subClass->$title[$idx] = $field;
							}
							$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
							continue;
						}
					}
					$htmlResult.= '<td class="messageData" style="color:#A0A0A0;border:1px solid black;">' . htmlEncode($field) . '</td>';
					continue;
				}
				$htmlResult.= '<TD class="messageData" width="20%" style="border:1px solid black;">';
				//$obj->id=null;
				if ($forceInsert or !$obj->id) {
					if (property_exists($obj, "creationDate") and !trim($obj->creationDate)) {
						$obj->creationDate = date('Y-m-d');
					}
					if (property_exists($obj, "creationDateTime") and !trim($obj->creationDateTime)) {
						$obj->creationDateTime = date('Y-m-d H:i');
					}
				}
				Sql::beginTransaction();
				if ($forceInsert) { // object with defined id was not found : force insert
					$result = $obj->insert();
				} else {
					$result = $obj->save();
				}
				if (stripos($result, 'id="lastOperationStatus" value="ERROR"') > 0) {
					Sql::rollbackTransaction();
					$htmlResult.= '<span class="messageERROR" >' . $result . '</span>';
					self::$cptError+=1;
				} else if (stripos($result, 'id="lastOperationStatus" value="OK"') > 0) {
					Sql::commitTransaction();
					$htmlResult.= '<span class="messageOK" >' . $result . '</span>';
					self::$cptOK+=1;
					if (stripos($result, 'id="lastOperation" value="insert"') > 0) {
						self::$cptCreated+=1;
					} else if (stripos($result, 'id="lastOperation" value="update"') > 0) {
						self::$cptModified+=1;
					} else {
						// ???
					}
				} else {
					Sql::commitTransaction();
					$htmlResult.= '<span class="messageWARNING" >' . $result . '</span>';
					self::$cptWarning+=1;
					if (stripos($result, 'id="lastOperationStatus" value="INVALID"') > 0) {
						self::$cptInvalid+=1;
					} else if (stripos($result, 'id="lastOperationStatus" value="NO_CHANGE"') > 0) {
						self::$cptUnchanged+=1;
					} else {
						// ???
					}
				}
				$htmlResult.= '</TD></TR>';
			}
		}
		self::$cptDone=self::$cptCreated+self::$cptModified+self::$cptUnchanged;
		self::$cptRejected=self::$cptInvalid+self::$cptError;

		$htmlResult.= "</TABLE>";
		self::$importResult=$htmlResult;
		if (self::$cptError==0) {
			if (self::$cptInvalid==0) {
				$globalResult="OK";
			} else {
				$globalResult="INVALID";
			}
		} else {
			$globalResult="ERROR";
		}
		$log=new ImportLog();
		$log->name=basename($fileName);
		$log->mode="automatic";
		$log->importDateTime=date('Y-m-d H:i:s');
		$log->importFile=$fileName;
		$log->importClass=$class;
		$log->importStatus=$globalResult;
		$log->importTodo=self::$cptTotal;
		$log->importDone=self::$cptDone;
		$log->importDoneCreated=self::$cptCreated;
		$log->importDoneModified=self::$cptModified;
		$log->importDoneUnchanged=self::$cptUnchanged;
		$log->importRejected=self::$cptRejected;
		$log->importRejectedInvalid=self::$cptInvalid;
		$log->importRejectedError=self::$cptError;
		$log->save();
		return $globalResult;
	}

	public static function importXLSX($fileName){
		require_once '../external/XLSXReader.php';
		$xlsx = new XLSXReader($fileName);
		$sheet1 = $xlsx->getSheet(1);
		$data = $sheet1->getData();
		return $data;
	}

	public static function importCSV($fileName) {
		$lines=file($fileName);
		$continuedLine="";
		$title=null;
		$csvSep=Parameter::getGlobalParameter('csvSeparator');
		$data = array();
		$index=1;
		foreach ($lines as $nbl=>$line) {
			if (trim($line)=='') {
				continue;
			}
			$line = str_replace(chr(146) , "'", $line); // replace Word special quote
			if (! mb_detect_encoding($line, 'UTF-8', true) ) {
				$line=utf8_encode($line);
			}
			if(!$title){
				if (function_exists('str_getcsv')) {
					$title=str_getcsv($line,$csvSep);
				} else {
					$title=explode($csvSep,$line);
				}
				$data[0]=$title;
			}
			else{
				if ($continuedLine) {
					$line=$continuedLine.$line;
					$continuedLine="";
				}
				if (function_exists('str_getcsv')) {
					$fields=str_getcsv($line,$csvSep);
				} else {
					$fields=explode($csvSep,$line);
				}
				if (count($fields)<count($title)) {
					$continuedLine=$line;
					continue;
				}

				$data[$index]=$fields;
				$index+=1;
			}
		}
		return $data;
	}
	public static function getLogHeader() {
		$nl=Parameter::getGlobalParameter('mailEol');
		$result="";
		$result.='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'.$nl;
		$result.='<html>'.$nl;
		$result.='<head>'.$nl;
		$result.='<meta http-equiv="content-type" content="text/html; charset=UTF-8" />'.$nl;
		$result.='<title>' . i18n("applicationTitle") . '</title>'.$nl;
		$result.='<style type="text/css">'.$nl;
		$result.='body{font-family:Verdana,Arial,Tahoma,sans-serif;font-size:8pt;}'.$nl;
		$result.='table{width:100%;border-collapse:collapse;border:1px;}'.$nl;
		$result.='.messageData{font-size:90%;padding:1px 5px 1px 5px;border:1px solid #AAAAAA;vertical-align:top;background-color:#FFFFFF;}'.$nl;
		$result.='.messageHeader{border:1px solid #AAAAAA;text-align:center;font-weight:bold;background:#DDDDDD;color:#505050;}';
		$result.='.messageERROR{color:red;font-weight:bold;}';
		$result.='.messageOK{color:green;}';
		$result.='.messageWARNING{color:black;}';
		$result.='</style>'.$nl;
		$result.='</head>'.$nl;
		$result.='<body style="font-family:Verdana,Arial,Tahoma,sans-serif;font-size:8pt;">'.$nl;
		return $result;
	}
	public static function getLogFooter() {
		$nl=Parameter::getGlobalParameter('mailEol');
		$nl=(isset($nl) and $nl)?$nl:"\r\n";
		$result="";
		$result.='</body>'.$nl;
		$result.='</html>';
		return $result;
	}
}

?>