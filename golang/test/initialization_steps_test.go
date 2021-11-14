package edgeauth

import (
	"time"

	edgeauth "github.com/PhenixRTS/EdgeAuth/golang"
)

func iHaveABadToken() error {
	tmp := "DIGEST:bad-token"
	token = &tmp
	return nil
}

func iHaveAGoodToken() error {
	builder = edgeauth.NewTokenBuilder()
	builder = builder.
		WithApplicationID("my-application-id").
		WithSecret("my-secret").
		ExpiresAt(time.UnixMilli(1000))
	return nil
}

func iHaveAGoodTokenWithURI(arg1 string) error {
	builder = edgeauth.NewTokenBuilder()
	builder = builder.
		WithApplicationID("my-application-id").
		WithSecret("my-secret").
		WithURI(arg1).
		ExpiresAt(time.UnixMilli(1000))
	return nil
}
