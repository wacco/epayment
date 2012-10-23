<?php

namespace Epayment\CardPay;
use Epayment;

require_once __DIR__ . '/../IResponse.php';
require_once __DIR__ . '/../BaseResponse.php';

/**
 * Response objekt pre CardPay branu
 * @author Branislav Vaculčiak
 */
class Response extends Epayment\BaseResponse implements Epayment\IResponse {

	/** @var string */
	protected $bankCode = '1100';

	/** @var string */
	protected $result;

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

	/**
	 * Vrati informaciu o priebehu spracovania platby
	 * @return int
	 */
	public function getPaymentResponse() {
		if (!$this->isVerified) {
			throw new Epayment\Exception(__METHOD__ . ': Poziadavka zatial nebola verifikovana.');
		}

		if ($this->result == 'OK')
			return Epayment\IResponse::SUCCESS;
		if ($this->result == 'TOUT')
			return Epayment\IResponse::TIMEOUT;
		if ($this->result == 'FAIL')
			return Epayment\IResponse::FAIL;
		return null;
	}
}