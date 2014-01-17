<?PHP
namespace account;
use nrns;

class accountProvider extends nrns\provider {

	public $accessTokenName = 'account-access-token';

	public function __construct($injection, $nrns) {
		$this->injection = $injection;
		$this->service = $injection->invoke('account\account', ['_provider'=>$this]);
	}

	public function PDOService($serviceName) {
		$this->pdoService = $serviceName;
		return $this;
	}

	public function useSession($bool=true) {
		$this->useSession = $bool;
		return $this;
	}

	public function useCookie($bool=true) {
		$this->useCookie = $bool;
		return $this;
	}

	public function useHTTPHeader($bool=true) {
		$this->useHTTPHeader = $bool;
		return $this;
	}

	public function setAccessTokenName($name) {
		$this->accessTokenName = $name;
	}

	public function getService() {
		$this->service->evaluate();
		return $this->service;
	}

}
	
	
	
	

?>