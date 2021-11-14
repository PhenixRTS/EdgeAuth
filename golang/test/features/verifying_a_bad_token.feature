Feature: Verifying a bad token

  Scenario: Verifying a bad token (good secret)
    Given I have a bad token
    When I try to verify a token with a good secret
    Then Verification should fail with error "bad-token"

  Scenario: Verifying a bad token (bad secret)
    Given I have a bad token
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-token"
