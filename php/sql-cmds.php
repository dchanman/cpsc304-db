<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
//$db_conn = OCILogon("ora_z2p8", "a37087129", "ug");
$db_conn = OCILogon("ora_o6z8", "a33184128", "ug");

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
		// connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	}

	return $statement;
}

function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}
}

/**
 * Hacky function to run a SQL script. The script cannot be terminated with a ;
 * Sorry guys
 *
 * Derek Chan - March 20 2016
 */
function runSQLScript($filename) {
	/* explode is equivalent to Java's 'String.split()' function */
	$cmds = explode(";", file_get_contents($filename));
	foreach ($cmds as &$sqlcmd) {
		if (preg_match('([a-zA-Z])', $sqlcmd)) {
			executePlainSQL($sqlcmd);
		}
	}
}

function printTable($table) {
	$result = executePlainSQL("select * from $table");
	echo "<br>Got data from table $table:<br>";
	printResult($result);
}

/* Prints results from a select statement */
function printResult($result) {
	echo "<table>";

	$ncols = oci_num_fields($result);
	for ($i = 1; $i <= $ncols; $i++) {
		$colname = oci_field_name($result, $i);
		echo "<th><b>" . htmlentities($colname, ENT_QUOTES) . "</b><?/th>\n";
	}

	while (($row = oci_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
	    echo "<tr>\n";
	    foreach ($row as $item) {
	        echo "  <td>".($item !== null ? htmlentities($item, ENT_QUOTES):" ")."</td>\n";
	    }
	    echo "</tr>\n";
	}

	echo "</table>";

}

function getIdFromUsername($username) {
	$s = executePlainSQL("select userid from users where username = '$username'");
	$result = oci_fetch_array($s);
	return $result[0];
}

function getMatches($username) {
	$selectUserId_result = executePlainSQL("select userid from users where username = '$username'");
	printResult($selectUserId_result);
	$users_matches1 = executePlainSQL("select userid2 from successfulmatch where userid1 = '$selectUserId_result'");
	$users_matches2 = executePlainSQL("select userid1 from successfulmatch where userid2 = '$selectUserId_result'");
}

function sendMessage($src_userid, $dest_userid, $msg_str){
	$result = executePlainSQL("insert into message values (MessageIDSequence.nextval, '$src_userid', '$dest_userid', '$msg_str', '24.3.2016'");
}

function query_images($userid) {
	/* Get user information */
	$result = executePlainSQL(
		"SELECT name, age, gender FROM Users U
		WHERE U.userid = $userid"
	);

	$row = oci_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS);
	$name = $row[NAME];
	$age = $row[AGE];

	/* Get their images */
	$result = executePlainSQL(
		"SELECT imageurl FROM Image I
		WHERE userid = $userid
		ORDER BY displayorder"
	);

	$images = array();

	while (($row = oci_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
	    array_push($images, $row[IMAGEURL]);
	}

	$returntuple = array(
		"images" => $images,
		"name" => $name,
		"age" => $age
		);

	return $returntuple;
}

function query_getCommonInterests($userid1, $userid2) {
	$result = executePlainSQL(
		"SELECT interest FROM InterestedIn WHERE userID = $userid1
		INTERSECT
		SELECT interest FROM InterestedIn WHERE userID = $userid2"
	);


	$commonInterests = array();

	while (($row = oci_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
	    array_push($commonInterests, $row[INTEREST]);
	}

	$returntuple = array(
		"commonInterests" => $commonInterests
		);

	return $returntuple;
}

/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode. 
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode */

/* OCI_Fetch_Array() Returns the next row from the result data as an  
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an 
     optinal second parameter which can be any combination of the 
     following constants:

     OCI_BOTH - return an array with both associative and numeric 
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default 
     behavior.  
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc() 
     works).  
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).  
     OCI_RETURN_NULLS - create empty elements for the NULL fields.  
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.  
     Default mode is OCI_BOTH.  */

define()
?>
