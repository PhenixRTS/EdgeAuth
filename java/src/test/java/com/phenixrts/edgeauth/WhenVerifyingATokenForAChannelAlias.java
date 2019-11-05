package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForAChannelAlias {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forChannelAlias("my-channel.134566")
        .forStreamingOnly()
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJNV21IVXBUL21qM3ZleURGZGt2ODdKVnpnRU5DeUR4eGovVkx5aXZnVWsvcUJvYjZmV1c1UGphbVJCVmlONUo4NjYzbENzSjNxZkZZZ2ZNS1JlazJoQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbEFsaWFzOm15LWNoYW5uZWxcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9'");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
    Assert.assertEquals(result.getValue().getString("subscribeTag"), "channelAlias:my-channel.134566");
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
