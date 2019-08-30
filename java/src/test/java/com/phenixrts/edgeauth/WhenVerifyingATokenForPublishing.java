package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForPublishing {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forStreamingOnly()
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI4WHEwMnNrZkM2R24vWVdtMExMalFOajVZTzJqR0RBYXAvc3NqUE1mdWgyamtrWXZpS1FGTkQwRm9DU0RxVXg5U2wrSTArYWpKMHRsQWhUdTN4dTdHQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInN0cmVhbVwifSJ9");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
