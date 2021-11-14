package edgeauth

import (
	"errors"
	"time"

	"github.com/iancoleman/orderedmap"
)

// Field names for token data.
const (
	ApplicationIDField  = "applicationId"
	SecretField         = "secret"
	URIField            = "uri"
	ExpiresField        = "expires"
	CapabilitiesField   = "capabilities"
	RequiredTagField    = "requiredTag"
	TypeField           = "type"
	SessionIDField      = "sessionId"
	RemoteAddressField  = "remoteAddress"
	OriginStreamIDField = "originStreamId"
	ApplyTagsField      = "applyTags"
)

// Token represents the fields in a token, maintaining the order that
// fields are added.
type Token struct {
	*orderedmap.OrderedMap
}

// NewToken wraps an ordered map as a Token.
func NewToken() *Token {
	t := Token{orderedmap.New()}
	return &t
}

// TokenBuilder is a helper type to create digest tokens that can be
// used with the Phenix platform.
type TokenBuilder struct {
	applicationID *string
	secret        *string
	token         *Token
}

// NewTokenBuilder creates a new token builder.
func NewTokenBuilder() *TokenBuilder {
	t := TokenBuilder{}
	t.token = NewToken()
	return &t
}

// Value returns the internal token value of a token builder.
func (b *TokenBuilder) Value() *Token {
	return b.token
}

// Add adds a single field to the token.
func (b *TokenBuilder) add(name string, value interface{}) *TokenBuilder {
	b.token.Set(name, value)
	return b
}

// AddToArray adds an entry to a string array field of the token.
func (b *TokenBuilder) addToArray(name string, value string) *TokenBuilder {
	if f, ok := b.token.Get(name); ok {
		if val, check := f.([]string); check {
			b.token.Set(name, append(val, value))
		}
	} else {
		b.token.Set(name, []string{value})
	}
	return b
}

// WithApplicationID sets the application ID used to sign the token.
// (required)
//
// Arguments:
//  applicationID -- the application ID to sign the token
func (b *TokenBuilder) WithApplicationID(applicationID string) *TokenBuilder {
	b.applicationID = &applicationID
	return b
}

// WithSecret sets the secret used to sign the token. (required)
//
//  Arguments:
// secret -- the shared secret to sign the token
func (b *TokenBuilder) WithSecret(secret string) *TokenBuilder {
	b.secret = &secret
	return b
}

// WithURI sets the backend URI. (optional)
//
// Arguments:
//   uri -- the backend URI
func (b *TokenBuilder) WithURI(uri string) *TokenBuilder {
	return b.add(URIField, uri)
}

// WithCapability sets a capability for the token, e.g. to publish a
// stream. (optional)
//
// Arguments:
//   capability -- the valid capability
func (b *TokenBuilder) WithCapability(capability string) *TokenBuilder {
	return b.addToArray(CapabilitiesField, capability)
}

// ExpiresInSeconds expires the token in the given time.
// NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
//
// Arguments:
//   seconds -- the time in seconds
func (b *TokenBuilder) ExpiresInSeconds(seconds int) *TokenBuilder {
	expires := time.Now().Add(time.Second * time.Duration(seconds)).UnixMilli()
	return b.add(ExpiresField, &expires)
}

// ExpiresAt expires the token at the given dateime
// NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
//
// Arguments:
//   exDatetime -- the time as a datetime
func (b *TokenBuilder) ExpiresAt(exDatetime time.Time) *TokenBuilder {
	expires := exDatetime.UnixMilli()
	return b.add(ExpiresField, &expires)
}

// ForAuthenticateOnly limits the token to authentication only.
// (optional)
func (b *TokenBuilder) ForAuthenticateOnly() *TokenBuilder {
	return b.add(TypeField, "auth")
}

// ForStreamingOnly limits the token to streaming only. (optional)
func (b *TokenBuilder) ForStreamingOnly() *TokenBuilder {
	return b.add(TypeField, "stream")
}

// ForPublishingOnly limits the token to publishing only. (optional)
func (b *TokenBuilder) ForPublishingOnly() *TokenBuilder {
	return b.add(TypeField, "publish")
}

// ForSession limits the token to the specified session ID. (optional)
//
// Arguments:
//   sessionID -- the session id
func (b *TokenBuilder) ForSession(sessionID string) *TokenBuilder {
	return b.add(SessionIDField, sessionID)
}

// ForRemoteAddress limits the token to the specified remote address.
// (optional)
//
// Arguments:
//   remoteAddress -- the remote address
func (b *TokenBuilder) ForRemoteAddress(remoteAddress string) *TokenBuilder {
	return b.add(RemoteAddressField, remoteAddress)
}

// ForOriginStream limits the token to the specified origin stream ID.
// (optional)
//
// Arguments:
//   originStreamID -- the origin stream ID
func (b *TokenBuilder) ForOriginStream(originStreamID string) *TokenBuilder {
	return b.add(OriginStreamIDField, originStreamID)
}

// ForChannel limits the token to the specified channel ID. (optional)
//
// Arguments:
//  channelID -- the channel id
func (b *TokenBuilder) ForChannel(channelID string) *TokenBuilder {
	return b.ForTag("channelId:" + channelID)
}

// ForChannelAlias limits the token to the specified channel alias.
// (optional)
//
// Arguments:
//  channelAlias -- the channel alais
func (b *TokenBuilder) ForChannelAlias(channelAlias string) *TokenBuilder {
	return b.ForTag("channelAlias:" + channelAlias)
}

// ForRoom limits the token to the specified room ID. (optional)
//
// Arguments:
//   roomID -- the room id
func (b *TokenBuilder) ForRoom(roomID string) *TokenBuilder {
	return b.ForTag("roomId:" + roomID)
}

// ForRoomAlias limits the token to the specified room alias.
// (optional)
//
// Arguments:
//   roomAlias -- the room alias
func (b *TokenBuilder) ForRoomAlias(roomAlias string) *TokenBuilder {
	return b.ForTag("roomAlias:" + roomAlias)
}

// ForTag limits the token to the specified tag on the origin stream.
// (optional)
//
// Arguments:
//  tag -- the tag required on the origin stream
func (b *TokenBuilder) ForTag(tag string) *TokenBuilder {
	return b.add(RequiredTagField, tag)
}

// ApplyTag applies the tag to the stream when it is setup. (optional)
//
// Arguments:
//   tag -- the tag added to the new stream
func (b *TokenBuilder) ApplyTag(tag string) *TokenBuilder {
	return b.addToArray(ApplyTagsField, tag)
}

// Build builds the signed token.
func (b *TokenBuilder) Build() (*string, error) {
	if b.applicationID == nil {
		return nil, errors.New("applicationID must be set using the WithApplicationID method before calling Build")
	}
	if b.secret == nil {
		return nil, errors.New("secret must be set using the WithSecret method before calling Build")
	}

	return SignAndEncode(*b.applicationID, *b.secret, b.token)
}
