from edgeauth.digest_tokens import DigestTokens


class TestWhenVerifyingABadToken:
    def test_the_token_fails_to_verify(self):
        token = 'DIGEST:bad-token'
        result = DigestTokens() \
            .verify_and_decode('bad-secret', token)

        assert result['verified'] is False
        assert result['code'] == 'bad-token'
        assert 'message' not in result
        assert 'value' not in result
