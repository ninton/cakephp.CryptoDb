CryptoDb
=================
Copyright (c) Ninton G.K. (http://www.ninton.co.jp)
Licensed under The MIT License

CakePHP Plugin
Encrypt/Decrypt text fields

----------------------------------------
Features
----------------------------------------
1) Plaintext and key are not recorded on binlog. 

2) Salt is generated each time.
   Even if it use the same key and the same message,
   an encrypted data is different.
   (encrypted data contains salt)  

----------------------------------------
MySQL field type	CryptoDb support
----------------------------------------
text				recommended
varchar(255)		plaintext length <= 32

----------------------------------------
Setup steps
----------------------------------------
Example you would encrypt YourModel.message.

1) edit App/Config/bootstrap.php

CakePlugin::loadAll(array(
	'CryptoDb' => array('bootstrap' => true),
));

2) create plugins/CryptoDb/Config/bootstrap.php

Configure::write( 'CryptoDb.cryptoKey', 'Your Cryptography Key' );

See also plugins/CryptoDb/Config/bootstrap.sample.php

*Caution*
Don't change key, if service is started.
The field encrypted with the old key cannot be decode with a new key. 

3) edit App/Model/YourModel.php

App::uses('Crypto', 'CryptDb.Crypto');
class YourModel extends Crypto {
	public $cryptoFields = array(
		'message'
	);
 
4) call save() and find()

$data['YourModel']['message'] = 'This is my secrets';
$YourModel->save( $data );

$data = $YourModel->find('first');
echo $data['YourModel']['message'];
--> 'This is my secrets'

Encrypted message is displayed using phpMyAdmin (or mysql command) 
--> 'a:4:{s:4:"algo";s:12:"rijndael-256";s:4:"mode";s:3:"cbc";s:2:"iv";s:44:"56...'
 
----------------------------------------
How to save without encryption 
----------------------------------------
$params['callbacks'] = false;
$this->saveAll($data, $params);

----------------------------------------
How to find without decryption 
----------------------------------------
way-1.
$this->virtualFields['hoge_raw'] = 'hoge';
$data = $this->find('all', $params);
		
way-2. 
unset($this->cryptoFields['hoge']);
$data = $this->find('all', $params);

