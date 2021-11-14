package edgeauth

import (
	"context"

	"github.com/cucumber/godog"
)

func InitializeScenario(ctx *godog.ScenarioContext) {
	ctx.Before(func(ctx context.Context, sc *godog.Scenario) (context.Context, error) {
		token = nil
		secret = nil
		correctToken = nil
		builder = nil
		result = nil
		return ctx, nil
	})

	ctx.Step(`^I have a bad token$`, iHaveABadToken)
	ctx.Step(`^I have a good token$`, iHaveAGoodToken)
	ctx.Step(`^I have a good token with URI "([^"]*)"$`, iHaveAGoodTokenWithURI)
	ctx.Step(`^I try to verify a token with a good secret$`, iTryToVerifyATokenWithAGoodSecret)
	ctx.Step(`^I try to verify a token with a bad secret$`, iTryToVerifyATokenWithABadSecret)
	ctx.Step(`^The correct token is "([^"]*)"$`, theCorrectTokenIs)
	ctx.Step(`^The token is for a channel "([^"]*)"$`, theTokenIsForAChannel)
	ctx.Step(`^The token is for a channel alias "([^"]*)"$`, theTokenIsForAChannelAlias)
	ctx.Step(`^The token is for a room "([^"]*)"$`, theTokenIsForARoom)
	ctx.Step(`^The token is for a room alias "([^"]*)"$`, theTokenIsForARoomAlias)
	ctx.Step(`^The token is for a remote address "([^"]*)"$`, theTokenIsForARemoteAddress)
	ctx.Step(`^The token is for a session "([^"]*)"$`, theTokenIsForASession)
	ctx.Step(`^The token is for tag "([^"]*)"$`, theTokenIsForTag)
	ctx.Step(`^The token has a "([^"]*)" tag applied$`, theTokenHasATagApplied)
	ctx.Step(`^The token is for streaming only$`, theTokenIsForStreamingOnly)
	ctx.Step(`^The token is for publishing only$`, theTokenIsForPublishingOnly)
	ctx.Step(`^The token has capability "([^"]*)"$`, theTokenHasCapability)
	ctx.Step(`^Verification should fail with error "([^"]*)"$`, verificationShouldFailWithError)
	ctx.Step(`^Verification should pass$`, verificationShouldPass)
	ctx.Step(`^The tag field should be "([^"]*)"$`, theTagFieldShouldBe)
	ctx.Step(`^The remote address field should be "([^"]*)"$`, theRemoteAddressFieldShouldBe)
	ctx.Step(`^The session field should be "([^"]*)"$`, theSessionFieldShouldBe)
	ctx.Step(`^The URI field should be "([^"]*)"$`, theURIFieldShouldBe)
	ctx.Step(`^The applied tags field should be "([^"]*)"$`, theAppliedTagsFieldShouldBe)
	ctx.Step(`^The type field should be "([^"]*)"$`, theTypeFieldShouldBe)
	ctx.Step(`^The capabilities field should be "([^"]*)"$`, theCapabilitiesFieldShouldBe)
}
