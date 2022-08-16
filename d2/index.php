<style>
  html{
    font-family: sans-serif, tahoma, verdana;
    font-size: 11px;
  }
  .toolbar{
    display: flex;
    }
  .toolbar button{
      margin-right: 20px;
  }
  .playlist{
      display:flex;
  }
  .playlist>div{
      margin-right: 10px;
  }
  
  .date{width:120px}
  .number{width:40px}
  .name{width:290px}
  .author{width:180px}
  .jukebox{color:#BE222A}
</style>
<?php
   ini_set('display_errors', 1); 
   error_reporting(E_ALL);
       
//   phpinfo();     
       
  function pre_print($msg){
    echo "<pre>";
    print_r($msg);
    echo "</pre>";
  }
  define('_nl',"<br>\n");
  
  echo "<h1>dracula</h1>"._nl;
  
 
  $DATAFOLDER = "../data";
  
  /*if($CONTENT != scandir($DATAFOLDER){
      die("Unable to scan directory: $DATAFOLDER ");
  };*/
  
  $CONTENT = scandir($DATAFOLDER);
  unset($CONTENT[0]);
  unset($CONTENT[1]);
  
  $CONTENT_TO_DELETE = [];
  foreach($CONTENT as $KEY=>$FOLDER){
    if(!(is_dir($DATAFOLDER."/".$FOLDER))){
      $CONTENT_TO_DELETE[] = $KEY;
    }
  }

  foreach($CONTENT_TO_DELETE as $index){
		unset($CONTENT[$index]);
	}
		
  require("_db.php");
  connectDB();
  
  $FOLDERS = rSQL("SELECT * FROM folders");
  ?>
  <div class="toolbar">
    <form method="POST">
      <input type=hidden name=action value="HOME">
      <button type="submit" >Home</button>
    </form>
    <form method="POST">
      <input type=hidden name=action value="DELETE">
      <button type="submit" >Clear All</button>
    </form>
    
     <form method="POST">
      <input type=hidden name=action value="LIST_FOLDERS">
      <button type="submit" >List Folders</button>
    </form>   
    <form method="POST">
      <input type=hidden name=action value="IMPORT">
      <button type="submit" >Import music into DB</button>
    </form>   
    <form method="POST">
      <input type=hidden name=action value="PLAYLIST">
      <button type="submit" >Show playlist</button>
    </form>   
   <form method="POST">
      <input type=hidden name=action value="UNPLAYED">
      <button type="submit" >Show unplayed songs</button>
    </form>   
    <form method="POST">
      <input type=hidden name=action value="JUKEBOX">
      <button type="submit" >Show Jukebox</button>
    </form>   
    
  </div>  
  <?php
  pre_print($_POST);
  
  if(!isset($_POST["action"])){
      die("EOL reached as no action specified....");
  }
  
  if($_POST["action"] === "PLAYLIST"){
      $Q = "SELECT playlist.time, playlist.reason, songs.* FROM playlist " ;
      $Q.= "LEFT JOIN songs ON songs.id=playlist.id_song ";
      $Q.= "ORDER BY playlist.id DESC";
      
      $PLAYLIST = rSQL($Q);
      //pre_print($PLAYLIST);
      foreach($PLAYLIST as $SONG){
          echo "<div class='playlist ".(($SONG["reason"]==1)?("random"):("jukebox"))."'>";
          echo "<div class='date'>".$SONG["time"]."</div>";
          echo "<div class='number'>".$SONG["number"]."</div>";
          echo "<div class='author'>".$SONG["author"]."</div>";
          echo "<div class='name'>".$SONG["name"]."</div>";
          echo "<div class='number'>".(($SONG["reason"]==1)?("random"):("jukebox"))."</div>";         
          echo "</div>";
      }
      
      
  }
  
    
  if($_POST["action"] === "UNPLAYED"){
      $Q = "SELECT songs.* FROM songs WHERE count=0 ORDER BY songs.author, songs.name";
      
      $PLAYLIST = rSQL($Q);
      //pre_print($PLAYLIST);
      echo "<h2>COUNT::".count($PLAYLIST)."</h2>"._nl;
      foreach($PLAYLIST as $SONG){
          echo "<div class='playlist'>";
          echo "<div class='number'>".$SONG["number"]."</div>";
          echo "<div class='author'>".$SONG["author"]."</div>";
          echo "<div class='name'>".$SONG["name"]."</div>";          
          echo "</div>";
      }      
  }
  
    if($_POST["action"] === "JUKEBOX"){
      $Q = "SELECT jukebox.added, jukebox.played, songs.* FROM jukebox " ;
      $Q.= "LEFT JOIN songs ON songs.number=jukebox.number ";
      $Q.= "ORDER BY jukebox.id DESC";
      
      $PLAYLIST = rSQL($Q);
      //pre_print($PLAYLIST);
      foreach($PLAYLIST as $SONG){
          echo "<div class='playlist'>";
          echo "<div class='date'>".$SONG["added"]."</div>";
          echo "<div class='date'>".$SONG["played"]."</div>";
          echo "<div class='number'>".$SONG["number"]."</div>";
          echo "<div class='name'>".$SONG["name"]."</div>";
          echo "<div class='author'>".$SONG["author"]."</div>";        
          echo "</div>";
      }            
  }

  if($_POST["action"] === "DELETE"){
      $q = "TRUNCATE playlist";
      echo $q._nl;
      $dbh->exec($q);
      
      $q = "TRUNCATE jukebox";
      echo $q._nl;
      $dbh->exec($q);
      
      $q = "UPDATE songs SET last_played=NULL, count=0";
      echo $q._nl;
      $dbh->exec($q);
  }
  
  if($_POST["action"] === "LIST_FOLDERS"){
      echo "Listing folders<hr>";
      foreach($CONTENT as $FOLDER){
          echo $FOLDER._nl;
      }
  }
   
  if($_POST["action"] === "IMPORT"){
    
    echo "<h2>importing</h2>"; 
     
    function isPresent($needle, $haystack, $field){
      $found = false;
      foreach($haystack as $F){
          $fff =  strcmp($needle, $F[$field]);
          if ($fff === 0){
              $found = true;
          }     
      }
      return $found;
    }      
       
    //~ foreach($CONTENT as $FOLDER){
      //~ if(!isPresent($FOLDER, $FOLDERS, "folder")){
          //~ $q = "INSERT INTO folders SET folder='".$FOLDER."',";
          //~ $q.= " description='".$FOLDER."'";
          //~ echo $q._nl;
          //~ $dbh->exec($q);
      //~ }
    //~ }  
    
    //~ $FOLDERS = rSQL("SELECT * FROM folders");
    $MAX = rSQL("SELECT MAX(number) AS M FROM songs")[0]["M"] * 1;
    if($MAX<100)$MAX=100;
    echo "MAX::".$MAX._nl;
     
    //~ foreach($FOLDERS as $FOLDER){  
    //~ $folderName = $DATAFOLDER."/".$FOLDER["folder"];
    $folderName = $DATAFOLDER;
    // echo $folderName."<hr>";
    $FILES =  scandir($folderName);
    //~ $MUSIC = rSQL("SELECT * FROM songs WHERE folder = ".$FOLDER["id"]);
    $MUSIC = rSQL("SELECT * FROM songs");
    unset($FILES[0]);
    unset($FILES[1]);

    foreach($FILES as $FILE){
        
        if(!isPresent($FILE, $MUSIC, "filename")){
          $name_array = explode( "-",substr($FILE,0, -4));
          //~ $q = "INSERT INTO songs SET folder=".$dbh->quote($FOLDER["id"]);
          $q = "INSERT INTO songs SET ";
          $q.= " filename=".$dbh->quote($FILE);
          $q.= ", number=".$dbh->quote($MAX++);
          $q.= ", name=".$dbh->quote($name_array[1]);
          $q.= ", author=".$dbh->quote($name_array[0]);
            
          echo $q._nl;
          $dbh->exec($q);
        }    
    }
    
    
  //~ }
  }
    
?>
