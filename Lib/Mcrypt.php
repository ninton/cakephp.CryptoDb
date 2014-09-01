<?php
App::uses('Security', 'Utility');

class Mcrypt {
	static public function rijndael( $i_mesg, $i_key, $i_operation ) {
		switch ( $i_operation ) {
			case 'decrypt':
				$data = self::decrypt( $i_mesg, $i_key );
				break;
				
			case 'encrypt':
				$data = self::encrypt( $i_mesg, $i_key );
				break;
			default:
				assert( false );
				break;
		}

		return $data;
	}
	
	static public function encrypt( $i_plaintext, $i_key ) {
		$algo = MCRYPT_RIJNDAEL_256;
		$mode = MCRYPT_MODE_CBC;
		
		$key_size = mcrypt_get_key_size($algo, $mode);
		$key = substr($i_key, 0, $key_size);
		
		$iv_size = mcrypt_get_iv_size($algo, $mode);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
		assert( $iv !== false );
		
		$buf = '';
		try {
			$data = serialize($i_plaintext);
			$ciphertext = mcrypt_encrypt($algo, $key, $data, $mode, $iv);
			
			$encrypted = array(
				'algo' => $algo,
				'mode' => $mode,
				'iv'   => base64_encode($iv),
				'ciphertext' => base64_encode($ciphertext),
			);
			
			$buf = serialize($encrypted);
		} catch (Excepton $e) {
			;
		}
		
		return $buf;
	}
	
	static public function decrypt( $i_buf, $i_key ) {
		$plaintext = '';
		
		try {
			$encrypted = unserialize($i_buf);
			
			$algo = $encrypted['algo'];
			$mode = $encrypted['mode'];
			$iv   = base64_decode($encrypted['iv'  ]);
			$ciphertext = base64_decode($encrypted['ciphertext']);
			
			$key_size = mcrypt_get_key_size($algo, $mode);
			$key = substr($i_key, 0, $key_size);
			
			$data = mcrypt_decrypt($algo, $key, $ciphertext, $mode, $iv);
			$plaintext = unserialize($data);
		} catch(Exception $e) {
			;
		}
		
		return $plaintext;
	}
}

?>