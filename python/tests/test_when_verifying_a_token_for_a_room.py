import pytest
from datetime import datetime
import pytz

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForARoomAlias:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(datetime.utcfromtimestamp(1.0).replace(tzinfo=pytz.UTC)) \
            .for_room_alias('my-room') \
            .for_streaming_only() \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiI1UkN3a0FrdFdJTDNWNllXN0V0dE14ejhpZXJvMWZkcXF0dEdRVFdaUDVCZ1k0OFhIUGltYmx3dDl1QUgyQWI3bHVVcWs0OG1DQktveE10WkhpaHNoQT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJyb29tQWxpYXM6bXktcm9vbVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] is True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['requiredTag'] == 'roomAlias:my-room'

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] is False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
