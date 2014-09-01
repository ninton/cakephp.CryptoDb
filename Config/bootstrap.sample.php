<?php
/**
 * Caution:
 *	Don't change key if production server started.
 */
/**
 * example 1:
 *	Default key
 */
Configure::write( 'CryptoDb.cryptoKey', 'oQIm9SlWnLWyE1Ne00WKN3iLEm3YEEyI' );

/**
 * example 2:
 * Individual key for tables.
 */
Configure::write( 'CryptoDb.model.User.cryptoKey'  , 'LCstCFkJehqI1nKleP8fJb6t4jt2TODJ' );
Configure::write( 'CryptoDb.model.Secret.cryptoKey', 'AQMqhnhC6MynUbJP7rnqxMsqr96nSkXv' );

/**
 * example 3:
 *	If you use cloud repository (github etc), you shuould not write key in source file.
 */
Configure::write( 'CryptoDb.cryptoKey', getenv('CryptoDb.cryptoKey') );

/**
 * example 4:
 *	Select Crypto Function	
 */
// CakePHP Security class 
Configure::write( 'CryptoDb.cryptoFunc', 'Security::rijndael' );

// CryptDb Mcrypt class (PHP Mcrypt function wrapper)
Configure::write( 'CryptoDb.cryptoFunc', 'Mcrypt::rijndael' );

?>