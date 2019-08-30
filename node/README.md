# Phenix EdgeAuth Digest Tokens for Node

Easily generate secure digest tokens to use with the Phenix platform without requiring any networking activity.

## Installation

To install Phenix Edge Authorization Digest Token with npm:

```shell script
$ npm install phenix-edge-auth --save
```

## JavaScript Example

```Javascript
const TokenBuilder = require('phenix-edge-auth');

// Create a token to access a channel
const token = new TokenBuilder()
	.withApplicationId('my-application-id')
	.withSecret('my-secret')
	.expiresInSeconds(3600)
	.forChannel('us-northeast#my-application-id#my-channel.1345')
	.build();
```

## Command Line Examples

Display the help information:
```shell script
node src/edgeAuth.js --help
```

Create a token for channel access:
```shell script
node src/edgeAuth.js --applicationId "my-application-id" --secret "my-secret" --expiresInSeconds 3600 --channel "us-northeast#my-application-id#my-channel.1345"
```
