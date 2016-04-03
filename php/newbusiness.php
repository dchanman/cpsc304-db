<html>
 	<head>
  		<title>Welcome to Tinder++</title>
		<?php include 'head-includes.php' ?>
	</head>
 	<body>
		<?php include 'menu.php';?>
		<h2>Tinder++: Signup your Business!</h2>
		<form method="POST" action="newbusiness.php" class="form-inline">
			<input type="text" name="username_text" class="form-control" size="20" placeholder="Business Name"><br>
			<input type="password" name="password_text" class="form-control" size="20" placeholder="Password"><br>
			<input type="password" name="confirm_password_text" class="form-control" size="20" placeholder="Confirm Password"><br>

			Location: <select name="location_text" class="form-control">
				<option value="UBC">UBC</option>
				<option value="Vancouver">Vancouver</option>
				<option value="North Vancouver">North Vancouver</option>
				<option value="Downtown">Downtown Vancouver</option>
				<option value="Langley">Langley</option>
				<option value="Richmond">Richmond</option>
			</select><br>'
			<input type="submit" value="Signup your Business!" class="btn btn-default" name="signup">
		</form>
		</body>
	</body>
</html>

<?php
  include 'credentials.php';
  include 'sql-cmds.php';

  if ($db_conn) {

   if (array_key_exists('signup', $_POST)) {
		
		if($_POST['password_text'] == NULL) {
			echo "Password cannot be blank";
			return;
		}

		if($_POST['password_text'] != $_POST['confirm_password_text']){
			echo "Passwords don't match";
			printResult($result);
			return;
		}



		//$password_hash = crypt($_POST['password_text']);

		$resultArr = insert_addNewBusiness($_POST['username_text'], $_POST['location_text'], $_POST['password_text']);

		if ($resultArr["SUCCESS"] == 0) {
			if ($resultArr["ERRCODE"] == 1) {
				echo "Username already exists!";
			} else {
				echo "Uh oh, unrecognized error code: ";
				echo $resultArr['ERRCODE'];
			}
		} else {
			/* Made a new account successfully */

			/* Commit to database */
			OCICommit($db_conn);
    		OCILogoff($db_conn);

			/* Redirect to login page */
    		header("Location: business_login.php");
		}
	}

    /* Commit to save changes... */
    OCICommit($db_conn);

    /* LOG OFF WHEN YOU'RE DONE! */
    OCILogoff($db_conn);

  } else { /* if ($db_conn) */
    echo "cannot connect";
    $e = OCI_Error(); // For OCILogon errors pass no handle
    echo htmlentities($e['message']);
  }
?>

		
