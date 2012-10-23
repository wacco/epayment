<?php

namespace Epayment;

/**
 * Interface response objektu
 * @author Branislav Vaculčiak
 */
interface IResponse {
	
	/** @const */
	const SUCCESS = 1;

	/** @const */
	const FAIL = 2;

	/** @const */
	const TIMEOUT = 3;

	/**
	 * Vrati prefix uctu
	 * @return string
	 */
	public function getAccountPrefix();

	/**
	 * Vrati cislo uctu
	 * @return string
	 */
	public function getAccount();

	/**
	 * Vrati kod banky
	 * @return string
	 */
	public function getBankCode();

	/**
	 * Vrati prefix uctu klienta
	 * @return string
	 */
	public function getClientAccountPrefix();

	/**
	 * Vrati cislo uctu klienta
	 * @return string
	 */
	public function getClientAccount();

	/**
	 * Vrati kod banky klienta
	 * @return string
	 */
	public function getClientBankCode();

	/**
	 * Vrati cenu
	 * @return float
	 */
	public function getPrice();

	/**
	 * Vrati menu
	 * @return string
	 */
	public function getCurrency();

	/**
	 * Vrati variabilny symbol
	 * @return string
	 */
	public function getVS();

	/**
	 * Vrati specificky symbol
	 * @return string
	 */
	public function getSS();

	/**
	 * Vrati parametre obchodnika
	 * @return array
	 */
	public function getParams();

	/**
	 * Vrati informaciu o priebehu spracovania platby
	 * @return int
	 */
	public function getPaymentResponse();
}