<?PHP
namespace account;
use nrns;

class account_session extends account implements accountInterface {

	private $session;

	public function init() {
		$this->session = $this->injection->service('session');
		$this->evaluate();
	}



	public function setOnline($user) {
		$this->mergeWith($user);
		$this->session->set( $this->provider->sessionKey, $user->id );
		$this->isOnline = true;
		return true;
	}

	public function setOffline() {
		$this->session->del( $this->provider->sessionKey );
	}

	private function evaluate() {

		if( $id = $this->session->get( $this->provider->sessionKey ) ) {
			if( $user = $this->db_getById($id) ) {
				$this->setOnline($user);
			}
		}

	}
}

?>