/**
 * Copyright 2019 Phenix Real Time Solutions, Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

const program = require('commander');
const TokenBuilder = require('./TokenBuilder');
const defaultExpirationInSeconds = 3600;

program
  .option('-y, --uri <uri>', 'The backend URI')
  .option('-u, --applicationId <applicationId>', 'The application ID')
  .option('-w, --secret <secret>', 'The application secret')
  .option('-l, --expiresInSeconds <timeInSeconds>', 'Token life time in seconds')
  .option('-e, --expiresAt <timestamp>', 'Token expires at timestamp measured in milliseconds since UNIX epoch')
  .option('-a, --authenticationOnly', 'Token can be used for authentication only')
  .option('-s, --streamingOnly', 'Token can be used for streaming only')
  .option('-p, --publishingOnly', 'Token can be used for publishing only')
  .option('-b, --capabilities <capabilities>', 'Comma separated list of capabilities, e.g. for publishing')
  .option('-z, --sessionId <sessionId>', 'Token is limited to the given session')
  .option('-x, --remoteAddress <remoteAddress>', 'Token is limited to the given remote address')
  .option('-o, --originStreamId <originStreamId>', '[STREAMING] Token is limited to the given origin stream')
  .option('-c, --channel <channelId>', '[STREAMING] Token is limited to the given channel')
  .option('-i, --channelAlias <channelAlias>', '[STREAMING] Token is limited to the given channel alias')
  .option('-m, --room <roomId>', '[STREAMING] Token is limited to the given room')
  .option('-n, --roomAlias <roomAlias>', '[STREAMING] Token is limited to the given room alias')
  .option('-t, --tag <tag>', '[STREAMING] Token is limited to the given origin stream tag')
  .option('-r, --applyTag <applyTag...>', '[REPORTING] Apply tag to the new stream');

program.parse(process.argv);

const options = program.opts();
const tokenBuilder = new TokenBuilder()
  .withApplicationId(options.applicationId)
  .withSecret(options.secret);

if (options.uri) {
  tokenBuilder.withUri(options.uri);
}

if (options.expiresAt !== undefined) {
  tokenBuilder.expiresAt(new Date(parseInt(options.expiresAt, 10)));
} else {
  tokenBuilder.expiresInSeconds(parseInt(options.expiresInSeconds || defaultExpirationInSeconds, 10));
}

if (options.authenticationOnly) {
  tokenBuilder.forAuthenticateOnly();
}

if (options.streamingOnly) {
  tokenBuilder.forStreamingOnly();
}

if (options.publishingOnly) {
  tokenBuilder.forPublishingOnly();
}

if (options.capabilities) {
  options.capabilities.split(',').forEach((capability) => tokenBuilder.withCapability(capability));
}

if (options.sessionId) {
  tokenBuilder.forSession(options.sessionId);
}

if (options.remoteAddress) {
  tokenBuilder.forRemoteAddress(options.remoteAddress);
}

if (options.originStreamId) {
  tokenBuilder.forOriginStream(options.originStreamId);
}

if (options.channel) {
  tokenBuilder.forChannel(options.channel);
}

if (options.channelAlias) {
  tokenBuilder.forChannelAlias(options.channelAlias);
}

if (options.room) {
  tokenBuilder.forRoom(options.room);
}

if (options.roomAlias) {
  tokenBuilder.forRoomAlias(options.roomAlias);
}

if (options.tag) {
  tokenBuilder.forTag(options.tag);
}

if (options.applyTag) {
  tokenBuilder.applyTags(options.applyTag);
}

const tokenObject = tokenBuilder.value();

console.log(tokenObject);

const token = tokenBuilder.build();

console.log(token);