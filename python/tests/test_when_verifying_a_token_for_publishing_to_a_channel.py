import pytest

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForPublishingToAChannel:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(1000) \
            .for_channel('us-northeast#my-application-id#my-channel.134566') \
            .for_publishing_only() \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJVZ3hjTDVVMlAvZDVtTXI4N3NzM3M5ZDdNNHo1elNZRGZrN0duL1BHS1d4S3NRS2t0c2pkN0Y5QTlRRHVQNnRSaTMzTG00TlpDVTZvSDFjbzFIa2Nmdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsSWQ6dXMtbm9ydGhlYXN0I215LWFwcGxpY2F0aW9uLWlkI215LWNoYW5uZWwuMTM0NTY2XCIsXCJ0eXBlXCI6XCJwdWJsaXNoXCJ9In0='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] is True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['type'] == 'publish'
        assert value['requiredTag'] == 'channelId:us-northeast#my-application-id#my-channel.134566'

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] is False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
