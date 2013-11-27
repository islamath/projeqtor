<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the note info
if (! array_key_exists('noteRefType',$_REQUEST)) {
  throwError('noteRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['noteRefType'];
if ($refType=='TicketSimple') {
  $refType='Ticket';    
}
if (! array_key_exists('noteRefId',$_REQUEST)) {
  throwError('noteRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['noteRefId'];
if (! array_key_exists('noteNote',$_REQUEST)) {
  throwError('noteNote parameter not found in REQUEST');
}
$noteNote=$_REQUEST['noteNote'];

$notePrivacy=null;
if (array_key_exists('notePrivacy',$_REQUEST)) {
  $notePrivacy=$_REQUEST['notePrivacy'];
}

$noteId=null;
if (array_key_exists('noteId',$_REQUEST)) {
  $noteId=$_REQUEST['noteId'];
}
$noteId=trim($noteId);
if ($noteId=='') {
  $noteId=null;
} 
Sql::beginTransaction();
// get the modifications (from request)
$note=new Note($noteId);

$user=$_SESSION['user'];
if (! $note->id) {
  $note->idUser=$user->id;
  $ress=new Resource($user->id);
  $note->idTeam=$ress->idTeam;
}

$note->refId=$refId;
$note->refType=$refType;
if ($note->creationDate==null) {
  $note->creationDate=date("Y-m-d H:i:s");
} else if ($note->note!=$noteNote) {
    $note->updateDate=date("Y-m-d H:i:s");
}
$note->note=$noteNote;
if ($notePrivacy) {
  $note->idPrivacy=$notePrivacy;
} else if (! $note->idPrivacy) {
	$note->idPrivacy=1;
}
$result=$note->save();

if ($note->idPrivacy==1) { // send mail if new note is public
  $elt=new $refType($refId);
  if ($noteId) {
  	$elt->sendMailIfMailable(false,false,false,false,false,false,true,false,false,false,false,true);
  } else {
	  $elt->sendMailIfMailable(false,false,false,false,true,false,false,false,false,false,false,true);
  }
}

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>