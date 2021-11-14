Feature: Verifying a token for a channel alias and with a tag added

  Scenario: Verifying a token for a channel alias and with a tag added (good secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for streaming only
    And The token has a "customer1" tag applied
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJMU0VnS2dGTy9aRUdxdEFLazVZb0F6cFJuTnQ4enhwUjNsdEJ3cWtOR3E1VWdjWWZpcnZKTDk3NHhpangyNS9XbHpqaUg1dk5ZMHdaYklFSkE2MzJqdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwidHlwZVwiOlwic3RyZWFtXCIsXCJhcHBseVRhZ3NcIjpbXCJjdXN0b21lcjFcIl19In0="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "channelAlias:my-channel"
    And The applied tags field should be "customer1"

  Scenario: Verifying a token for a channel alias and with a tag added (bad secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for streaming only
    And The token has a "customer1" tag applied
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
