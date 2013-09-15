<?php

// constructor should set up DSNs
interface IDatabaseConnection
{
	function Open();
	function Close();

	function SelectSchema($schema);

	// prepared statements should be supported, i.e. select * from table where id = ?
	function Execute($sql, $params = array());
	function Query($sql, $params = array());

	function Prepare($sql);

	function GetLastInsertId();
	function Escape($text);
}

interface IQuery
{
	function Execute($params = array());
}

interface IQueryResult extends Countable, Iterable
{

}

interface IQueryRow extends Countable
{
	function Get($n);
}

class DatabaseConnection implements IDatabaseConnection
{
	public function __construct($connectionString)
	{
		// need to determine the db type and then instantiate the correct database class
		$connectionParameters = new DatabaseConnectionString($connectionString);
		switch (strtolower($connectionParameters->GetDatabaseType()))
		{
			case 'mysql':
				$this->conn = new MySqlDatabaseConnection($connectionParameters);
				break;
			default:
				throw new Exception("");
		}
	}

	function Open()
	{
		$this->CheckValidConnection();

		return $this->conn->Open();
	}

	function Close()
	{
		$this->CheckValidConnection();

		return $this->conn->Close();
	}

	// prepared statements should be supported, i.e. select * from table where id = ?
	function Execute($sql, $params = array())
	{
		$this->CheckValidConnection();

		return $this->conn->Execute($sql, $params);
	}

	function Query($sql, $params = array())
	{
		$this->CheckValidConnection();

		return $this->conn->Query($sql, $params);
	}

	function Prepare($sql)
	{
		$this->CheckValidConnection();

		return $this->conn->Prepare($sql);
	}

	function GetLastInsertId()
	{
		$this->CheckValidConnection();

		return $this->conn->GetLastInsert();
	}

	function Escape($text)
	{
		$this->CheckValidConnection();

		return $this->conn->Escape($text);
	}

	private function CheckValidConnection()
	{
		if (!$this->conn instanceof IDatabaseConnection)
		{
			throw new Exception("No database connection");
		}
	}

	private $conn;
}

class DatabaseConnectionString
{
	public const DatabaseType = "type";
	public const Username = "username";
	public const Password = "password";
	public const Schema = "schema";

	public function __construct($connectionString)
	{
		$this->parameters = explode(";", $connectionString);
	}

	public function GetDatabaseType()
	{
		return $this->GetParameter(self::DatabaseType);
	}

	public function GetUsername()
	{
		return $this->GetParameter(self::Username);
	}

	public function GetPassword()
	{
		return $this->GetParameter(self::Password);
	}

	public function GetSchema()
	{
		return $this->GetParameter(self::Schema);
	}

	public function HasParameter($key)
	{
		return isset($this->parameters[$key]);	
	}

	public function GetParameter($key, $default = null)
	{
		if ($this->HasParameter($key)
		{
			return $this->parameters[$key];
		}
		else
		{
			return $default;
		}
	}

	private $parameters;
}

class MySqlDatabaseConnection implements IDatabaseConnection
{
	public function __construct(DatabaseConnectionString $connectionString)
	{

	}
}

/*
class PostgresDatabaseConnection implements IDatabaseConnection
{

}
*/

// usage

try 
{
	$db = new DatabaseConnection("username=pete;password=1234;database=my-db;");
}
catch (Exception $e)
{
	die("Failed to get db connection");
}

$id = 2;
$db->Execute("delete from randomtable where id = ?", array($id));

$result = $db->Query("select id, name, email from anothertable where id > ? and id < ?");

if ($result == false)
{
	die("No db result");
}

$numberOfRows = count($result);

foreach ($result as $row)
{
	echo $row['id'] . "\n";
	echo $row['name'] . "\n";

	echo $row->id . "\n";
	echo $row->name . "\n";
}


?>