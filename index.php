<style>
	.song{
		display: flex;
		}
		
	.song div{
		margin-right: 10px;
		}	
	</style>
	
	<form method="POST">
		SEARCH <input type=text name="song" autofocus>
		<input type=hidden name=search value=yes>
		</form>
<?php

  require("./d2/_db.php");
  connectDB(); 
  
  $q = "SELECT * FROM songs";
  if($_POST["search"]=="yes" && $_POST["song"]!="") {
	$q.=" WHERE filename LIKE '%".$_POST["song"]."%' ";  
  }
  $q.=" ORDER BY number";
  
  $SONGS = rSQL($q);
  
  foreach($SONGS as $SONG){
	echo "<div class='song'>";
	  echo "<div>".$SONG["number"]."</div>";
	  echo "<div>".$SONG["filename"]."</div>";  
	echo "</div>";  
  }
  

?>
