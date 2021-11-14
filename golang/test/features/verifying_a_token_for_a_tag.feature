Feature: Verifying a token for a tag

  Scenario: Verifying a token for a tag (good secret)
    Given I have a good token
    And The token is for tag "my-tag=awesome"
    And The token is for streaming only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJGUGRrTFFyVGlsS0toRDduc2QzeDZoNWV1aXVsaDVCYy9lNEtmQWY0THB5Qno4N2trK2lrQWN5ZUppcFk3alo4clpTN1N0bWw1aERMWEJIZXkrbmw2QT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJteS10YWc9YXdlc29tZVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "my-tag=awesome"

  Scenario: Verifying a token for a tag (bad secret)
    Given I have a good token
    And The token is for tag "my-tag=awesome"
    And The token is for streaming only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
