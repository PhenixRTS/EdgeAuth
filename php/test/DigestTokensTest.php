<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class DigestTokensTest extends TestCase
{
    /* WhenVerifyingABadToken */
    public function testWhenVerifyingABadTokenTheTokenFailsToVerify()
    {
        $token = 'DIGEST:bad-token';
        $result = (new DigestTokens())->verifyAndDecode('bad-secret', $token);
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-token', $result->code);
        $this->assertFalse(isset($result->value));
    }


    /* WhenVerifyingATokenForAChannel */
    private function buildTokenForWhenVerifyingATokenForAChannel()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forChannel('us-northeast#my-application-id#my-channel.134566')
            ->forStreamingOnly()
            ->build();
    }

    public function testWhenVerifyingATokenForAChannelTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiIzSHByd0VienJEOWp1ODhuSkIzZklhdXNCd2tQQUFIdUV3aHZUNWYzRGJMaUpGcHJmeGFBVHd0ODdwcDlqNkNWSTlBQWZVTTVLY3NVVmd5K1c0MHFMdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbElkOnVzLW5vcnRoZWFzdCNteS1hcHBsaWNhdGlvbi1pZCNteS1jaGFubmVsLjEzNDU2NlwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0=',
            $this->buildTokenForWhenVerifyingATokenForAChannel()
        );
    }

    public function testWhenVerifyingATokenForAChannelTheTokenSuccessfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannel()
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('channelId:us-northeast#my-application-id#my-channel.134566', $result->value->subscribeTag);
    }

    public function testWhenVerifyingATokenForAChanelTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannel()
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }

    /* WhenVerifyingATokenForAChannelAlias */
    private function buildTokenForWhenVerifyingATokenForAChannelAlias()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forChannelAlias('my-channel')
            ->forStreamingOnly()
            ->build();
    }

    public function testWhenVerifyingATokenForAChannelAliasTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJNV21IVXBUL21qM3ZleURGZGt2ODdKVnpnRU5DeUR4eGovVkx5aXZnVWsvcUJvYjZmV1c1UGphbVJCVmlONUo4NjYzbENzSjNxZkZZZ2ZNS1JlazJoQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbEFsaWFzOm15LWNoYW5uZWxcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9',
            $this->buildTokenForWhenVerifyingATokenForAChannelAlias()
        );
    }

    public function testWhenVerifyingATokenForAChannelAliasTheTokenSuccesfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannelAlias()
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('channelAlias:my-channel', $result->value->subscribeTag);
    }

    public function testWhenVerifyingATokenForAChannelAliasTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannelAlias()
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }


    /* WhenVerifyingATokenForAChannelAliasAndWithATagAdded */
    private function buildTokenForWhenVerifyingATokenForAChannelAliasAndWithATagAdded()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forChannelAlias('my-channel')
            ->forStreamingOnly()
            ->applyTag('customer1')
            ->build();
    }

    public function testWhenVerifyingATokenForAChannelAliasAndWithATagAddedTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJyU0NmTzlmTDlLc3ZnZVBvQVpBRDBDZG1Xc1hrNDhybm4zK2VlZXlZVXFxQUU3aTBGbnBYbFJjMDhOa3BuYmdtb3hDWWpKenNDR0Yra3kyWWR5NWprZz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbEFsaWFzOm15LWNoYW5uZWxcIixcInR5cGVcIjpcInN0cmVhbVwiLFwiYXBwbHlUYWdzXCI6W1wiY3VzdG9tZXIxXCJdfSJ9',
            $this->buildTokenForWhenVerifyingATokenForAChannelAliasAndWithATagAdded()
        );
    }

    public function testWhenVerifyingATokenForAChannelAliasAndWithATagAddedTheTokenSuccesfullyVerifiesWithTheCorrectSecret(
    )
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannelAliasAndWithATagAdded()
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('channelAlias:my-channel', $result->value->subscribeTag);
        $this->assertIsArray($result->value->applyTags);
        $this->assertEquals(1, count($result->value->applyTags));
        $this->assertEquals('customer1', $result->value->applyTags[0]);
    }

    public function testWhenVerifyingATokenForAChannelAliasAndWithATagAddedTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->buildTokenForWhenVerifyingATokenForAChannelAliasAndWithATagAdded()
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }


    /* WhenVerifyingATokenForATag */
    private function buildTokenForWhenVerifyingATokenForATag()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forTag('my-tag=awesome')
            ->forStreamingOnly()
            ->build();
    }

    public function testWhenVerifyingATokenForATagTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI4L2IzRjFDUlVHNTFvR1p4VitsRkcwemlMaGszclZjek1zVGFUMHBIakNBSE1nU0ltQmh2a2NFS09Fc1ErcXgzOHlLRmNFaWJMZUsvdEtrWTBGaFJtdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwibXktdGFnPWF3ZXNvbWVcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9',
            $this->buildTokenForWhenVerifyingATokenForATag()
        );
    }

    public function testWhenVerifyingATokenForATagTheTokenSuccesfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode('my-secret', $this->buildTokenForWhenVerifyingATokenForATag());
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('my-tag=awesome', $result->value->subscribeTag);
    }

    public function testWhenVerifyingATokenForATagTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode('bad-secret', $this->buildTokenForWhenVerifyingATokenForATag());
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }


    /* WhenVerifyingATokenForPublishing */
    private function buildTokenForWhenVerifyingATokenForPublishing()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forStreamingOnly()
            ->build();
    }

    public function testWhenVerifyingATokenForPublishingTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI4WHEwMnNrZkM2R24vWVdtMExMalFOajVZTzJqR0RBYXAvc3NqUE1mdWgyamtrWXZpS1FGTkQwRm9DU0RxVXg5U2wrSTArYWpKMHRsQWhUdTN4dTdHQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInN0cmVhbVwifSJ9',
            $this->buildTokenForWhenVerifyingATokenForPublishing()
        );
    }

    public function testWhenVerifyingATokenForPublishingTheTokenSuccesfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->buildTokenForWhenVerifyingATokenForPublishing()
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
    }

    public function testWhenVerifyingATokenForPublishingTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->buildTokenForWhenVerifyingATokenForPublishing()
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }


    /* WhenVerifyingATokenForPublishingWithCapabilities */
    private function buildTokenForWhenVerifyingATokenForPublishingWithCapabilities()
    {
        return (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forStreamingOnly()
            ->withCapability('multi-bitrate')
            ->withCapability('streaming')
            ->build();
    }

    public function testWhenVerifyingATokenForPublishingWithCapabilitiesTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJMQU5xV1d2TWZvMmNxMzM2cEZEZU11VTFHa25YWnhCdEpTNnc1dE9VRXdCK1pmaTA1dWFwaFowUmNpZGFhNmFaUm4rSHkzMUF1eDNqUFlubE9pTnowUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInN0cmVhbVwiLFwiY2FwYWJpbGl0aWVzXCI6W1wibXVsdGktYml0cmF0ZVwiLFwic3RyZWFtaW5nXCJdfSJ9',
            $this->buildTokenForWhenVerifyingATokenForPublishingWithCapabilities()
        );
    }

    public function testWhenVerifyingATokenForPublishingWithCapabilitiesTheTokenSuccesfullyVerifiesWithTheCorrectSecret(
    )
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->buildTokenForWhenVerifyingATokenForPublishingWithCapabilities()
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertIsArray($result->value->capabilities);
        $this->assertEquals('multi-bitrate', $result->value->capabilities[0]);
        $this->assertEquals('streaming', $result->value->capabilities[1]);
    }

    public function testWhenVerifyingATokenForPublishingWithCapabilitiesTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->buildTokenForWhenVerifyingATokenForPublishingWithCapabilities()
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }
}
