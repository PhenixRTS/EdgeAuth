package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"os"
	"strings"
	"time"

	edgeauth "github.com/PhenixRTS/EdgeAuth/golang"
)

// Args collects all the command line arguments together.
type Args struct {
	uri                string
	applicationID      string
	secret             string
	expiresInSeconds   int
	expiresAt          int64
	capabilities       string
	sessionID          string
	remoteAddress      string
	originStreamID     string
	channel            string
	channelAlias       string
	room               string
	roomAlias          string
	tag                string
	applyTag           string
	authenticationOnly bool
	streamingOnly      bool
	publishingOnly     bool
}

func main() {
	args := parseArgs(os.Args)

	if args.applicationID == "" || args.secret == "" {
		fmt.Fprintln(os.Stderr, "Must specify application ID and secret")
		os.Exit(1)
	}
	token := edgeauth.NewTokenBuilder().
		WithApplicationID(args.applicationID).
		WithSecret(args.secret)

	if args.uri != "" {
		token.WithURI(args.uri)
	}

	if args.expiresAt != 0 {
		token.ExpiresAt(time.UnixMilli(args.expiresAt))
	} else {
		token.ExpiresInSeconds(args.expiresInSeconds)
	}

	if args.authenticationOnly {
		token.ForAuthenticateOnly()
	}
	if args.streamingOnly {
		token.ForStreamingOnly()
	}
	if args.publishingOnly {
		token.ForPublishingOnly()
	}

	if args.capabilities != "" {
		for _, capability := range strings.Split(args.capabilities, ",") {
			token.WithCapability(strings.TrimSpace(capability))
		}
	}

	if args.sessionID != "" {
		token.ForSession(args.sessionID)
	}

	if args.remoteAddress != "" {
		token.ForRemoteAddress(args.remoteAddress)
	}

	if args.originStreamID != "" {
		token.ForOriginStream(args.originStreamID)
	}

	if args.channel != "" {
		token.ForChannel(args.channel)
	}

	if args.channelAlias != "" {
		token.ForChannelAlias(args.channelAlias)
	}

	if args.room != "" {
		token.ForRoom(args.room)
	}

	if args.roomAlias != "" {
		token.ForRoomAlias(args.roomAlias)
	}

	if args.tag != "" {
		token.ForTag(args.tag)
	}

	if args.applyTag != "" {
		token.ApplyTag(args.applyTag)
	}

	tokenAsString, err := json.Marshal(token.Value())
	if err != nil {
		fmt.Println("Token encoding error:", err.Error())
		os.Exit(1)
	}
	fmt.Println(string(tokenAsString))

	digest, err := token.Build()
	if err != nil {
		fmt.Println("Token encoding error:", err.Error())
		os.Exit(1)
	}
	fmt.Println(*digest)
}

func parseArgs(argsIn []string) Args {
	args := Args{}

	flagSet := flag.NewFlagSet(argsIn[0], flag.ExitOnError)

	flagSet.StringVar(&args.uri, "uri", "",
		"the backend URI")

	flagSet.StringVar(&args.applicationID, "application_id", "",
		"the application ID")
	flagSet.StringVar(&args.secret, "secret", "",
		"the application secret")

	flagSet.IntVar(&args.expiresInSeconds, "expires_in_seconds", 3600,
		"token life time in seconds")
	flagSet.Int64Var(&args.expiresAt, "expires_at", 0,
		"token expires at timestamp measured in milliseconds since UNIX epoch")

	flagSet.StringVar(&args.capabilities, "capabilities", "",
		"comma separated list of capabilities, e.g. for publishing")

	flagSet.StringVar(&args.sessionID, "session_id", "",
		"token is limited to the given session")
	flagSet.StringVar(&args.remoteAddress, "remote_address", "",
		"token is limited to the given remote address")
	flagSet.StringVar(&args.originStreamID, "origin_stream_id", "",
		"[STREAMING] token is limited to the given origin stream")
	flagSet.StringVar(&args.channel, "channel", "",
		"[STREAMING] token is limited to the given channel")
	flagSet.StringVar(&args.channelAlias, "channel_alias", "",
		"[STREAMING] token is limited to the given channel alias")
	flagSet.StringVar(&args.room, "room", "",
		"[STREAMING] token is limited to the given room")
	flagSet.StringVar(&args.roomAlias, "room_alias", "",
		"[STREAMING] token is limited to the given room alias")

	flagSet.StringVar(&args.tag, "tag", "",
		"[STREAMING] token is limited to the given origin stream tag")
	flagSet.StringVar(&args.applyTag, "apply_tag", "",
		"[REPORTING] apply tag to the new stream")

	flagSet.BoolVar(&args.authenticationOnly, "authentication_only", false,
		"token can be used for authentication only")
	flagSet.BoolVar(&args.streamingOnly, "streaming_only", false,
		"token can be used for streaming only")
	flagSet.BoolVar(&args.publishingOnly, "publishing_only", false,
		"token can be used for publishing only")

	flagSet.Parse(argsIn[1:])

	return args
}
