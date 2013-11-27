<?php 
include_once "../tool/projeqtor.php";
scriptLog("saveAttachement.php");
header ('Content-Type: text/html; charset=UTF-8');
/** ===========================================================================
 * Save an attachement (file) : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

// ATTENTION, this PHP script returns its result into an iframe (the only way to submit a file)
// then the iframe returns the result to resultDiv to reproduce expected behaviour
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
  $isIE=$_REQUEST['isIE'];
} 
if ($isIE and $isIE<=9) {?>
<html>
<head>   
</head>
<body onload="parent.saveAttachementAck();">
<?php } ?>
<?php 
$error=false;
$type='file';
if (! array_key_exists('attachementType',$_REQUEST)) {
    //$error=htmlGetErrorMessage('attachementType parameter not found in REQUEST');
    //errorLog('attachementType parameter not found in REQUEST');
    //$error=true;
} else {
  $type=$_REQUEST['attachementType'];
}
$attachementMaxSize=Parameter::getGlobalParameter('paramAttachementMaxSize');
$uploadedFileArray=array();
if ($type=='file') {
  if (array_key_exists('attachementFile',$_FILES)) {
    $uploadedFileArray[]=$_FILES['attachementFile'];
  } else if (array_key_exists('uploadedfile0',$_FILES)) {
  	$cnt = 0;
  	while(isset($_FILES['uploadedfile'.$cnt])){
  		$uploadedFileArray[]=$_FILES['uploadedfile'.$cnt];
  	}
  } else if (array_key_exists('attachementFiles',$_FILES) and array_key_exists('name',$_FILES['attachementFiles'])) {
    for ($i=0;$i<count($_FILES['attachementFiles']['name']);$i++) {
    	$uf=array();
    	$uf['name']=$_FILES['attachementFiles']['name'][$i];
    	$uf['type']=$_FILES['attachementFiles']['type'][$i];
    	$uf['tmp_name']=$_FILES['attachementFiles']['tmp_name'][$i];
    	$uf['error']=$_FILES['attachementFiles']['error'][$i];
    	$uf['size']=$_FILES['attachementFiles']['size'][$i];
      $uploadedFileArray[$i]=$uf;
    }
  } else {
    $error=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachementMaxSize,'paramAttachementMaxSize')));
    errorLog(i18n('errorTooBigFile',array($attachementMaxSize,'paramAttachementMaxSize')));
    //$error=true;
  }
  foreach ($uploadedFileArray as $uploadedFile) {
	  if (! $error) {
	    if ( $uploadedFile['error']!=0) {
	      $error="[".$uploadedFile['error']."] ";
	      errorLog("[".$uploadedFile['error']."] saveAttachement.php");
	      //$error=true;
	      switch ($uploadedFile['error']) {
	        case 1:
	          $error.=htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	          errorLog(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	          break;
	        case 2:
	          $error.=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachementMaxSize,'paramAttachementMaxSize')));
	          errorLog(i18n('errorTooBigFile',array($attachementMaxSize,'paramAttachementMaxSize')));
	          break;
	        case 4:
	          $error.=htmlGetWarningMessage(i18n('errorNoFile'));
	          errorLog(i18n('errorNoFile'));
	          break;
	        default:
	          $error.=htmlGetErrorMessage(i18n('errorUploadFile',array($uploadedFile['error'])));
	          errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
	          break;
	      }
	      
	    }
	  }
	  if (! $error) {
	    if (! $uploadedFile['name']) {
	      $error=htmlGetWarningMessage(i18n('errorNoFile'));
	      errorLog(i18n('errorNoFile'));
	      //$error=true;
	    }
	  }
  }
} else if ($type=='link') {
  if (! array_key_exists('attachementLink',$_REQUEST)) {
    $error=htmlGetWarningMessage(i18n('attachementLink parameter not found in REQUEST'));
    errorLog(i18n('attachementLink parameter not found in REQUEST'));
    //$error=true;
  } else {
    $link=$_REQUEST['attachementLink'];
  }
  $uploadedFileArray[]="link";
} else {
  $error=htmlGetWarningMessage(i18n('error : unknown type '));
  errorLog(i18n('error : unknown type '.$type));
  //$error=true;
}
$obj=null;
$refType="";
if (! array_key_exists('currentObject',$_SESSION)) {
  //$error=htmlGetErrorMessage('unkown current object in SESSION');
  //errorLog('unkown current object in SESSION');
} else { 
  $obj=$_SESSION['currentObject'];
}
if (! $error) {
  if (! array_key_exists('attachementRefType',$_REQUEST)) {
  	if (!$obj) {
      $error=htmlGetErrorMessage('attachementRefType parameter not found in REQUEST');
      errorLog('attachementRefType parameter not found in REQUEST');
      //$error=true;
  	} else {
  		$refType=get_class($obj);
  	} 
  } else {
    $refType=$_REQUEST['attachementRefType'];
  }
}
if ($refType=='TicketSimple') {
  $refType='Ticket';    
}
if ($refType=='User' or $refType=='Contact') {
	$refType='Resource';
}
if (! $error) {  
  if (! array_key_exists('attachementRefId',$_REQUEST)) {
  	if (!$obj) {
      $error=htmlGetErrorMessage('attachementRefId parameter not found in REQUEST');
      errorLog('attachementRefId parameter not found in REQUEST');
      //$error=true;
  	} else {
  		$refId=$obj->id;
  	} 
  } else {
    $refId=$_REQUEST['attachementRefId'];
  }
}
if (! $error) {    
  if (! array_key_exists('attachementDescription',$_REQUEST)) {
  	//$error= htmlGetErrorMessage('attachementDescrition parameter not found in REQUEST');
    //errorLog('attachementDescrition parameter not found in REQUEST');
    //$error=true;
    $attachementDescription="";
  } else {
    $attachementDescription=$_REQUEST['attachementDescription'];
  }
}
if (! array_key_exists('attachmentPrivacy',$_REQUEST)) {
	//$error='attachmentPrivacy parameter not found in REQUEST';
	//$error=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachementMaxSize,'paramAttachementMaxSize')));
  //errorLog('attachmentPrivacy parameter not found in REQUEST');
  $idPrivacy=1;
} else  {
  $idPrivacy=$_REQUEST['attachmentPrivacy'];
}

$result="";
$user=$_SESSION['user'];
Sql::beginTransaction();
foreach ($uploadedFileArray as $uploadedFile) {
  $attachement=new Attachement();
	if (! $error) {
		if ($refType=="Resource") {
			// To avoid dupplicate image (if 2 users save picture on same time)
	    $attachement->purge("refType='Resource' and refId=".$refId);
	  }
	  $attachement->refId=$refId;
	  $attachement->refType=$refType;
	  $attachement->idUser=$user->id;
	  $ress=new Resource($user->id);
	  $attachement->idTeam=$ress->idTeam;
		if ($idPrivacy) {
		  $attachement->idPrivacy=$idPrivacy;
		} else if (! $attachement->idPrivacy) {
		  $attachement->idPrivacy=1;
		}
	  $attachement->creationDate=date("Y-m-d H:i:s");
	  if ($type=='file') {
	    $attachement->fileName=basename($uploadedFile['name']);
	    if (strtolower(substr($attachement->fileName,-4))=='.php') {
	    	$attachement->fileName.=".projeqtor";
	    }
	    $attachement->mimeType=$uploadedFile['type'];
	    $attachement->fileSize=$uploadedFile['size'];
	  } else if ($type=='link') {
	    $attachement->link=$link;
	    $attachement->fileName=urldecode(basename($link));
	  }
	  $attachement->type=$type;
	  $attachement->description=$attachementDescription;
	  $subResult=$attachement->save();
	  $newId=$attachement->id;
	  if (! $result) {
	  	$result=$subResult;
	  } else {
	  	$pos=strpos($result, '#');
	  	if ($pos) {
	  	  $result=substr_replace($result, '#'.$newId.', #', $pos,1);
	  	} 
	  } 
	} 
	$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
	$attachementDirectory=Parameter::getGlobalParameter('paramAttachementDirectory');
	if (! $error and $type=='file') {
	  $uploaddir = $attachementDirectory . $pathSeparator . "attachement_" . $newId . $pathSeparator;
	  if (! file_exists($uploaddir)) {
	    mkdir($uploaddir,0777,true);
	  }
	  $uploadfile = $uploaddir . $attachement->fileName;
	  if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
	     $error = htmlGetErrorMessage(i18n('errorUploadFile','hacking ?'));
	     errorLog(i18n('errorUploadFile','hacking ?'));
	     //$error=true;
	     $attachement->delete(); 
	  } else {
	    $attachement->subDirectory=str_replace(Parameter::getGlobalParameter('paramAttachementDirectory'),'${attachementDirectory}',$uploaddir);
	    $otherResult=$attachement->save();
	  }
	}
	
	if (! $error and $attachement->idPrivacy==1) { // send mail if new attachment is public
	  $elt=new $refType($refId);
		$mailResult=$elt->sendMailIfMailable(false,false,false,false,false,true,false,false,false,false,false,true);
		if ($mailResult) {
		  $result.=' - ' . i18n('mailSent');
		}
	}
}
if (! $error) {
  // Message of correct saving
  if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
  	Sql::rollbackTransaction();
    $message='<span class="messageERROR" >' . $result . '</span>';
  } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  	Sql::commitTransaction();
    $message= '<span class="messageOK" >' . $result . '</span>';
  } else { 
  	Sql::rollbackTransaction();
    $message= '<span class="messageWARNING" >' . $result . '</span>';
  }
} else {
	Sql::rollbackTransaction();
	//$message=htmlGetErrorMessage($error);
	$message=$error;
}

$jsonReturn='{"file":"'.$attachement->fileName.'",'
 .'"name":"'.$attachement->fileName.'",'
 .'"type":"'.$type.'",'
 .'"size":"'.$attachement->fileSize.'"  ,'
 .'"message":"'.str_replace('"',"'",$message).'"}';


if ($isIE and $isIE<=9) {
	echo $message;
  echo '</body>';
  echo '</html>';
} else {
  echo $jsonReturn;
}?>