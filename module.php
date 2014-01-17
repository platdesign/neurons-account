<?PHP

	
	
	nrns::module('account', ['sql'])
	
	->config(function(){
	
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