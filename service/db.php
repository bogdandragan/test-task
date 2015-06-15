 <?php
class DB
{	
	function __construct()
	{
		# code...
	}

	public static function initDB() {
		// Configuration
		$dbhost = 'localhost';
		$dbname = 'test';

		//Connect to test database
		$m = new MongoClient($dbhost);
		$db = $m->$dbname;

		return $db;
	}

	public static function getUsersCollection() {
		$db = self::initDB();
		$collection = $db->users;

		return $collection;
	}

}
?>
