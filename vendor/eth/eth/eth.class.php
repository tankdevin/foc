<?php

require_once __DIR__ . '/vendor/autoload.php';

use Web3\Utils;
use Web3\Contract;
use Elliptic\EC;
use kornrunner\Keccak;
use Web3p\EthereumTx\Transaction;
use Web3\Contracts\Ethabi;
use Web3\Contracts\Types\Address;
use Web3\Contracts\Types\Boolean;
use Web3\Contracts\Types\Bytes;
use Web3\Contracts\Types\DynamicBytes;
use Web3\Contracts\Types\Integer;
use Web3\Contracts\Types\Str;
use Web3\Contracts\Types\Uinteger;
use WebSocket\Client;

class eth
{
    //public static $url = 'https://ropsten.infura.io/v3/19d5973d8da642afb4777d5dae874632';
    public static $url = 'https://mainnet.infura.io/v3/19d5973d8da642afb4777d5dae874632';
    public static $web3;
    public static $eth;
    public static $personal;

    // 保存到网站根目录上层，防止被下载
    public static $keystoreDir = __DIR__ . '/keystore/';

    public static $error = 0;
    public static $errorMessage = '';

    public static function err($msg = '', $error = 1)
    {
        self::$errorMessage = $msg;
        self::$error = $error;
    }

    public static function callback($data)
    {
        if ($data->error == 0) {
            self::$error = 0;
            return $data->result;
        } else {
            self::err($data->message, $data->error);
            return false;
        }
    }

    public static function init($url = '')
    {

        if (empty($url)) {
            $url = self::$url;
        } else {
            self::$url = $url;
        }

        self::$web3 = new Web3\Web3(self::$url);
        self::$eth = self::$web3->eth;
        self::$personal = self::$web3->personal;
    }

    /*
     * 获取当前同步到的区块高度
     * */
    public static function blockNumber()
    {
        $data = new ethCallback();
        self::$eth->blockNumber($data);
        return self::callback($data);
    }

    /*
     * 获取GasPrice
     * */
    public static function gasPrice()
    {
        $data = new ethCallback();
        self::$eth->gasPrice($data);
        return self::callback($data);
    }

    /*
     * 转入区块编号，返回区块数据
     * */
    public static function getBlockByNumber($number)
    {
        $data = new ethCallback();
        self::$eth->getBlockByNumber($number, true, $data);
        return self::callback($data);
    }

    /*
     * 转入区块数字，返回交易列表
     * */
    public static function getBlockTransaction($number)
    {
        try {
            $number = intval($number);
            $data = self::getBlockByNumber($number);
        } catch (\Exception $e) {
            return false;
        }
        if (!$data) {
            return false;
        }
        return $data->transactions;
    }

    /*
     * 转入HASH，返回交易详细内容
     * */
    public static function getTransactionByHash($hash)
    {
        $data = new ethCallback();
        self::$eth->getTransactionByHash($hash, $data);
        return self::callback($data);
    }

    /*
     * geth托管钱包时，新建钱包
     * */
    public static function newAccount($password = '')
    {
        $data = new ethCallback();
        self::$personal->newAccount($password, $data);
        return self::callback($data);
    }

    /*
     * geth托管钱包时，解锁钱包
     * */
    public static function unlockAccount($address = '', $password = '')
    {
        $data = new ethCallback();
        self::$personal->unlockAccount($address, $password, $data);
        return self::callback($data);
    }

    /*
     * 本地钱包时，新建钱包
     * */
    public static function newWallet($password = '')
    {
    	
        try {
            $data = Credential::newWallet($password, self::$keystoreDir);
        } catch (\Exception $e) {
            return false;
        }
        return $data;
    }

    /*
     * geth托管钱包时，解锁钱包
     * */
    public static function loadWallet($address = '', $password = '')
    {
        $data = new ethCallback();
        self::$personal->unlockAccount($address, $password, $data);
        return self::callback($data);
    }

    /*
     * 获取余额
     * */
    public static function getBalance($add)
    {
        $data = new ethCallback();
        self::$eth->getBalance($add, 'latest', $data);
        return self::callback($data);

    }

    /*
     * 发送普通交易
     * */
    public static function sendTransaction($from = '', $to = '', $value = 0)
    {
        $raw = array(
            'from' => $from,
            'to' => $to,
            'value' => '0x' . Utils::toWei($value, 'ether')->toHex()
        );
        $data = new ethCallback();
        self::$eth->sendTransaction($raw, $data);
        return self::callback($data);

    }

    /*
     * 发送裸交易
     * */
    public static function sendRawTransaction($from = '', $password = '', $to = '', $value = '0', $price = '0', $limit = '0', $data = '', $nonce = false)
    {

//        if (!is_string($value) || !is_string($price) || !is_string($limit)) {
//            self::err('转账金额或者转账手续费不是一个字符串');
//            return false;
//        }

        $to = strtolower($to);
        $from = strtolower($from);
        $from = str_replace('0x', '', $from);
        $credential = Credential::fromWallet($password, self::$keystoreDir . $from . '.key');
        
        if ($nonce == false) {
            try {
                $nonce = self::getAccountNonce($credential->getAddress());
                
                if (!is_object($nonce)) {
                    self::err('交易序列获取失败');
                    return false;
                }
            } catch (\Exception $e) {
                self::err($e->getMessage());
                return false;
            }
        }

        /*
         * 此处chainId是超级大坑，不同网络要使用不同ID
         * 0: Olympic, Ethereum public pre-release testnet
        1: Frontier, Homestead, Metropolis, the Ethereum public main network
        1: Classic, the (un)forked public Ethereum Classic main network, chain ID 61
        1: Expanse, an alternative Ethereum implementation, chain ID 2
        2: Morden, the public Ethereum testnet, now Ethereum Classic testnet
        3: Ropsten, the public cross-client Ethereum testnet
        4: Rinkeby, the public Geth PoA testnet
        8: Ubiq, the public Gubiq main network with flux difficulty chain ID 8
        42: Kovan, the public Parity PoA testnet
        77: Sokol, the public POA Network testnet
        99: Core, the public POA Network main network
        7762959: Musicoin, the music blockchain
        61717561: Aquachain, ASIC resistant chain
        [Other]: Could indicate that your connected to a local development test network.
         *
         * */

        $value = Utils::toWei($value, 'ether');
        $value = self::unitToString($value, 'wei');

        $price = Utils::toWei($price, 'gwei');
        $limit = new \phpseclib\Math\BigInteger($limit);

        $raw = array(
            'nonce' => Utils::toHex($nonce, true),
            'gasPrice' => Utils::toHex($price, true),
            'gasLimit' => Utils::toHex($limit, true),
            'to' => $to,
            'value' => Utils::toHex($value, true),
            'chainId' => 1
        );

        if (!empty($data)) {
            $raw['data'] = $data;
        }

        $signed = $credential->signTransaction($raw);
        $data = new ethCallback();
        self::$eth->sendRawTransaction($signed, $data);
        return self::callback($data);
    }

    /*
     * 获取收据
     * */
    public static function getTransactionReceipt($hash)
    {
        $data = new ethCallback();
        self::$eth->getTransactionReceipt($hash, $data);
        
        return self::callback($data);

    }

    /*
     * 获取钱包笔数标识
     * */
    public static function getAccountNonce($address)
    {
        $data = new ethCallback();
        self::$eth->getTransactionCount($address, 'pending', $data);
        return self::callback($data);
    }

    /*
     * 高精度数字类型转字符串
     * */
    public static function unitToString($bn, $unit)
    {
        if (is_array($bn)) {
            $bn = $bn[0]->toString() . '.' . str_pad($bn[1]->toString(), strlen(Utils::UNITS[$unit]) - 1, '0', STR_PAD_LEFT);
            $bn = rtrim($bn, '0');
            return $bn;
        } else {
            return $bn->toString();
        }
    }

    /*
     * 转wei单位
     * */
    public static function toWei($number, $unit)
    {
        $fee = Utils::toWei($number, $unit);
        return $fee->toString();
    }

    /*
     * 转wei单位
     * */
    public static function toEther($number, $unit)
    {
        $number = Utils::toEther($number, $unit);
        $number[] = strlen(Utils::UNITS['ether']);
        return $number;
    }

    /*
      * 返回高精度对象
      * */
    public static function bigNumber($number, $base = 10)
    {
        return new phpseclib\Math\BigInteger($number, $base);
    }

    /*
     * 返回高精度对象
     * */
    public static function hexToBin($number)
    {
        return Utils::hexToBin($number);
    }

    /*
     * 返回高精度对象
     * */
    public static function toHex($number, $isPrefix = false)
    {
        return Utils::toHex($number, $isPrefix = false);
    }

    /*
     * 返回高精度对象
     * */
    public static function bigAdd($number, $add)
    {
        return $number->add($add);
    }

    /*
     * 返回高精度对象
     * */
    public static function loadContract($abi, $contract_address)
    {

        $contract = new Contract(self::$web3->provider, $abi);
        $contract->at($contract_address);

        return $contract;
    }

    public static function loadClient()
    {
        $client = new Client("wss://mainnet.infura.io/ws/v3/097ef64a79ee4bea951c86e812435ac9", [
            'timeout' => 30
        ]);
        return $client;
    }

}

class ethCallback
{
    function __invoke($error, $result)
    {
        if ($error) {
            $this->error = 1;
            $this->message = $error->getMessage();
        } else {
            $this->error = 0;
            $this->result = $result;
        }
    }
}

class Credential
{
    private $keyPair;

    public function __construct($keyPair)
    {
        $this->keyPair = $keyPair;
    }

    public function getPublicKey()
    {
        return $this->keyPair->getPublic()->encode('hex');
    }

    public function getPrivateKey()
    {
        return $this->keyPair->getPrivate()->toString(16, 2);
    }

    public function getAddress()
    {
        $pubkey = $this->getPublicKey();
        return "0x" . substr(Keccak::hash(substr(hex2bin($pubkey), 1), 256), 24);
    }

    public function signTransaction($raw)
    {
        $txreq = new Transaction($raw);
        $privateKey = $this->getPrivateKey();
        $signed = '0x' . $txreq->sign($privateKey);
        return $signed;
    }

    public static function new()
    {
        $ec = new EC('secp256k1');
        
        $keyPair = $ec->genKeyPair();
        
        return new self($keyPair);
    }

    public static function fromKey($privateKey)
    {
        $ec = new EC('secp256k1');
        $keyPair = $ec->keyFromPrivate($privateKey);
        return new self($keyPair);
    }

    public static function newWallet($pass, $dir)
    {
        try{
            $credential = self::new();
            
            $private = $credential->getPrivateKey();
         
            $wallet = KeyStore::save($private, $pass, $dir);
           
            if (empty($wallet)) {
                return false;
            }
            return array(
                'address' => $wallet,
                'private' => $private
            );
        }
         catch (\Exception $e) {
            
            return false;
        }
    }

    public static function fromWallet($pass, $wallet)
    {
        $private = KeyStore::load($pass, $wallet);
        return self::fromKey($private);
    }
}


define("AddressPrefix", "19");
define("NormalType", '57');
define("ContractType", '58');
define("AddressStringLength", 35);
define("AddressLength", 26);
define("KeyCurrentVersion", 4);
define("KeyVersion3", 3);

class KeyStore
{
    static function save($privateKey, $password, $dir, $version = 3)
    {

        $address = self::privateToAddress($privateKey);
        $opts = [];
       
        try {

            $salt = isset($opts['salt']) ? $opts['salt'] : random_bytes(32);
            $iv = isset($opts['iv']) ? $opts['iv'] : random_bytes(16);

            $kdf = isset($opts['kdf']) ? $opts['kdf'] : "scrypt";
            $kdfparams = array(
                "dklen" => isset($opts['dklen']) ? $opts['dklen'] : 32,
                'salt' => bin2hex($salt),
            );
            if ($kdf === 'pbkdf2') {
                $kdfparams['c'] = isset($opts['c']) ? $opts['c'] : 262144;
                $kdfparams['prf'] = 'hmac-sha256';
                $derivedKey = hash_pbkdf2("sha256", $password, $salt, $kdfparams['c'], $kdfparams['dklen'] * 2, false);
            } else if ($kdf = 'scrypt') {
                $kdfparams['n'] = isset($opts['n']) ? $opts['n'] : 262144;
                $kdfparams['r'] = isset($opts['r']) ? $opts['r'] : 8;
                $kdfparams['p'] = isset($opts['p']) ? $opts['p'] : 1;
                $derivedKey = self::getScrypt($password, $salt, $kdfparams['n'], $kdfparams['r'], $kdfparams['p'], $kdfparams['dklen']);
            } else {
                throw new Exception('kdf加密方式未指定');
            }
            $derivedKeyBin = hex2bin($derivedKey); //$derivedKey is a hex string
            $method = 'aes-128-ctr';
            $ciphertext = openssl_encrypt(hex2bin($privateKey), $method, substr($derivedKeyBin, 0, 16), $options = 1, $iv); //binary strinig
            if ($version == KeyCurrentVersion) {
                $mac = hash("sha3-256", substr($derivedKeyBin, 16, 32) . $ciphertext . $iv . $method);
            } else {
                $mac = Keccak::hash(substr($derivedKeyBin, 16, 32) . $ciphertext, '256');
                //$mac = hash("sha3-256", substr($derivedKeyBin,16,32) . $ciphertext);
            }

            $uuid = self::guidv4(random_bytes(16));
            $json = array(
                "version" => $version,
                "id" => $uuid,
                "address" => $address,
                'crypto' => array(
                    'ciphertext' => bin2hex($ciphertext),
                    'cipherparams' => array(
                        'iv' => bin2hex($iv),
                    ),
                    'cipher' => $method,
                    'kdf' => $kdf,
                    'kdfparams' => $kdfparams,
                    'mac' => $mac,
                    'machash' => 'sha3256'
                ),
            );
            
            if (!is_dir($dir)) {
                
                throw new Exception('目录不存在');
            }
            
            $txt = json_encode($json);
            $wallet = $dir . '/' . substr($address, 2) . '.key';
            file_put_contents($wallet, $txt);

        } catch (\Exception $e) {
            return false;
        }

        return $address;
    }


    static function load($password, $wallet)
    {

        $input = file_get_contents($wallet);

        $json = json_decode($input);

        if ($json->version !== KeyVersion3 && $json->version !== KeyCurrentVersion)
            throw new \Exception('Not supported wallet version');
        if ($json->crypto->kdf === 'scrypt') {
            $kdfparams = $json->crypto->kdfparams;
            $derivedKey = self::getScrypt($password, hex2bin($kdfparams->salt), $kdfparams->n, $kdfparams->r, $kdfparams->p, $kdfparams->dklen); //hex string
        } else if ($json->crypto->kdf === 'pbkdf2') {
            $kdfparams = $json->crypto->kdfparams;
            $derivedKey = hash_pbkdf2("sha256", $password, hex2bin($kdfparams->salt), $kdfparams->c, $kdfparams->dklen * 2, false);
        } else {
            throw new \Exception('Unsupported key derivation scheme');
        }
        $derivedKeyBin = hex2bin($derivedKey);
        $ciphertext = hex2bin($json->crypto->ciphertext);
        $method = $json->crypto->cipher;
        $iv = hex2bin($json->crypto->cipherparams->iv);
        if ($json->version === KeyCurrentVersion) {
            $mac = hash('sha3-256', substr($derivedKeyBin, 16, 32) . $ciphertext . $iv . $method);
        } else {
            $mac = Keccak::hash(substr($derivedKeyBin, 16, 32) . $ciphertext, '256');
        }
        if ($mac !== $json->crypto->mac) {
            throw new \Exception('Key derivation failed - possibly wrong passphrase');
        }
        $seed = openssl_decrypt($ciphertext, $method, substr($derivedKeyBin, 0, 16), $options = 1, $iv);
        if (strlen($seed) < 32) {
            $string = hex2bin("00000000" . "00000000" . "00000000" . "00000000" . "00000000" . "00000000" . "00000000" . "00000000") . $seed;
            $seed = substr($string, -32);
        }
        //echo "seed: ", bin2hex($seed) ,PHP_EOL;
        //$this->setPrivateKey(bin2hex($seed));
        return bin2hex($seed);
    }

    static function privateToAddress($priv_hex)
    {
        $ec = new EC('secp256k1');
        $keyPair = $ec->keyFromPrivate($priv_hex);
        $public = $keyPair->getPublic()->encode('hex');
        return "0x" . substr(Keccak::hash(substr(hex2bin($public), 1), 256), 24);
    }

    static function guidv4($data)
    {
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    static function getScrypt($password, $salt, $N, $r, $p, $kdlen)
    {
        if ($N == 0 || ($N & ($N - 1)) != 0) {
            throw new \InvalidArgumentException("N must be > 0 and a power of 2");
        }
        if ($N > PHP_INT_MAX / 128 / $r) {
            throw new \InvalidArgumentException("Parameter N is too large");
        }
        if ($r > PHP_INT_MAX / 128 / $p) {
            throw new \InvalidArgumentException("Parameter r is too large");
        }
        return scrypt($password, $salt, $N, $r, $p, $kdlen);
    }

}