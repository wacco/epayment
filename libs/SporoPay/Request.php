<?php

namespace Epayment\SporoPay;
use Epayment;

require_once __DIR__ . '/../IRequest.php';
require_once __DIR__ . '/../BaseRequest.php';

/**
 * Reqest objekt pre SporoPay branu
 * @author Branislav Vaculčiak
 */
class Request extends Epayment\BaseRequest implements Epayment\IRequest {

	/** @const */
	const BASE_URL = 'https://ib.slsp.sk/epayment/epayment/epayment.xml';

	/** @var string */
	protected $accountPrefix = '000000';

	/** @var string */
	protected $redirectUrl = self::BASE_URL;

	/** @var string */
	protected $bankCode = '0900';

	/**
	 * Podpise request tajnym klucom obchodnika
	 * @param string tajny kluc obchodnika, ktorym podpisuje poziadavky
	 * @return IRequest
	 */
	public function signMessage($secretKey) {
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
		$this->sign = $signature;
		return $this;
	}

	/**
	 * Vrati zakladne data, ktore sa budu podpisovat
	 * @return string
	 */
	protected function getSignatureBase() {
		return "{$this->accountPrefix};{$this->account};{$this->bankCode};{$this->getFormatedPrice()};{$this->currency};{$this->getFormatedVS()};{$this->getFormatedSS()};{$this->returnUrl};{$this->getParamsHash()}";
	}

	/**
	 * Vrati spravne naformatovanu cenu
	 * @return string
	 */
	protected function getFormatedPrice() {
		return number_format($this->price, 2, '.', '');
	}

	/**
	 * Vrati spravne naformatovany VS
	 * @return string
	 */
	protected function getFormatedVS() {
		return str_pad($this->vs, 10, '0', STR_PAD_LEFT);
	}

	/**
	 * Vrati spravne naformatovany SS
	 * @return string
	 */
	protected function getFormatedSS() {
		return str_pad($this->ss, 10, '0', STR_PAD_LEFT);
	}

	/**
	 * Overi ci su vsetky udaje spravne a bude moct byt vygenerovany request
	 * @return boolean
	 */
	public function validate() {
		if (!preg_match('/^[0-9]*$/', $this->accountPrefix)) return false;
		if (!preg_match('/^[0-9]+$/', $this->account)) return false;
		if (!preg_match('/^([0-9]+|[0-9]*\\.[0-9]{0,2})$/', $this->getFormatedPrice())) return false;
		if (!preg_match('/^[0-9]{10}$/', $this->getFormatedVS())) return false;
		if (!preg_match('/^[0-9]{10}$/', $this->getFormatedSS())) return false;
		if (preg_match('[\\;\\?\\&]', $this->returnUrl)) return false;
		if (preg_match('[\\;\\?\\&]', $this->getParamsHash())) return false;
		return $this->isValid = true;
	}

	/**
	 * Vrati vygenerovany a podpisany request, ktory bude pouziti na presmerovanie na branu banky
	 * @return string
	 */
	public function getRedirectUrl() {
		$params = array(
			'pu_predcislo' => $this->accountPrefix,
			'pu_cislo' => $this->account,
			'pu_kbanky' => $this->bankCode,
			'suma' => $this->getFormatedPrice(),
			'mena' => $this->currency,
			'vs' => $this->getFormatedVS(),
			'ss' => $this->getFormatedSS(),
			'url' => $this->returnUrl,
			'param' => $this->getParamsHash(),
			'sign1' => $this->sign
		);

		// TODO: toto treba este implementovat
		/*
		if (!isempty($this->acc_prefix))
			$url .= "&acc_prefix={$this->acc_prefix}";
		if (!isempty($this->acc_number))
			$url .= "&acc_number={$this->acc_number}";
		if (!isempty($this->mail_notif_att))
			$url .= "&mail_notif_att={$this->mail_notif_att}";
		if (!isempty($this->email_adr))
			$url .= "&email_adr=".urlencode($this->email_adr);
		if (!isempty($this->client_login))
			$url .= "&clien_login={$this->client_login}";
		if (!isempty($this->auth_tool_type))
			$url .= "&auth_tool_type={$this->auth_tool_type}";
		*/

		return $this->redirectUrl . '?' . http_build_query($params);
	}
}