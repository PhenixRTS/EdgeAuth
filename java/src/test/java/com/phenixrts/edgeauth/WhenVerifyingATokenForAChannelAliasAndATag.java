package com.phenixrts.edgeauth;

import java.util.Date;

import org.testng.Assert;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class WhenVerifyingATokenForAChannelAliasAndATag {
  private String token;

  @BeforeTest
  void givenASignedToken() {
    token = new TokenBuilder()
        .withApplicationId("my-application-id")
        .withSecret("my-secret")
        .expiresAt(new Date(1000L))
        .forChannelAlias("my-channel")
        .forStreamingOnly()
        .applyTag("customer1")
        .build();
  }

  @Test
  void theTokenMatchesTheExpectedValue() {
    Assert.assertEquals(token, "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJyU0NmTzlmTDlLc3ZnZVBvQVpBRDBDZG1Xc1hrNDhybm4zK2VlZXlZVXFxQUU3aTBGbnBYbFJjMDhOa3BuYmdtb3hDWWpKenNDR0Yra3kyWWR5NWprZz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInN1YnNjcmliZVRhZ1wiOlwiY2hhbm5lbEFsaWFzOm15LWNoYW5uZWxcIixcInR5cGVcIjpcInN0cmVhbVwiLFwiYXBwbHlUYWdzXCI6W1wiY3VzdG9tZXIxXCJdfSJ9");
  }

  @Test
  void theTokenSuccessfullyVerifiesWithTheCorrectSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("my-secret", token);

    Assert.assertTrue(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.VERIFIED);
    Assert.assertNotNull(result.getValue());
    Assert.assertEquals(result.getValue().getString("subscribeTag"), "channelAlias:my-channel");
    Assert.assertEquals(result.getValue().getJsonArray("applyTags").size(), 1);
    Assert.assertEquals(result.getValue().getJsonArray("applyTags").getString(0), "customer1");
  }

  @Test
  void theTokenFailsToVerifyWithABadSecret() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_DIGEST);
    Assert.assertNull(result.getValue());
  }
}
