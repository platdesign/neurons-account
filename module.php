<?PHP

	
	
	nrns::module('account', ['sql'])
	
	->config(function(){
	
		require "lib/password.php";
		require "service/account.php";
	
	})
	
	
	->service('account', 'account\account');
	

?>