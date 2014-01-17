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

	public function __construct($_provider, $injection, $request, $response, $sql) {
		$this->provider = $_provider;
		$this->injection = $injection;
		$this->req = $request;
		$this->res = $response;
		$this->sql = $sql;
		$this->pdo = $this->injection->service($this->provider->pdoService);

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

	public function signUp($username, $secret, $email=NULL) {

		if( $this->validateInput($username, $secret, $email) ) {

			

			if( !$this->getUserByUsernameOrEmail($username, $email) ) {
				$hashedSecret = $this->hashSecret($secret);

				if( $id = $this->db_create($username, $hashedSecret, $email) ) {
					return $this->signIn($username, $secret, $email);
				}

			} else {
				throw new \Exception('Username or email already exists.');
			}



		} else {
			return false;
		}

	}

	public function signIn($username, $secret, $email=null) {

		if( $user = $this->valdiateInputSignIn($username, $secret, $email) ) {
			return $this->setOnline($user);
		}

	}


	protected function getUserByUsernameOrEmail($username, $email) {
		if( !$this->provider->emailRequired ) {
			return $this->db_getByUsername($username);
		} else {
			return $this->db_getByUsernameOrEmail($username, $email);
		}
	}


	protected function validateInput($username, $secret, $email=null) {
		if( 
			$this->provider->validate('username', $username) 
			&&
			$this->provider->validate('email', $email) 
			&&
			$this->provider->validate('secret', $secret) 
		) {
			return true;
		} else {
			throw new \Exception('Missing input.');
		}
	}

	protected function valdiateInputSignIn($username, $secret, $email=null) {
		if( $this->validateInput($username, $secret, $email) ) {

			if( $user = $this->getUserByUsernameOrEmail($username, $email) ) {

				if( $user->email == $email && $user->username == $username ) {
					if( $this->validateSecret($secret, $user->secret) ) {
						return $user;
					} else {
						throw new \Exception('Username and secret does not match.');
					}
				} else {
					throw new \Exception('User not found.');
				}

			} else {
				throw new \Exception('User not found.');
			}

		}
	}

	public function validateSecret($input, $hash) {
		return password_verify($input, $hash);
	}

	public function hashSecret($input) {
		return password_hash($input, PASSWORD_BCRYPT, ['cost'=>10]);
	}

	protected function mergeWith($obj) {
		foreach($this->provider->attrs as $key) {

			if( isset($obj->{$key}) ) {
				$this->{$key} = $obj->{$key};
			}
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


}





?>