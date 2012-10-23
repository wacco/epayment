<?php

namespace Epayment;

require_once __DIR__ . '/IRequest.php';
require_once __DIR__ . '/Exception.php';

/**
 * Abstraktna trieda request
 * @author Branislav Vaculčiak
 */
abstract class BaseRequest implements IRequest {

	/** @var string */
	protected $accountPrefix;

	/** @var string */
	protected $account;

	/** @var string */
	protected $bankCode;

	/** @var string */
	protected $vs;

	/** @var string */
	protected $ss;

	/** @var float */
	protected $price = 0;

	/** @var string */
	protected $currency = 'EUR';

	/** @var array */
	protected $params = array();

	/** @var string */
	protected $redirectUrl;

	/** @var string */
	protected $returnUrl;

	/** @var string */
	protected $sign = null;

	/** @var boolean */
	protected $isValid = false;

	/**
	 * @param string predcislie uctu
	 * @param string cislo uctu obchodnika
	 */
	public function __construct($account, $accountPrefix = null) {
		$this->account = $account;
		if ($accountPrefix) {
			$this->accountPrefix = $accountPrefix;
		}
	}

	/**
	 * Nastavi cenu
	 * @param float
	 * @return IRequest
	 */
	public function setPrice($price) {
		if (!is_numeric($price)) {
			throw new Exception("Zadana cena musi byt cislo");
		}
		$this->price = (float)$price;
		return $this;
	}

	/**
	 * Nastavi variabilny symbol
	 * @param string
	 * @return IRequest
	 */
	public function setVS($vs) {
		if (!is_string($vs) || strlen($vs) > 10) {
			throw new Exception("Zadany VS nieje string alebo je priliz dlhy");
		}
		$this->vs = $vs;
		return $this;
	}

	/**
	 * Nastavi specificky symbol
	 * @param string
	 * @return IRequest
	 */
	public function setSS($ss) {
		if (!is_string($ss) || strlen($ss) > 10) {
			throw new Exception("Zadany SS nieje string alebo je priliz dlhy");
		}
		$this->ss = $ss;
		return $this;
	}

	/**
	 * Nastavi parameter obchodnika, ktory bude zaslany naspat s odpovedi
	 * @param string
	 * @param string
	 * @return IRequest
	 */
	public function setParam($key, $value) {
		if (!is_string($key)) {
			throw new Exception("Kluc parametru nie je string");
		}
		if (!is_string($value)) {
			throw new Exception("Hodnota parametru nie je string");
		}
		$this->params[$key] = $value;
		return $this;
	}

	/**
	 * Nastavi URL presmerovania na branu banky
	 * @param string
	 * @return IRequest
	 */
	public function setRedirectUrl($url) {
		if (!is_string($url) || !$this->isUrl($url)) {
			throw new Exception("URL pre presmerovanie nie je spravna");
		}
		$this->redirectUrl = $url;
		return $this;
	}

	/**
	 * Nastavi navratovu URL, kam bude zaslany response
	 * @param string
	 * @return IRequest
	 */
	public function setReturnUrl($url) {
		if (!is_string($url) || !$this->isUrl($url)) {
			throw new Exception("Navratova URL nie je spravna");
		}
		$this->returnUrl = $url;
		return $this;
	}

	/**
	 * Vrati vsetky parametre obchodnika v naformatovanom retazci
	 * @return string
	 */
	protected function getParamsHash() {
		return http_build_query($this->params, null, self::PARAMETER_SEPARATOR);
	}

	/**
	 * Vrati prefix uctu
	 * @return string
	 */
	public function getAccountPrefix() {
		return $this->accountPrefix;
	}

	/**
	 * Vrati cislo uctu
	 * @return string
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * Vrati kod banky
	 * @return string
	 */
	public function getBankCode() {
		return $this->bankCode;
	}

	/**
	 * Vrati cenu
	 * @return float
	 */
	public function getPrice() {
		return (float)$this->price;
	}

	/**
	 * Vrati menu
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * Vrati variabilny symbol
	 * @return string
	 */
	public function getVS() {
		return $this->vs;
	}

	/**
	 * Vrati specificky symbol
	 * @return string
	 */
	public function getSS() {
		return $this->ss;
	}

	/**
	 * Vrati parametre obchodnika
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * URL validator
	 * @param string
	 * @return boolean
	 */
	protected function isUrl($value) {
		$alpha = "a-z\x80-\xFF";
		$domain = "[0-9$alpha](?:[-0-9$alpha]{0,61}[0-9$alpha])?";
		$topDomain = "[$alpha][-0-9$alpha]{0,17}[$alpha]";
		return (bool) preg_match("(^https?://(?:(?:$domain\\.)*$topDomain|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:\d{1,5})?(/\S*)?\\z)i", $value);
	}
}