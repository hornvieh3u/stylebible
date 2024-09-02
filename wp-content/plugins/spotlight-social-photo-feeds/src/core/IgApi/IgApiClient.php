<?php

namespace RebelCode\Spotlight\Instagram\IgApi;

/**
 * The main API client, for both the Basic Display API and the Graph API.
 *
 * @since 0.1
 */
class IgApiClient
{
    /**
     * @since 0.1
     *
     * @var IgBasicApiClient
     */
    protected $basicApi;

    /**
     * @since 0.1
     *
     * @var IgGraphApiClient
     */
    protected $graphApi;

    /**
     * Constructor.
     *
     * @since 0.1
     *
     * @param IgBasicApiClient $basicApi The client for the Basic Display API.
     * @param IgGraphApiClient $graphApi The client for the Graph API.
     */
    public function __construct(IgBasicApiClient $basicApi, IgGraphApiClient $graphApi)
    {
        $this->basicApi = $basicApi;
        $this->graphApi = $graphApi;
    }

    /**
     * Retrieves the client for the Basic Display API.
     *
     * @since 0.1
     *
     * @return IgBasicApiClient
     */
    public function getBasicApi() : IgBasicApiClient
    {
        return $this->basicApi;
    }

    /**
     * Retrieves the client for the Graph API.
     *
     * @since 0.1
     *
     * @return IgGraphApiClient
     */
    public function getGraphApi() : IgGraphApiClient
    {
        return $this->graphApi;
    }

    /**
     * Fetches an account's information from the API.
     *
     * @since 0.3
     *
     * @param IgAccount $account The account.
     *
     * @return IgAccount The new account.
     */
    public function getAccountInfo(IgAccount $account): IgAccount
    {
        $user = $account->user;
        $accessToken = $account->accessToken;

        if ($user->type === IgUser::TYPE_PERSONAL) {
            return new IgAccount($this->basicApi->getTokenUser($accessToken), $accessToken);
        } else {
            return $this->graphApi->getAccountForUser($user->id, $accessToken);
        }
    }

    /**
     * Retrieves media for a given account, using either the Basic Display or the Graph API depending on user type.
     *
     * @since 0.1
     *
     * @param IgAccount $account The account for which to retrieve media.
     *
     * @return array An array containing two keys, "media" and "next", which correspond to the media list and a function
     *               for retrieving the next batch of media or null if there are is more media to retrieve.
     */
    public function getAccountMedia(IgAccount $account)
    {
        return ($account->user->type === IgUser::TYPE_PERSONAL)
            ? $this->basicApi->getMedia($account->user->id, $account->accessToken)
            : $this->graphApi->getMedia($account->user->id, $account->accessToken->code);
    }
}
