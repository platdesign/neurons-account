<?PHP
namespace account;
use nrns;

class account {
	private $provider;


	const svar = 'uid';
	const table = 'account';
	
	public $id, $username, $email;
	private $pdo, $session, $sql, $cookie, $secret, $injection, $evaluated, $request;
	
	public function __construct($request, $response, $session, $cookie,  $sql, $_provider, $injection) {
		$this->provider = $_provider;
		$this->session = $session;
		$this->sql = $sql;
		$this->cookie = $cookie;
		$this->injection = $injection;
		$this->request = $request;
		$this->response = $response;
	}
	
	public function evaluate() {

		if(!$this->evaluated) {

			if( $this->provider->pdoService ) {
				if( $this->pdo = $this->injection->service($this->provider->pdoService) ) {
					$this->evaluated = true;

					$this->load();

				}
			} else {
				throw new \NRNSException('Missing PDO-Object in account-service');
			}
		}
		
	}

	public function load() {
		
		if( $this->provider->useSession ) {
			$this->load_by_Session();
		}

		if( $this->provider->useCookie ) {
			$this->load_by_Cookie();
		}

		if( $this->provider->useHTTPHeader ) {
			$this->load_by_HTTPHeader();
		}

		return $this;

	}


	
	private function load_by_HTTPHeader() {

		$headerName = $this->provider->accessTokenName;

		if( $token = $this->request->getHeader()->{$headerName} ) {

			if( $user = $this->validateToken($token) ) {
				$this->setUserOnline($user);
			}

		}
	}

	

	private function load_by_Cookie() {

		$cookieName = $this->provider->accessTokenName;

		if( $token = $this->cookie->get($cookieName) ) {
			if( $user = $this->validateToken($token) ) {
				$this->setUserOnline($user);
			}
		}
	}


	private function load_by_Session() {

		$sessionName = $this->provider->accessTokenName;

		if( $id = $this->session->get('uid') ) {
			if( $user = $this->getById($id) ) {
				$this->setUserOnline($user);
			}
		}
	}


	private function setUserOnline($user) {

		if( $this->provider->useSession ) {
			$this->session->set('uid', $user->id);
		}

		if( $this->provider->useCookie ) {
			$this->cookie->set(
				$this->provider->accessTokenName, 
				$this->implodeToken($user), 
				$user->token_expires - time(),
				'/'
			);
		}

		if( $this->provider->useHTTPHeader ) {
			$this->response->sendHeader(
				$this->provider->accessTokenName . ':' . $this->implodeToken($user)
			);
		}

		$this->mergeWith($user);
		return $this->id;
	}



	private function implodeToken($user) {
		return $user->id.':'.$user->token;
	}
	private function validateToken($token) {
		list($id, $token) = explode(':', $token);

		if( $user = $this->getByIdAndToken($id, $token) ) {

			if( $user->token_expires == 1 || $user->token_expires > time() ) {

				if( ($user->token_expires - (60*5)) < time() ) {

					$newToken = md5(time());
					$newExpires = time()+3600;

					if( $this->updateToken($user->id, $newToken, $newExpires) ) {
						$user->token = $newToken;
						$user->token_expires = $newExpires;
					}
				}

				return $user;
			}
		}
	}




	private function mergeWith($data) {
		foreach($this as $key => $val) {
			if( isset($data->{$key}) ) {
				$this->{$key} = $data->{$key};
			}
		}
	}

	private function checkSecret($secret, $hash) {
		return password_verify($secret, $hash);
	}

	private function hashSecret($secret) {
		return password_hash($secret, PASSWORD_BCRYPT, ['cost'=>10]);
	}

	public function getById($id) {
		
		return $this->sql->selectSingle('SELECT * FROM `'.account::table.'` WHERE `id` = :id;', [
			'id'=>$id
		])->execute($this->pdo);
		
	}

	public function getByIdAndToken($id, $token) {

		$query = 
			'SELECT * FROM `'.account::table.'`
			WHERE `id` = :id
			AND `token` = :token';

		$binds = [
			'id'	=>	$id,
			'token'	=>	$token
		];

		return $this->sql->selectSingle($query, $binds)->execute($this->pdo);
	}

	public function getByUsername($username) {
		return $this->sql->selectSingle('SELECT * FROM `'.account::table.'` WHERE `username` = :username;', [
			'username'=>$username
		])->execute($this->pdo);
	}
	
	public function createNew($username, $secret, $email=null) {

		$secretHash = $this->hashSecret($secret);

		return $this->sql->insert('INSERT INTO `'.account::table.'` (`username`, `secret`, `email`) VALUES (:username, :secret, :email);', [
			'username'	=>	$username,
			'secret'	=>	$secretHash,
			'email'		=>	$email
		])->execute($this->pdo);
	}

	private function updateToken($id, $token, $expires) {
		
		$query = 
			'UPDATE `account` 
			SET 
				`token` = :token,
				`token_expires` = :expires
			WHERE `id` = :id;';

		$binds = [
			'id'		=>	$id, 
			'token'		=>	$token,
			'expires'	=>	$expires
		];

		return $this->sql->update($query, $binds)->execute($this->pdo);

	}



	public function setPDO($pdo) {
		$this->pdo = $pdo;
		return $this;
	}
	
	public function signUp($username, $secret, $email=null) {
		
		if( isset($username, $secret) AND !empty($username) AND !empty($secret) ) {

			if( !$data = $this->getByUsername($username) ) {

				if( $this->createNew($username, $secret, $email) ) {

					return $this->signIn($username, $secret);
					

				} else {
					throw new \Exception('Something went wrong!', 401);
				}

			} else {
				throw new \Exception('User already exists!', 401);
			}

		} else {
			throw new \Exception('Missing Data - No Access!', 401);
		}
		
	}
	
	public function signIn($username, $secret) {

		if( isset($username, $secret) AND !empty($username) AND !empty($secret) ) {
			

			if( $user = $this->getByUsername($username) ) {
		
				if( $this->checkSecret($secret, $user->secret) ) {
					if( $this->setUserOnline($user) ) {
						return $this;
					}
				} else {
					throw new \Exception('Username and secret does not match - No Access!', 401);
				}
				
			} else {
				throw new \Exception('User not found! - No Access!', 401);
			}

		} else {
			throw new \Exception('Missing Data - No Access!', 401);
		}
		
	}
	
	public function signOut() {
		$this->session->del( account::svar );
		$this->cookie->del( account::svar, '/' );
		return true;
	}
	
	public function destroy() {
		
	}
	
	public function isOnline() {
		return isset($this->id);
	}
	
}

?>