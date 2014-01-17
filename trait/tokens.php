<?php 
namespace account;

trait tokens {

	private $token, $token_expires;

	private function createToken($user) {

		return str_replace(':', '', crypt(microtime(1).$user->secret.$user->token_expires));
	}

	

	private function updateToken($user) {

	}

	private function getUserByToken($token) {
		list($id, $token) = explode(':', $token);

		if( $user = $this->db_getByIdToken($id, $token) ) {
			if( $this->validateToken($user) ) {
				return $user;
			}
			
		}
	}

	private function validateToken($user) {

		if( $user->token_expires >= time() ) {
			return true;
		} else {
			$this->resetToken($user);
		}

	}

	private function renewToken($user) {
		if( ($user->token_expires - $this->provider->token_renew) <= time() ) {
			$user->token_expires = time() + $this->provider->token_expire;
			$user->token = $this->createToken($user);

			$this->db_updateToken($user->id, $user->token, $user->token_expires);
		}
	}

	private function resetToken($user) {
		$this->db_updateToken($user->id, NULL, NULL);
	}

}

 ?>