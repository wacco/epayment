<?php

namespace Epayment\CardPay;
use Epayment;

require_once __DIR__ . '/../IResponse.php';
require_once __DIR__ . '/../TatraPay/Response.php';

/**
 * Response objekt pre CardPay branu
 * @author Branislav Vaculčiak
 */
class Response extends Epayment\TatraPay\Response implements Epayment\IResponse {

	/** @var string */
	protected $ac;

	/**
	 * @param string predcislie uctu
	 * @param string cislo uctu obchodnika
	 */
	public function __construct($fields = null) {
		if ($fields == null) {
			$fields = $_GET;
		}

		$this->vs = isset($fields['VS']) ? $fields['VS'] : null;
		$this->result = isset($fields['RES']) ? $fields['RES'] : null;
		$this->ac = isset($fields['AC']) ? $fields['AC'] : null;
		$this->sign = isset($fields['SIGN']) ? $fields['SIGN'] : null;
	}

	/**
	 * Vrati zakladne data, ktore sa budu podpisovat
	 * @return string
	 */
	protected function getSignatureBase() {
		return "{$this->vs}{$this->result}{$this->ac}";
	}

	/**
	 * Overi ci su vsetky udaje spravne a bude moct byt vygenerovany request
	 * @return boolean
	 */
	public function validate() {
		if (empty($this->vs)) throw new Epayment\Exception('Chyba - nebol prijaty VS');
		if (!preg_match('/^[0-9]*$/', $this->ac)) throw new Epayment\Exception('Chyba v AC');
		if (!preg_match('/^[0-9]{10}$/', $this->vs)) throw new Epayment\Exception('Chyba vo formate VS');
		if (!in_array($this->result, array('OK', 'FAIL', 'TOUT'))) throw new Epayment\Exception('Chyba v navratovom stave "result"');
		return $this->isValid = true;
	}
}