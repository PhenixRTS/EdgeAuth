Feature: Verifying a token for a room alias

  Scenario: Verifying a token for a room alias (good secret)
    Given I have a good token
    And The token is for a room alias "my-room"
    And The token is for streaming only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI1UkN3a0FrdFdJTDNWNllXN0V0dE14ejhpZXJvMWZkcXF0dEdRVFdaUDVCZ1k0OFhIUGltYmx3dDl1QUgyQWI3bHVVcWs0OG1DQktveE10WkhpaHNoQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJyb29tQWxpYXM6bXktcm9vbVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "roomAlias:my-room"

  Scenario: Verifying a token for a room alias (bad secret)
    Given I have a good token
    And The token is for a room alias "my-room"
    And The token is for streaming only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
