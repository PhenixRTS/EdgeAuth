<?php


namespace EdgeAuth;



use Garden\Cli\Cli;

class EdgeAuthCommand
{
    const defaultExpirationInSeconds = 3600;

    /**
     * @var Cli
     */
    private $cli;

    function __construct()
    {

    }

    public function Execute(&$args){
        try{
            $this->cli = new Cli();
            $this->cli->description('Generate an EdgeAuth token')
                ->opt('uri:y', 'The backend URI')
                ->opt('applicationId:u', 'The application ID', true)
                ->opt('secret:w', 'The application secret', true)
                ->opt('expiresInSeconds:l', 'Token life time in seconds')
                ->opt('expiresAt:e', 'Token expires at timestamp measured in milliseconds since UNIX epoch')
                ->opt('authenticationOnly:a',  'Token can be used for authentication only')
                ->opt('streamingOnly:s',  'Token can be used for streaming only')
                ->opt('publishingOnly:p',  'Token can be used for publishing only')
                ->opt('capabilities:b',  'Comma separated list of capabilities, e.g. for publishing')
                ->opt('sessionId:z',  'Token is limited to the given session')
                ->opt('remoteAddress:x',  'Token is limited to the given remote address')
                ->opt('originStreamId:o', '[STREAMING] Token is limited to the given origin stream')
                ->opt('channel:c', '[STREAMING] Token is limited to the given channel')
                ->opt('channelAlias:i', '[STREAMING] Token is limited to the given channel alias')
                ->opt('room:m', '[STREAMING] Token is limited to the given room')
                ->opt('roomAlias:n', '[STREAMING] Token is limited to the given room alias')
                ->opt('tag:t', '[STREAMING] Token  is  limited to  the given origin stream tag')
                ->opt('applyTag:r',  '[REPORTING] Apply tag to the new stream');

            // Parse the args
            $parsedArgs = $this->cli->parse($args, true);

            // Setup the token builder on required arguments
            $tokenBuilder = (new TokenBuilder())
                ->withApplicationId($parsedArgs->getOpt('applicationId'))
                ->withSecret($parsedArgs->getOpt('secret'))
            ;

            $uri = $parsedArgs->getOpt('uri');
            if ($uri !== null) {
                $tokenBuilder->withUri($uri);
            }

            $expiresAt = $parsedArgs->getOpt('expiresAt');
            if($expiresAt !== null){
                $tokenBuilder->expiresAt(\DateTime::createFromFormat('U.u', sprintf('%F', $expiresAt / 1000)));
            } else {
                $expiresInSeconds= $parsedArgs->getOpt('expiresInSeconds');
                if($expiresInSeconds !== null){
                    $tokenBuilder->expiresInSeconds($expiresInSeconds);
                } else {
                    $tokenBuilder->expiresInSeconds(self::defaultExpirationInSeconds);
                }
            }

            $authenticationOnly = $parsedArgs->getOpt('authenticationOnly');
            if($authenticationOnly !== null){
                $tokenBuilder->forAuthenticateOnly();
            }

            $streamingOnly = $parsedArgs->getOpt('streamingOnly');
            if($streamingOnly !== null){
                $tokenBuilder->forStreamingOnly();
            }

            $publishingOnly = $parsedArgs->getOpt('publishingOnly');
            if($publishingOnly !== null){
                $tokenBuilder->forPublishingOnly();
            }

            $capabilities = $parsedArgs->getOpt('capabilities');
            if($capabilities !== null){
                $allCapabilities = explode(',', $capabilities);
                foreach($allCapabilities as $curCapability){
                    $tokenBuilder->withCapability($curCapability);
                }
            }

            $sessionId = $parsedArgs->getOpt('sessionId');
            if($sessionId !== null){
                $tokenBuilder->forSession($sessionId);
            }

            $remoteAddress = $parsedArgs->getOpt('remoteAddress');
            if($remoteAddress !== null){
                $tokenBuilder->forRemoteAddress($remoteAddress);
            }

            $originStreamId = $parsedArgs->getOpt('originStreamId');
            if($originStreamId !== null){
                $tokenBuilder->forOriginStream($originStreamId);
            }

            $channel = $parsedArgs->getOpt('channel');
            if($channel !== null){
                $tokenBuilder->forChannel($channel);
            }

            $channelAlias = $parsedArgs->getOpt('channelAlias');
            if($channelAlias !== null){
                $tokenBuilder->forChannelAlias($channelAlias);
            }

            $room = $parsedArgs->getOpt('room');
            if($room !== null){
                $tokenBuilder->forRoom($room);
            }

            $roomAlias = $parsedArgs->getOpt('roomAlias');
            if($roomAlias !== null){
                $tokenBuilder->forRoomAlias($roomAlias);
            }

            $tag = $parsedArgs->getOpt('tag');
            if($tag !== null){
                $tokenBuilder->forTag($tag);
            }

            $applyTag = $parsedArgs->getOpt('applyTag');
            if($applyTag !== null){
                $tokenBuilder->applyTag($applyTag);
            }

            echo $tokenBuilder->build().PHP_EOL;
        } catch(\Exception $ex){
            echo $ex->getMessage();
        }

    }
}
