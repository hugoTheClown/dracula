<?php

  function pre_print($msg){
    echo "<pre>";
    print_r($msg);
    echo "</pre>";
  }
  define('_nl',"<br>\n");

  require("_db.php");
  connectDB();
  
  //SELECT songs.*, folders.folder as folder FROM songs LEFT JOIN folders ON folders.id = songs.folder WHERE (count=0 OR DATE_ADD(last_played, INTERVAL 5 MINUTE)<NOW()) AND songs.id<5 ORDER BY count, RAND()
  
  $q = "SELECT songs.*, folders.folder as folder FROM songs ";
  $q.= "LEFT JOIN folders ON folders.id = songs.folder ";
  $q.= "WHERE (count=0 OR DATE_ADD(last_played, INTERVAL 15 MINUTE) < NOW())";
  $q.= "ORDER BY count, RAND() LIMIT 1;";
//   echo $q._nl;
  $SONG = rSQL($q)[0];
  
  $q = "UPDATE songs SET last_played=NOW(), count=count+1 WHERE id=".$SONG["id"];
  $dbh->exec($q);
  
  echo json_encode($SONG);

?>