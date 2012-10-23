<?php

namespace Epayment;

require_once __DIR__ . '/IRequest.php';
require_once __DIR__ . '/IResponse.php';
require_once __DIR__ . '/Exception.php';

/**
 * Abstraktna trieda response
 * @author Branislav Vaculčiak
 */
abstract class BaseResponse implements IResponse {

	/** @var string */
	protected $accountPrefix;

	/** @var string */
	protected $account;

	/** @var string */
	protected $bankCode;

	/** @var string */
	protected $clientAccountPrefix;

	/** @var string */
	protected $clientAccount;

	/** @var string */
	protected $clientBankCode;

	/** @var string */
	protected $clientName;

	/** @var string */
	protected $vs;

	/** @var string */
	protected $cs;

	/** @var string */
	protected $ss;

	/** @var float */
	protected $price = 0;

	/** @var string */
	protected $currency = 'EUR';

	/** @var array */
	protected $params = array();

	/** @var string */
	protected $returnUrl;

	/** @var string */
	protected $sign = null;

	/** @var boolean */
	protected $isValid = false;

	/** @var boolean */
	protected $isVerified = false;

	/**
	 * Vrati meno klienta
	 * @return string
	 */
	protected function getClientName() {
		return urldecode($this->clientName);
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
	 * Vrati prefix uctu klienta
	 * @return string
	 */
	public function getClientAccountPrefix() {
		return $this->clientAccountPrefix;
	}

	/**
	 * Vrati cislo uctu klienta
	 * @return string
	 */
	public function getClientAccount() {
		return $this->clientAccount;
	}

	/**
	 * Vrati kod banky klienta
	 * @return string
	 */
	public function getClientBankCode() {
		return $this->clientBankCode;
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
	 * Vrati konstantny symbol
	 * @return string
	 */
	public function getCS() {
		return $this->cs;
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
		if (empty($this->params)) {
			return null;
		}
		$params = array();
		foreach (explode(IRequest::PARAMETER_SEPARATOR, $this->params) as $values) {
			$values = explode('=', $values);
			$params[$values[0]] = $values[1];
		}
		return $params;
	}
}