<?php

namespace RebelCode\Spotlight\Instagram\IgApi;

use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

class IgGraphApiClient
{
    const API_URI = 'https://graph.facebook.com';
    const TOKEN_EXPIRY = 60 * 24 * 3600;
    const TOP_MEDIA = 'top_media';
    const RECENT_MEDIA = 'recent_media';

    /**
     * @since 0.1
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * @since 0.1
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @since 0.1
     *
     * @param ClientInterface $client
     * @param CacheInterface  $cache
     */
    public function __construct(
        ClientInterface $client,
        CacheInterface $cache
    ) {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * Retrieves the Instagram Business account associated with a given Facebook page.
     *
     * @since 0.1
     *
     * @param string      $pageId      The ID of the Facebook page.
     * @param AccessToken $accessToken The access token for the Facebook page.
     *
     * @return IgAccount|null The associated Instagram Business account, or null if the page has no associated account.
     */
    public function getAccountForPage(string $pageId, AccessToken $accessToken) : ?IgAccount
    {
        // Get the info for the Facebook page
        $request = IgApiUtils::createRequest('GET', static::API_URI . "/${pageId}", [
            'fields' => 'instagram_business_account,access_token',
            'access_token' => $accessToken->code,

        ]);
        $response = IgApiUtils::sendRequest($this->client, $request);
        $body = IgApiUtils::parseResponse($response);

        if (!isset($body['instagram_business_account'])) {
            return null;
        }

        $userId = $body['instagram_business_account']['id'];
        $userToken = $body['access_token'];

        // Get the user info
        $request = IgApiUtils::createRequest('GET', static::API_URI . "/${userId}", [
            'fields' => implode(',', IgApiUtils::getGraphUserFields()),
            'access_token' => $userToken,
        ]);
        $response = IgApiUtils::sendRequest($this->client, $request);

        $userData = IgApiUtils::parseResponse($response);
        $userData['account_type'] = IgUser::TYPE_BUSINESS;

        $user = IgUser::create($userData);
        $token = new AccessToken($userToken, time() + static::TOKEN_EXPIRY);

        return new IgAccount($user, $token);
    }

    /**
     * Retrieves the Instagram Business account associated with a given user ID and access token.
     *
     * @since 0.2
     *
     * @param string      $userId      The ID of the Instagram user.
     * @param AccessToken $accessToken The access token for the account.
     *
     * @return IgAccount|null The Instagram Business account, or null if no account was found for the given user ID
     *                        and access token.
     */
    public function getAccountForUser(string $userId, AccessToken $accessToken) : ?IgAccount
    {
        // Get the user info
        $request = IgApiUtils::createRequest('GET', static::API_URI . "/${userId}", [
            'fields' => implode(',', IgApiUtils::getGraphUserFields()),
            'access_token' => $accessToken->code,
        ]);
        $response = IgApiUtils::sendRequest($this->client, $request);

        $userData = IgApiUtils::parseResponse($response);
        $userData['account_type'] = IgUser::TYPE_BUSINESS;

        $user = IgUser::create($userData);

        return new IgAccount($user, $accessToken);
    }

    /**
     * @since 0.1
     *
     * @param string $userId
     * @param string $accessToken
     *
     * @return array An array containing two keys, "media" and "next", which correspond to the media list and a function
     *               for retrieving the next batch of media or null if there are is more media to retrieve.
     */
    public function getMedia(string $userId, string $accessToken) : array
    {
        $getRemote = function () use ($userId, $accessToken) {
            $request = IgApiUtils::createRequest('GET', static::API_URI . "/{$userId}/media", [
                'fields' => implode(',', IgApiUtils::getBusinessMediaFields()),
                'access_token' => $accessToken,
                'limit' => 9,
            ]);

            return IgApiUtils::sendRequest($this->client, $request);
        };

        $body = IgApiUtils::getCachedResponse($this->cache, "media_b_{$userId}", $getRemote);
        $media = $body['data'];
        $media = !is_array($media) ? [] : $media;
        $media = array_map([IgMedia::class, 'create'], $media);

        $nextUrl = $body['paging']['next'] ?? null;
        $next = ($nextUrl !== null)
            ? function () use ($nextUrl, $userId, $accessToken) {
                $request = IgApiUtils::createRequest('GET', $nextUrl);
                $response = IgApiUtils::sendRequest($this->client, $request);
                $responseData = IgApiUtils::parseResponse($response);
                $responseData['data'] = $this->expandWithComments($responseData['data'], $accessToken);

                return $responseData;
            }
            : null;

        return compact('media', 'next');
    }

    /**
     * @since 0.1
     *
     * @param array  $mediaList
     * @param string $accessToken
     *
     * @return IgMedia[]
     */
    protected function expandWithComments(array $mediaList, string $accessToken) : array
    {
        $mediaIds = array_filter(array_column($mediaList, 'id'));

        $request = IgApiUtils::createRequest('GET', static::API_URI . "/comments", [
            'ids' => implode(',', $mediaIds),
            'fields' => implode(',', IgApiUtils::getCommentFields()),
            'access_token' => $accessToken,
        ]);

        $response = IgApiUtils::sendRequest($this->client, $request);

        $comments = IgApiUtils::parseResponse($response);

        foreach ($mediaList as $idx => $media) {
            $mediaId = $media['id'];

            if (!isset($comments[$mediaId])) {
                continue;
            }

            $mediaList[$idx]['comments'] = $comments[$mediaId]['data'];
        }

        return $mediaList;
    }
}
