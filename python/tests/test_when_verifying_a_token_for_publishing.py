import pytest

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForPublishing:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(1000) \
            .for_publishing_only() \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJrVElBcDh4ZUlqRXBxU2p0R3Zha3JOR2FFWnl5S1hMdmRMdmpBTHpJYkhYQmtqVXg2eU9hOHNmTGVoMFJydnNHaDJFbHF5OE5MMVBFVG51QjdQR3Z6dz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInB1Ymxpc2hcIn0ifQ=='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] is True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['type'] == 'publish'

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] is False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
