<?PHP
namespace account;
use nrns;

class account {
	
	const svar = 'uid';
	const table = 'account';
	
	public $id, $username, $email;
	private $pdo, $session, $sql, $cookie, $secret;
	
	public function __construct($session, $cookie,  $sql) {
		$this->session = $session;
		$this->sql = $sql;
		$this->cookie = $cookie;
	}
	
	public function load() {
		
		if( $uid = $this->session->get( account::svar ) OR $uid = $this->cookie->get( account::svar ) ) {
			
			if($account = $this->getById($uid)) {
				$this->mergeWith($account);
			} else {
				$this->signOut();
			}
		
		} else {
			$this->signOut();
		}
		return $this;

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
			

			if( $data = $this->getByUsername($username) ) {
	
				if( $this->checkSecret($secret, $data->secret) ) {
					$this->mergeWith( $data );
					$this->session->set( account::svar, $this->id);
					$this->cookie->set( account::svar, $this->id, 3600, '/');
					return $this;
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
		//$this->load();
		
		return isset($this->id);
	}
	
}

?>