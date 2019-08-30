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
  .option('-u, --applicationId <applicationId>', 'The application ID')
  .option('-p, --secret <secret>', 'The application secret')
  .option('-l, --expiresInSeconds <timeInSeconds>', 'Token life time in seconds')
  .option('-e, --expiresAt <timestamp>', 'Token expires at timestamp measured in milliseconds since UNIX epoch')
  .option('-a, --authenticationOnly', 'Token can be used for authentication only')
  .option('-s, --streamingOnly', 'Token can be used for streaming only')
  .option('-o, --originStreamId <originStreamId>', '[STREAMING] Token is limited to the given origin stream')
  .option('-c, --channel <channelId>', '[STREAMING] Token is limited to the given channel')
  .option('-t, --tag <tag>', '[REPORTING] Apply tag to the viewer stream');

program.parse(process.argv);

const tokenBuilder = new TokenBuilder()
  .withApplicationId(program.applicationId)
  .withSecret(program.secret);

if (program.expiresAt !== undefined) {
  tokenBuilder.expiresAt(new Date(parseInt(program.expiresAt, 10)));
} else {
  tokenBuilder.expiresInSeconds(parseInt(program.expiresInSeconds || defaultExpirationInSeconds, 10));
}

if (program.authenticationOnly) {
  tokenBuilder.forAuthenticateOnly();
}

if (program.streamingOnly) {
  tokenBuilder.forStreamingOnly();
}

if (program.originStreamId) {
  tokenBuilder.forOriginStream(program.originStreamId);
}

if (program.channel) {
  tokenBuilder.forChannel(program.channel);
}

if (program.tag) {
  tokenBuilder.forTag(program.tag);
}

const token = tokenBuilder.build();

console.log(token);