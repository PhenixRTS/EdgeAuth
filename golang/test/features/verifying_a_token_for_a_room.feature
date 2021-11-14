Feature: Verifying a token for a room

  Scenario: Verifying a token for a room (good secret)
    Given I have a good token
    And The token is for a room "my-room.123456"
    And The token is for streaming only
    And The correct token is "DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI2WWdud09qWkx4Mk8zQXJjd29CUlVKU0UyYkRVNWVGY0FIYjI3OEJxVlMvcmplMXlsRU51bE5BSTVqakd2Mjc3VnZTTEtkYk1jTW1HenA3Nm9wNkNmZz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJyb29tSWQ6bXktcm9vbS4xMjM0NTZcIixcInR5cGVcIjpcInN0cmVhbVwifSJ9"
    When I try to verify a token with a good secret
    Then Verification should pass
    And The tag field should be "roomId:my-room.123456"

  Scenario: Verifying a token for a room (bad secret)
    Given I have a good token
    And The token is for a room "my-room.123456"
    And The token is for streaming only
    When I try to verify a token with a bad secret
    Then Verification should fail with error "bad-digest"
