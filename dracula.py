import time
import serial
import mariadb
import sys
# import msvcrt

from pygame import mixer

mixer.init()

canGoRandom = True;


print("DRACULA 1");

try:
		conn = mariadb.connect(
				user="dracula",
				password="a3i5rgnVWkXv4yoV",
				host="localhost",
				port=3306,
				database="dracula"

		)
except mariadb.Error as e:
		#print(f"Error connecting to MariaDB Platform: {e}")
		print("Cannot connect to MARIADB.");
		sys.exit(1)
		
print("MariaDB connected");   
print("-------------------")

cursor = conn.cursor()

ser = serial.Serial(
	port='/dev/ttyUSB0',
	baudrate = 9600,
	parity=serial.PARITY_NONE,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.EIGHTBITS,
	timeout=1)

def read_serial():
	if ser.in_waiting > 0:
		buffer = ser.readline()
		data = buffer.decode('ascii').split(':')		
		if(data[0] == 'SONG'):
			SQL = "INSERT INTO jukebox SET number=" + data[1].rstrip() + ",added = NOW(), status=0"
			print("ADDING SONG", data[1].rstrip() )
			try:
				cursor.execute(SQL)	
				conn.commit() 
				#print(f"Last Inserted ID: {cursor.lastrowid}")				
			except mariadb.Error as e:
				print("DB Error: while insert")

def get_song_info(songNumber=0):
		#print("GET SONG INFO", songNumber)
		query = "SELECT songs.id, songs.filename, folders.folder as folder FROM songs LEFT JOIN folders ON folders.id = songs.folder WHERE songs.number=" + str(songNumber);
		#print(query);
		cursor.execute(query)
		result = cursor.fetchone()
		if not result:
			return None
		else:
			#print("SONG INFO DONE", result)
			return result

def get_random_song():
	SQL = """SELECT songs.id, songs.filename, folders.folder as folder, songs.number FROM songs  
		LEFT JOIN folders ON folders.id = songs.folder 
		WHERE noplay<>1 AND (count=0 OR DATE_ADD(last_played, INTERVAL 30 MINUTE) < NOW()) 
		ORDER BY count, RAND() LIMIT 1;""";
			
	cursor.execute(SQL)
	result = cursor.fetchone()
	if not result:
		print("NO RANDOM SONG")
		return None
	else:
		print("RANDOM SONG No.:", result[3])
		return result

def make_jukebox_song_played(idJukebox = 0, song = None):
	if not song:
		result = -1
	else: 
		result = 1		
		cursor.execute("UPDATE songs SET last_played=NOW(), count = count+1 " + 
		"WHERE id = " + str(song[0]))
		conn.commit();
		
	cursor.execute("UPDATE jukebox SET played=NOW(), status = '" + str(result) + 
	"' WHERE id = " + str(idJukebox))
	conn.commit();
	

	
def make_song_played(idSong= 0):
	cursor.execute("UPDATE songs SET last_played=NOW(), count = count+1 " + 
	"WHERE id = " + str(idSong))
	conn.commit();
	SQL = "INSERT INTO playlist SET id_song=" + str(idSong) + ", time=NOW()";
	cursor.execute(SQL)	
	conn.commit() 	

def cmdProcess(cmd = 0):
	global canGoRandom
	if cmd==10:
		canGoRandom = True;
		print("CMD RANDOM ON", canGoRandom);
		return True;

	if cmd==11:
		canGoRandom = False;
		print("CMD RANDOM OFF");
		return True;

	if cmd==777:
		print("TEST CMD");
		return True;
			
	return False;


def choose_song():
	query = """
	SELECT jukebox.id, jukebox.number, songs.id as idsong  
	FROM jukebox 
	LEFT JOIN songs ON songs.number = jukebox.number 
	WHERE played IS NULL AND ( DATE_ADD(songs.last_played, INTERVAL 30 MINUTE) < NOW() OR songs.last_played IS NULL) 
	ORDER BY added LIMIT 1""";
	
	cursor.execute(query)
	
	result = cursor.fetchone()
	#SongId = result[2];
	# print("result::", result);
	
	
	
	if result is None:
		# print("CHECK FOR COMMANDS FIRST HERE :)");
				
		if(result):
			print("DOPRDELEEEEEE");
			if(cmdProcess(result[1])):
				make_jukebox_song_played(result[0])	
				return False;
		
		# print("canGoRandom",canGoRandom);					
		if(canGoRandom):
			print("NO SONG IN THE JUKEBOX > going RANDOM")
			song = get_random_song()
			if song:
				make_song_played(song[0])
				return song
		else:
			return False;
	else:
		print("JUKEBOX SONG No.", result[1])
		song = get_song_info(result[1])
		
		if not song:
			if(cmdProcess(result[1])):
				make_jukebox_song_played(result[0])	
				return False;
			
			print("JUKEBOX SONG DOES NOT EXIST > going RANDOM")
			make_jukebox_song_played(result[0])
			if canGoRandom:
				return get_random_song() 
			else: 
				return False;
		else: 
			make_jukebox_song_played(result[0], song)		
			SQL = "INSERT INTO playlist SET id_song=" + str(result[2]) + ", time=NOW(), reason=2";
			cursor.execute(SQL)	
			conn.commit() 	
			return song
	

while True:
	jukeboxsong = choose_song();
	if(jukeboxsong):
		print("PLAYING: ", jukeboxsong[1]);
		song = '/var/www/html/data/' + jukeboxsong[1];
		#print(song)
		try:
			mixer.music.load(song)
			mixer.music.play()		
			while mixer.music.get_busy() == True:
				read_serial()
				#if msvcrt.kbhit() and ord(msvcrt.getch()) == 27:
				#	aborted = True
			#		print("ESCAPE");
			#		break
				continue
		except:
			print("ERROR:", song);
			
		print("-------------------")
	else:
		# print("Waiting for listeners :D");
		read_serial()
		time.sleep(1)
	
	
