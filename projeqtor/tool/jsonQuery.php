<?PHP
/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
    require_once "../tool/projeqtor.php";
    scriptLog('   ->/tool/jsonQuery.php'); 
    $objectClass=$_REQUEST['objectClass'];
    
    $hiddenFields=array();
    if (isset($_REQUEST['hiddenFields'])) {
    	$hiddens=explode(';',$_REQUEST['hiddenFields']);
    	foreach ($hiddens as $hidden) {
    		if (trim($hidden)) {
    			$hiddenFields[$hidden]=$hidden;
    		}
    	}
    }
    $print=false;
    if ( array_key_exists('print',$_REQUEST) ) {
      $print=true;
      include_once('../tool/formatter.php');
    }
    $comboDetail=false;
    if ( array_key_exists('comboDetail',$_REQUEST) ) {
      $comboDetail=true;
    }
    $quickSearch=false;
    if ( array_key_exists('quickSearch',$_REQUEST) ) {
      $quickSearch=$_REQUEST['quickSearch'];
    }
    if (! isset($outMode)) { $outMode=""; } 
       
    $obj=new $objectClass();
    $table=$obj->getDatabaseTableName();
    $accessRightRead=securityGetAccessRight($obj->getMenuClass(), 'read');  
    $querySelect = '';
    $queryFrom=$table;
    $queryWhere='';
    $queryOrderBy='';
    $idTab=0;

    $res=array();
    $layout=$obj->getLayout();
    $array=explode('</th>',$layout);

    if ($quickSearch) {
    	$queryWhere.= ($queryWhere=='')?'':' and ';
    	$queryWhere.="( 1=2 ";
    	$note=new Note();
    	$noteTable=$note->getDatabaseTableName();
    	foreach($obj as $fld=>$val) {
    		if ($obj->getDataType($fld)=='varchar') {    				
            $queryWhere.=' or '.$table.".".$fld." ".((Sql::isMysql())?'LIKE':'ILIKE')." '%".$quickSearch."%'";
    		}
    	}
    	if (is_numeric($quickSearch)) {
    		$queryWhere.= ' or ' . $table . ".id=" . $quickSearch . "";
    	}
    	$queryWhere.=" or exists ( select 'x' from $noteTable " 
    	                           . " where $noteTable.refType='$objectClass' "
    	                           . " and $noteTable.refId=$table.id " 
    	                           . " and $noteTable.note ".((Sql::isMysql())?'LIKE':'ILIKE')." '%" . $quickSearch . "%' ) ";
    	$queryWhere.=" )";
    }
    
    $showIdleProjects=(! $comboDetail and isset($_SESSION['projectSelectorShowIdle']) and $_SESSION['projectSelectorShowIdle']==1)?1:0;
    
    if (! isset($showIdle)) $showIdle=false;
    if (!$showIdle and ! array_key_exists('idle',$_REQUEST) and ! $quickSearch) {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.= $table . "." . $obj->getDatabaseColumnName('idle') . "=0";
    } else {
      $showIdle=true;
    }
    if (array_key_exists('listIdFilter',$_REQUEST)  and ! $quickSearch) {
      $param=$_REQUEST['listIdFilter'];
      $param=strtr($param,"*?","%_");
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.=$table.".".$obj->getDatabaseColumnName('id')." like '%".$param."%'";
    }
    if (array_key_exists('listNameFilter',$_REQUEST)  and ! $quickSearch) {
      $param=$_REQUEST['listNameFilter'];
      $param=strtr($param,"*?","%_");
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.=$table.".".$obj->getDatabaseColumnName('name')." ".((Sql::isMysql())?'LIKE':'ILIKE')." '%".$param."%'";
    }
    if ( array_key_exists('objectType',$_REQUEST)  and ! $quickSearch) {
      if (trim($_REQUEST['objectType'])!='') {
        $queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.= $table . "." . $obj->getDatabaseColumnName('id' . $objectClass . 'Type') . "='" . $_REQUEST['objectType'] . "'";
      }
    }
    if ($objectClass=='Project' and $accessRightRead!='ALL') {
        $accessRightRead='ALL';
        $queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.=  '(' . $table . ".id in " . transformListIntoInClause($_SESSION['user']->getVisibleProjects(! $showIdle)) ;
        if ($objectClass=='Project') {
          $queryWhere.= " or codeType='TMP' ";
        }
        $queryWhere.= ')';
    }  
    if (property_exists($obj, 'idProject') and array_key_exists('project',$_SESSION)) {
        if ($_SESSION['project']!='*') {
          $queryWhere.= ($queryWhere=='')?'':' and ';
          if ($objectClass=='Project') {
            $queryWhere.=  $table . '.id in ' . getVisibleProjectsList(! $showIdleProjects) ;
          } else if ($objectClass=='Document') {
          	$queryWhere.= "(" . $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) . " or " . $table . ".idProject is null)";
          } else {
            $queryWhere.= $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) ;
          }
        }
    }

    if ( ($objectClass=='Version' or $objectClass=='Resource') and $comboDetail) {
    	// No limit 
    } else if ( $objectClass=='Project' ) {
    	// Restriction already applied
    } else {
      $queryWhere.= ($queryWhere=='')?'(':' and (';
      $queryWhere.= getAccesResctictionClause($objectClass,$table, $showIdleProjects);
      if ($objectClass=='Project') {
        $queryWhere.= " or codeType='TMP' ";
      }
      $queryWhere.= ')';
    }
    
    
    
    $crit=$obj->getDatabaseCriteria();
    foreach ($crit as $col => $val) {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . "=" . Sql::str($val) . " ";
    }

    if ($objectClass=='Document') {
    	if (array_key_exists('Directory',$_SESSION) and ! $quickSearch) {
    		$queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName('idDocumentDirectory') . "='" . $_SESSION['Directory'] . "'";
    	}
    }
    
    $arrayFilter=array();
    if (! $quickSearch) {
      if (! $comboDetail and is_array( $_SESSION['user']->_arrayFilters)) {
        if (array_key_exists($objectClass, $_SESSION['user']->_arrayFilters)) {
        	$arrayFilter=$_SESSION['user']->_arrayFilters[$objectClass];
        }
      } else if ($comboDetail and is_array( $_SESSION['user']->_arrayFiltersDetail)) {
        if (array_key_exists($objectClass, $_SESSION['user']->_arrayFiltersDetail)) {
          $arrayFilter=$_SESSION['user']->_arrayFiltersDetail[$objectClass];
        }
      }
    }
    
    // first sort from index (checked in List Header)
    $sortIndex=null;   
    if ($print) {
      if (array_key_exists('sortIndex', $_REQUEST)) {
        $sortIndex=$_REQUEST['sortIndex']+1;
        $sortWay=(array_key_exists('sortWay', $_REQUEST))?$_REQUEST['sortWay']:'asc';
        $nb=0;
        $numField=0;
        foreach ($array as $val) {
          $fld=htmlExtractArgument($val, 'field');      
          if ($fld and $fld!="photo") {            
            $numField+=1;
            if ($sortIndex and $sortIndex==$numField) {
              $queryOrderBy .= ($queryOrderBy=='')?'':', ';
              if (Sql::isPgsql()) $fld='"'.$fld.'"';
              $queryOrderBy .= " " . $fld . " " . $sortWay;
            }
          }
        }
      }
    }
    
    // Then sort from Filter Criteria
    if (! $quickSearch) {
	    foreach ($arrayFilter as $crit) {
	      if ($crit['sql']['operator']=='SORT') {
	        $doneSort=false;
          $split=explode('_', $crit['sql']['attribute']);
	        if (count($split)>1 ) {
	          $externalClass=$split[0];
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();          
	          $idTab+=1;
	          $externalTableAlias = 'T' . $idTab;
	          $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	           ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
	          $queryOrderBy .= ($queryOrderBy=='')?'':', ';
            $queryOrderBy .= " " . $externalTableAlias . '.' . $split[1] 
            . " " . $crit['sql']['value'];
	          $doneSort=true;
          }
	        if (substr($crit['sql']['attribute'],0,2)=='id' and strlen($crit['sql']['attribute'])>2 ) {
	          $externalClass = substr($crit['sql']['attribute'],2);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $sortColumn='id';          
	          if (property_exists($externalObj,'sortOrder')) {
	          	$sortColumn=$externalObj->getDatabaseColumnName('sortOrder');
	          } else {
	          	$sortColumn=$externalObj->getDatabaseColumnName('name');
	          }
            $idTab+=1;
            $externalTableAlias = 'T' . $idTab;
            $queryOrderBy .= ($queryOrderBy=='')?'':', ';
            $queryOrderBy .= " " . $externalTableAlias . '.' . $sortColumn
               . " " . str_replace("'","",$crit['sql']['value']);
            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
            ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
            ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
            $doneSort=true;
	        }
	        if (! $doneSort) {
	          $queryOrderBy .= ($queryOrderBy=='')?'':', ';
	          $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName($crit['sql']['attribute']) 
	                             . " " . $crit['sql']['value'];
	        }
	      }
	    }
    }
    
    // Build select clause, and eventualy extended From clause and Where clause
    // Also include default Sort criteria
    $numField=0;
    $formatter=array();
    $arrayWidth=array();
    if ($outMode=='csv') {
    	$obj=new $objectClass();
    	$clause=$obj->buildSelectClause(false,$hiddenFields);
    	$querySelect .= ($querySelect=='')?'':', ';
    	$querySelect .= $clause['select'];
    	//$queryFrom .= ($queryFrom=='')?'':', ';
    	$queryFrom .= $clause['from'];
    } else {
	    foreach ($array as $val) {
	      //$sp=preg_split('field=', $val);
	      //$sp=explode('field=', $val);
	      $fld=htmlExtractArgument($val, 'field');      
	      if ($fld) {
	        $numField+=1;    
	        $formatter[$numField]=htmlExtractArgument($val, 'formatter');
	        $from=htmlExtractArgument($val, 'from');
	        $arrayWidth[$numField]=htmlExtractArgument($val, 'width');
	        $querySelect .= ($querySelect=='')?'':', ';
	        if (substr($formatter[$numField],0,5)=='thumb') {
            $querySelect.=substr($formatter[$numField],5).' as ' . $fld;;
            continue;
          }    
	        if (strlen($fld)>9 and substr($fld,0,9)=="colorName") {
	          $idTab+=1;
	          // requested field are colorXXX and nameXXX => must fetch the from external table, using idXXX
	          $externalClass = substr($fld,9);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          if (Sql::isPgsql()) {
	          	//$querySelect .= 'concat(';
		          if (property_exists($externalObj,'sortOrder')) {
	              $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('sortOrder');
	              $querySelect .=  " || '#split#' ||";
	            }
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name');
	            $querySelect .=  " || '#split#' ||";
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color');
	            //$querySelect .= ') as "' . $fld .'"';
	            $querySelect .= ' as "' . $fld .'"'; 
	          } else {
	            $querySelect .= 'convert(concat(';
	            if (property_exists($externalObj,'sortOrder')) {
                $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('sortOrder');
                $querySelect .=  ",'#split#',";
	            }
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name');
	            $querySelect .=  ",'#split#',";
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color');
	            $querySelect .= ') using utf8) as ' . $fld;
	          }	          
	          $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	            ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
	            ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	        } else if (strlen($fld)>4 and substr($fld,0,4)=="name" and !$from) {
	          $idTab+=1;
	          // requested field is nameXXX => must fetch it from external table, using idXXX
	          $externalClass = substr($fld,4);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          //if (! stripos($queryFrom,$externalTable)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	              ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
	              ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	          //}   
	        } else if (strlen($fld)>5 and substr($fld,0,5)=="color") {
	          $idTab+=1;
	          // requested field is colorXXX => must fetch it from external table, using idXXX
	          $externalClass = substr($fld,5);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          //if (! stripos($queryFrom,$externalTable)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias . 
	              ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
	              ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	          //}
	        } else if ($from) {
	          // Link to external table
	          $externalClass = $from;
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();          
	          $externalTableAlias = strtolower($externalClass);
	          if (! stripos($queryFrom,$externalTableAlias)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	              ' on (' . $externalTableAlias . '.refId=' . $table . ".id" . 
	              ' and ' . $externalTableAlias . ".refType='" . $objectClass . "')";
	          }
	          if (strlen($fld)>4 and substr($fld,0,4)=="name") {
              $idTab+=1;
              // requested field is nameXXX => must fetch it from external table, using idXXX
              $externalClassName = substr($fld,4);
              $externalObjName=new $externalClassName();
              $externalTableName = $externalObjName->getDatabaseTableName();
              $externalTableAliasName = 'T' . $idTab;
              $querySelect .= $externalTableAliasName . '.' . $externalObjName->getDatabaseColumnName('name') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
              $queryFrom .= ' left join ' . $externalTableName . ' as ' . $externalTableAliasName .
                  ' on ' . $externalTableAlias . "." . $externalObj->getDatabaseColumnName('id' . $externalClassName) . 
                  ' = ' . $externalTableAliasName . '.' . $externalObjName->getDatabaseColumnName('id');   
            } else {
            	$querySelect .=  $externalTableAlias . '.' . $externalObj->getDatabaseColumnName($fld) . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
            } 	
            
	          if ( property_exists($externalObj,'wbsSortable') 
	            and strpos($queryOrderBy,$externalTableAlias . "." . $externalObj->getDatabaseColumnName('wbsSortable'))===false) {
	            $queryOrderBy .= ($queryOrderBy=='')?'':', ';
	            $queryOrderBy .= " " . $externalTableAlias . "." . $externalObj->getDatabaseColumnName('wbsSortable') . " ";
	          } 
	        } else {      
	        //var_dump($fld); echo '<br/>';
	          // Simple field to add to request 
	          $querySelect .= $table . '.' . $obj->getDatabaseColumnName($fld) . ' as ' . ((Sql::isPgsql())?'"'.strtr($fld,'.','_').'"':strtr($fld,'.','_'));
	        }
	      }
	    }
    }
    // build order by clause
    if ($objectClass=='DocumentDirectory') {
    	$queryOrderBy .= ($queryOrderBy=='')?'':', ';
    	$queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('location');
    } else if ( property_exists($objectClass,'wbsSortable')) {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('wbsSortable');
    } else if (property_exists($objectClass,'sortOrder')) {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('sortOrder');
    } else {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('id') . " desc";
    }
    
    // Check for an advanced filter (stored in User)
    foreach ($arrayFilter as $crit) {
      if ($crit['sql']['operator']!='SORT') {
      	$split=explode('_', $crit['sql']['attribute']);
      	$critSqlValue=$crit['sql']['value'];
      	if ($crit['sql']['operator']=='IN' and $crit['sql']['attribute']=='idProduct') {
          $critSqlValue=str_replace(array(' ','(',')'), '', $critSqlValue);
      		$splitVal=explode(',',$critSqlValue);
      		$critSqlValue='(0';
      		foreach ($splitVal as $idP) {
      			$prod=new Product($idP);
      			$critSqlValue.=', '.$idP;
      	    $list=$prod->getRecursiveSubProductsFlatList(false, false);
      	    foreach ($list as $idPrd=>$namePrd) {
      	    	$critSqlValue.=', '.$idPrd;
      	    }
      		}      		
      		$critSqlValue.=')';
      	}
        if (count($split)>1 ) {
          $externalClass=$split[0];
          $externalObj=new $externalClass();
          $externalTable = $externalObj->getDatabaseTableName();          
          $idTab+=1;
          $externalTableAlias = 'T' . $idTab;
          $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
           ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
          $queryWhere.=($queryWhere=='')?'':' and ';
          $queryWhere.=$externalTableAlias . "." . $split[1] . ' ' 
                 . $crit['sql']['operator'] . ' '
                 . $critSqlValue;
        } else {
          $queryWhere.=($queryWhere=='')?'':' and ';
          $queryWhere.="(".$table . "." . $crit['sql']['attribute'] . ' ' 
		                 . $crit['sql']['operator'] . ' '
		                 . $critSqlValue;
		      if (strlen($crit['sql']['attribute'])>=9 
		      and substr($crit['sql']['attribute'],0,2)=='id'
		      and substr($crit['sql']['attribute'],-7)=='Version'
		      and $crit['sql']['operator']=='IN') {
		      	$scope=substr($crit['sql']['attribute'],2);
		      	$vers=new OtherVersion();
		      	$queryWhere.=" or exists (select 'x' from ".$vers->getDatabaseTableName()." VERS "
		      	  ." where VERS.refType='".$objectClass."' and VERS.refId=".$table.".id and scope='".$scope."'"
		      	  ." and VERS.idVersion IN ".$critSqlValue
		      	  .")";
		      }
		      $queryWhere.=")";
        }
      }
    }
    
    // constitute query and execute
    $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
    $query='select ' . $querySelect 
         . ' from ' . $queryFrom
         . ' where ' . $queryWhere 
         . ' order by' . $queryOrderBy;
    $result=Sql::query($query);
    $nbRows=0;
    $dataType=array();
    if ($print) {
    	if ($outMode=='csv') {
    		$csvSep=Parameter::getGlobalParameter('csvSeparator');
    		$csvQuotedText=true;
    		$obj=new $objectClass();
    		$first=true;
    		$arrayFields=array();
    	  //if (Sql::isPgsql()) {
    	  	$arrayFields=$obj->getLowercaseFieldsArray();
    	  	//$arrayFieldsWithCase=$obj->getFieldsArray();        
        //}
    		while ($line = Sql::fetchLine($result)) {
    			if ($first) {
	    			foreach ($line as $id => $val) {
	    				$colId=$id;
	    				if (Sql::isPgsql() and isset($arrayFields[$id])) {
	    					$colId=$arrayFields[$id];
	    				}
	    				$val=utf8_decode($obj->getColCaption($colId));
	    				if (substr($id,0,9)=='idContext' and strlen($id)==10) {
                $ctx=new ContextType(substr($id,-1));
                $val=utf8_decode($ctx->name);
              } 
	    				//$val=utf8_decode($id);
	    				$val=str_replace($csvSep,' ',$val);
	            if ($id!='id') { echo $csvSep ;}
	    				echo $val;
	            $dataType[$id]=$obj->getDataType($id);
	          }
	          echo "\r\n";
    			}
    			foreach ($line as $id => $val) {
    				$foreign=false;
    				if (substr($id, 0,2)=='id' and strlen($id)>2) {
    					$class=substr($arrayFields[strtolower($id)], 2);
    					if (ucfirst($class)==$class) {
    						$foreign=true;
    					  $val=SqlList::getNameFromId($class, $val);
    					}
    				}
    				$val=utf8_decode($val);
    				if ($csvQuotedText) {
    				  $val=str_replace('"','""',$val);	
    				}
            $val=str_replace($csvSep,' ',$val);
            if ($id!='id') { echo $csvSep ;}
            if ( ($dataType[$id]=='varchar' or $foreign) and $csvQuotedText) {
              echo '"' . $val . '"';
            } else {
            	echo $val;
            }
    			}
    			$first=false;
    			echo "\r\n";
    		}
    		if ($first) {
    			echo utf8_decode(i18n("reportNoData")); 
    		}
    	} else {
        echo '<br/>';
        echo '<div class="reportTableHeader" style="width:99%; font-size:150%;border: 0px solid #000000;">' . i18n('menu'.$objectClass) . '</div>';
        echo '<br/>';
	      echo '<table>';
	      echo '<tr>';
	      $layout=str_ireplace('width="','style="border:1px solid black;width:',$layout);
	      $layout=str_ireplace('<th ','<th class="reportHeader" ',$layout);
	      echo $layout;
	      echo '</tr>';
	      if (Sql::$lastQueryNbRows > 0) {
	        while ($line = Sql::fetchLine($result)) {
	          echo '<tr>';
	          $numField=0;
	          foreach ($line as $id => $val) {
	            $numField+=1;
	            $disp="";
	            if ($formatter[$numField]=="colorNameFormatter") {
	              $disp=colorNameFormatter($val);
	            } else if ($formatter[$numField]=="booleanFormatter") {
	              $disp=booleanFormatter($val);
	            } else if ($formatter[$numField]=="colorFormatter") {
	              $disp=colorFormatter($val);
	            } else if ($formatter[$numField]=="dateTimeFormatter") {
	              $disp=dateTimeFormatter($val);
	            } else if ($formatter[$numField]=="dateFormatter") {
	              $disp=dateFormatter($val);
	            } else if ($formatter[$numField]=="timeFormatter") {
                $disp=timeFormatter($val);
	            } else if ($formatter[$numField]=="translateFormatter") {
	              $disp=translateFormatter($val);
	            } else if ($formatter[$numField]=="percentFormatter") {
	              $disp=percentFormatter($val);
	            } else if ($formatter[$numField]=="numericFormatter") {
	              $disp=numericFormatter($val);
	            } else if ($formatter[$numField]=="sortableFormatter") {
	              $disp=sortableFormatter($val);
	            } else if ($formatter[$numField]=="workFormatter") {
                $disp=workFormatter($val);
              } else if ($formatter[$numField]=="costFormatter") {
                $disp=costFormatter($val);
	            } else if (substr($formatter[$numField],0,5)=='thumb') {
	            	$disp=thumbFormatter($objectClass,$line['id'],substr($formatter[$numField],5));
	            } else {
	              $disp=htmlEncode($val);
	            }
	            echo '<td class="tdListPrint" style="width:' . $arrayWidth[$numField] . ';">' . $disp . '</td>';
	          }
	          echo '</tr>';       
	        }
	      }
	      echo "</table>";
	      //echo "</div>";
    	}
    } else {
      // return result in json format
      echo '{"identifier":"id",' ;
      echo ' "items":[';
      if (Sql::$lastQueryNbRows > 0) {
        while ($line = Sql::fetchLine($result)) {
          echo (++$nbRows>1)?',':'';
          echo  '{';
          $nbFields=0;
          foreach ($line as $id => $val) {
            echo (++$nbFields>1)?',':'';
            $numericLength=0;
            if ($id=='id') {
            	$numericLength=6;
            } else if ($formatter[$nbFields]=='percentFormatter') {
            	$numericLength=3;
            } else if ($formatter[$nbFields]=='workFormatter' or $formatter[$nbFields]=='costFormatter') {
            	$numericLength=9;
            } else if ($formatter[$nbFields]=='numericFormatter') {
            	$numericLength=9;
            }
            if ($id=='colorNameRunStatus') {
            	$split=explode('#',$val);
            	foreach ($split as $ix=>$sp) {
            	  if ($ix==0) {
            	  	$val=$sp;
            	  } else if ($ix==2) {
            		  $val.='#'.i18n($sp);	
            	  } else {
            	  	$val.='#'.$sp;
            	  }
            	} 
            }
            if (substr($formatter[$nbFields],0,5)=='thumb') {          	
            	$image=SqlElement::getSingleSqlElementFromCriteria('Attachement', array('refType'=>$objectClass, 'refId'=>$line['id']));
              if ($image->id and $image->isThumbable()) {
            	  $val=getImageThumb($image->getFullPathFileName(),$val).'#'.$image->id.'#'.$image->fileName; 
              } else {
              	$val="##";
              }
            } 
            echo '"' . htmlEncode($id) . '":"' . htmlEncodeJson($val, $numericLength) . '"';
          }
          echo '}';       
        }
      }
       echo ']';
      //echo ', "numberOfRow":"' . $nbRows . '"' ;
      echo ' }';
    }
?>
