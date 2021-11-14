# Phenix EdgeAuth Digest Tokens for Go

Easily generate secure digest tokens to use with the Phenix platform
without requiring any networking activity.

## Installation

To install the `edgeauth` Go executable to create Phenix Edge
Authorization Digest Tokens (this installs the executable into
`$GOPATH/bin`):

```shell script
$ go install github.com/PhenixRTS/EdgeAuth/golang/...@v0.0.1
```

To install the Go package to develop against:

```shell script
$ go get github.com/PhenixRTS/EdgeAuth/golang@v0.0.1
```

To build the `edgeauth` executable from source, clone this repository
and in the `golang` directory:

```shell script
$ go build ./cmd/edgeauth
```

## Testing

The BDD tests require the godog BBD test framework. Add $GOPATH/bin to your $PATH.

To run Behavior-Driven Development tests with `godog`, in the `golang`
directory:


```shell script
$ go install github.com/cucumber/godog/cmd/godog@v0.12.0
$ cd test
$ godog run
```

## Example

After installing the `golang` package as described above, create
a Go test program:

```shell script
$ mkdir ~/edgeauth-test
$ cd ~/edgeauth-test
$ go mod init example.com/edgeauth-test
```

Copy the following text into a `main.go` file:

```go
package main

import (
	"fmt"
	edgeauth "github.com/PhenixRTS/EdgeAuth/golang"
)

func main() {
	// Create a token to access a channel
	token, err := edgeauth.NewTokenBuilder().
		WithApplicationID("my-application-id").
		WithSecret("my-secret").
		ExpiresInSeconds(3600).
		ForChannel("us-northeast#my-application-id#my-channel.1345").
		Build()

	if err != nil {
		fmt.Println("Token encoding error:", err.Error())
	} else {
		fmt.Println(*token)
	}
}
```

Build and run the example:

```shell script
$ go get github.com/PhenixRTS/EdgeAuth/golang@v0.0.1
$ go build
$ ./edgeauth-test
```

## Command Line Examples

Using the `edgeauth` program installed or build as described above,
display the help information:

```shell script
$ edgeauth -help
```

Create a token for channel access:

```shell script
$ edgeauth -application_id "my-application-id" -secret "my-secret" -expires_in_seconds 3600 -channel "us-northeast#my-application-id#my-channel.1345"
```
