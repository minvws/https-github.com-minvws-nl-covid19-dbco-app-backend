<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Predis\Client as PredisClient;
use Ramsey\Uuid\Uuid;

function tryBlock(string $description, callable $callback) {
    try {
        echo "$description... ";
        flush();
        $result = $callback();
        echo "[OK]\n";
        flush();
        return $result;
    } catch (Throwable $e) {
        echo "[ERROR]\n";
        flush();
        if ($e instanceof RequestException) {
            echo "ERROR:\n";
            echo (string)$e->getResponse()->getBody();
            echo "\n";
        } else {
            echo "ERROR: " . $e->getMessage() . "\n";
        }

        exit;
    }
}

$client = new GuzzleClient(['base_uri' => $_ENV['PUBLIC_API_BASE_URI']]);

$questionnaire = tryBlock(
    'Fetch questionnaire',
    function () use ($client) {
        $response = $client->get('questionnaires');
        $data = json_decode((string)$response->getBody());

        $questionnaires = array_filter($data->questionnaires, fn ($q) => $q->taskType == 'contact');
        if (count($questionnaires) === 0) {
            throw new Exception ('No questionnaire of type "contact" found!');
        }

        return $questionnaires[0];
    }
);


$generalPublicKey = null;
if (isset($_ENV['HEALTHAUTHORITY_PK_KEY_EXCHANGE'])) {
    $generalPublicKey = base64_decode($_ENV['HEALTHAUTHORITY_PK_KEY_EXCHANGE']);
} else if (isset($_ENV['REDIS_HOST'])) {
    $generalPublicKey = tryBlock(
        'Extract health authority public key from secret key in Redis',
        function () {
            $redisClient = new PredisClient(['host' => $_ENV['REDIS_HOST'], 'port' => $_ENV['REDIS_PORT'] ?? 6379]);
            $secretKey = base64_decode($redisClient->get('secretKey:key_exchange'));
            if ($secretKey !== null) {
                return sodium_crypto_box_publickey_from_secretkey($secretKey);
            } else {
                throw new Exception("Health authority key exchange secret key not available in Redis!");
            }
        }
    );
} else {
    echo "ERROR: No key exchange public key specified\n";
    exit(-1);
}

echo "Enter pairing code: ";
$pairingCode = str_replace('-', '', trim(fgets(STDIN)));

$clientKeyPair = tryBlock('Generate client key pair', function () {
   return sodium_crypto_kx_keypair();
});

$clientPublicKey = tryBlock(
    'Extract client public key',
    fn () => sodium_crypto_kx_publickey($clientKeyPair)
);

$sealedClientPublicKey = tryBlock(
    'Encrypt client public key with general health authority public key',
    fn () => sodium_crypto_box_seal($clientPublicKey, $generalPublicKey)
);

$pairingResponse = tryBlock(
    'Pair device to case',
    function () use ($client, $pairingCode, $sealedClientPublicKey) {
        $options = [
            'json' => [
                'pairingCode' => $pairingCode,
                'sealedClientPublicKey' => base64_encode($sealedClientPublicKey)
            ]
        ];

        $response = $client->post('pairings', $options);
        $data = json_decode((string)$response->getBody());

        return $data;
    }
);

$sealedHealthAuthorityPublicKey = base64_decode($pairingResponse->sealedHealthAuthorityPublicKey);
$healthAuthorityPublicKey = tryBlock(
    'Decrypt case specific health authority public key',
    fn () => sodium_crypto_box_seal_open($sealedHealthAuthorityPublicKey, $clientKeyPair)
);

[$rxKey, $txKey] = tryBlock(
    'Calculate shared secret keys',
    fn () => sodium_crypto_kx_client_session_keys($clientKeyPair, $healthAuthorityPublicKey)
);

$token = tryBlock(
    'Derive shared token',
    fn () => bin2hex(sodium_crypto_generichash($rxKey . $txKey))
);

$caseResponse = tryBlock(
    'Fetch case data',
    function () use ($client, $token) {
        $response = $client->get('cases/' . $token);
        $data = json_decode((string)$response->getBody());
        return $data;
    }
);

$case = tryBlock(
    'Decrypt case data',
    fn () => json_decode(
        sodium_crypto_secretbox_open(
            base64_decode($caseResponse->sealedCase->ciphertext),
            base64_decode($caseResponse->sealedCase->nonce),
            $rxKey
        )
    )
);

echo "Case data:\n";
echo json_encode($case, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo "\n";

$filledCase = tryBlock(
    'Fill out case answers',
    function () use ($questionnaire, $case) {
        $filledCase = json_decode(json_encode($case)); // deep clone

        $extraTask = new stdClass();
        $extraTask->uuid = Uuid::uuid4()->toString();
        $extraTask->taskType = 'contact';
        $extraTask->source = 'app';
        $extraTask->label = 'Random ' . rand(1, 10000);
        $extraTask->taskContext = 'Feestje';
        $extraTask->category = '3';
        $extraTask->communication = 'staff';
        $extraTask->dateOfLastExposure = date('Y-m-d', strtotime('-2 days'));
        $filledCase->tasks[] = $extraTask;

        foreach ($filledCase->tasks as $task) {
            $task->questionnaireResult = new stdClass();
            $task->questionnaireResult->questionnaireUuid = $questionnaire->uuid;

            $task->questionnaireResult->answers = [];
            foreach ($questionnaire->questions as $question) {
                $answer = new stdClass();
                $answer->uuid = Uuid::uuid4()->toString();
                $answer->lastModified = gmdate(DATE_ATOM);
                $answer->questionUuid = $question->uuid;
                $answer->value = new stdClass();

                switch ($question->questionType) {
                    case 'open':
                        $answer->value->value = 'Example text for open question';
                        break;
                    case 'classificationdetails':
                        $answer->value->category1Risk = true;
                        $answer->value->category2ARisk = false;
                        $answer->value->category2BRisk = false;
                        $answer->value->category3Risk = false;
                        break;
                    case 'contactdetails':
                        $answer->value->firstName = 'John';
                        $answer->value->lastName = 'Doe';
                        $answer->value->phoneNumber = '555-1234';
                        $answer->value->email = 'john@example.org';
                        break;
                    case 'contactdetails_full':
                        $answer->value->firstName = 'John';
                        $answer->value->lastName = 'Doe';
                        $answer->value->phoneNumber = '555-1234';
                        $answer->value->email = 'john@example.org';
                        $answer->value->address1 = 'Wegisweg';
                        $answer->value->houseNumber = '1';
                        $answer->value->address2 = '';
                        $answer->value->zipcode = '9999 XX';
                        $answer->value->city = 'Lanterfanten';
                        break;
                    case 'multiplechoice':
                        $answer->value->value = $question->answerOptions[0]->value;
                        break;
                    case 'date':
                        $answer->value->value = date('Y-m-d');
                        break;
                    case 'text':
                        $answer->value->value = 'Example text for text question';
                        break;
                    default:
                        throw new Exception('Unknown question type: ' . $question->type);
                }

                $task->questionnaireResult->answers[] = $answer;
            }
        }

        return $filledCase;
    }
);

echo "Filled case data:\n";
echo json_encode($filledCase, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo "\n";

$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
$ciphertext = tryBlock(
    'Encrypt filled case',
    fn () => sodium_crypto_secretbox(json_encode($filledCase), $nonce, $txKey)
);

tryBlock(
    'Submit filled case',
    function () use ($client, $token, $ciphertext, $nonce) {
        $options = [
            'json' => [
                'sealedCase' => [
                    'ciphertext' => base64_encode($ciphertext),
                    'nonce' => base64_encode($nonce)
                ]
            ]
        ];

        $client->put('cases/' . $token, $options);
    }
);