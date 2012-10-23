<?php

namespace Epayment\CardPay;
use Epayment;

require_once __DIR__ . '/../IRequest.php';
require_once __DIR__ . '/../BaseRequest.php';

/**
 * Reqest objekt pre CardPay branu
 * @author Branislav Vaculčiak
 */
class Request extends Epayment\BaseRequest implements Epayment\IRequest {

	/** @const */
	const BASE_URL = 'https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/e-commerce.jsp';

	/** @var string */
	protected $redirectUrl = self::BASE_URL;

	/** @var string */
	protected $bankCode = '1100';

	/** @var string */
	protected $currency = 978;

	/**
	 * Nastavi parameter obchodnika, ktory bude zaslany naspat s odpovedi
	 * @param string
	 * @param string
	 */
	public function setParam($key, $value) {
		throw new Epayment\Exception('CardPay brana nepodporuje prenos parametrov obchodnika');
	}

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

            // uprava pre PHP < 5.0
            if (strlen($bytesHash) != 20) {
                $bytes = "";
                for ($i = 0; $i < strlen($bytesHash); $i+=2)
                    $bytes .= chr(hexdec(substr($str, $i, 2)));
                $bytesHash = $bytes;
            }

            $des = mcrypt_module_open(MCRYPT_DES, "", MCRYPT_MODE_ECB, "");
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($des), MCRYPT_RAND);
            mcrypt_generic_init($des, $secretKey, $iv);
            $bytesSign = mcrypt_generic($des, substr($bytesHash, 0, 8));
            mcrypt_generic_deinit($des);
            mcrypt_module_close($des);
            $signature = strtoupper(bin2hex($bytesSign));
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
		return "{$this->account}{$this->getFormatedPrice()}{$this->currency}{$this->getFormatedVS()}{$this->getFormatedCS()}{$this->returnUrl}{$this->getPublicIP()}{$this->getFormatedClientName()}";
	}

	/**
	 * Overi ci su vsetky udaje spravne a bude moct byt vygenerovany request
	 * @return boolean
	 */
	public function validate() {
		if (empty($this->clientName)) throw new Epayment\Exception('Chyba v mene klienta');
		if (!preg_match('/^[0-9a-z]{3,4}$/', $this->account)) throw new Epayment\Exception('Chyba v cisle uctu (MIT)');
		if (!preg_match('/^([0-9]+|[0-9]*\\.[0-9]{0,2})$/', $this->getFormatedPrice())) throw new Epayment\Exception('Chyba vo formate ceny');
		if ($this->currency != 978) throw new Epayment\Exception('Chyba v mene');
		if (!preg_match('/^[0-9]{10}$/', $this->getFormatedVS())) throw new Epayment\Exception('Chyba vo formate VS');
		if (!preg_match('/^[0-9]{4}$/', $this->getFormatedCS())) throw new Epayment\Exception('Chyba vo formate CS');
		if (preg_match('[\\;\\?\\&]', $this->returnUrl)) throw new Epayment\Exception('Chyba v navratovej URL');
		if (preg_match('[\\;\\?\\&]', $this->getParamsHash())) throw new Epayment\Exception('Chyba v parametroch');
		return $this->isValid = true;
	}

	/**
	 * Vrati vygenerovany a podpisany request, ktory bude pouziti na presmerovanie na branu banky
	 * @return string
	 */
	public function getRedirectUrl() {
		$params = array(
			'PT' => 'CardPay',
			'MID' => $this->account,
			'AMT' => $this->getFormatedPrice(),
			'CURR' => $this->currency,
			'VS' => $this->getFormatedVS(),
			'CS' => $this->getFormatedCS(),
			'RURL' => $this->returnUrl,
			'NAME' => $this->getFormatedClientName(),
			'IPC' => $this->getPublicIP(),
			'SIGN' => $this->sign
		);

		// TODO: toto treba este implementovat
		/*
        if (!isempty($this->RSMS))
            $url .= "&RSMS=".urlencode($this->RSMS);
        if (!isempty($this->REM))
            $url .= "&REM=".urlencode($this->REM);
        if (!isempty($this->DESC))
            $url .= "&DESC=".urlencode($this->DESC);
        if (!isempty($this->AREDIR))
            $url .= "&AREDIR={$this->AREDIR}";
        if (!isempty($this->LANG))
            $url .= "&LANG={$this->LANT}";
		*/

		return $this->redirectUrl . '?' . http_build_query($params);
	}

	/**
	 * Vrati verejnu IP
	 * Je to potrebne pre testovanie triedy na lokalnej masine
	 */
	protected function getPublicIP() {
		if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://ip.devel.sk');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$ip = curl_exec($ch);
			curl_close($ch);
			return trim($ip);
		}
		return $_SERVER['REMOTE_ADDR'];
	}
}