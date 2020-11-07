import pytest

from edgeauth.digest_tokens import DigestTokens
from edgeauth.token_builder import TokenBuilder


class TestWhenVerifyingATokenForATag:
    token = None

    @pytest.fixture(autouse=True)
    def before_each(self):
        self.token = TokenBuilder() \
            .with_application_id('my-application-id') \
            .with_secret('my-secret') \
            .expires_at(1000) \
            .for_tag('my-tag=awesome') \
            .for_streaming_only() \
            .build()

    def test_token_matches_expected_value(self):
        assert self.token == 'DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJGUGRrTFFyVGlsS0toRDduc2QzeDZoNWV1aXVsaDVCYy9lNEtmQWY0THB5Qno4N2trK2lrQWN5ZUppcFk3alo4clpTN1N0bWw1aERMWEJIZXkrbmw2QT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJteS10YWc9YXdlc29tZVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0='

    def test_the_token_successfully_verifies_with_the_correct_secret(self):
        result = DigestTokens() \
            .verify_and_decode('my-secret', self.token)

        assert result['verified'] == True
        assert result['code'] == 'verified'
        assert 'value' in result

        value = result['value']

        assert value['requiredTag'] == 'my-tag=awesome'

    def test_the_token_fails_to_verify_with_a_bad_secret(self):
        result = DigestTokens() \
            .verify_and_decode('bad-secret', self.token)

        assert result['verified'] == False
        assert result['code'] == 'bad-digest'
        assert 'value' not in result
