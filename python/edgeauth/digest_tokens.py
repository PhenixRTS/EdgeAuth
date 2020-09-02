import hashlib
import hmac
import base64
import json
from io import StringIO


DIGEST_TOKEN_PREFIX = 'DIGEST:'
ENCODING = 'utf-8'


class BadToken(Exception):
    pass


class BadDigest(Exception):
    pass


class DigestTokens:
    def is_digest_token(self, encoded_token):
        """Check if a value is a valid digest token.

        Keyword arguments:
        encodedToken -- an encoded token
        """
        return encoded_token and isinstance(encoded_token, str) and encoded_token.startswith(DIGEST_TOKEN_PREFIX)

    def verify_and_decode(self, secret, encoded_token):
        """Verifies and decodes a digest token.

        Keywork arguments:
        secret -- the shared secret used to sign the token
        encoded_token -- the encoded token
        """
        try:
            if not isinstance(secret, str):
                raise TypeError('Secret must be a string')

            if not isinstance(encoded_token, str):
                raise TypeError('Encoded token must be a string')

            if not self.is_digest_token(encoded_token):
                return {
                    'verified': False,
                    'code': 'not-a-digest-token',
                }

            encoded_digest_token = encoded_token[len(DIGEST_TOKEN_PREFIX):]
            decoded_digest_token_as_string = base64 \
                .b64decode(encoded_digest_token)

            info = None

            try:
                info = json.loads(decoded_digest_token_as_string)
            except Exception:
                raise BadToken()

            if not info['applicationId'] or not isinstance(info['applicationId'], str):
                raise BadToken()

            if not info['digest'] or not isinstance(info['digest'], str):
                raise BadToken()

            if not info['token'] or not isinstance(info['token'], str):
                raise BadToken()

            digest_as_string = self.calculate_digest(
                info['applicationId'],
                secret,
                info['token']
            )

            digest = info['digest']

            if not digest_as_string == digest:
                raise BadDigest()

            value = json.loads(info['token'])

            value['applicationId'] = info['applicationId']

            return {
                'code': 'verified',
                'value': value,
                'verified': True,
            }
        except BadToken as ex:
            return {
                'code': 'bad-token',
                # 'message': str(ex),
                'verified': False,
            }
        except BadDigest:
            return {
                'code': 'bad-digest',
                'verified': False,
            }
        except Exception as ex:
            return {
                'code': 'server-error',
                'message': str(ex),
                'verified': False,
            }

    def sign_and_encode(self, application_id, secret, token):
        """Signs and encodes a digest token.

        Keyword arguments:
        application_id -- the application ID used to sign the token
        secret -- the shared secret used to sign the token
        token -- the raw token object to sign
        """
        if not isinstance(secret, str):
            raise TypeError('Secret must be a string')

        if not isinstance(token, dict):
            raise TypeError('Token must be a dictionary')

        if 'expires' not in token or not type(token['expires']) in [float, int]:
            raise ValueError('Token must have an expiration (milliseconds since UNIX epoch)')

        if 'application_id' in token:
            raise ValueError('Token should not have an application_id property')

        # io = StringIO()
        # json.dump(token, io)
        # token_as_string = io.getvalue()

        token_as_string = json.dumps(token, separators=(',', ':'))

        digest = self.calculate_digest(application_id, secret, token_as_string)

        info = {
            'applicationId': application_id,
            'digest': digest,
            'token': token_as_string,
        }

        decoded_digest_token_as_string = json.dumps(info, separators=(',', ':'))
        encoded_digest_token = base64 \
            .b64encode(decoded_digest_token_as_string.encode(ENCODING)) \
            .decode(ENCODING)

        return DIGEST_TOKEN_PREFIX + encoded_digest_token

    def calculate_digest(self, application_id, secret, token):
        """Calculates the digest for a token.

        Keyword arguments:
        application_id -- the application ID used to sign the token
        secret -- the shared secret used to sign the token
        token -- encoded token
        """
        if not isinstance(application_id, str):
            raise TypeError('Application Id must be a string')

        if not isinstance(secret, str):
            raise TypeError('Secret must be a string')

        if not isinstance(token, str):
            raise TypeError('Token must be a string')

        salt = (application_id + secret).encode(ENCODING)
        verify = hmac.new(salt, digestmod=hashlib.sha512)

        verify.update(token.encode(ENCODING))

        digest = base64.b64encode(verify.digest()).decode(ENCODING)

        return digest
