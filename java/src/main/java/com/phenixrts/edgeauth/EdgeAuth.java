/**
 * Copyright 2019 Phenix Real Time Solutions, Inc. All Rights Reserved.
 * <p>
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * <p>
 * http://www.apache.org/licenses/LICENSE-2.0
 * <p>
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package com.phenixrts.edgeauth;

import java.util.Date;

import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.DefaultParser;
import org.apache.commons.cli.HelpFormatter;
import org.apache.commons.cli.Options;
import org.apache.commons.cli.ParseException;

public class EdgeAuth {
  private static final long DEFAULT_EXPIRATION_IN_SECONDS = 3600;

  /**
   * Edge Auth token generator CLI.
   *
   * @param args the CLI arguments
   */
  public static void main(String[] args) {
    final Options options = createOptions();
    final CommandLineParser parser = new DefaultParser();
    final CommandLine cmd;

    try {
      cmd = parser.parse(options, args);
    } catch (ParseException e) {
      HelpFormatter formatter = new HelpFormatter();
      formatter.printHelp(EdgeAuth.class.getSimpleName(), options);

      System.exit(1);

      return;
    }

    if (cmd.hasOption("help")) {
      HelpFormatter formatter = new HelpFormatter();
      formatter.printHelp(EdgeAuth.class.getSimpleName(), options);

      return;
    }

    final TokenBuilder tokenBuilder = new TokenBuilder()
        .withApplicationId(cmd.getOptionValue("applicationId"))
        .withSecret(cmd.getOptionValue("secret"));

    if (cmd.hasOption("expiresAt")) {
      tokenBuilder.expiresAt(new Date(Long.parseLong(cmd.getOptionValue("expiresAt"), 10)));
    } else {
      tokenBuilder.expiresInSeconds(Long.parseLong(cmd.getOptionValue("expiresInSeconds", Long.toString(DEFAULT_EXPIRATION_IN_SECONDS)), 10));
    }

    if (cmd.hasOption("authenticationOnly")) {
      tokenBuilder.forAuthenticationOnly();
    }

    if (cmd.hasOption("streamingOnly")) {
      tokenBuilder.forStreamingOnly();
    }

    if (cmd.hasOption("originStreamId")) {
      tokenBuilder.forOriginStream(cmd.getOptionValue("originStreamId"));
    }

    if (cmd.hasOption("channel")) {
      tokenBuilder.forChannel(cmd.getOptionValue("channel"));
    }

    if (cmd.hasOption("channelAlias")) {
      tokenBuilder.forChannelAlias(cmd.getOptionValue("channelAlias"));
    }

    if (cmd.hasOption("tag")) {
      tokenBuilder.applyTag(cmd.getOptionValue("tag"));
    }

    final String token;
    try {
      token = tokenBuilder.build();
    } catch (Exception e) {
      System.err.println(e.getMessage());
      System.exit(7);

      return;
    }

    System.out.println(token);
  }

  private static Options createOptions() {
    final Options options = new Options();

    options.addOption("u", "applicationId", true, "The application ID");
    options.addOption("p", "secret", true, "The application secret");
    options.addOption("l", "expiresInSeconds", true, "Token life time in seconds");
    options.addOption("e", "expiresAt", true, "Token expires at timestamp measured in milliseconds since UNIX epoch");
    options.addOption("a", "authenticationOnly", false, "Token can be used for authentication only");
    options.addOption("s", "streamingOnly", false, "Token can be used for streaming only");
    options.addOption("o", "originStreamId", true, "[STREAMING] Token is limited to the given origin stream");
    options.addOption("c", "channel", true, "[STREAMING] Token is limited to the given channel");
    options.addOption("i", "channelAlias", true, "[STREAMING] Token is limited to the given channel alias");
    options.addOption("t", "tag", true, "[REPORTING] Apply tag to the viewer stream");
    options.addOption("h", "help", false, "Print this message");

    return options;
  }
}
