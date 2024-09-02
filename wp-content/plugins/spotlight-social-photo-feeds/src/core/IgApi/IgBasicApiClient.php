<?php

namespace RebelCode\Spotlight\Instagram\IgApi;

use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * API client for the Instagram Basic Display API.
 *
 * @since 0.1
 */
class IgBasicApiClient
{
    /**
     * The base URI to the Instagram Basic Display API.
     *
     * @since 0.1
     */
    const BASE_URL = 'https://graph.instagram.com';

    /**
     * The API client driver.
     *
     * @since 0.1
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * The cache to use for caching responses.
     *
     * @since 0.1
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Whether or not to use the legacy Instagram API to compensate for data that is omitted by the Basic Display API.
     *
     * @since 0.1
     *
     * @var bool
     */
    protected $legacyComp;

    /**
     * Constructor.
     *
     * @since 0.1
     *
     * @param ClientInterface $client     The client driver to use for sending requests.
     * @param CacheInterface  $cache      The cache to use for caching responses.
     * @param bool            $legacyComp If true, the legacy Instagram API will be used to compensate for data that is
     *                                    omitted by the Basic Display API.
     */
    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        bool $legacyComp = false
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->legacyComp = $legacyComp;
    }

    /**
     * Retrieves information about the user with whom the access token is associated with.
     *
     * @since 0.1
     *
     * @param AccessToken $accessToken The access token.
     *
     * @return IgUser The user associated with the given access token.
     */
    public function getTokenUser(AccessToken $accessToken) : IgUser
    {
        $request = IgApiUtils::createRequest('GET', static::BASE_URL . '/me', [
            'fields' => implode(',', IgApiUtils::getBasicUserFields()),
            'access_token' => $accessToken->code,
        ]);

        $response = IgApiUtils::sendRequest($this->client, $request);

        // Make sure the account is marked as PERSONAL, even if Instagram treats it as a BUSINESS account.
        // Otherwise, media fetching in IgApiClient will use the Graph API instead of the BasicDisplay API, which will
        // fail (access tokens cannot be used across the two APIs).
        $body = IgApiUtils::parseResponse($response);
        $body['account_type'] = 'PERSONAL';

        return $this->createUserFromResponse($body);
    }

    /**
     * Refreshes a long-lived access token.
     *
     * @since 0.1
     *
     * @param AccessToken $accessToken The access token to refresh.
     *
     * @return AccessToken The new access token.
     */
    public function refreshAccessToken(AccessToken $accessToken) : AccessToken
    {
        $request = IgApiUtils::createRequest('GET', static::BASE_URL . '/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $accessToken->code,
        ]);

        $response = IgApiUtils::sendRequest($this->client, $request);

        $body = IgApiUtils::parseResponse($response);
        $code = $body['access_token'];
        $expiry = time() + intval($body['expires_in']);

        return new AccessToken($code, $expiry);
    }

    /**
     * Retrieves media for a specific user.
     *
     * @since 0.1
     *
     * @param string      $userId      The ID of the user whose media to fetch.
     * @param AccessToken $accessToken The access token.
     * @param int         $limit       The max number of media to fetch.
     *
     * @return array An array containing two keys, "media" and "next", which correspond to the media list and a function
     *               for retrieving the next batch of media or null if there are is more media to retrieve.
     */
    public function getMedia($userId, AccessToken $accessToken, int $limit = 200) : array
    {
        $getRemote = function () use ($userId, $accessToken, $limit) {
            $request = IgApiUtils::createRequest('GET', static::BASE_URL . "/{$userId}/media", [
                'fields' => implode(',', IgApiUtils::getPersonalMediaFields()),
                'access_token' => $accessToken->code,
                'limit' => $limit,
            ]);

            return IgApiUtils::sendRequest($this->client, $request);
        };

        $body = IgApiUtils::getCachedResponse($this->cache, "media_p_{$userId}", $getRemote);
        $media = $body['data'];
        $media = array_map([IgMedia::class, 'create'], $media);

        $nextUrl = $body['paging']['next'] ?? null;
        $next = ($nextUrl !== null)
            ? function () use ($nextUrl, $userId, $accessToken) {
                $request = IgApiUtils::createRequest('GET', $nextUrl);
                $response = IgApiUtils::sendRequest($this->client, $request);

                return IgApiUtils::parseResponse($response);
            }
            : null;

        return compact('media', 'next');
    }

    /**
     * Creates an IgUser instance from a response.
     *
     * @since 0.1
     *
     * @param array $data The user response data.
     *
     * @return IgUser The created user instance.
     */
    protected function createUserFromResponse(array $data)
    {
        $data = $this->populateMissingUserInfo($data);

        return IgUser::create($data);
    }

    /**
     * Attempts to populate missing user info from the legacy API.
     *
     * @since 0.1
     *
     * @param array $data The user data.
     *
     * @return array The user data, populated with additional info from the legacy API if successful.
     */
    protected function populateMissingUserInfo(array $data)
    {
        if (!$this->legacyComp || !isset($data['username'])) {
            return $data;
        }

        $username = $data['username'];

        try {
            $getRemote = function () use ($username) {
                $request = IgApiUtils::createRequest('GET', "https://instagram.com/{$username}?__a=1");

                return IgApiUtils::sendRequest($this->client, $request);
            };

            $info = IgApiUtils::getCachedResponse($this->cache, "legacy_p_{$username}", $getRemote);
        } catch (Exception $exception) {
            return $data;
        }

        if (!isset($info['graphql']['user'])) {
            return $data;
        }

        $legacy = $info['graphql']['user'];

        $data['profile_picture_url'] = $legacy['profile_pic_url'] ?? "";
        $data['biography'] = $legacy['biography'] ?? "";
        $data['followers_count'] = $legacy['edge_followed_by']['count'] ?? 0;

        return $data;
    }

    /**
     * Attempts to populate missing media info from the legacy API.
     *
     * @since 0.1
     *
     * @param array $data The media data.
     *
     * @return array The media data, populated with additional info from the legacy API if successful.
     */
    protected function populateMissingMediaInfo(array $data)
    {
        if (!$this->legacyComp || !isset($data['permalink'])) {
            return $data;
        }

        $mediaId = $data['id'];
        $permalink = $data['permalink'];

        try {
            $getRemote = function () use ($mediaId, $permalink) {
                $request = IgApiUtils::createRequest('GET', "{$permalink}?__a=1");

                return IgApiUtils::sendRequest($this->client, $request);
            };

            $info = IgApiUtils::getCachedResponse($this->cache, "legacy_m_{$mediaId}", $getRemote);
        } catch (Exception $exception) {
            return $data;
        }

        if (!isset($info['graphql']['shortcode_media'])) {
            return $data;
        }

        $legacy = $info['graphql']['shortcode_media'];

        $data['comments_count'] = $legacy['edge_media_preview_comment']['count'] ?? 0;
        $data['like_count'] = $legacy['edge_media_preview_comment']['count'] ?? 0;

        return $data;
    }
}
