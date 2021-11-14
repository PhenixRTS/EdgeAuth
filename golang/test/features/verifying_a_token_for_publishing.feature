Feature: Verifying a token for publishing

  Scenario: Verifying a token for publishing (good secret)
    Given I have a good token
    And The token is for publishing only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJrVElBcDh4ZUlqRXBxU2p0R3Zha3JOR2FFWnl5S1hMdmRMdmpBTHpJYkhYQmtqVXg2eU9hOHNmTGVoMFJydnNHaDJFbHF5OE5MMVBFVG51QjdQR3Z6dz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInB1Ymxpc2hcIn0ifQ=="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The type field should be "publish"

  Scenario: Verifying a token for publishing (bad secret)
    Given I have a good token
    And The token is for publishing only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
