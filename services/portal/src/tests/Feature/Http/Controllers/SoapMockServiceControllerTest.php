<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

use function config;

final class SoapMockServiceControllerTest extends TestCase
{
    public function testWsdlEndpointReturnsValidXml(): void
    {
        $response = $this->get('/osiris/wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $wsdlFile = config('services.osiris.mock_wsdl_path');
        $this->assertIsString($wsdlFile);
        $this->assertStringEqualsFile($wsdlFile, $response->getContent());
    }

    public function testWsdlPointsToToLocalWebServer(): void
    {
        $expected = <<<'XML'
    <wsdl:port name="WSVragenLijstSoap" binding="tns:WSVragenLijstSoap">
      <soap:address location="http://portal:8080/osiris" />
    </wsdl:port>
    <wsdl:port name="WSVragenLijstSoap12" binding="tns:WSVragenLijstSoap12">
      <soap12:address location="http://portal:8080/osiris" />
    </wsdl:port>
XML;
        $response = $this->get('/osiris/wsdl');
        $response->assertSee($expected, false);
    }

    public function testMockServerReturnsSuccessResponse(): void
    {
        $xml = $this->putMessageSuccessRequest();
        $response = $this->call('POST', '/osiris', [], [], [], [], $xml);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/xml');
        $response->assertSee('<?xml version="1.0"', false);
        $response->assertSee('osiris_nummer', false);
    }

    private function putMessageSuccessRequest(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope   xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
	<soap12:Body>
		<PutMessage xmlns="RIVM">
			<SysLogin>xxxx</SysLogin>
			<SysPassword>xxxx</SysPassword>
			<Protocol>xmlv2</Protocol>
			<Message><![CDATA[
				<melding xmlns="http://tempuri.org/PutMessage.xsd">
					<meld_nummer>GGDNUMMER_TEST</meld_nummer>
					<meld_code>NCOV</meld_code>
					<vragenlijst_versie>10</vragenlijst_versie>
					<status_code>A2FIAT</status_code>
					<meld_locatie></meld_locatie>
					<wis_missend_antwoord>true</wis_missend_antwoord>
					<osiris_gebruiker_login>xxxxx</osiris_gebruiker_login>
					<antwoord>
						<vraag_code>MELGGDOntvDt</vraag_code>
						<antwoord_tekst>01-05-2022</antwoord_tekst>
					</antwoord>
					<antwoord>
						<vraag_code>PATGeslacht</vraag_code>
						<antwoord_tekst>V</antwoord_tekst>
					</antwoord>
					<antwoord>
						<vraag_code>NCOVgebdat</vraag_code>
						<antwoord_tekst>10-09-1976</antwoord_tekst>
					</antwoord>
					<antwoord>
						<vraag_code>NCOVondaandcomorV2</vraag_code>
						<antwoord_tekst>12</antwoord_tekst>
					</antwoord>
					<antwoord>
						<vraag_code>NCOVCoronITMonnr</vraag_code>
						<antwoord_tekst>936C8561652</antwoord_tekst>
					</antwoord>
				</melding>
			]]></Message>
			<CommunicatieId>GGDC{{$timestamp}}</CommunicatieId>
		</PutMessage>
	</soap12:Body>
</soap12:Envelope>
XML;
    }
}
