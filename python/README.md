# Phenix EdgeAuth Digest Tokens for Node

Easily generate secure digest tokens to use with the Phenix platform without requiring any networking activity.

## Installation

To install Phenix Edge Authorization Digest Token with npm:

```shell script
$ pip install phenix-edge-auth
```

## Testing

```shell script
$ pytest -vv
```

## Example

```python
from edgeauth.token_builder import TokenBuilder

# Create a token to access a channel
token = TokenBuilder()
    .with_application_id('my-application-id') \
    .with_secret('my-secret') \
    .expires_in_seconds(3600) \
    .for_channel('us-northeast#my-application-id#my-channel.1345') \
    .build()
```

## Command Line Examples

Display the help information:
```shell script
$ edgeauth_cli --help
```

Create a token for channel access:
```shell script
$ edgeauth_cli --application_id "my-application-id" --secret "my-secret" --expires_in_seconds 3600 --channel "us-northeast#my-application-id#my-channel.1345"
```
