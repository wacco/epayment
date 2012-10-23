<?php

namespace Epayment;

/**
 * Interface reqest objektu
 * @author Branislav Vaculčiak
 */
interface IRequest {

	/** @const */
	const PARAMETER_SEPARATOR = '|';

	/**
	 * @param string predcislie uctu
	 * @param string cislo uctu obchodnika
	 */
	public function __construct($account, $accountPrefix);

	/**
	 * Nastavi cenu
	 * @param float
	 * @return IRequest
	 */
	public function setPrice($price);

	/**
	 * Nastavi variabilny symbol
	 * @param string
	 * @return IRequest
	 */
	public function setVS($vs);

	/**
	 * Nastavi specificky symbol
	 * @param string
	 * @return IRequest
	 */
	public function setSS($ss);

	/**
	 * Nastavi parameter obchodnika, ktory bude zaslany naspat s odpovedi
	 * @param string
	 * @param string
	 * @return IRequest
	 */
	public function setParam($key, $value);

	/**
	 * Nastavi URL presmerovania na branu banky
	 * @param string
	 * @return IRequest
	 */
	public function setRedirectUrl($url);

	/**
	 * Nastavi navratovu URL, kam bude zaslany response
	 * @param string
	 * @return IRequest
	 */
	public function setReturnUrl($url);

	/**
	 * Podpise request tajnym klucom obchodnika
	 * @param string tajny kluc obchodnika, ktorym podpisuje poziadavky
	 * @return IRequest
	 */
	public function signMessage($secretKey);

	/**
	 * Overi ci su vsetky udaje spravne a bdue moct byt vygenerovany request
	 * @return boolean
	 */
	public function validate();

	/**
	 * Vrati vygenerovany a podpisany request, ktory bude pouziti na presmerovanie na branu banky
	 * @return string
	 */
	public function getRedirectUrl();
}