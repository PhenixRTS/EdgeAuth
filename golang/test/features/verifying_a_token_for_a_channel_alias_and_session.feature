Feature: Verifying a token for a channel alias and session

  Scenario: Verifying a token for a channel alias and session (good secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for a session "session-id"
    And The token is for streaming only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJBQi9Nanp2a1lnMGRTODF6aU1SVDZ3OUtwWmtjMU42U3VMTW56V09CQVJQZWJuenRHZTlmM2ZNS1FURXVqaHpVTkY0TWVsNkpMekFiWlZ3TFBSbEN4QT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwic2Vzc2lvbklkXCI6XCJzZXNzaW9uLWlkXCIsXCJ0eXBlXCI6XCJzdHJlYW1cIn0ifQ=="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "channelAlias:my-channel"
    And The session field should be "session-id"

  Scenario: Verifying a token for a channel alias and session (bad secret)
    Given I have a good token
    And The token is for a channel alias "my-channel"
    And The token is for a session "session-id"
    And The token is for streaming only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
