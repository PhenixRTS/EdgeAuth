Feature: Verifying a token for a channel alias

  Scenario: Verifying a token for a channel alias (good secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for streaming only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJPMk90R1ZBMlErTGlhRkdjSjZ0cnlXZWE4L2l2dWFQR2gzcFJpcVd3ZlJPVWdBSSs0dFdaYXdBc011Y2MyMHNRTjZpaGZtVGVDNFVubXVoWko5aHBxUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "channelAlias:my-channel"

  Scenario: Verifying a token for a channel alias (bad secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for streaming only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
