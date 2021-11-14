Feature: Verifying a token for publishing with capabilities

  Scenario: Verifying a token for publishing with capabilities (good secret)
    Given I have a good token
    And The token is for publishing only
    And The token has capability "multi-bitrate"
    And The token has capability "streaming"
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJFKytBK3EwWGhGQ09LT011RnZqcnRIOVNyeHpwZ0Q1VVZYb1B6Q1VPaGNLU3pHTGRQZmsyRVYzVkZOOWRyM2tBVGZtSWRUeCtSTlFodjJ3aVJGbUM1Zz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInB1Ymxpc2hcIixcImNhcGFiaWxpdGllc1wiOltcIm11bHRpLWJpdHJhdGVcIixcInN0cmVhbWluZ1wiXX0ifQ=="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The type field should be "publish"
    And The capabilities field should be "multi-bitrate,streaming"

  Scenario: Verifying a token for publishing with capabilities (bad secret)
    Given I have a good token
    And The token is for publishing only
    And The token has capability "multi-bitrate"
    And The token has capability "streaming"
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
