<?php
declare(strict_types=1);
/**
 * Part of this code is written by github.com/clue.
 * The encrypt, bcrypt and hash function has been taken from Laravel.
 */


if (!function_exists('bcrypt')) {
    /**
     * Hash the given value against the bcrypt algorithm.
     *
     * @param string $value
     *
     * @return string
     */
    function bcrypt($value)
    {
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => 10,]);
    }
}

/**
 * This script will remove the default users, and add a user of your choice,
 * IFF (if and only if) the two default users exist in the DB.
 */
debugMessage('Start of init.php');

# database config:
$config = [
    'driver'   => env('DB_CONNECTION', 'mysql'),
    'user'     => env('DB_USERNAME', 'monica'),
    'password' => env('DB_PASSWORD', 'monica'),
    'dbname'   => env('DB_DATABASE', 'monica'),
    'charset'  => 'utf8',
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => env('DB_PORT', '3306'),
];

if (false === dbCheck($config)) {
    errorMessage(sprintf('Could not connect to database using host "%s:%s" and username "%s".', $config['host'], $config['port'], $config['user']));
}
debugMessage('Connection to DB is OK.');

# if only the two default users exist, respond:
$pdo        = dbConnect($config);
$stmt       = $pdo->query('SELECT count(*) as ct FROM users');
$row        = $stmt->fetch();
$createUser = false;
if (2 === (int)$row['ct']) {
    debugMessage('Two users in the DB.');
    $stmt = $pdo->query('SELECT count(*) as ct FROM users where email="admin@admin.com" OR email="blank@blank.com"');
    $row  = $stmt->fetch();
    if (2 === (int)$row['ct']) {
        debugMessage('DB contains only default users.');
        $createUser = true;
    }
}

if (true === $createUser) {
    # delete one of the default users:
    $pdo->exec('DELETE FROM users WHERE email="blank@blank.com"');
    // TODO delete specific accounts, not ALL.
    //$pdo->exec('DELETE FROM accounts');
    debugMessage('Deleted the default users.');

    // update the admin@admin.com account so it reflects the preferred things:
    $password  = randomString(16);
    $encrypted = bcrypt($password);
    $sql       = 'UPDATE users SET email = ?, password = ? WHERE email = ? LIMIT 1';
    $pdo->prepare($sql)->execute([env('FIRST_USER'), $encrypted, 'admin@admin.com']);

    debugMessage('----------------------------------------------------------------');
    debugMessage(sprintf('Created new user "%s" with password: %s', env('FIRST_USER'), $password));
    debugMessage('----------------------------------------------------------------');
}
if (false === $createUser) {
    debugMessage('----------------------------------------------------------------');
    debugMessage('Will NOT remove default users and create user "%s", because users already exist in the system.');
    debugMessage('----------------------------------------------------------------');
}
debugMessage('End of init.php');


/**
 * @param array $config
 *
 * @return PDO
 */
function dbConnect(array $config): PDO
{
    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s;charset=%s',
        $config['driver'], $config['host'], $config['port'],
        $config['dbname'], $config['charset']
    );
    debugMessage(sprintf('The DSN is: %s', $dsn));

    $pdo = new PDO($dsn, $config['user'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

/**
 * @param array $config
 *
 * @return bool
 */
function dbCheck(array $config): bool
{
    try {
        dbConnect($config);

        return true;
    } catch (PDOException $e) {
        debugMessage(sprintf('PDOException: %s', $e->getMessage()));

        return false;
    }
}

/**
 * @param string      $name
 * @param string|null $default
 *
 * @return string|null
 */
function env(string $name, string $default = null): ?string
{
    $v = getenv($name) ?: $default;

    if (null === $v) {
        errorMessage(sprintf('env variable "%s" does not exist', $name));
    }

    return (string)$v;
}


/**
 * @param string $message
 */
function errorMessage(string $message): void
{
    echo sprintf('ERROR: %s%s', $message, PHP_EOL);
    exit (1);
}

/**
 * @param string $message
 */
function debugMessage(string $message): void
{
    echo sprintf('%s%s', $message, PHP_EOL);
}


/**
 * Encrypt the given value.
 *
 * @param mixed $value
 * @param bool  $serialize
 *
 * @return string
 *
 * @throws Exception
 */
function encrypt($value, $serialize = true)
{
    $cipher = 'AES-256-CBC';
    $key    = env('APP_KEY');
    $iv     = random_bytes(openssl_cipher_iv_length($cipher));

    // First we will encrypt the value using OpenSSL. After this is encrypted we
    // will proceed to calculating a MAC for the encrypted value so that this
    // value can be verified later as not having been changed by the users.
    $value = \openssl_encrypt(
        $serialize ? serialize($value) : $value,
        $cipher, $key, 0, $iv
    );

    if (false === $value) {
        errorMessage('Could not encrypt the data.');
    }

    // Once we get the encrypted value we'll go ahead and base64_encode the input
    // vector and create the MAC for the encrypted value so we can then verify
    // its authenticity. Then, we'll JSON the data into the "payload" array.
    $mac = do_hash($iv = base64_encode($iv), $value);

    $json = json_encode(compact('iv', 'value', 'mac'), JSON_THROW_ON_ERROR, 512);

    if (JSON_ERROR_NONE !== json_last_error()) {
        errorMessage('Could not encrypt the data.');
    }

    return base64_encode($json);
}

/**
 * Create a MAC for the given value.
 *
 * @param string $iv
 * @param mixed  $value
 *
 * @return string
 */
function do_hash($iv, $value)
{
    $key = env('APP_KEY');

    return hash_hmac('sha256', $iv . $value, $key);
}


/**
 * Generate a more truly "random" alpha-numeric string.
 *
 * @param int $length
 *
 * @return string
 */
function randomString($length = 16)
{
    $string = '';

    while (($len = strlen($string)) < $length) {
        $size = $length - $len;

        $bytes = random_bytes($size);

        $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }

    return $string;
}
