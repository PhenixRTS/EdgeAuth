import argparse
import readline

from pprint import PrettyPrinter
from sys import version_info

from token_builder import TokenBuilder

PY3_MIN_VERSION = (3, 6)

if version_info < PY3_MIN_VERSION:
    raise Exception('Please use Python >= 3.6')

parser = argparse.ArgumentParser(description='EdgeAuth token generator')


parser.add_argument('-u', '--application_id', required=True, help='The application ID')
parser.add_argument('-w', '--secret', required=True, help='The application secret')
parser.add_argument('-l', '--expires_in_seconds', type=int, default=3600, help='Token life time in seconds')
parser.add_argument('-e', '--expires_at', help='Token expires at timestamp measured in milliseconds since UNIX epoch')
parser.add_argument('-b', '--capabilities', help='Comma separated list of capabilities, e.g. for publishing')
parser.add_argument('-z', '--session_id', help='Token is limited to the given session')
parser.add_argument('-x', '--remote_address', help='Token is limited to the given remote address')

parser.add_argument('-o', '--origin_stream_id', help='[STREAMING] Token is limited to the given origin stream')
parser.add_argument('-c', '--channel', help='[STREAMING] Token is limited to the given channel')
parser.add_argument('-i', '--channel_alias', help='[STREAMING] Token is limited to the given channel alias')
parser.add_argument('-m', '--room', help='[STREAMING] Token is limited to the given room')
parser.add_argument('-n', '--room_alias', help='[STREAMING] Token is limited to the given room alias')
parser.add_argument('-t', '--tag', help='[STREAMING] Token is limited to the given origin stream tag')

parser.add_argument('-r', '--apply_tag', help='[REPORTING] Apply tag to the new stream')

parser.add_argument('-a', '--authentication_only', action="store_true", help='Token can be used for authentication only')
parser.add_argument('-s', '--streaming_only', action="store_true", help='Token can be used for streaming only')
parser.add_argument('-p', '--publishing_only', action="store_true", help='Token can be used for publishing only')


args = parser.parse_args()

pp = PrettyPrinter().pprint

token = TokenBuilder() \
    .with_application_id(args.application_id) \
    .with_secret(args.secret)


if args.expires_at is not None:
    token.expires_at(args.expires_at)
else:
    token.expires_in_seconds(args.expires_in_seconds)

pp(args)

pp(token.build())