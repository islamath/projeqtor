<?php
require_once "../tool/projeqtor.php";
function cronAbort() {Cron::abort();}
register_shutdown_function('cronAbort');
//Cron::init();
Cron::run();