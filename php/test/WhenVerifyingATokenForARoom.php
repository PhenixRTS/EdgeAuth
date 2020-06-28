<?php


use EdgeAuth\DigestTokens;
use PHPUnit\Framework\TestCase;

final class WhenVerifyingATokenForARoom extends TestCase
{
    private $token;

    protected function setUp(): void
    {
        $this->token = (new \EdgeAuth\TokenBuilder())
            ->withApplicationId('my-application-id')
            ->withSecret('my-secret')
            ->expiresAt(\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", "1970-01-01T00:00:01.000Z"))
            ->forRoom('my-room.123456')
            ->forStreamingOnly()
            ->build();
    }

    public function testTheTokenMatchesTheExpectedValue()
    {
        $this->assertEquals(
            'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI2WWdud09qWkx4Mk8zQXJjd29CUlVKU0UyYkRVNWVGY0FIYjI3OEJxVlMvcmplMXlsRU51bE5BSTVqakd2Mjc3VnZTTEtkYk1jTW1HenA3Nm9wNkNmZz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJyb29tSWQ6bXktcm9vbS4xMjM0NTZcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9',
            $this->token
        );
    }

    public function testTheTokenSuccessfullyVerifiesWithTheCorrectSecret()
    {
        $result = (new DigestTokens())->verifyAndDecode(
            'my-secret',
            $this->token
        );
        $this->assertTrue($result->verified);
        $this->assertEquals('verified', $result->code);
        $this->assertTrue(isset($result->value));
        $this->assertEquals('roomId:my-room.123456', $result->value->requiredTag);
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