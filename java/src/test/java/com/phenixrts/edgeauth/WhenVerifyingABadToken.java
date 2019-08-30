package com.phenixrts.edgeauth;

import org.testng.Assert;
import org.testng.annotations.Test;

public class WhenVerifyingABadToken {
  private String token = "DIGEST:bad-token";

  @Test
  void theTokenFailsToVerify() {
    DigestTokens.VerifyAndDecodeResult result = new DigestTokens().verifyAndDecode("bad-secret", token);

    Assert.assertFalse(result.isVerified());
    Assert.assertEquals(result.getCode(), ECode.BAD_TOKEN);
    Assert.assertNull(result.getValue());
  }
}
