<?php

# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"


#This is MMRJason class, do not fuck this file;
#Just exemples - This wil not unsed in here, Not allowed.
#Exemple to update or update data to db.
#
#        $mmr = new MMRJson("add", "americas");
#
#        if($mmr->selectmode == "add")
#        {
#           $mmr->AddData();
#        }
#        if($mmr->selectmode == "update")
#        {
#           //need limit for update filter
#           $mmr->UpdateData(11);
#        }
#        if($mmr->selectmode == "raw")
#        {
#           $mmr->ShowRawData();
#        }
################################################################



class MMRJson
{
	private $json_file;
   	private $json_array;
    private $db;
    private $timestamp;
    //Variables public do not mess
    private $region;
    public  $DEBUG;
    public  $selectmode;
    private $regionArray = array( '_americas' => 'americas' , '_europe' => 'europe', '_sea' => 'sea', '_china' => 'china' );
    private $json_world = array();
    /**
     * Default constructor to get args for mmr drive.
     * @param string $_selectmode Set the string for mode -> (Add, Update)
     * @param string $_region Set regior for mmr query -> (World, Americas, europe , sea , china )
     * @return type
     */
	public function __construct($_selectmode, $_region)
    {
    	//Set region for json file
        $this->region = $_region;
        
        //get json file and send to array
        //Verify if is word and return array of if
        if(strtolower($this->region) == "world")
        {
        	foreach ($this->regionArray as $key => $value) 
        	{
        		$this->json_file = @file_get_contents("http://www.dota2.com/webapi/ILeaderboard/GetDivisionLeaderboard/v0001?division=$value", 0, null, null);
        		$this->json_world[$key] = $this->json_file;
        	}
        }
        else
        {
        	$this->json_file = @file_get_contents("http://www.dota2.com/webapi/ILeaderboard/GetDivisionLeaderboard/v0001?division=$this->region", 0, null, null);
        	$this->json_array = json_decode($this->json_file, true);
        }

        //Databank handle
        $this->db = new CDBConnect("localhost", "root", "Zelda0503", "dota2u");
        $this->DEBUG = "false";

        //Slect mode for query and get timestamp.
        $this->selectmode = $_selectmode;
        $time_stamp = new DateTime();
        $this->timestamp = $time_stamp->getTimestamp();

        //unset variable and delete it.
        unset($time_stamp);
        $time_stamp = null;
    }

    public function __destruct()
    {
 		$this->json_file = null;
	    $this->json_array = null;
        $this->db = null;
        $this->DEBUG = null;
        $this->selectmode = null;
        $this->timestamp = null;
	}

	/**
    * Add Data retrived from json file to mysql db,
    * provied in class.
    */
    public function AddData()
    {

        $limit = 0;
        $json_array = array ();
		$_region = "";

        if(strtolower($this->region == "world"))
        {

        	foreach ($this->json_world as $key => $value) 
        	{
        		//decode required file
        		$this->json_array = json_decode($value, true);
        		$_region = $key; //Get region correcly

        		//Run trought loop
    	 		foreach($this->json_array['leaderboard'] as $item)
            	{
                	$_array = array(
                           'rank' => $item['rank'],
                           'name' => $item['name'],
                           'team_id' => $item['team_id'],
                           'team_tag' => $item['team_tag'],
                           'country' => $item['country'],
                           'sponsor' => $item['sponsor'],
                           'solo_mmr' => $item['solo_mmr'],
                           'timestamp' => $this->timestamp );

                	//Veirfy if the key´s value is empty, if is, insert null string;
                	foreach($_array as $key => $value)
                	{
                    	   if(empty($value))
                       	   		$_array["$key"] = "NULL";
                	}
                //set and insert query
              	$sql_query = "INSERT INTO dota2u.mmr$_region (rank, name, team_id, team_tag, country, sponsor, solo_mmr, timestamp) VALUES (:rank, :name, :team_id, :team_tag, :country, :sponsor, :solo_mmr, :timestamp)";
               	if($this->DEBUG == true)
           		     echo $sql_query."\n";
                $this->db->InsertDataWithBind($sql_query, $_array);
         		}
         	}
        }
        else
        {
        	//loop from array
        	foreach($this->json_array['leaderboard'] as $item)
           	{
                	$_array = array(
            	       'rank' => $item['rank'],
              		   'name' => $item['name'],
                  	   'team_id' => $item['team_id'],
                       'team_tag' => $item['team_tag'],
                       'country' => $item['country'],
                       'sponsor' => $item['sponsor'],
                       'solo_mmr' => $item['solo_mmr'],
                       'timestamp' => $this->timestamp );
                
                //Veirfy if the key´s value is empty, if is, insert null string;
            	foreach($_array as $key => $value)
            	{
                	    if(empty($value))
            				$_array["$key"] = "NULL";
            	}

            	$sql_query = "INSERT INTO dota2u.mmr_$this->region (rank, name, team_id, team_tag, country, sponsor, solo_mmr, timestamp) VALUES (:rank, :name, :team_id, :team_tag, :country, :sponsor, :solo_mmr, :timestamp)";

            	//Execute query and debug info
          		if($this->DEBUG == true) { echo $sql_query."\n";}
            	$this->db->InsertDataWithBind($sql_query, $_array);
        	}
    	}
	}
		   /**
		    * Update Default mmr Data with row limit
		    * @param int $limit_data number row limit for query.
		    * @return null
		    */
           public function UpdateDefaultData($limit_data)
           {

        		$limit = 0;
        		$json_array = array ();
				$_region = "";

				//Bring string to lowercaps to comparer
        		if(strtolower($this->region == "world"))
        		{

        			foreach ($this->json_world as $key => $value) 
        			{
        				$limit = 0;
        				//decode required file
        				$this->json_array = json_decode($value, true);
        				$_region = $key; //Get region correcly

        				//Run trought loop
    	 				foreach($this->json_array['leaderboard'] as $item)
            			{

       						$_array = array(
  	        	       	       'rank' => $item['rank'],
              		   	   	   'name' => $item['name'],
   	              		   	   'team_id' => $item['team_id'],
                   	   		   'team_tag' => $item['team_tag'],
                      		   'country' => $item['country'],
                       		   'sponsor' => $item['sponsor'],
                       		   'solo_mmr' => $item['solo_mmr'],
   	                   		   'timestamp' => $this->timestamp,
               				   'limit' => $limit );

		                	//Veirfy if the key´s value is empty, if is, insert null string;
        		        	foreach($_array as $key => $value)
                			{
                    			   if(empty($value))
                       	   				$_array["$key"] = "NULL";
                			}

            	    		//set and insert query
            	    		if($limit_data == 0)
            	    		{
        	      				$sql_query = "UPDATE dota2u.mmr$_region SET rank=:rank, name=:name, team_id=:team_id, team_tag=:team_tag, country=:country, sponsor=:sponsor, solo_mmr=:solo_mmr, timestamp=:timestamp WHERE PLR_ID=:limit";

        	      				if($this->DEBUG == true) { echo $sql_query."\n"; }

	                			$this->db->InsertDataWithBind($sql_query, $_array);
                				if (++$limit == $this->db->GetRowCount("mmr$_region")) { break; }

            	    		}
            	    		else
            	    		{
            	    			$sql_query = "UPDATE dota2u.mmr$_region  SET rank=:rank, name=:name, team_id=:team_id, team_tag=:team_tag, country=:country, sponsor=:sponsor, solo_mmr=:solo_mmr, timestamp=:timestamp WHERE PLR_ID=:limit";

            	    			if($this->DEBUG == true) { echo $sql_query."\n"; }

	                			$this->db->InsertDataWithBind($sql_query, $_array);
                				if (++$limit == $limit_data) { break; }


            	    		}

            	    		echo "\nWas updated $limit registers with sucesss.\n";
         				}

         			}
        		}
        		else
        		{
	        		//loop from array
        			foreach($this->json_array['leaderboard'] as $item)
           			{

           				$_array = array(
    	        	       'rank' => $item['rank'],
		              	   'name' => $item['name'],
 		   	               'team_id' => $item['team_id'],
	                       'team_tag' => $item['team_tag'],
		                   'country' => $item['country'],
		                   'sponsor' => $item['sponsor'],
		                   'solo_mmr' => $item['solo_mmr'],
	    	               'timestamp' => $this->timestamp,
	                	   'limit' => $limit );	


		                //Veirfy if the key´s value is empty, if is, insert null string;
    	        		foreach($_array as $key => $value)
        	    		{
            	    		    if(empty($value))
            						$_array["$key"] = "NULL";
            			}

            			//set and insert query
            	    	if($limit_data == 0)
            	    	{
        	      			$sql_query = "UPDATE dota2u.mmr_$this->region  SET rank=:rank, name=:name, team_id=:team_id, team_tag=:team_tag, country=:country, sponsor=:sponsor, solo_mmr=:solo_mmr, timestamp=:timestamp WHERE PLR_ID=:limit";

        	      			if($this->DEBUG == true) { echo $sql_query."\n"; }
            				$this->db->InsertDataWithBind($sql_query, $_array);
            				if (++$limit == $this->db->GetRowCount("mmr_$this->region")) { break; }
            	    	}
            	    	else
            	    	{
            	    		$sql_query = "UPDATE dota2u.mmr_$this->region  SET rank=:rank, name=:name, team_id=:team_id, team_tag=:team_tag, country=:country, sponsor=:sponsor, solo_mmr=:solo_mmr, timestamp=:timestamp WHERE PLR_ID=:limit";

            	    		if($this->DEBUG == true) { echo $sql_query."\n"; }

            				$this->db->InsertDataWithBind($sql_query, $_array);
            				if (++$limit == $limit_data) { break; }
            	    	}

                   		echo "\nWas updated $limit registers with sucesss.\n";
                   	}
        		}
    	   }

    /**
    	 * Gets mmr from mysql table.
    	 * @param string $region Region you whant to get
    	 * @param string $mode Select the return mode, json | for json file, array for array
    	 * @return type
    	 */
    public function get_mmr($region, $mode = null , $filepath = null)
    {

			$sql_query = "SELECT * FROM dota2u.mmr_$region";
			#echo $sql_query;
			$array = $this->db->GetArrayResult($sql_query);
			if($mode == "array" || $mode == null)
			{
				return $array;
			}
			else
			{
				if($filepath == null)
				{
					$json = new JsonConvert(".", $array);
					$json->ConvertArrayToJSON();
				}
				else
				{
					$json = new JsonConvert($filepath, $array);
					$json->ConvertArrayToJSON();
				}
			}
    }

       /**
    	 * Gets mmr from mysql table.
    	 * @param string $region Region you whant to get
    	 * @param string $mode Select the return mode, json | for json file, array for array
    	 * @param string $filepath Provide path for save file, if is not given, will be set the defaul path
    	 * @return NULL
    	 */
    	public function get_mmr_with_query($query, $region, $mode = null,  $filepath = null)
    	{

			$sql_query = $query;
			#echo $sql_query;
			//Get array and return as mode selected.
			$array = $this->db->GetArrayResult($sql_query);
			if($mode == "array" || $mode == null)
			{
				return $array;
			}
			else
			{
				if($filepath == null)
				{
					$json = new JsonConvert(".", $array);
					$json->ConvertArrayToJSON();
				}
				else
				{
					$json = new JsonConvert($filepath, $array);
					$json->ConvertArrayToJSON();
				}
			}
    	}


    //Retrive raw data
    public function ShowRawData()
    {
        print_r($this->json_array);
    }
};

class JsonConvert
{
 	#Global variables, note they are almost private.
 	private $filepath;
 	private $array_enconde;

 	/**
 	 * Default construtor
 	 * @param string $_filepath File path of json
 	 * @param array $_array_enconde array to encode
 	 */
 	function __construct ($_filepath, $_array_enconde)
 	{
 		//Pass by value
 		$this->filepath = $_filepath."file.json";
 		$this->array_enconde = $_array_enconde;
 	}
 	/**
 	 * Default destructor
 	 * @return none
 	 */
 	function __destruct ()
 	{
 		$this->filepath = NULL;
 		$this->array_enconde = NULL;
 		$this->debuglvl = 0;
 	}

 	public function ConvertArrayToJSON()
 	{
 		//Open File
 		$fp = fopen($this->filepath, 'w+');
 		if(!$fp) {
 			echo "\nError while opening file, please set permission to write mode.\n";
 			return false;
 			exit;
 		}

 		//Verification
 		if(!file_exists($this->filepath))
 		{
 			printf("Error, can´t save file in %s.\nPlease verify if directory path is valid,\nor have enought permission to write on it.\n", $this->filepath);
 			return false;
 		}
 		
 		//Write file and return true if is sucessfull;
 		fwrite($fp, json_encode($this->array_enconde));
 		fclose($fp);
 		return true;
 	}

 	public function ConvertJSONToArray()
 	{
 		//Get file for JSON.
 		if($this->filepath === null)
 			printf("Error while opening archive.");
 		$json_in = file_get_contents($this->filepath, 0);
 		//Convert to array
 		$convert_json = json_decode($json_in, true);
 		return $convert_json;
 	}

};

 class CDBConnect
{
    private $username;
    private $password;
	private $submit;
	private $host;
	private $db_selected;
	private $mysqli;

	#constructor
	function __construct ($host,$user,$pass,$db_selected)
	{
		#Get Variables | ERROR ERROR  = username undefined variable
		$this->host = $host;
		$this->username = $user;
		$this->password = $pass;
		$this->db_selected = $db_selected;
	}

	#Desctructor
	function __destruct() 
	{
		$this->host = NULL;
		$this->username = NULL;
		$this->password = NULL;
		$this->db_selected = NULL;
		$this->submit = NULL;
	}

	#Get Connection !! DEPRICIATED  USE  GetConnByPDO !!
	private function GetConn()
	{
	 //Debug :
	 //printf("Informations:\n %s, %s, %s\n",$this->host, $this->username, $this->db_selected);
	 //Connect with mysqli
	 $mysqli = new MySQLi($this->host, $this->username, $this->password, $this->db_selected);
     	 //Get Error
   	 	if(mysqli_connect_errno())
	 	{
			 printf("Connect failed: %s\n", mysqli_connect_error());
		     exit();
	 	}
		//Return conn
		return $mysqli;
	}

	/**
	* Get connection by using PDO, with securyty attrib.
	* @return PDO class with charged db socket.
    */
	public function GetConnByPDO()
	{
	   try {
	   $pdo = new PDO(
	   'mysql:host='.$this->host.';dbname='.$this->db_selected.'',
	   $this->username,
	   $this->password);
           $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	   	   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	       $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES UTF8');
	   } catch (PDOException $e) {
		throw new PDOException("Error  : " .$e->getMessage());
	   }
		return $pdo;
	} 

	public function GetResult($_query)
	{
		//open conn
		$link = $this->GetConn();
		//Execute paramented Query
		$link->query($_query);
		//Get info
		//printf("Affected rows (SELECT): %d\n", $link->affected_rows);
		//Returl affected rows numbers
		return $link->affected_rows;
		//Close Conn;
		$link->close();
	}

	/**
	 * Function to insert data in db by query no paramitezed
	 * @param string $query_insert The string from query
	 * @return array Return an array containing the results from query.
	 */
	public function InsertData($query_insert)
	{
		//Get com open
		$link = $this->GetConn();
		//Return error if was pressent a fail
		if(!$link) 
		{ 
			printf("ErrorMessage: %s\n", $mysqli_error); 
		}
		//Execute query from args
		$link->query($query_insert);
		return $link->affected_rows;
		//Close Conn;
		$link = null;
	}
	/**
	 * Execute a query and return the results in array.
	 * @param string $query Query for exectue
	 * @return array return the array from fetch
	 */
	public function GetArrayResult($query)
	{
		//Get com open
		$link = $this->GetConnByPDO();
		//Verify link
		if(!$link)
		{
			echo "\nAn error has ocurred please fix it.\n";
			printf("ErrorMessage: %s\n", $mysqli_error);
		}

		// prepare stmt
		$stmt = $link->prepare($query);
		//execute query
	   	if($stmt->execute() == FALSE)
		{
			echo "\nAn error has ocurred, please see log below:\n";
			$arr = $stmt->errorInfo();
			print_r($arr);
		}
		else
		{

	   		//Fetch to array
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//Close Conn;
			$link = null;
			$stmt = null;

			/* Return Result */
			return $row;
		}

	}

	public function GetRowCount($table)
	{
		$link = $this->GetConnByPDO();
		if(!$link)
		{
            printf("ErrorMessage: %s\n", $mysqli_error);
		}

		$stmt = $link->prepare("SELECT COUNT(*) FROM $table");
		$stmt->execute();

		$row = $stmt->fetchColumn();
		return $row;
	}



	/**
	* Insert a provied query in sql db, no paramented query 
	* (JUST FOR TEST) RISK OF SQL_INJECTION
	*
	**/
	public function InsertDataNoBind($query)
	{
		$link = $this->GetConnByPDO();
		if(!$link)
                {
                        printf("ErrorMessage: %s\n", $mysqli_error);
                }

		$stmt = $link->prepare($query);
		$stmt->execute();

		$link = null;
		$stmt = null;

	}

	/**
	* Insert a query with bind stmt, prevencion of sql injection
	* @param $query -> Query to execute
	* @param array -> array to bind the values
	**/
	  public function InsertDataWithBind($query, $array)
      {
	    //Verify conn
        $link = $this->GetConnByPDO();
        if(!$link)
        {
            printf("ErrorMessage: %s\n", $mysqli_error);
        }

		// prepare stmt
		$stmt = $link->prepare($query);
		//Get data filter for query
		foreach ($array as $key => &$value)
		{
		   if(is_int($value))
		       $param = PDO::PARAM_INT;
		   elseif(is_bool($value))
               $param = PDO::PARAM_BOOL;
		   elseif(is_string($value))
               $param = PDO::PARAM_STR;
		   elseif(is_null($value))
               $param = PDO::PARAM_NULL;
		   else
		   {
		       $param = FALSE;
		   }
		   	#printf("Key = %s | Value = %s | Type = %s\n", $key, $value, $param);
 		  if($param)
			 $stmt->bindParam(":$key",$value,$param);
		}
		//execute query
	   	$stmt->execute();

		//free memory
        $link = null;
        $stmt = null;

      }


	public function HasUser($_query_user)
	{
		//Catch result and returns rows
		if($this->GetResult($_query_user) > 0)
			{
				echo "<p> Usuario logado com sucesso <p>";
				return 1;
			}
			else
			{
				print("Falha no Login");
				return 0;
			}
	}
};