<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace PyzTest\Glue\Auth\RestApi;

use Codeception\Util\HttpCode;
use PyzTest\Glue\Auth\AuthRestApiTester;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group Auth
 * @group RestApi
 * @group RefreshTokensRestApiCest
 * Add your own group annotations below this line
 * @group EndToEnd
 */
class RefreshTokensRestApiCest
{
    /**
     * @var string
     */
    protected const INVALID_REFRESH_TOKEN = 'invalid refresh token';

    /**
     * @var \PyzTest\Glue\Auth\RestApi\RefreshTokensRestApiFixtures
     */
    protected $fixtures;

    /**
     * @param \PyzTest\Glue\Auth\AuthRestApiTester $I
     *
     * @return void
     */
    public function loadFixtures(AuthRestApiTester $I): void
    {
        /** @var \PyzTest\Glue\Auth\RestApi\RefreshTokensRestApiFixtures $fixtures */
        $fixtures = $I->loadFixtures(RefreshTokensRestApiFixtures::class);

        $this->fixtures = $fixtures;
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Auth\AuthRestApiTester $I
     *
     * @return void
     */
    public function requestRefreshTokenWithInvalidPostData(AuthRestApiTester $I): void
    {
        //Act
        $I->sendPOST(AuthRestApiConfig::RESOURCE_REFRESH_TOKENS, [
            'type' => AuthRestApiConfig::RESOURCE_REFRESH_TOKENS,
            'attributes' => [
                'refreshToken' => $this->fixtures->getOauthResponseTransfer()->getRefreshToken(),
            ],
        ]);

        //Assert
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->dontSeeResponseHasAccessToken();
        $I->dontSeeResponseHasRefreshToken();
        $I->seeResponseMatchesOpenApiSchema();
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Auth\AuthRestApiTester $I
     *
     * @return void
     */
    public function requestRefreshTokenWithEmptyType(AuthRestApiTester $I): void
    {
        //Act
        $I->sendPOST(AuthRestApiConfig::RESOURCE_REFRESH_TOKENS, [
            'data' => [
                'type' => '',
                'attributes' => [
                    'refreshToken' => $this->fixtures->getOauthResponseTransfer()->getRefreshToken(),
                ],
            ],
        ]);

        //Assert
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->dontSeeResponseHasAccessToken();
        $I->dontSeeResponseHasRefreshToken();
        $I->seeResponseMatchesOpenApiSchema();
    }
}
