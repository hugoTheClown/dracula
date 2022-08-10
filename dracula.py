import time
import serial
import mariadb
import sys
from pygame import mixer

mixer.init()

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
			print("ADDING SONG", SQL)
			try:
				cursor.execute(SQL)	
				conn.commit() 
				#print(f"Last Inserted ID: {cursor.lastrowid}")				
			except mariadb.Error as e:
				print("DB Error: while insert")

def get_song_info(songNumber=0):
		print("SONG INFO", songNumber)
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
	cursor.execute("SELECT songs.id, songs.filename, folders.folder as folder FROM songs " +  
		"LEFT JOIN folders ON folders.id = songs.folder " +
		"WHERE (count=0 OR DATE_ADD(last_played, INTERVAL 15 MINUTE) < NOW()) " +
		"ORDER BY count, RAND() LIMIT 1;")
	result = cursor.fetchone()
	if not result:
		print("NO RANDOM SONG")
		return None
	else:
		print("RANDOM SONG", result)
		return result

def make_jukebox_song_played(idJukebox = 0, song = None):
	if not song:
		result = -1
	else: 
		result = 1
	cursor.execute("UPDATE jukebox SET played=NOW(), status = '" + str(result) + 
	"' WHERE id = " + str(idJukebox))
	conn.commit();
	
def make_song_played(idSong= 0):
	cursor.execute("UPDATE songs SET last_played=NOW(), count = count+1 " + 
	"WHERE id = " + str(idSong))
	conn.commit();	

def choose_song():
	cursor.execute("SELECT id, number FROM jukebox " + 
	"WHERE played IS NULL ORDER BY added LIMIT 1")
	
	result = cursor.fetchone()
	if not result:
		print("NO SONG IN THE JUKEBOX > going RANDOM")
		song = get_random_song()
		if song:
			make_song_played(song[0])
			return song
	else:
		print("JUKEBOX SONG No.", result[1])
		song = get_song_info(result[1])
		make_jukebox_song_played(result[0], song)

		if not song:
			print("JUKEBOX SONG DOES NOT EXIST > going RANDOM")
			return get_random_song()
		else: 
			make_song_played(song[0])
			return song
	

while True:
	jukeboxsong = choose_song();
	print("PLAYING: ", jukeboxsong[1], " IN FOLDER: ", jukeboxsong[2] );
	song = '/var/www/html/data/' + jukeboxsong[2] + "/" + jukeboxsong[1];
	#print(song)
	mixer.music.load(song)
	mixer.music.play()
	while mixer.music.get_busy() == True:
		read_serial()
		continue
	print("-------------------")
	
	
	
	