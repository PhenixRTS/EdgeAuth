import pytest
from datetime import datetime
import pytz

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForAChannelAliasAndWithATagAdded:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(datetime.utcfromtimestamp(1.0).replace(tzinfo=pytz.UTC)) \
            .for_channel_alias('my-channel') \
            .for_streaming_only() \
            .apply_tag('customer1') \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJMU0VnS2dGTy9aRUdxdEFLazVZb0F6cFJuTnQ4enhwUjNsdEJ3cWtOR3E1VWdjWWZpcnZKTDk3NHhpangyNS9XbHpqaUg1dk5ZMHdaYklFSkE2MzJqdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwidHlwZVwiOlwic3RyZWFtXCIsXCJhcHBseVRhZ3NcIjpbXCJjdXN0b21lcjFcIl19In0='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] is True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['requiredTag'] == 'channelAlias:my-channel'
        assert 'customer1' in value['applyTags']

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] is False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
