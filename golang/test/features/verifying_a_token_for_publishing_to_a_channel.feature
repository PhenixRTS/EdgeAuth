Feature: Verifying a token for publishing to a channel

  Scenario: Verifying a token for publishing to a channel (good secret)
    Given I have a good token
    And The token is for a channel "us-northeast#my-application-id#my-channel.134566"
    And The token is for publishing only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJVZ3hjTDVVMlAvZDVtTXI4N3NzM3M5ZDdNNHo1elNZRGZrN0duL1BHS1d4S3NRS2t0c2pkN0Y5QTlRRHVQNnRSaTMzTG00TlpDVTZvSDFjbzFIa2Nmdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsSWQ6dXMtbm9ydGhlYXN0I215LWFwcGxpY2F0aW9uLWlkI215LWNoYW5uZWwuMTM0NTY2XCIsXCJ0eXBlXCI6XCJwdWJsaXNoXCJ9In0="
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "channelId:us-northeast#my-application-id#my-channel.134566"
    And The type field should be "publish"

  Scenario: Verifying a token for publishing to a channel (bad secret)
    Given I have a good token
    And The token is for a channel "us-northeast#my-application-id#my-channel.134566"
    And The token is for publishing only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
