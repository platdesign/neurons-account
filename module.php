<?PHP

	
	
	nrns::module('account', ['sql'])
	
	->config(function(){
	
		require "lib/password.php";
		require "service/account.php";
		require "provider/accountProvider.php";
	
	})
	
	
	
	->provider('accountProvider', 'account\accountProvider')
	;
	

?>