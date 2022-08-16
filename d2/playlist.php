<?php
//require_once('./tcpdf/config/tcpdf_config_alt.php');
require_once ('./tcpdf/tcpdf.php');
  
function pre_print($msg){
echo "<pre>";
print_r($msg);
echo "</pre>";
}
define('_nl',"<br>\n");

require("_db.php");
connectDB();

$q = "SELECT songs.* FROM songs ORDER BY author, name";
$SONGS = rSQL($q);
  
//pre_print($SONGS);  
  
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Eli&Hugo');
$pdf->setTitle('Dracula`s playlist');
$pdf->setSubject('Dracula`s playlist');
$pdf->setKeywords('dracula, playlist, jukebox, eli&hugo, wedding');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setAutoPageBreak(false);
//~ $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->setMargins(10, 10, 10);
//~ $pdf->setFontSubsetting(true);

$pdf->setFont('dejavusans', '', 14, '', true);
$pdf->AddPage();

$xstart = 10;
$ystart = 20;

$xpos=$xstart;
$ypos=$ystart;


$PLAYLIST=[];



foreach($SONGS as $SONG){
	$PLAYLIST[mb_strtoupper(trim($SONG["author"]),"UTF-8")][]=$SONG;
}
	
//pre_print($PLAYLIST);	
	
function getSectionHeight(){
	
}	

$pageHeight = 287;	
	
$COL = 0;	
$A_FONT_SIZE = 11;
$S_FONT_SIZE = 10;

foreach($PLAYLIST as $AUTHOR=>$ASONGS){
	if($ypos!=$ystart)$ypos+=1;
	$pdf->setFont('dejavusans', 'B', $A_FONT_SIZE, '', true);
	$H = 0;
	$hy = $pdf->getStringHeight(100,$AUTHOR);
	$pdf->setFont('dejavusans', '', $S_FONT_SIZE, '', true);
	
	$H+=$ypos + $hy + $pdf->getStringHeight(100,"AAAA")*count($ASONGS);
	if($H>$pageHeight){
		if($COL == 0){
			$COL++;
			$ypos = $ystart;
			$xpos = 110;
		} else {
			$pdf->AddPage();	
			$COL=0;
			$xpos=$xstart;
			$ypos=$ystart;
		}
	}
	
	$pdf->setFont('dejavusans', 'B', $A_FONT_SIZE, '', true);
	$pdf->Text($xpos,$ypos, strToUpper($AUTHOR));
	$ypos+=$hy;
	$pdf->setFont('dejavusans', '', $S_FONT_SIZE, '', true);
	foreach($ASONGS as $SONG){
		$pdf->setFont('dejavusans', '', $S_FONT_SIZE, '', true);
		$hy = $pdf->getStringHeight(100,$SONG["name"]);
		
		$pdf->Text($xpos+10,$ypos, $SONG["name"]);
		
		$pdf->setFont('dejavusans', 'B', $S_FONT_SIZE, '', true);
		$pdf->Text($xpos,$ypos, $SONG["number"]);
		$ypos+=$hy;		
	}	
}	
	
	
	
	
	/*$hy = $pdf->getStringHeight(100,$SONG["name"]);
	$pdf->Text($xpos,$ypos, $SONG["name"]);
	$ypos+=$hy;
	*/
//}



$pdf->Output('example_001.pdf', 'I');
?>
