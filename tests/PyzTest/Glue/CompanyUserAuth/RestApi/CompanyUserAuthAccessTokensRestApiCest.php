<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace PyzTest\Glue\CompanyUserAuth\RestApi;

use Codeception\Util\HttpCode;
use PyzTest\Glue\CompanyUserAuth\CompanyUserAuthRestApiTester;
use Spryker\Glue\CompanyUserAuthRestApi\CompanyUserAuthRestApiConfig;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group CompanyUserAuth
 * @group RestApi
 * @group CompanyUserAuthAccessTokensRestApiCest
 * Add your own group annotations below this line
 * @group EndToEnd
 */
class CompanyUserAuthAccessTokensRestApiCest
{
    /**
     * @var \PyzTest\Glue\CompanyUserAuth\RestApi\CompanyUserAuthAccessTokensRestApiFixtures
     */
    protected $fixtures;

    /**
     * @param \PyzTest\Glue\CompanyUserAuth\CompanyUserAuthRestApiTester $I
     *
     * @return void
     */
    public function loadFixtures(CompanyUserAuthRestApiTester $I): void
    {
        /** @var \PyzTest\Glue\CompanyUserAuth\RestApi\CompanyUserAuthAccessTokensRestApiFixtures $fixtures */
        $fixtures = $I->loadFixtures(CompanyUserAuthAccessTokensRestApiFixtures::class);

        $this->fixtures = $fixtures;
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\CompanyUserAuth\CompanyUserAuthRestApiTester $I
     *
     * @return void
     */
    public function requestCompanyUserAccessTokenForExistingCustomerWithInvalidPostData(
        CompanyUserAuthRestApiTester $I,
    ): void {
        //Arrange
        $I->amBearerAuthenticated($this->fixtures->getOauthResponseTransferForCompanyUser()->getAccessToken());

        //Act
        $I->sendPOST(CompanyUserAuthRestApiConfig::RESOURCE_COMPANY_USER_ACCESS_TOKENS, [
            'type' => uniqid(CompanyUserAuthRestApiConfig::RESOURCE_COMPANY_USER_ACCESS_TOKENS),
            'attributes' => [
                'idCompanyUser' => $this->fixtures->getOauthResponseTransferForCompanyUser()->getIdCompanyUser(),
            ],
        ]);

        //Assert
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->dontSeeResponseHasRefreshToken();
        $I->dontSeeResponseHasAccessToken();

        $I->seeResponseMatchesOpenApiSchema();
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\CompanyUserAuth\CompanyUserAuthRestApiTester $I
     *
     * @return void
     */
    public function requestAccessTokenForNonDefaultCompanyUser(CompanyUserAuthRestApiTester $I): void
    {
        //Arrange
        $firstCompanyUserAccessToken = $this->fixtures->getOauthResponseTransferForCustomerWithTwoCompanyUsers()->getAccessToken();

        $I->amBearerAuthenticated($firstCompanyUserAccessToken);

        //Act
        $I->sendPOST(CompanyUserAuthRestApiConfig::RESOURCE_COMPANY_USER_ACCESS_TOKENS, [
            'data' => [
                'type' => CompanyUserAuthRestApiConfig::RESOURCE_COMPANY_USER_ACCESS_TOKENS,
                'attributes' => [
                    'idCompanyUser' => $this->fixtures->getNonDefaultCompanyUserTransfer()->getUuid(),
                ],
            ],
        ]);

        //Assert
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseHasRefreshToken();
        $I->seeResponseHasAccessToken();

        $secondCompanyUserAccessToken = $I->grabAccessTokenFromResponse();
        $I->assertNotNull($secondCompanyUserAccessToken);
        $I->assertNotEquals($firstCompanyUserAccessToken, $secondCompanyUserAccessToken);

        $I->seeResponseMatchesOpenApiSchema();
    }
}
