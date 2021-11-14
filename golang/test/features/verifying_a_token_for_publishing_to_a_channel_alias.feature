Feature: Verifying a token for publishing to a channel alias

  Scenario: Verifying a token for publishing to a channel alias (good secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for publishing only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJIREJPRzdiOFRuV0ZoNVMrR0Y5Z1lWQkNrM1J4WlhXNWh6UUN0bk9raXZLNlY0K1AxcDVKcHJ2TTNIVElyTUFBclUxMkY5bkltNGRvRm5TWXVjSzloUT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwidHlwZVwiOlwicHVibGlzaFwifSJ9"
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "channelAlias:my-channel"
    And The type field should be "publish"

  Scenario: Verifying a token for publishing to a channel alias (bad secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for publishing only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
