package edgeauth

import (
	edgeauth "github.com/PhenixRTS/EdgeAuth/golang"
)

var token *string
var correctToken *string
var secret *string
var builder *edgeauth.TokenBuilder
var result *edgeauth.VerifyAndDecodeResult
