<?PHP

	
	
	nrns::module('account', ['sql'])
	
	->config(function(){
	
		define('ACCOUNT_WITH_USERNAME', 1);
		define('ACCOUNT_WITH_EMAIL', 2);
		define('ACCOUNT_WITH_USERNAME_AND_EMAIL', 3);

		require "lib/password.php";
		
		require "trait/accountDB.php";
		require "trait/tokens.php";
		require "service/account.php";
		require "service/account_session.php";
		require "service/account_cookie.php";
		require "service/account_header.php";

		require "provider/accountProvider.php";
	
	})
	
	
	
	->provider('accountProvider', 'account\accountProvider')
	;
	

?>