<html>
 	<head>
  		<title>Welcome to Tinder++</title>
	</head>
 	<body>
		<p>Tinder++ Main Page</p><br>
		<p>UserID:<p><br>
		<form method="POST" action="newuser.php">
			Username: <input type="text" name="username_text" size="6"><br>
			Password: <input type="password" name="password_text" size="6"><br>
			Confirm Password: <input type="password" name="confirm_password_text" size="6"><br>

			Age: <input type="text" name="age_text" size='6'><br>
			Location: <input type="text" name="location_text" size="6"><br>
			Gender: <br>
			<input type="radio" name="gender" value="m"> Male<br>
			<input type="radio" name="gender" value="f"> Female<br>
			Preference: <br>
			Men: <input type="checkbox" name="interestedInMen" value=1>
			Women: <input type="checkbox" name="interestedInWomen" value=1>
			<input type="submit" value="Sign Up!" name="signup">
	</body>
</html>

<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_z2p8", "a37087129", "ug");

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
	} else {

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

function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table tab1:<br>";
	echo "<table>";
	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";

}

// Connect Oracle...
if ($db_conn) {


	if (array_key_exists('signup', $_POST)) {
		// Drop old table...
		$tuple = array (
			":userId_text" => 11112,
			":username_text" => $_POST['username_text'],
			":password_text" => $_POST['password_text'],
			":confirm_password_text" => $_POST['confirm_password_text'],
			":password_hash" => '',
			":username_text" => $_POST['username_text'],
			":gender" => $_POST['gender'],
			":age_text" => 69,
			":location_text" => $_POST['location_text'],
			":preference" => '',
			":interestedInMen" => $_POST['interestedInMen'],
			":interestedInWomen" => $_POST['interestedInWomen'],
			":date_joined" => 6060
			//date("m.d.y")
		);


		if($_POST['interestedInMen'] == 1) {
			$tuple["preference"] .= "m";
		}	

		if($_POST['interestedInWomen'] == 1) {
			$tuple["preference"] .= "f";
		}		


		if($tuple[':password_text'] != $tuple[':confirm_password_text']){
			break;
		}


		$tuple[':password_hash'] = "asdfadsfdaf";
		//password_hash($tuple[':password_text'], PASSWORD_DEFAULT);


		$alltuples = array (
			$tuple
		);

		executeBoundSQL("INSERT INTO users VALUES (:userId_text, :username_text, :date_joined, :location_text, :age_text, :gender, :preference, :password_hash)", $alltuples);

		// Create new table...
		echo "<br> creating new user <br>";
		OCICommit($db_conn);
	} else
		if (array_key_exists('reset', $_POST)) {
			// Drop old table...
			echo "<br> dropping table <br>";
			executePlainSQL("Drop table tab1");

			// Create new table...
			echo "<br> creating new table <br>";
			executePlainSQL("create table tab1 (nid number, name varchar2(30), primary key (nid))");
			OCICommit($db_conn);

		} else
			if (array_key_exists('insertsubmit', $_POST)) {
				//Getting the values from user and insert data into the table
				$tuple = array (
					":bind1" => $_POST['insNo'],
					":bind2" => $_POST['insName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("insert into tab1 values (:bind1, :bind2)", $alltuples);
				OCICommit($db_conn);

			} else
				if (array_key_exists('updatesubmit', $_POST)) {
					// Update tuple using data from user
					$tuple = array (
						":bind1" => $_POST['oldName'],
						":bind2" => $_POST['newName']
					);
					$alltuples = array (
						$tuple
					);
					executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);
					OCICommit($db_conn);

				} else
					if (array_key_exists('dostuff', $_POST)) {
						// Insert data into table...
						executePlainSQL("insert into tab1 values (10, 'Frank')");
						// Inserting data into table using bound variables
						$list1 = array (
							":bind1" => 6,
							":bind2" => "All"
						);
						$list2 = array (
							":bind1" => 7,
							":bind2" => "John"
						);
						$allrows = array (
							$list1,
							$list2
						);
						executeBoundSQL("insert into tab1 values (:bind1, :bind2)", $allrows); //the function takes a list of lists
						// Update data...
						//executePlainSQL("update tab1 set nid=10 where nid=2");
						// Delete data...
						//executePlainSQL("delete from tab1 where nid=1");
						OCICommit($db_conn);
					} else
						if (array_key_exists('dostuff', $_POST)) {
							// Insert data into table...
							executePlainSQL("insert into tab1 values (10, 'Frank')");
							// Inserting data into table using bound variables
							$list1 = array (
								":bind1" => 6,
								":bind2" => "All"
							);
							$list2 = array (
								":bind1" => 7,
								":bind2" => "John"
							);
							$allrows = array (
								$list1,
								$list2
							);
							executeBoundSQL("insert into tab1 values (:bind1, :bind2)", $allrows); //the function takes a list of lists
							// Update data...
							//executePlainSQL("update tab1 set nid=10 where nid=2");
							// Delete data...
							//executePlainSQL("delete from tab1 where nid=1");
							OCICommit($db_conn);
						}

	if ($_POST && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: newuser.php");
	} else {
		// Select data...
		$result = executePlainSQL("select * from tab1");
		printResult($result);
	}

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database
     You will need to replace "username" and "password" for this to
     to work. 
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment 
     statement */

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
?>
