<?PHP
namespace account;
use nrns;

class accountProvider extends nrns\provider {


	// Constants for Scenation definition
	
	// Sign up with username and secret
	// Sign in with username and secret
	// ACCOUNT_WITH_USERNAME = 1;
		

	// sign up with email and secret
	// sign up with email and secret
	// ACCOUNT_WITH_EMAIL = 2;
		

	// sign up with username, email and secret
	// sign in with username OR email and secret
	// ACCOUNT_WITH_USERNAME_AND_EMAIL = 3;


	// Default scenario: ACCOUNT_WITH_USERNAME_AND_EMAIL
	public $scenario = 3;

	public $tokenKey 		= 'account-access-token';
	public $sessionKey 		= 'accountID';
	public $cookieKey 		= 'accountID';

	public $token_renew		= 1000;
	public $token_expire	= 3600;

	public $db_table 		= 'account';
	public $publicAttrs		= ['id'];

	private $validators 	= [];



	public function __construct($injection, $nrns) {
		$this->injection = $injection;

		

		$this->validator('username', function($val) {

			$minleng = 3;

			if( empty($val) ) {
				throw new \Exception('Username cannot be empty');
			} else if( strlen($val) < $minleng ) {
				throw new \Exception('Username needs at least '.$minleng.' characters');
			}


			return true;
		});

		$this->validator('email', function($val) {
			if( empty($val) ) {
				throw new \Exception('eMail cannot be empty');
			} else if( !filter_var($val, FILTER_VALIDATE_EMAIL) ) {
				throw new \Exception('Invalid eMail-address');
			}
			return true;
		});

		$this->validator('secret', function($val) {
			
			$minleng = 6;

			if( empty($val) ) {
				throw new \Exception('Secret cannot be empty');
			} else if( strlen($val) < $minleng ) {
				throw new \Exception('Secret needs at least '.$minleng.' characters');
			}
			return true;
		});

	}




	public function setScenario($scenario) {
		$this->scenario = $scenario;

		return $this;
	}

	public function setMethod($method) {
		$this->method = strtolower($method);
		return $this;
	}

	public function setPDOServiceName($serviceName) {
		$this->pdoService = $serviceName;
		return $this;
	}

	public function setTokenKey($name) {
		$this->tokenKey = $name;
		return $this;
	}

	public function setSessionKey($key) {
		$this->sessionKey = $key;
		return $this;
	}

	public function setCookieKey($key) {
		$this->cookieKey = $key;
		return $this;
	}



	public function setPublicAttrs() {
		$this->publicAttrs = func_get_args();
		return $this;
	}












	public function validate($key, $val){

		if( isset($this->validators[$key]) ) {
			$validator = $this->validators[$key];

			$result = call_user_func_array($validator->bindTo($this->service), [$val]);

			if($result == false) {
				throw new \Exception('Validation failure on `'.$key.'`');
			}
		}
	}

	public function validator($key, $closure) {
		$this->validators[$key] = $closure;
		return $this;
	}









	public function getService() {
		
		if(!$this->service) {
			$this->service = $this->injection->invoke('account\account_'.$this->method, ['_provider'=>$this]);
		}

		return $this->service;
	}

}
	
	
	
	

?>