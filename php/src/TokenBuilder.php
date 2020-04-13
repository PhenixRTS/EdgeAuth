<?php


namespace EdgeAuth;

class TokenBuilder
{
    private $applicationId;

    private $secret;

    /**
     * @var \stdClass
     */
    private $token;

    function __construct()
    {
        $this->applicationId = null;
        $this->secret = null;
        $this->token = new \stdClass();
    }

    /**
     * The application ID used to sign the token. (required)
     * @param string $applicationId
     * @return $this
     * @throws \Exception
     */
    function withApplicationId($applicationId)
    {
        if (!isset($applicationId) || !is_string($applicationId)) {
            throw new \Exception('Application ID must be a string.');
        }

        $this->applicationId = $applicationId;
        return $this;
    }

    /**
     * The secret used to sign the token. (required)
     * @param string $secret
     * @return $this
     * @throws \Exception
     */
    function withSecret($secret)
    {
        if (!isset($secret) || !is_string($secret)) {
            throw new \Exception('Secret must be a string.');
        }

        $this->secret = $secret;
        return $this;
    }

    /**
     * Set a capability for the token, e.g. to publish a stream. (optional)
     * @param string $capability
     * @return $this
     * @throws \Exception
     */
    function withCapability($capability)
    {
        if (!isset($capability) || !is_string($capability)) {
            throw new \Exception('Capability must be a string.');
        }

        if (!isset($this->token->capabilities)) {
            $this->token->capabilities = [];
        }

        $this->token->capabilities[] = $capability;
        return $this;
    }

    /**
     * Expires the token in the given time.
     * NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
     * @param $seconds
     * @return $this
     * @throws \Exception
     */
    function expiresInSeconds($seconds)
    {
        $this->token->expires = round(microtime(true) * 1000) + ($seconds * 1000);
        return $this;
    }


    /**
     * Expires the token at the given date.
     * NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
     * @param \DateTime $expirationDate
     * @return $this
     * @throws \Exception
     */
    function expiresAt(\DateTime $expirationDate)
    {
        if (!isset($expirationDate)) {
            throw new \Exception('Expiration date must be a valid date.');
        }

        $this->token->expires = $expirationDate->format('U.u') * 1000;
        return $this;
    }

    /**
     * Limit the token to authentication only. (optional)
     * @return $this
     */
    function forAuthenticateOnly()
    {
        $this->token->type = 'auth';
        return $this;
    }

    /**
     * Limit the token to streaming only. (optional)
     * @return $this
     */
    function forStreamingOnly()
    {
        $this->token->type = 'stream';
        return $this;
    }

    /**
     * Limit the token to the specified origin stream ID. (optional)
     * @param $originStreamId
     * @return $this
     * @throws \Exception
     */
    function forOriginStream($originStreamId)
    {
        if (!isset($originStreamId) || !is_string($originStreamId)) {
            throw new \Exception('Origin Stream ID must be a string');
        }

        $this->token->originStreamId = $originStreamId;
        return $this;
    }

    /**
     * Limit the token to the specified channel ID. (optional)
     * @param $channelId
     * @return $this
     * @throws \Exception
     */
    function forChannel($channelId)
    {
        if (!isset($channelId) || !is_string($channelId)) {
            throw new \Exception('Channel ID must be a string');
        }

        return $this->forTag('channelId:' . $channelId);
    }

    /**
     * Limit the token to the specified channel alias. (optional)
     * @param $channelAlias
     * @return $this
     * @throws \Exception
     */
    function forChannelAlias($channelAlias)
    {
        if (!isset($channelAlias) || !is_string($channelAlias)) {
            throw new \Exception('Channel alias must be a string');
        }

        return $this->forTag('channelAlias:' . $channelAlias);
    }

    /**
     * Limit the token to the specified tag on the origin stream. (optional)
     * @param $tag
     * @return $this
     * @throws \Exception
     */
    function forTag($tag)
    {
        if (!isset($tag) || !is_string($tag)) {
            throw new \Exception('Tag must be a string');
        }

        $this->token->subscribeTag = $tag;
        return $this;
    }

    /**
     * Apply the tag to the stream when it is setup. (optional)
     * @param $tag
     * @return $this
     * @throws \Exception
     */
    function applyTag($tag)
    {
        if (!isset($tag) || !is_string($tag)) {
            throw new \Exception('Tag must be a string');
        }

        if (!isset($this->token->applyTags)) {
            $this->token->applyTags = [];
        }

        $this->token->applyTags[] = $tag;

        return $this;
    }

    /**
     * Build the signed token
     * @return string
     * @throws \Exception
     */
    function build()
    {
        $digestTokens = new DigestTokens();
        return $digestTokens->signAndEncode($this->applicationId, $this->secret, $this->token);
    }
}
