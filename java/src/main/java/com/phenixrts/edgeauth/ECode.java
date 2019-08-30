package com.phenixrts.edgeauth;

/**
 * Verification result code.
 */
public enum ECode {
  VERIFIED,
  BAD_TOKEN,
  BAD_DIGEST,
  NOT_A_DIGEST_TOKEN,
  UNSUPPORTED
}
