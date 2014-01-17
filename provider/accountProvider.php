<?PHP
namespace account;
use nrns;

class accountProvider extends nrns\provider {

	public $tokenKey 		= 'account-access-token';
	public $sessionKey 		= 'accountID';
	public $cookieKey 		= 'accountID';
	public $token_renew		= 1000;
	public $token_expire	= 3600;


	public $emailRequired	= false;
	public $db_table 		= 'account';
	public $attrs 			= ['id', 'username'];

	private $validators 	= [];

	public function __construct($injection, $nrns) {
		$this->injection = $injection;
	}

	public function method($method) {
		$this->method = strtolower($method);
		return $this;
	}

	public function PDOService($serviceName) {
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



	public function setAttributes($attrs=[]) {
		$this->attrs = $attrs;
		return $this;
	}



	public function emailRequired() {
		$this->emailRequired = true;
		$this->validator('email', function($email) {
			return isset($email) && $email!=NULL && !empty($email) && strlen($email) >= 6;
		});
		return $this;
	}




	public function validate($key, $val){
		$result = true;

		if( $validators = $this->validators[$key] ) {

			foreach($validators as $closure) {
				if( !call_user_func_array($closure->bindTo($this->service), [$val]) ) {
					$result = false;
				}
			}
		}
		
		return $result;
	}

	public function validator($key, $closure) {
		$this->validators[$key][] = $closure;
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