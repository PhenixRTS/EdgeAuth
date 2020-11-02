<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class WhenVerifyingATokenForAUriAndAChannelAlias extends TestCase
{
    private $token;

    protected function setUp(): void
    {
        $this->token = (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->withUri("https://my-custom-backend.example.org")
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forChannelAlias('my-channel')
            ->forStreamingOnly()
            ->build();
    }

    public function testTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJLUjJIb0xDbXJTZTRQWktpbXZDZ2dDWWJxOEprdG5iQlJGWDJuRTR3WVl3SUdleGdacUR3MGZLUDNZbEM1aFpLbi9ZRTFzYWFlUE9lR040U0ZOTWMzdz09IiwidG9rZW4iOiJ7XCJ1cmlcIjpcImh0dHBzOi8vbXktY3VzdG9tLWJhY2tlbmQuZXhhbXBsZS5vcmdcIixcImV4cGlyZXNcIjoxMDAwLFwicmVxdWlyZWRUYWdcIjpcImNoYW5uZWxBbGlhczpteS1jaGFubmVsXCIsXCJ0eXBlXCI6XCJzdHJlYW1cIn0ifQ==',
            $this->token
        );
    }

    public function testTheTokenSuccesfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->token
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('https://my-custom-backend.example.org', $result->value->uri);
        $this->assertEquals('channelAlias:my-channel', $result->value->requiredTag);
    }

    public function testTheTokenFailsToVerifyWithABadSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'bad-secret',
            $this->token
        );
        $this->assertFalse($result->verified);
        $this->assertEquals('bad-digest', $result->code);
        $this->assertFalse(isset($result->value));
    }
}
