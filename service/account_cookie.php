<?PHP
namespace account;
use nrns;

class account_cookie extends account implements accountInterface {
	use tokens;

	private $cookie;

	public function init() {
		$this->cookie = $this->injection->service('cookie');
		$this->evaluate();
	}

	public function setOnline($user) {
		$this->renewToken($user);

		$this->mergeWith($user);
		$this->cookie->set( $this->provider->cookieKey, $user->id.':'.$user->token, $this->provider->token_expire, '/' );
		$this->isOnline = true;
		$this->db_setAccountVar($user->id);
		return true;
	}

	public function setOffline() {
		$this->resetToken($this);
		$this->cookie->del( $this->provider->cookieKey, '/' );
	}

	private function evaluate() {

		if( $token = $this->cookie->get( $this->provider->cookieKey ) ) {
			if( $user = $this->getUserByToken( $token ) ) {
				$this->setOnline($user);
			}
		}

	}

}

?>