<?php 
namespace account;
use nrns;

trait accountDB {




	protected function db($method, $query, $binds) {
		return $this->sql->{$method}($query, $binds)->execute($this->pdo);
	}


	public function db_createTable() {

		$query = "CREATE TABLE IF NOT EXISTS `".$this->provider->db_table."` (
  		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`username` varchar(127) DEFAULT '',
		`email` varchar(127) DEFAULT '',
		`secret` varchar(255) NOT NULL DEFAULT '',
		`token` varchar(255) DEFAULT NULL,
		`token_expires` int(15) DEFAULT NULL,
		`createTS` int(11) DEFAULT NULL,
		`lastsigninTS` int(11) DEFAULT NULL,
		`lastsigninIP` varchar(11) DEFAULT NULL,
		PRIMARY KEY (`id`)

		) ENGINE=InnoDB CHARSET=utf8;";

		$stmt = $this->pdo->prepare($query)->execute();

	}




	public function db_getByUsername($username) {

		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `username` = :username;';

		$binds = [
			'username'	=>	$username
		];

		return $this->db('selectSingle', $query, $binds);
	}


	public function db_getById($id) {

		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `id` = :id;';

		$binds = [
			'id'	=>	$id
		];

		return $this->db('selectSingle', $query, $binds);
	}

	public function db_getByIdToken($id, $token) {
		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `id` = :id
			AND `token` = :token';

		$binds = [
			'id'	=>	$id,
			'token'	=>	$token
		];

		return $this->db('selectSingle', $query, $binds);
	}


	public function db_getByUsernameOrEmail($username, $email) {
		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `username` = :username
			OR `email` = :email';

		$binds = [
			'username'	=>	$username,
			'email'		=>	$email
		];

		return $this->db('selectSingle', $query, $binds);
	}

	public function db_getByUernameOrEmailForSignIn($username_email) {
		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `username` = :ue
			OR `email` = :ue';

		$binds = [
			'ue'	=>	$username_email
		];

		return $this->db('selectSingle', $query, $binds);
	}

	public function db_getByUsernameEmail($username, $email) {
		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `username` = :username
			AND `email` = :email';

		$binds = [
			'username'	=>	$username,
			'email'		=>	$email
		];

		return $this->db('selectSingle', $query, $binds);
	}

	public function db_getByEmail($email) {


		$query = 
			'SELECT * FROM `'.$this->provider->db_table.'` 
			WHERE `email` = :email';

		$binds = [
			'email'		=>	$email
		];

		return $this->db('selectSingle', $query, $binds);

	}


	/* CREATE */
	public function db_create($username, $email, $secretHash) {

		$query = 
			'INSERT INTO `'.$this->provider->db_table.'` 
			(`username`, `secret`, `email`, `createTS`) 
			VALUES 
			(:username, :secret, :email, UNIX_TIMESTAMP());';

		$binds = [
			'username'	=>	$username,
			'secret'	=>	$secretHash,
			'email'		=>	$email
		];

		return $this->db('insert', $query, $binds);
		
	}


	public function db_deleteById($id) {
		$query = 
			'DELETE FROM `'.$this->provider->db_table.'` WHERE `id` = :id LIMIT 1';

		$binds = [
			'id'	=>	$id
		];

		return $this->db('delete', $query, $binds);

	}


	public function db_updateToken($id, $token, $expires) {


		$query = 
			'UPDATE `'.$this->provider->db_table.'`
			SET 
				`token` = :token,
				`token_expires` = :expires
			WHERE `id` = :id
			LIMIT 1
			';

		$binds = [
			'id'		=>	$id,
			'token'		=>	$token,
			'expires'	=>	$expires
		];

		return $this->db('update', $query, $binds);

	}


	public function db_updateLastSignInForId($id, $ip) {


		$query = 
			'UPDATE `'.$this->provider->db_table.'`
			SET 
				`lastsigninTS` = UNIX_TIMESTAMP(),
				`lastsigninIP` = :ip

			WHERE `id` = :id';

		$binds = [
			'id'	=>	$id,
			'ip'	=>	$ip
		];
		return $this->db('update', $query, $binds);
	}
}


?>