<?php
App::uses('Crypto', 'CryptoDb.Model');

/**
 * Crypto Test Case
 *
 */
class CryptoTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.crypto_db.crypto',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Crypto = ClassRegistry::init( 'CryptoDb.Crypto' );			
		$this->assertEqual( get_class($this->Crypto), 'Crypto' );		
		$this->Crypto->cryptoFields = array('data_E');
		
		Configure::write( 'CryptoDb.cryptoKey', 'CEpSvLtiQ6fbBMfttbhdH3RUXzVwJhWMxAvBhLmsS7WK0K2cI404fw7cJdJflUg3' );
		$this->plaintext = 'This is CryptoDb.Crypto test.';
		$this->encrypted = 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"568LvA0nRZmE77XFTcUM9ScQ7Bme9YHV+1RieXNJ0Sg=";s:10:"ciphertext";s:88:"lekqSwntbShpjIH+4B3tI7m1hPJZQjd/wrO3T8slWGa/OyCtmaaTCl1KA1jLobhLCztZYYDweDUwDDvT4gGFRQ==";}'; 
	}

/**_
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Crypto);

		parent::tearDown();
	}

/**
 * test functions
 */
	public function test_find_1() {
		$this->assertEqual( get_class($this->Crypto), 'Crypto' );
				
		//	fixture
		//		message	: 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...' 
		//
		//	find result
		//		message : 'This is CryptoDb.Crypto test.'
		//		data_E  : 'This is CryptoDb.Crypto test.'
		//
		$data = $this->process_find_decrypt( 1 );
		
		$this->assertEqual( $data['Crypto']['message'], $this->plaintext );
		$this->assertEqual( $data['Crypto']['data_E' ], $this->plaintext );
	}
	
	public function test_find_2() {
		//	fixture
		//		message	: 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...' 
		//
		//	find result
		//		message : 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...'
		//
		$data = $this->process_find_not_decrypt_1( 1 );
		
		$this->assertEqual( $data['Crypto']['message'], $this->plaintext );
		$this->assertEqual( $data['Crypto']['data_E' ], $this->encrypted );
	}
	
	public function test_find_3() {
		//	fixture
		//		message	: 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...' 
		//
		//	find result
		//		message : 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...'
		//
		$data = $this->process_find_not_decrypt_2( 1 );
		
		$this->assertEqual( $data['Crypto']['message' ], $this->plaintext );
		$this->assertEqual( $data['Crypto']['data_raw'], $this->encrypted );
	}
	
	public function test_save_1() {
		//	------------------------------------------------------------
		//						message			data_E
		//	------------------------------------------------------------
		//	original			'This is...'	'This is...'
		//	save encrypt(DB)	'This is...'	'a:4:{s:4:"algo...'
		//	find decrypt		'This is...'	'This is...'
		//	find not decrypt	'This is...'	'a:4:{s:4:"algo...'
		//	------------------------------------------------------------
		$data = array();
		$data['Crypto']['message'] = $this->plaintext;
		$data['Crypto']['data_E' ] = $this->plaintext;
		
		$this->process_save_encrypted( $data );
		$id = $this->Crypto->id; 
		
		$result = $this->process_find_decrypt( $id );
		$this->assertEqual( $result['Crypto']['message'], $this->plaintext );
		$this->assertEqual( $result['Crypto']['data_E' ], $this->plaintext );

		$result = $this->process_find_not_decrypt_1( $id );
		$this->assertEqual( $result['Crypto']['message'], $this->plaintext );
		// [MEMO]
		// Since salt is generated each time,
		// even if it use the same key and the same message,
		// an encrypted data is different.
		// (encrypted data contains salt)  
		$this->assertNotEqual( $result['Crypto']['message'], $this->encrypted );
		$this->assertPattern( '/^a:4:{s:4:"algo"/', $result['Crypto']['data_E'] );
	}

	public function test_save_2() {
		//	------------------------------------------------------------
		//							message			data_E
		//	------------------------------------------------------------
		//	original				'This is...'	'This is...'
		//	save not encrypt(DB)	'This is...'	'This is...'
		//	find decrypt			'This is...'	''
		//	find not decrypt		'This is...'	'This is...'
		//	------------------------------------------------------------
		$data = array();
		$data['Crypto']['message'] = $this->plaintext;
		$data['Crypto']['data_E' ] = $this->plaintext;
		
		$this->process_save_not_encrypted( $data );
		$id = $this->Crypto->id;
		
		$result = $this->process_find_decrypt( $id );
		$this->assertEqual( $result['Crypto']['message'], $this->plaintext );
		$this->assertEqual( $result['Crypto']['data_E' ], '' );		

		$result = $this->process_find_not_decrypt_1( $id );
		$this->assertEqual( $result['Crypto']['message'], $this->plaintext );
		$this->assertEqual( $result['Crypto']['data_E' ], $this->plaintext );	
	}

	public function process_find_decrypt( $i_id ) {
		$this->Crypto->cryptoFields = array('data_E');
		$data = $this->Crypto->findById( $i_id );
		return $data;
	}

	public function process_find_not_decrypt_1( $i_id ) {
		$this->Crypto->cryptoFields = array();
		$data = $this->Crypto->findById( $i_id );
		return $data;		
	}
	
	public function process_find_not_decrypt_2( $i_id ) {
		$this->Crypto->cryptoFields = array('data_E');
		$this->Crypto->virtualFields['data_raw'] = 'data_E';
		$data = $this->Crypto->findById( $i_id );
		return $data;
	}
	
	public function process_save_encrypted( $i_data ) {
		$this->Crypto->cryptoFields = array('data_E');
		$this->Crypto->save( $i_data );
	}

	public function process_save_not_encrypted( $i_data ) {
		$this->Crypto->cryptoFields = array();
		$data = $this->Crypto->save( $i_data );
	}

}
