<?php

namespace EdgeAuth;

class DigestTokens
{
    const digestTokenPrefix = 'DIGEST:';
    const digestAlgorithm = 'SHA512';

    function __construct()
    {
    }

    /**
     * Check if a value is a valid digest token
     * @param string $encodedToken an encoded token
     * @return bool true if the encodedToken is a valid digest token
     */
    public function isDigestToken($encodedToken)
    {
        return isset($encodedToken)
            && is_string($encodedToken)
            && substr($encodedToken, 0, strlen(self::digestTokenPrefix)) == self::digestTokenPrefix;
    }

    /**
     * Verifies and decodes a digest token.
     * @param string $secret the shared secret used to sign the token
     * @param string $encodedToken encodedToken the encoded token
     * @return mixed|object  An object {verified,code,value} with verified set to true if the token was successfully verified. And with verified set to false if the token was not verified. In that case, code is provided to indicate the type of problem.
     * @throws \Exception
     */
    public function verifyAndDecode($secret, $encodedToken)
    {
        if (is_string($secret) === false) {
            throw new \Exception('Secret must be a string.');
        }

        if (is_string($encodedToken) === false) {
            throw new \Exception('Encoded token must be a string.');
        }

        if ($this->isDigestToken($encodedToken) === false) {
            return (object)[
                'verified' => false,
                'code' => 'not-a-digest-token'
            ];
        }

        $encodedDigestToken = substr($encodedToken, strlen(self::digestTokenPrefix));
        $decodedDigestTokenAsString = base64_decode($encodedDigestToken);

        $info = json_decode($decodedDigestTokenAsString);
        if ($info === false) {
            return (object)[
                'verified' => false,
                'code' => 'bad-token'
            ];
        }

        if (!isset($info->applicationId) || !is_string($info->applicationId)) {
            return (object)[
                'verified' => false,
                'code' => 'bad-token'
            ];
        }


        if (!isset($info->digest) || !is_string($info->digest)) {
            return (object)[
                'verified' => false,
                'code' => 'bad-token'
            ];
        }

        if (!isset($info->token) || !is_string($info->token)) {
            return (object)[
                'verified' => false,
                'code' => 'bad-token'
            ];
        }

        $digestAsString = $this->calculateDigest($info->applicationId, $secret, $info->token);
        $digest = $info->digest;

        if ($digestAsString !== $digest) {
            return (object)[
                'verified' => false,
                'code' => 'bad-digest'
            ];
        }

        $value = json_decode($info->token);
        $value->applicationId = $info->applicationId;

        return (object)[
            'verified' => true,
            'code' => 'verified',
            'value' => $value
        ];
    }

    /**
     * Signs and encodes a digest token.
     * @param string $applicationId the application ID used to sign the token
     * @param string $secret the shared secret used to sign the token
     * @param object $token the raw token object to sign
     * @return string
     * @throws \Exception
     */
    public function signAndEncode($applicationId, $secret, $token)
    {
        if (!isset($applicationId) || !is_string($applicationId)) {
            throw new \Exception('Application ID must be a string.');
        }

        if (!isset($secret) || !is_string($secret)) {
            throw new \Exception('Secret must be a string.');
        }

        if (!isset($token) || !is_object($token)) {
            throw new \Exception('Encoded token must be a object.');
        }

        if (!isset($token->expires) || !is_numeric($token->expires)) {
            throw new \Exception('Token must have an expiration (milliseconds since UNIX epoch)');
        }

        if (isset($token->applicationId)) {
            throw new \Exception('Token should not have an application ID property');
        }

        $tokenAsString = json_encode($token);
        $digest = $this->calculateDigest($applicationId, $secret, $tokenAsString);

        $info = (object)[
            'applicationId' => $applicationId,
            'digest' => $digest,
            'token' => $tokenAsString
        ];

        $decodedDigestTokenAsString = json_encode($info, JSON_UNESCAPED_SLASHES);
        $encodedDigestToken = base64_encode($decodedDigestTokenAsString);


        return self::digestTokenPrefix . $encodedDigestToken;
    }

    /**
     * Calculates the digest for a token.
     * @param $applicationId
     * @param $secret
     * @param $token
     * @return string The Base64 encoded digest
     * @throws \Exception
     */
    public function calculateDigest($applicationId, $secret, $token)
    {
        if (!isset($applicationId) || !is_string($applicationId)) {
            throw new \Exception('Application ID must be a string');
        }

        if (!isset($secret) || !is_string($secret)) {
            throw new \Exception('Secret must be a string');
        }

        if (!isset($token) || !is_string($token)) {
            throw new \Exception('Token must be a string');
        }

        // The hmac salt is the concatenation of application ID and secret to eliminate the use of lookup table for brute force attacks.
        $salt = $applicationId . $secret;
        return base64_encode(hash_hmac(self::digestAlgorithm, $token, $salt, true));
    }

}
