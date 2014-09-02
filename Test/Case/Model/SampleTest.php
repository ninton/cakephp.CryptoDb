<?php
App::uses('Crypto', 'CryptoDb.Model');

/**
 * Sample Test Case
 *
 */
class SampleTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.crypto_db.sample',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Sample = ClassRegistry::init( 'CryptoDb.Sample' );			
		$this->assertEqual( get_class($this->Sample), 'Sample' );		
		
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
		unset($this->Sample);

		parent::tearDown();
	}

/**
 * test functions
 */
	public function test_find_1() {
		$this->assertEqual( get_class($this->Sample), 'Sample' );
				
		//	fixture
		//		message	: 'This is CryptoDb.Crypto test.'
		//		data_E  : 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"znRS...' 
		//
		//	find result
		//		message : 'This is CryptoDb.Crypto test.'
		//		data_E  : 'This is CryptoDb.Crypto test.'
		//
		$data = $this->process_find_decrypt( 1 );
		
		$this->assertEqual( $data['Sample']['message'], $this->plaintext );
		$this->assertEqual( $data['Sample']['data_E' ], $this->plaintext );
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
		
		$this->assertEqual( $data['Sample']['message'], $this->plaintext );
		$this->assertEqual( $data['Sample']['data_E' ], $this->encrypted );
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
		
		$this->assertEqual( $data['Sample']['message' ], $this->plaintext );
		$this->assertEqual( $data['Sample']['data_raw'], $this->encrypted );
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
		$data['Sample']['message'] = $this->plaintext;
		$data['Sample']['data_E' ] = $this->plaintext;
		
		$this->process_save_encrypted( $data );
		$id = $this->Sample->id; 
		
		$result = $this->process_find_decrypt( $id );
		$this->assertEqual( $result['Sample']['message'], $this->plaintext );
		$this->assertEqual( $result['Sample']['data_E' ], $this->plaintext );

		$result = $this->process_find_not_decrypt_1( $id );
		$this->assertEqual( $result['Sample']['message'], $this->plaintext );
		// [MEMO]
		// Since iv is generated each time,
		// even if it use the same key and the same message,
		// an encrypted data is different.
		// (encrypted data contains iv)  
		$this->assertNotEqual( $result['Sample']['message'], $this->encrypted );
		$this->assertPattern( '/^a:4:{s:4:"algo"/', $result['Sample']['data_E'] );
	}

	public function test_save_2() {
		//	------------------------------------------------------------
		//							message			data_E
		//	------------------------------------------------------------
		//	original				'This is...'	'This is...'
		//	save not encrypt(DB)	'This is...'	'This is...'
		//	find decrypt			'This is...'	'This is...'
		//	find not decrypt		'This is...'	'This is...'
		//	------------------------------------------------------------
		$data = array();
		$data['Sample']['message'] = $this->plaintext;
		$data['Sample']['data_E' ] = $this->plaintext;
		
		$this->process_save_not_encrypted( $data );
		$id = $this->Sample->id;
		
		$result = $this->process_find_decrypt( $id );
		$this->assertEqual( $result['Sample']['message'], $this->plaintext );
		$this->assertEqual( $result['Sample']['data_E' ], $this->plaintext );		

		$result = $this->process_find_not_decrypt_1( $id );
		$this->assertEqual( $result['Sample']['message'], $this->plaintext );
		$this->assertEqual( $result['Sample']['data_E' ], $this->plaintext );	
	}

	public function process_find_decrypt( $i_id ) {
		$data = $this->Sample->findById( $i_id );
		return $data;
	}

	public function process_find_not_decrypt_1( $i_id ) {
		$cryptoFields = $this->Sample->cryptoFields;
		
		$this->Sample->cryptoFields = array();
		$data = $this->Sample->findById( $i_id );
		
		$this->Sample->cryptoFields = $cryptoFields;
		return $data;		
	}
	
	public function process_find_not_decrypt_2( $i_id ) {
		$this->Sample->virtualFields['data_raw'] = 'data_E';
		$data = $this->Sample->findById( $i_id );
		return $data;
	}
	
	public function process_save_encrypted( $i_data ) {
		$this->Sample->save( $i_data );
	}

	public function process_save_not_encrypted( $i_data ) {
		$params['callbacks'] = false;
		$this->Sample->saveAll( $i_data, $params );
	}
	
	public function test_blank() {
		$data = $this->Sample->findById( 2 );
		
		$this->assertEqual( $data['Sample']['id'     ], 2 );
		$this->assertEqual( $data['Sample']['message'], 'blank test' );
		$this->assertEqual( $data['Sample']['data_E' ], '' );
	}

	public function test_unserialize_error() {
		$data = $this->Sample->findById( 3 );
		
		$this->assertEqual( $data['Sample']['id'     ], 3 );
		$this->assertEqual( $data['Sample']['message'], 'unserialize error test' );
		$this->assertEqual( $data['Sample']['data_E' ], '_x_y_z_' );
	}
}
