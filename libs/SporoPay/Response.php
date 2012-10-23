<?php

namespace Epayment\SporoPay;
use Epayment;

require_once __DIR__ . '/../IResponse.php';
require_once __DIR__ . '/../BaseResponse.php';

/**
 * Response objekt pre SporoPay branu
 * @author Branislav Vaculčiak
 */
class Response extends Epayment\BaseResponse implements Epayment\IResponse {

	/** @var string */
	protected $accountPrefix = '000000';

	/** @var string */
	protected $bankCode = '0900';

	/** @var string */
	protected $result;

	/** @var string */
	protected $real;

	/**
	 * @param string predcislie uctu
	 * @param string cislo uctu obchodnika
	 */
	public function __construct($fields = null) {
		if ($fields == null) {
			$fields = $_GET;
		}

		$this->clientAccountPrefix = isset($fields['u_predcislo']) ? $fields['u_predcislo'] : null;
		$this->clientAccount = isset($fields['u_cislo']) ? $fields['u_cislo'] : null;
		$this->clientBankCode = isset($fields['u_kbanky']) ? $fields['u_kbanky'] : null;
		$this->accountPrefix = isset($fields['pu_predcislo']) ? $fields['pu_predcislo'] : null;
		$this->account = isset($fields['pu_cislo']) ? $fields['pu_cislo'] : null;
		$this->bankCode = isset($fields['pu_kbanky']) ? $fields['pu_kbanky'] : null;
		$this->price = isset($fields['suma']) ? $fields['suma'] : null;
		$this->currency = isset($fields['mena']) ? $fields['mena'] : null;
		$this->vs = isset($fields['vs']) ? $fields['vs'] : null;
		$this->ss = isset($fields['ss']) ? $fields['ss'] : null;
		$this->returnUrl = isset($fields['url']) ? $fields['url'] : null;
		$this->params = isset($fields['param']) ? $fields['param'] : null;
		$this->sign = isset($fields['SIGN2']) ? $fields['SIGN2'] : null;
		$this->result = isset($fields['result']) ? $fields['result'] : null;
		$this->real = isset($fields['real']) ? $fields['real'] : null;
	}

	/**
	 * Podpise response tajnym klucom obchodnika
	 * @param string tajny kluc obchodnika, ktorym podpisuje poziadavky
	 * @return IRequest
	 */
	public function verifySignature($secretKey) {
		$signature = null;
		if (!$this->isValid) {
			throw new Epayment\Exception(__METHOD__ . ': Poziadavka zatial nebola validovana.');
		}
		try {
			$bytesHash = sha1($this->getSignatureBase(), true);
			while (strlen($bytesHash) < 24) {
				$bytesHash .= chr(0xFF);
			}

			$ssBytes = base64_decode($secretKey);
			$key = $ssBytes . substr($ssBytes, 0, 8);

			$iv = chr(0x00);
			$iv .= $iv; // 2
			$iv .= $iv; // 4
			$iv .= $iv; // 8

			$signatureBytes = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $bytesHash, MCRYPT_MODE_CBC, $iv);
			$signature = base64_encode($signatureBytes);
		} catch (\Exception $e) {
			return false;
		}
		
		if ($this->sign == $signature) {
			$this->isVerified = true;
			return true;
		}
		return false;
	}

	/**
	 * Vrati zakladne data, ktore sa budu podpisovat
	 * @return string
	 */
	protected function getSignatureBase() {
		return "{$this->clientAccountPrefix};{$this->clientAccount};{$this->clientBankCode};{$this->accountPrefix};{$this->account};{$this->bankCode};{$this->price};{$this->currency};{$this->vs};{$this->ss};{$this->returnUrl};{$this->params};{$this->result};{$this->real}";
	}

	/**
	 * Overi ci su vsetky udaje spravne a bude moct byt vygenerovany request
	 * @return boolean
	 */
	public function validate() {
		if (!preg_match('/^[0-9]*$/', $this->accountPrefix)) return false;
		if (!preg_match('/^[0-9]+$/', $this->account)) return false;
		if (!preg_match('/^[0-9]+$/', $this->bankCode)) return false;

		if (!preg_match('/^[0-9]*$/', $this->clientAccountPrefix)) return false;
		if (!preg_match('/^[0-9]+$/', $this->clientAccount)) return false;
		if ($this->clientBankCode != '0900') return false;

		if (!preg_match('/^([0-9]+|[0-9]*\\.[0-9]{0,2})$/', $this->price)) return false;
		if ($this->currency != 'EUR') return false;
		if (!preg_match('/^[0-9]{10}$/', $this->vs)) return false;
		if (!preg_match('/^[0-9]{10}$/', $this->ss)) return false;
		if (preg_match('[\\;\\?\\&]', $this->returnUrl)) return false;
		$results = array('OK', 'NOK');
		if (!in_array($this->result, $results)) return false;
		if (!in_array($this->real, $results)) return false;
		return $this->isValid = true;
	}

	/**
	 * Vrati informaciu o priebehu spracovania platby
	 * @return int
	 */
	public function getPaymentResponse() {
		if (!$this->isVerified) {
			throw new Epayment\Exception(__METHOD__ . ': Poziadavka zatial nebola verifikovana.');
		}

		if ($this->result == 'OK' && $this->real == 'OK')
			return Epayment\IResponse::SUCCESS;
		if ($this->result == 'OK' && $this->real != 'OK')
			return Epayment\IResponse::TIMEOUT;
		if ($this->result != 'OK')
			return Epayment\IResponse::FAIL;
		return null;
	}
}