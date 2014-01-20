<?PHP
namespace account;
use nrns;


interface accountInterface {

	public function setOnline($user);

	public function setOffline();

	public function init();

}

class account {
	use accountDB;

	protected $provider, $injection, $req, $res, $sql, $pdo, $isOnline;

	public function __construct($nrns, $client, $_provider, $injection, $request, $response, $sql) {
		$this->provider = $_provider;
		$this->injection = $injection;
		$this->req = $request;
		$this->res = $response;
		$this->sql = $sql;
		$this->pdo = $this->injection->service($this->provider->pdoService);
		$this->client = $client;

		if( $nrns->devMode ) {

			$this->db_createTable();

		}

		$this->init();
	}

	public function signOut() {
		$this->isOnline = false;
		$this->setOffline();
		$this->reset();
		return true;
	}

	public function destroy() {
		if( $this->isOnline() ) {
			$id = $this->id;
			$this->signOut();
			return $this->db_deleteById($id);
		}
	}

	public function publicAttrs() {
		$return = (object) [];

		foreach($this->provider->publicAttrs as $key) {
			if( isset($this->{$key}) ) {
				$return->{$key} = $this->{$key};
			}
		}
		return $return;
	}

	public function signUp() {

		if( $this->isOnline() ) {
			$this->signOut();
		}

		$method = 'signUp_scenario_' . $this->provider->scenario;

		try {
			return call_user_func_array([$this, $method], func_get_args());
		}catch(\Exception $e) {
			$code = ($e->getCode() == 0) ? 401 : $e->getCode();
			throw new \Exception( $e->getMessage(), $code);

		}
		
	}


	private function signUp_scenario_1($username, $secret) {


		$this->provider->validate('username', $username);
		$this->provider->validate('secret', $secret);

		if( !$this->db_getByUsername($username) ) {
			if( $this->createNew($username, $secret) ) {
				return $this->signIn($username, $secret);
			}
		} else {
			throw new \Exception('Username already assigned', 401);
		}
	}

	private function signUp_scenario_2($email, $secret) {
		
		$this->provider->validate('email', $email);
		$this->provider->validate('secret', $secret);

		if( !$this->db_getByEmail($email) ) {
			if( $this->createNew($email, $secret) ) {
				return $this->signIn($email, $secret);
			}
		} else {
			throw new \Exception('eMail-Address already assigned', 401);
		}
	}

	private function signUp_scenario_3($username, $email, $secret) {

		$this->provider->validate('username', $username);
		$this->provider->validate('email', $email);
		$this->provider->validate('secret', $secret);
		
		if( !$exists = $this->db_getByUsernameOrEmail($username, $email) ) {
			if( $this->createNew($username, $email, $secret) ) {
				return $this->signIn($email, $secret);
			}
		} else {
			if( $exists->username == $username ) {
				throw new \Exception('Username already assigned', 401);
			} else if( $exists->email == $email ) {
				throw new \Exception('eMail-Address already assigned', 401);
			}
		}
	}




	public function signIn() {

		if( $this->isOnline() ) {
			$this->signOut();
		}

		$method = 'signIn_scenario_' . $this->provider->scenario;

		if( call_user_func_array([$this, $method], func_get_args()) ) {
			$this->db_updateLastSignInForId($this->id, $this->client->getIp());
			return true;
		}

	}

	private function signIn_scenario_1($username, $secret) {
		// WITH_USERNAME

		if( $user = $this->db_getByUsername($username) ) {
			if( $this->validateSecret($secret, $user->secret) ) {
				return $this->setOnline($user);
			} else {
				throw new \Exception('Username and secret does not match', 401);
			}
			
		} else {
			throw new \Exception('Username not found', 401);
		}
	}

	private function signIn_scenario_2($email, $secret) {
		// WITH_EMAIL
				
		if( $user = $this->db_getByEmail($email) ) {
			if( $this->validateSecret($secret, $user->secret) ) {
				return $this->setOnline($user);
			} else {
				throw new \Exception('eMail and secret does not match', 401);
			}
			
		} else {
			throw new \Exception('eMail not found', 401);
		}
	}

	private function signIn_scenario_3($username_email, $secret) {
		// WITH_USERNAME_EMAIL
				
		if( $user = $this->db_getByUernameOrEmailForSignIn($username_email) ) {
			if( $this->validateSecret($secret, $user->secret) ) {
				return $this->setOnline($user);
			} else {
				throw new \Exception('Username and secret does not match', 401);
			}
			
		} else {
			throw new \Exception('Username or eMail not found', 401);
		}
	}








	public function createNew() {

		switch ($this->provider->scenario) {
			case 1:
				list($username, $secret) = func_get_args();
			break;
			case 2:
				list($email, $secret) = func_get_args();
			break;
			case 3:
				list($username, $email, $secret) = func_get_args();
			break;
		}

		$hashedSecret = $this->hashSecret($secret);
		return $this->db_create($username, $email, $hashedSecret);

	}





	public function validateSecret($input, $hash) {
		return password_verify($input, $hash);
	}

	public function hashSecret($input) {
		return password_hash($input, PASSWORD_BCRYPT, ['cost'=>10]);
	}

	protected function mergeWith($obj) {

		foreach($obj as $key => $val) {
			$this->{$key} = $val;
		}

	}

	protected function reset() {
		foreach($this->provider->attrs as $key) {
			$this->{$key} = NULL;
		}
	}

	public function isOnline() {
		return $this->isOnline;
	}













	public function formSignIn($key, $ctrl) {
		$directive = $this->injection->service('directive');

		$account = $this;

		$directive('form', $key, function()use($ctrl, $account){
			
			$this->on('error', function(){
				$this->reset('secret');
			});

			$this->on('valid', function()use($account){
				$account->signIn($this->scope->value->username, $this->scope->value->secret, $this->scope->value->email);
			});

			$this->callCtrl($ctrl);
		});
	}

	public function formSignUp($key, $ctrl) {
		$directive = $this->injection->service('directive');

		$account = $this;

		$directive('form', $key, function()use($ctrl, $account){

			$this->on('error', function(){
				$this->reset('secret');
			});

			$this->on('valid', function()use($account){
				$account->signUp($this->scope->value->username, $this->scope->value->secret, $this->scope->value->email);
			});

			$this->callCtrl($ctrl);
		});
	}


}





?>