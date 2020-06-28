<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class WhenVerifyingATokenForARoomAlias extends TestCase
{
    private $token;

    protected function setUp(): void
    {
        $this->token = (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forRoomAlias('my-room')
            ->forStreamingOnly()
            ->build();
    }

    public function testTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI1UkN3a0FrdFdJTDNWNllXN0V0dE14ejhpZXJvMWZkcXF0dEdRVFdaUDVCZ1k0OFhIUGltYmx3dDl1QUgyQWI3bHVVcWs0OG1DQktveE10WkhpaHNoQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJyb29tQWxpYXM6bXktcm9vbVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0=',
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
        $this->assertEquals('roomAlias:my-room', $result->value->requiredTag);
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