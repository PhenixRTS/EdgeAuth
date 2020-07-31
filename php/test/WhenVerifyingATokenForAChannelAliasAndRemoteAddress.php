<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class WhenVerifyingATokenForAChannelAliasAndRemoteAddress extends TestCase
{
    private $token;

    protected function setUp(): void
    {
        $this->token = (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forChannelAlias('my-channel')
            ->forRemoteAddress('10.1.2.3')
            ->forStreamingOnly()
            ->build();
    }

    public function testTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI4MitYd1dITVRUc0xWYThKcnFPUmdjYlRXL2g2clFBTlF1MjgvRytQeHllQ09qSHEyb2xDYzVacUJ1MktqN0tGYmYyTC84TDZyaE9xTTZPMjNBR29HUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwicmVtb3RlQWRkcmVzc1wiOlwiMTAuMS4yLjNcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9',
            $this->token
        );
    }

    public function testTheTokenSuccesfullyVerifiesWithTheCorrectSecret(
    )
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->token
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('channelAlias:my-channel', $result->value->requiredTag);
        $this->assertEquals('10.1.2.3', $result->value->remoteAddress);
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