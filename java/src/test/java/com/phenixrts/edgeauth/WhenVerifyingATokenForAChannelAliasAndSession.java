package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForAChannelAliasAndSession {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forChannelAlias("my-channel")
        .forSession("session-id")
        .forStreamingOnly()
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJBQi9Nanp2a1lnMGRTODF6aU1SVDZ3OUtwWmtjMU42U3VMTW56V09CQVJQZWJuenRHZTlmM2ZNS1FURXVqaHpVTkY0TWVsNkpMekFiWlZ3TFBSbEN4QT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwic2Vzc2lvbklkXCI6XCJzZXNzaW9uLWlkXCIsXCJ0eXBlXCI6XCJzdHJlYW1cIn0ifQ==");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
    Assert.assertEquals(result.getValue().getString("requiredTag"), "channelAlias:my-channel");
    Assert.assertEquals(result.getValue().getString("sessionId"), "session-id");
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
