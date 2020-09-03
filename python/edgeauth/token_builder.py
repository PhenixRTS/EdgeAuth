from datetime import datetime, timezone

from .digest_tokens import DigestTokens


class TokenBuilder:
    application_id = None
    secret = None
    token = {}

    def __init__(self):
        """Token builder helper class to create digest tokens that can be used with the Phenix platform.
        """
        self.application_id = None
        self.secret = None
        self.token = {}

    def with_application_id(self, application_id):
        """The application ID used to sign the token. (required)

        Keyword arguments:
        application_id -- the application ID to sign the token
        """
        if not isinstance(application_id, str):
            raise TypeError('Application Id must be a string')

        self.application_id = application_id

        return self

    def with_secret(self, secret):
        """The secret used to sign the token. (required)

        Keyword arguments:
        secret -- the shared secret ro sigh the token
        """
        if not isinstance(secret, str):
            raise TypeError('Secret must be a string')

        self.secret = secret

        return self

    def with_capability(self, capability):
        """Set a capability for the token, e.g. to publish a stream. (optional)

        Keyword arguments:
        capability -- the valid capability
        """
        if not isinstance(capability, str):
            raise TypeError('Capability must be a string')

        token = self.token
        capabilities = set(token['capabilities']) if 'capabilities' in token else set([])

        capabilities.add(capability)

        self.token['capabilities'] = sorted(list(capabilities))

        return self

    def expires_in_seconds(self, seconds):
        """Expires the token in the given time.
        NOTE: Your time must be synced with the atomic clock for expiration time to work properly.

        Keyword arguments:
        seconds -- the time in seconds
        """
        if not isinstance(seconds, int) and not isinstance(seconds, float):
            raise TypeError('Seconds must be a float or an int')

        self.token['expires'] = (datetime.now().replace(tzinfo=timezone.utc).timestamp() + seconds) * 1000

        return self

    def expires_at(self, timestamp):
        """Expires the token at the given timestamp
        NOTE: Your time must be synced with the atomic clock for expiration time to work properly.

        Keyword arguments:
        timestamp -- the time as a timestamp
        """
        if not isinstance(timestamp, int) and not isinstance(timestamp, float):
            raise TypeError('Timestamp must be a float or an int')

        self.token['expires'] = timestamp

        return self

    def for_authenticate_only(self):
        """Limit the token to authentication only. (optional)
        """
        self.token['type'] = 'auth'

        return self

    def for_streaming_only(self):
        """Limit the token to streaming only. (optional)
        """
        self.token['type'] = 'stream'

        return self

    def for_publishing_only(self):
        """Limit the token to publishing only. (optional)
        """
        self.token['type'] = 'publish'

        return self

    def for_session(self, session_id):
        """Limit the token to the specified session ID. (optional)

        Keyword arguments:
        session_id -- the session id
        """
        if not isinstance(session_id, str):
            raise TypeError('Session Id must be a string')

        self.token['sessionId'] = session_id

        return self

    def for_remote_address(self, remote_address):
        """Limit the token to the specified remote address. (optional)

        Keyword arguments:
        remote_address -- the remote address
        """
        if not isinstance(remote_address, str):
            raise TypeError('Remote Address must be a string')

        self.token['remoteAddress'] = remote_address

        return self

    def for_origin_stream(self, origin_stream_id):
        """Limit the token to the specified origin stream ID. (optional)

        Keyword arguments:
        origin_stream_id -- the origin stream ID
        """
        if not isinstance(origin_stream_id, str):
            raise TypeError('Origin Stream Id must be a string')

        self.token['originStreamId'] = origin_stream_id

        return self

    def for_channel(self, channel_id):
        """Limit the token to the specified channel ID. (optional)

        Keyword arguments:
        channel_id -- the channel id
        """
        if not isinstance(channel_id, str):
            raise TypeError('Channel ID must be a string')

        self.for_tag('channelId:{}'.format(channel_id))

        return self

    def for_channel_alias(self, channel_alias):
        """Limit the token to the specified channel alias. (optional)

        Keyword arguments:
        channel_alias -- the channel alias
        """
        if not isinstance(channel_alias, str):
            raise TypeError('Channel Alias must be a string')

        self.for_tag('channelAlias:{}'.format(channel_alias))

        return self

    def for_room(self, room_id):
        """Limit the token to the specified room ID. (optional)

        Keyword arguments:
        room_id -- the room id
        """
        if not isinstance(room_id, str):
            raise TypeError('Room ID must be a string')

        self.for_tag('roomId:{}'.format(room_id))

        return self

    def for_room_alias(self, room_alias):
        """Limit the token to the specified room alias. (optional)

        Keyword arguments:
        room_alias -- the room alias
        """
        if not isinstance(room_alias, str):
            raise TypeError('Room Alias must be a string')

        self.for_tag('roomAlias:{}'.format(room_alias))

        return self

    def for_tag(self, tag):
        """Limit the token to the specified tag on the origin stream. (optional)

        Keyword arguments:
        tag -- the tag required on the origin stream
        """
        if not isinstance(tag, str):
            raise TypeError('Tag must be a string')

        self.token['requiredTag'] = tag

        return self

    def apply_tag(self, tag):
        """Apply the tag to the stream when it is setup. (optional)

        Keyword arguments:
        tag -- the tag added to the new stream
        """
        if not isinstance(tag, str):
            raise TypeError('Tag must be a string')

        token = self.token
        apply_tags = set(token['applyTags']) if 'applyTags' in token else set()

        apply_tags.add(tag)

        self.token['applyTags'] = list(apply_tags)

        return self

    def build(self):
        """Build the signed token
        """
        token = DigestTokens()

        if not self.application_id:
            raise ValueError('application_id must be set using the "with_application_id" method before calling "build"')

        if not self.secret:
            raise ValueError('secret must be set using the "with_secret" method call before calling "build"')

        return token.sign_and_encode(self.application_id, self.secret, self.token)
