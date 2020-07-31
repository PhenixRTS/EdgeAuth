package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForAChannelAliasAndRemoteAddress {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forChannelAlias("my-channel")
        .forRemoteAddress("10.1.2.3")
        .forStreamingOnly()
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI4MitYd1dITVRUc0xWYThKcnFPUmdjYlRXL2g2clFBTlF1MjgvRytQeHllQ09qSHEyb2xDYzVacUJ1MktqN0tGYmYyTC84TDZyaE9xTTZPMjNBR29HUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwicmVtb3RlQWRkcmVzc1wiOlwiMTAuMS4yLjNcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
    Assert.assertEquals(result.getValue().getString("requiredTag"), "channelAlias:my-channel");
    Assert.assertEquals(result.getValue().getString("remoteAddress"), "10.1.2.3");
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
