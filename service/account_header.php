<?PHP
namespace account;
use nrns;

class account_header extends account implements accountInterface {
	use tokens;

	public function init() {
		
		$this->evaluate();
	}


	public function setOnline($user) {
		$this->renewToken($user);

		$this->mergeWith($user);
		$this->res->sendHeader( $this->provider->tokenKey.':'.$user->id.':'.$user->token );
		$this->isOnline = true;
		$this->db_setAccountVar($user->id);
		return true;
	}

	public function setOffline() {
		$this->resetToken($this);
		$this->reset();
		$this->isOnline = true;
		return true;
	}

	private function evaluate() {

		if( $token = $this->req->getHeader()->{ $this->provider->tokenKey } ) {
			if( $user = $this->getUserByToken($token) ) {
				$this->setOnline($user);
			}
		}

	}


}

?>