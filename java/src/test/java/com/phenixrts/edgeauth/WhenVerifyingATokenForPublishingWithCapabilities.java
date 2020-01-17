package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForPublishingWithCapabilities {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forStreamingOnly()
        .withCapability("multi-bitrate")
        .withCapability("streaming")
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJMQU5xV1d2TWZvMmNxMzM2cEZEZU11VTFHa25YWnhCdEpTNnc1dE9VRXdCK1pmaTA1dWFwaFowUmNpZGFhNmFaUm4rSHkzMUF1eDNqUFlubE9pTnowUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInN0cmVhbVwiLFwiY2FwYWJpbGl0aWVzXCI6W1wibXVsdGktYml0cmF0ZVwiLFwic3RyZWFtaW5nXCJdfSJ9");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
    Assert.assertEquals(result.getValue().getJsonArray("capabilities").size(), 2);
    Assert.assertEquals(result.getValue().getJsonArray("capabilities").getString(0), "multi-bitrate");
    Assert.assertEquals(result.getValue().getJsonArray("capabilities").getString(1), "streaming");
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
