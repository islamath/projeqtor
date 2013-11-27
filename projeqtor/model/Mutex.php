<?php
 
class Mutex
{
    var $lockname;
    var $timeout;
    var $locked;
 
    function Mutex($name, $timeout = 10)
    {
        $this->lockname = $name;
        $this->timeout = $timeout;
        $this->locked = -1;
    }
 
    function reserve()
    {
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT GET_LOCK('".$this->lockname."', ".$this->timeout.") as mutex");
        $line=Sql::fetchLine($rs);
        $this->locked = $line['mutex'];
        //mysqli_free_result($rs);
    	} else if (Sql::isPgsql()) {
    		$prefix=Parameter::getGlobalParameter('paramDbPrefix');
    		$rs=Sql::query("LOCK TABLE ".$prefix."mutex IN ACCESS EXCLUSIVE MODE");
    		$rs=Sql::query("SELECT * FROM ".$prefix."mutex WHERE name='".$this->lockname."'");
    		if (count($rs)==0) {
    			$rs=Sql::query("INSERT INTO ".$prefix."mutex (name) VALUES ('".$this->lockname."')");
    		} 
    	}
    }
 
    function release()
    {
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT RELEASE_LOCK('".$this->lockname."') as mutex");
        $line=Sql::fetchLine($rs);
        $this->locked = !$line['mutex'];
        //mysqli_free_result($rs);
    	} else if (Sql::isPgsql() and 0) {
    		$prefix=Parameter::getGlobalParameter('paramDbPrefix');
    		$rs=Sql::query("LOCK TABLE ".$prefix."mutex IN ACCESS SHARE MODE");
    	}
    }
 
    function isFree()
    {
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT IS_FREE_LOCK('".$this->lockname."') as mutex");
        $line=Sql::fetchLine($rs);
        $lock = (bool)$line['mutex'];
        //mysqli_free_result($rs);
        return $lock;
    	}
    }
}
 