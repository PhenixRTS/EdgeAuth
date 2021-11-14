package edgeauth

import (
	"crypto/hmac"
	"crypto/sha512"
	"encoding/base64"
	"encoding/json"
	"errors"
	"strings"
)

type digestInfo struct {
	ApplicationID string `json:"applicationId"`
	Digest        string `json:"digest"`
	Token         string `json:"token"`
}

// DigestTokenPrefix is the prefix used for all digest tokens.
const DigestTokenPrefix = "DIGEST:"

// Check if a value is a valid digest token.
//
// Arguments:
//   encodedToken -- an encoded token
func isDigestToken(encodedToken string) bool {
	return strings.HasPrefix(encodedToken, DigestTokenPrefix)
}

// SignAndEncode signs and encodes a digest token.
//
// Arguments:
//   applicationID -- the application ID used to sign the token
//   secret -- the shared secret used to sign the token
//   token -- the raw token object to sign
func SignAndEncode(applicationID string, secret string, token *Token) (*string, error) {
	if _, ok := token.Get(ExpiresField); !ok {
		return nil, errors.New("Token must have an expiration (milliseconds since UNIX epoch)")
	}
	if _, ok := token.Get(ApplicationIDField); ok {
		return nil, errors.New("Token should not have an ApplicationID property")
	}

	tokenAsString, err := json.Marshal(token)
	if err != nil {
		return nil, err
	}

	digest := calculateDigest(applicationID, secret, tokenAsString)

	info := &digestInfo{
		ApplicationID: applicationID,
		Digest:        digest,
		Token:         string(tokenAsString),
	}

	decodedDigestTokenAsString, err := json.Marshal(info)
	if err != nil {
		return nil, err
	}
	encodedDigestToken := base64.StdEncoding.EncodeToString(decodedDigestTokenAsString)

	retval := DigestTokenPrefix + encodedDigestToken
	return &retval, nil
}

// VerifyAndDecodeResult is the result of verifying and decoding a
// digest token.
type VerifyAndDecodeResult struct {
	Verified bool
	Code     string
	Message  string
	Value    *Token
}

// VerifyAndDecode verifies and decodes a digest token.
//
// Arguments:
//  secret -- the shared secret used to sign the token
//  encodedToken -- the encoded token
func VerifyAndDecode(secret string, encodedToken string) *VerifyAndDecodeResult {
	if !isDigestToken(encodedToken) {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "not-a-digest-token",
		}
	}

	encodedDigestToken := encodedToken[len(DigestTokenPrefix):]
	decodedDigestTokenAsString, err := base64.StdEncoding.DecodeString(encodedDigestToken)
	if err != nil {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-token",
		}
	}

	info := digestInfo{}

	err = json.Unmarshal(decodedDigestTokenAsString, &info)
	if err != nil {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-token",
		}
	}

	if len(info.ApplicationID) == 0 {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-token",
		}
	}
	if len(info.Digest) == 0 {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-token",
		}
	}
	if len(info.Token) == 0 {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-token",
		}
	}

	digestAsString := calculateDigest(info.ApplicationID, secret, []byte(info.Token))
	digest := info.Digest

	if digestAsString != digest {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "bad-digest",
		}
	}

	value := NewToken()
	err = json.Unmarshal([]byte(info.Token), &value)
	if err != nil {
		return &VerifyAndDecodeResult{
			Verified: false,
			Code:     "server-error",
			Message:  err.Error(),
		}
	}

	value.Set(ApplicationIDField, info.ApplicationID)

	return &VerifyAndDecodeResult{
		Verified: true,
		Code:     "verified",
		Value:    value,
	}
}

// Calculates the digest for a token.
//
// Arguments:
//  applicationID -- the application ID used to sign the token
//  secret -- the shared secret used to sign the token
//  token -- encoded token
func calculateDigest(applicationID string, secret string, token []byte) string {
	salt := []byte(applicationID + secret)
	verify := hmac.New(sha512.New, salt)
	verify.Write(token)

	digest := base64.StdEncoding.EncodeToString(verify.Sum(nil))

	return digest
}
