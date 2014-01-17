<?php 
namespace account;
use nrns;

trait accountDB {

	protected function db($method, $query, $binds) {
		return $this->sql->{$method}($query, $binds)->execute($this->pdo);
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




	/* CREATE */
	public function db_create($username, $secretHash, $email=NULL) {

		$query = 
			'INSERT INTO `'.$this->provider->db_table.'` 
			(`username`, `secret`, `email`) 
			VALUES 
			(:username, :secret, :email);';

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
}


?>