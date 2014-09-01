<?php
/**
 *	Encrypt/Decrypt text fields in beforeSave/afterFind
 *
 *	----------------------------------------
 *	MySQL field type	CryptoDb support
 *	----------------------------------------
 *	text				recommended
 *	varchar(255)		plaintext length <= 64
 *	----------------------------------------
 *
 *	----------------------------------------
 *	plaintext length	encrypted length
 *	----------------------------------------
 *		<= 64		 		236
 *		<= 128				324
 *	----------------------------------------
 *
 *	step-1:
 *		App/Config/bootstrap.php
 * 		Configure::write( 'CryptoDb.cryptoKey', 'YOUR CRYPTION KEY' );
 * 
 *	step-2:
 *		App/Model/YourModel.php
 *		App::uses('Crypto', 'CryptDb.Crypto');
 *		class YourModel extends Crypto {
 *			public $cryptoFields = array(
 *				'my_secrets'
 *			);
 *
 * Copyright (c) Ninton G.K. (http://www.ninton.co.jp)
 * Licensed under The MIT License
 *
 * @copyright     Copyright (c) Ninton G.K. (http://www.ninton.co.jp)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
//	save option
//		1. encrypt and save
//		$this->save($data, $params);
//
//		2. save raw data
//		$params['callbacks'] = false;
//		$this->saveAll($data, $params);
//
//	find option
//		1. get and decrypt
//		$data = $this->find('all', $params);
//
//		2. not decrypt
//		$this->virtualFields['hoge_raw'] = 'hoge';
//		$data = $this->find('all', $params);
//		
//		3. not decrypt another way
//		unset($this->cryptoFields['hoge']);
//		$data = $this->find('all', $params);

App::uses('CryptoDbAppModel', 'CryptoDb.Model');
App::uses('Security', 'Utility');
App::uses('Mcrypt', 'CryptoDb.Lib');

class Crypto extends CryptoDbAppModel {
	public $cryptoFields = array();
	public $cryptoKey    = '';
	public $cryptoFunc   = '';
	
	/**
	 * callback functions
	*/
	public function beforeSave( $i_options = array() ) {
		$this->getCryptoKey();
		$this->getCryptoFunc();
		$this->encryptData();
		return parent::beforeSave( $i_options );
	}
	
	public function afterFind($results, $primary = false) {
		$this->getCryptoKey();
		$this->getCryptoFunc();
		
		$this->afterFindDecrypt( $results, $primary );
		return parent::afterFind( $results, $primary );
	}
	
	public function afterFindDecrypt( &$io_results, $i_primary ) {
		$case = 0;
		
		if ( !$this->cryptoFields ) {
			$case = 1;
		} elseif ( isset($io_results[0][0]) ) {
			// $results[0][0]['count']
			$case = 3;
		} elseif ( isset($io_results[0][$this->alias]) ) {
			$case = 11;
		} elseif ( isset($io_results[0]) ) {
			$case = 12;
		} elseif ( isset($io_results[$this->alias]) ) {
			$case = 13;
		} else {
			$case = 14;
		}

		switch ( $case ) {
			case 11:
				foreach ( $io_results as $i => $result ) {
					$this->decryptData( $io_results[$i][$this->alias] );
				}
				break;
			
			case 12:
				foreach ( $io_results as $i => $result ) {
					$this->decryptData( $io_results[$i] );
				}
				break;
			
			case 13:
				$this->decryptData( $io_results[$this->alias] );
				break;
				
			case 14:
				$this->decryptData( $io_results );
				break;
			
			default:
				break;
		}
	}
	
	public function decryptData( &$io_data ) {
		assert( $this->cryptoKey != '' );
	
		foreach ( $this->cryptoFields as $name ) {
			if ( isset($io_data[$name]) ) {
				$io_data[$name] = call_user_func( $this->cryptoFunc, $io_data[$name], $this->cryptoKey, 'decrypt' );
			}
		}
	}
	
	public function encryptData() {
		assert( $this->cryptoKey != '' );
			
		foreach ( $this->cryptoFields as $name ) {
			if ( isset($this->data[$this->alias][$name]) ) {
				$this->data[$this->alias][$name] = call_user_func( $this->cryptoFunc, $this->data[$this->alias][$name], $this->cryptoKey, 'encrypt' );
			}
		}
	}
	
	public function getCryptoKey() {
		if ( '' == $this->cryptoKey ) {
			$this->cryptoKey = Configure::read( 'CryptoDb.model.' . $this->alias . '.cryptoKey' ); 
		}
		if ( '' == $this->cryptoKey ) {
			$this->cryptoKey = Configure::read( 'CryptoDb.cryptoKey' );
		}
		
		assert( $this->cryptoKey != '' );
	}	

	public function getCryptoFunc() {
		/*
		 * Priority
		 * 	
		 * 1.	class YourModel {
		 * 			public cryptoFunc = 'your_crypto_class::crypto_method';
		 * 		}
		 * 		Don't forget App::uses() for include your_crypto_class
		 * 
		 * 2.	Configure::write('CryptoDb.model.YourModel.cryptoFunc', 'your_crypto_class::crypto_method');
		 * 		Don't forget App::uses() for include your_crypto_class
		 *
		 * 3. 	Configure::write('CryptoDb.cryptoFunc', 'your_crypto_class::crypto_method');
		 * 
		 * 4.	'Security::rijndael'
		 */	
	
		if ( '' != $this->cryptoFunc ) {
			return ;
		}
		
		$this->cryptoFunc = Configure::read( 'CryptoDb.model.' . $this->alias . '.cryptoFunc' );
		if ( '' != $this->cryptoFunc ) {
			return ;
		}
		
		$this->cryptoFunc = Configure::read( 'CryptoDb.cryptoFunc' );
		if ( '' != $this->cryptoFunc ) {
			return ;
		}
		
		$this->cryptoFunc = 'Mcrypt::rijndael';
	}	
}
?>