package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForAChannel {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forChannel("us-northeast#my-application-id#my-channel.134566")
        .forStreamingOnly()
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiIzSHByd0VienJEOWp1ODhuSkIzZklhdXNCd2tQQUFIdUV3aHZUNWYzRGJMaUpGcHJmeGFBVHd0ODdwcDlqNkNWSTlBQWZVTTVLY3NVVmd5K1c0MHFMdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbElkOnVzLW5vcnRoZWFzdCNteS1hcHBsaWNhdGlvbi1pZCNteS1jaGFubmVsLjEzNDU2NlwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0=");
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
