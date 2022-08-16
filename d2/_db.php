<?php

function normalizeString($str){
    $str = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $str));
    $str = preg_replace('/[^a-z0-9.]/', ' ', $str);
    $str = preg_replace('/\s\s+/', ' ', $str);
    $str = str_replace(' ', '-', trim($str));
    return $str;
}

// TODO ERROR HANDLING
function connectDB(){
  global $dbh;
  
  $__db_host    = "localhost";
  $__db_user    = "dracula";
  $__db_pass    = "a3i5rgnVWkXv4yoV";
  $__db_schema  = "dracula";
  
  $dsn = 'mysql:host='.$__db_host.';dbname='.$__db_schema;
  $username = $__db_user;
  $password = $__db_pass;
  $options = array(
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
  ); 
    
  $dbh = new PDO($dsn, $username, $password, $options);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
}

function rSQL($sql){
  global $dbh;
  if(!$dbh) return false;
   
  $res = array();
  foreach($dbh->query($sql, PDO::FETCH_ASSOC) as $row){
    $res[] = $row;
  }
  return $res;
}



function getMaxInArray($arr,$fld){
  $retMax = null;
  foreach($arr as $oneRow){
    $retMax = max($oneRow[$fld], $retMax);
  }
  return $retMax;
}



?>