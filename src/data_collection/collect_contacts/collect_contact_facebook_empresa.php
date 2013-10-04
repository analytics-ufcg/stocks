<?php
/*
*This script search company pages at facebook and save the page links at facebook_links.txt file.
*script's logic: The company's name is looked at facebook, if the request is empty the last name of company
*name is removed of name
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
  /*
  *The function string_decrement recive a string parameter and remove the last of string.
  *Return: The string decremented
  */
  function string_decrement($string){
	
	$string_array = explode('+',$string);
	$string_changed = "";
	for($i=0;$i<count($string_array)-1;$i++){
		$string_changed = $string_changed . $string_array[$i]."+";
	}
	
	return substr($string_changed,0,-1);
  }
  
	$company_name;
    if($user_id) {
		  //Read the file with list of companies
		  $handle = fopen ("DadosEmpresas.csv","r");
		  //Create file which will save pages links
		  $arquivoLinks = fopen("facebook_links.txt", 'w+');
		  //loop that read all companies
		  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		  
		  	//Column at DadosEmpresas.csv which is the companies name
			$companyNameRow = 1;
			$companyName = $data[$companyNameRow];
			//remove the word S.A. , LTD. and change BCO by BANCOof companies name
			$companyName = str_replace(" S.A.","",$compame);
			$companyName = str_replace(" LTD.","",$companyName);
			$companyName = str_replace("BCO","BANCO",$companyName);
			$companyName = str_replace(" ","+",$companyName);
			
			try {
					//query format for facebook api.
					$query = '/search?q='.$companyName.'&type=page';
					$ret_obj = $facebook->api($query, 'GET');
					//array with all query anwsers
					$anwserArray = $ret_obj['data'];

					//the company name is only decremented if have 3 or more words
					if(count(explode('+',$companyName) > 2) &&  (count($anwserArray) == 0)){
						
						while(count(explode('+',$companyName)) > 2 && (count($anwserArray) == 0)){
							
							$companyName = string_decrement($companyName);
							$query = '/search?q='.$companyName.'&type=page';
							$ret_obj = $facebook->api($query, 'GET');
							$anwserArray = $ret_obj['data'];
							sleep(3);
							
						}
					}

					$company_link_max = "NA";
					$company_likes_max = 0;
					//goes through all anwser list and get index that have more likes number
					for($i=0;$i < count($anwserArray);$i++){
						$company_id = $anwserArray[$i]['id'];
						$ret_obj2 = $facebook->api('/'.$company_id, 'GET');
						$company_likes = $ret_obj2['likes'];
						$company_link = $ret_obj2['link'];
						if($company_likes > $company_likes_max){
							$company_likes_max = $company_likes;
							$company_link_max = $company_link;
						}
					}
			
					
			
					
					
				
				fwrite($arquivoLinks, $company_link_max.',');
				


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
		fclose ($handle);
		fclose($arquivoLinks);
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


