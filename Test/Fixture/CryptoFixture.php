<?php
/**
 * Crypto
 *	CryptoFixture is used only for purpose to CryptoTest.php.
 *	Not necessary to create table `cryptos` on database.
 */
class CryptoFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'message' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'data_E' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		//	Configure::write( 'CryptoDb.cryptoKey', 'CEpSvLtiQ6fbBMfttbhdH3RUXzVwJhWMxAvBhLmsS7WK0K2cI404fw7cJdJflUg3' );
		array(
			'id' => 1,
			'message' => 'This is CryptoDb.Crypto test.',
			'data_E' => 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"568LvA0nRZmE77XFTcUM9ScQ7Bme9YHV+1RieXNJ0Sg=";s:10:"ciphertext";s:88:"lekqSwntbShpjIH+4B3tI7m1hPJZQjd/wrO3T8slWGa/OyCtmaaTCl1KA1jLobhLCztZYYDweDUwDDvT4gGFRQ==";}',
		),
	);

}
