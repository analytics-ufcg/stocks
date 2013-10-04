<?php
/*
*This script search investor company pages at facebook and save the page links at facebook_links.txt file.
*script's logic: The investor name is looked at facebook.
*/


set_time_limit (0); 
ignore_user_abort(true);
?> 

<?php
  // Remember to copy files from the SDK's src/ directory to a
  // directory in your application on the server, such as php-sdk/
  require_once('src/facebook.php');
  //for execute this script is necessary to create your own facebook app
  $config = array(
    'appId' => '1395938227299294',
    'secret' => '19c828849bc36d4bb5d2f15f3188bdab',
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
 
?>
<html>
  <head></head>
  <body>

  <?php
  
	$company_name;
    if($user_id) {
			//Read the file with list of investor names
		  $handle = fopen ("DadosEmpresas.csv","r");
		  //Create file which will save pages links
		  $arquivoLinks = fopen("linksPessoasFacebook.txt", 'w+');
		  //loop that read all names
		  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		  	//Column at DadosEmpresas.csv which stay the names
		 	$personNameRow = 17;
			$personName = $data[$personNameRow];
			$personNameFix = $personName;
			$personName = str_replace(" ","+",$personName);
			
			
			try {
					//query format for facebook api.
					$query = '/search?q='."'".$personName."'".'&type=user';
					$ret_obj = $facebook->api($query, 'GET');
					//array with all query anwsers
					$anwserArray = $ret_obj['data'];
					sleep(1);
					
					$person_link = "NA";
					$cont = 0;
					$notFindPerson = TRUE;
					//goes through all anwser list while not find the best similar person name
					while($cont < count($anwserArray) && $notFindPerson){
						
						$person_id = $anwserArray[$cont]['id'];
						$current_person_name = $anwserArray[$cont]['name'];
						//checks similarity between names
						if(preg_match('/'.$current_person_name.'/', $personNameFix) == 1){
							$ret_obj2 = $facebook->api('/'.$person_id, 'GET');
							$person_link = $ret_obj2['link'];
							$notFindPerson = FALSE;
						}
						
						$cont++;
					}
					
					fwrite($arquivoLinks, $person_link.',');

					
			
			
			

        // Give the user a logout link 
        echo '<br /><a href="' . $facebook->getLogoutUrl() . '">logout</a>';
      } catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        $login_url = $facebook->getLoginUrl( array(
                       'scope' => 'publish_stream'
                       )); 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      } 
	  
		}
		
		fclose($arquivoLinks);
		fclose ($handle);
    } else {

      // No user, so print a link for the user to login
      // To post to a user's wall, we need publish_stream permission
      // We'll use the current URL as the redirect_uri, so we don't
      // need to specify it here.
      $login_url = $facebook->getLoginUrl( array( 'scope' => 'publish_stream' ) );
      echo 'Please <a href="' . $login_url . '">login.</a>';

    } 

  ?>      

  </body> 
</html> 
