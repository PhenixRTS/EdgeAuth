# Phenix EdgeAuth Digest Tokens for Java

Easily generate secure digest tokens to use with the Phenix platform without requiring any networking activity.

## Installation

To install the Phenix Edge Authorization Digest Token library with gradle, add the following depencency line to your `build.gradle`:

```Groovy
implementation 'com.phenixrts.edgeauth:edge-auth:1.0.5'
```

OR

```Groovy
compile group: 'com.phenixrts.edgeauth', name: 'edge-auth', version: '1.0.5'
```

### Bundled UberJar

To install the Phenix Edge Authorization Digest Token uberjar bundled with all its dependencies, add the following dependency to your `build.gradle`:

```Groovy
compile group: 'com.phenixrts.edgeauth', name: 'edge-auth', version: '1.0.5', clasifier: 'bundle'
```

## Java Example

```Java
import com.phenixrts.edgeauth.TokenBuilder;

// Create a token to access a channel
final String token = new TokenBuilder()
	.withApplicationId("my-application-id")
	.withSecret("my-secret")
	.expiresInSeconds(3600)
	.forChannel("us-northeast#my-application-id#my-channel.1345")
	.build();
```

## Command Line Examples

Display the help information:
```shell script
java -jar build/libs/edge-auth-1.1-SNAPSHOT-bundle.jar --help
```

Create a token for channel access:
```shell script
java -jar build/libs/edge-auth-1.1-SNAPSHOT-bundle.jar --applicationId "my-application-id" --secret "my-secret" --expiresInSeconds 3600 --channel "us-northeast#my-application-id#my-channel.1345"
```
