<?PHP
/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
    require_once "../tool/projeqtor.php"; 
    scriptLog('   ->/tool/jsonList.php');
    echo '{"identifier":"id",' ;
    echo 'label: "name",';
    echo ' "items":[';
    
    getSubdirectories(null);
    
    echo ' ] }';
    
    function getSubdirectories($id) {
    	$dir=new DocumentDirectory();
    	$dirList=$dir->getSqlElementsFromCriteria(array('idDocumentDirectory'=>$id),false,null,'location asc');
      $nbRows=0;
      foreach ($dirList as $dir) {
        if ($nbRows>0) echo ', ';
        echo '{id:"' . $dir->id . '", name:"'. str_replace('"', "''",$dir->name) . '", type:"folder"';
        echo ', children : [';
        getSubdirectories($dir->id);
        echo ' ]';
        echo '}' ;
        $nbRows+=1;
      }   
    }
    
?>
