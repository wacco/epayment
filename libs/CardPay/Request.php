<?php

namespace Epayment\CardPay;
use Epayment;

require_once __DIR__ . '/../IRequest.php';
require_once __DIR__ . '/../TatraPay/Request.php';

/**
 * Reqest objekt pre CardPay branu
 * @author Branislav Vaculčiak
 */
class Request extends Epayment\TatraPay\Request implements Epayment\IRequest {

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