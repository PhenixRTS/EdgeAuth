import pytest
from datetime import datetime
import pytz

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForAChannel:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(datetime.utcfromtimestamp(1.0).replace(tzinfo=pytz.UTC)) \
            .for_channel('us-northeast#my-application-id#my-channel.134566') \
            .for_streaming_only() \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJZNGM3Tmp6eDVhalkzLzRWK3pwTVliNTBBU1ZCUXc0NlAvS2dwc3JrTnpDdFAzZWM5NzVzblorN3lJNzZiM0wrTmNtb2FoL3hOTUhQZ00vNEExaDI4UT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsSWQ6dXMtbm9ydGhlYXN0I215LWFwcGxpY2F0aW9uLWlkI215LWNoYW5uZWwuMTM0NTY2XCIsXCJ0eXBlXCI6XCJzdHJlYW1cIn0ifQ=='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] is True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['requiredTag'] == 'channelId:us-northeast#my-application-id#my-channel.134566'

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] is False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
