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
const DigestTokens = require('./DigestTokens');

/**
 * Token builder helper class to create digest tokens that can be used with the Phenix platform.
 *
 * @constructor
 */
function TokenBuilder() {
  this.applicationId = null;
  this.secret = null;
  this.token = {};
}

/**
 * The application ID used to sign the token (required).
 *
 * @param applicationId the application ID to sign the token
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.withApplicationId = function(applicationId) {
  if (typeof applicationId !== 'string') {
    throw new Error('Application ID must be a string');
  }

  this.applicationId = applicationId;

  return this;
};

/**
 * The secret used to sign the token (required).
 *
 * @param secret the shared secret to sign the token
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.withSecret = function(secret) {
  if (typeof secret !== 'string') {
    throw new Error('Secret must be a string');
  }

  this.secret = secret;

  return this;
};

/**
 * Expires the token in the given time.
 * NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
 *
 * @param seconds the time in seconds
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.expiresInSeconds = function(seconds) {
  if (typeof seconds !== 'number') {
    throw new Error('Expiration seconds must be a number');
  }

  this.token.expires = Date.now() + (seconds * 1000);

  return this;
};

/**
 * Expires the token at the given date.
 * NOTE: Your time must be synced with the atomic clock for expiration time to work properly.
 *
 * @param expirationDate the expiration date
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.expiresAt = function(expirationDate) {
  if (!(expirationDate instanceof Date)) {
    throw new Error('Expiration date must be a valid date');
  }

  this.token.expires = expirationDate.getTime();

  return this;
};

/**
 * Limit the token to authentication only (optional).
 *
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.forAuthenticateOnly = function() {
  this.token.type = 'auth';

  return this;
};

/**
 * Limit the token to streaming only (optional).
 *
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.forStreamingOnly = function() {
  this.token.type = 'stream';

  return this;
};

/**
 * Limit the token to the specified origin stream ID (optional).
 *
 * @param originStreamId the origin stream ID
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.forOriginStream = function(originStreamId) {
  if (typeof originStreamId !== 'string') {
    throw new Error('Origin Stream ID must be a string');
  }

  this.token.originStreamId = originStreamId;

  return this;
};

/**
 * Limit the token to the specified channel ID (optional).
 *
 * @param channelId the channel ID
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.forChannel = function(channelId) {
  if (typeof channelId !== 'string') {
    throw new Error('Channel ID must be a string');
  }

  this.token.subscribeTag = 'channelId:' + channelId;

  return this;
};

/**
 * Limit the token to the specified channel alias (optional).
 *
 * @param channelAlias the channel alias
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.forChannelAlias = function(channelAlias) {
  if (typeof channelAlias !== 'string') {
    throw new Error('Channel alias must be a string');
  }

  this.token.subscribeTag = 'channelAlias:' + channelAlias;

  return this;
};

/**
 * Apply the tag to the stream when it is setup (optional).
 *
 * @param tag the tag
 * @returns {TokenBuilder} itself
 */
TokenBuilder.prototype.applyTag = function(tag) {
  if (typeof tag !== 'string') {
    throw new Error('Tag must be a string');
  }

  if (!this.token.applyTags) {
    this.token.applyTags = [];
  }

  this.token.applyTags.push(tag);

  return this;
};

/**
 * Build the signed token
 *
 * @returns {String} the signed token that can be used with the Phenix platform
 */
TokenBuilder.prototype.build = function() {
  const digestTokens = new DigestTokens();

  return digestTokens.signAndEncode(this.applicationId, this.secret, this.token);
};

module.exports = TokenBuilder;