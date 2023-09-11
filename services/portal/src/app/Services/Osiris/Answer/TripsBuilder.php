<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Trip;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function count;

class TripsBuilder implements Builder
{
    private const MAX_TRIPS = 5;

    public function __construct()
    {
    }

    public function build(EloquentCase $case): array
    {
        if ($case->abroad->wasAbroad !== YesNoUnknown::yes()) {
            return [];
        }

        return Utils::collectAnswers(
            $case->abroad->trips,
            self::MAX_TRIPS,
            fn (Trip $trip, int $index) => $this->buildTripAnswers($trip, $index + 1)
        );
    }

    private function buildTripAnswers(Trip $trip, int $index): array
    {
        $answers = [];

        // The first trip answer to this question is based on the `$case->abroad->wasAbroad` value
        // and is handled by the `MERSPATbuitenlBuilder`.
        if ($index > 1) {
            $answers[] = new Answer("MERSPATbuitenl{$index}", 'J');
        }

        if ($trip->returnDate !== null) {
            $answers[] = new Answer("NCOVBtnlndDatTer{$index}", Utils::formatDate($trip->returnDate));
        }

        if (isset($trip->countries) && count($trip->countries) > 0) {
            $countryCode = $this->getCountryCode($trip->countries[0]);
            if ($countryCode !== null) {
                $answers[] = new Answer("EPILand{$index}", $countryCode);
            }
        }

        return $answers;
    }

    private function getCountryCode(Country $country): ?string
    {
        return match ($country) {
            Country::afg() => '6023',
            Country::ala() => '0',
            Country::alb() => '5034',
            Country::dza() => '6047',
            Country::vir() => '7088',
            Country::asm() => '8002',
            Country::and() => '7005',
            Country::ago() => '5026',
            Country::aia() => '8036',
            Country::ata() => '0',
            Country::atg() => '8045',
            Country::arg() => '7015',
            Country::arm() => '5054',
            Country::abw() => '5095',
            Country::aus() => '6016',
            Country::aze() => '5097',
            Country::bhs() => '6033',
            Country::bhr() => '5057',
            Country::bgd() => '7084',
            Country::brb() => '7004',
            Country::bel() => '5010',
            Country::blz() => '8017',
            Country::ben() => '8023',
            Country::bmu() => '9048',
            Country::btn() => '5058',
            Country::bol() => '6015',
            Country::bih() => '6065',
            Country::bwa() => '5011',
            Country::bvt() => '0',
            Country::bra() => '6008',
            Country::vgb() => '7030',
            Country::iot() => '7096',
            Country::brn() => '5042',
            Country::bgr() => '7024',
            Country::bfa() => '5096',
            Country::bdi() => '6001',
            Country::khm() => '6031',
            Country::can() => '5001',
            Country::bes() => '7011',
            Country::caf() => '9086',
            Country::chl() => '5021',
            Country::chn() => '6022',
            Country::cxr() => '8012',
            Country::cck() => '8013',
            Country::col() => '5033',
            Country::com() => '5060',
            Country::cog() => '6070',
            Country::cod() => '6069',
            Country::cok() => '7097',
            Country::cri() => '7007',
            Country::cub() => '5006',
            Country::cuw() => '5107',
            Country::cyp() => '5040',
            Country::dnk() => '5015',
            Country::dji() => '9087',
            Country::dma() => '8030',
            Country::dom() => '7027',
            Country::deu() => '6029',
            Country::ecu() => '7039',
            Country::egy() => '7014',
            Country::slv() => '7032',
            Country::gnq() => '9043',
            Country::eri() => '9003',
            Country::est() => '7065',
            Country::eth() => '5020',
            Country::fro() => '8014',
            Country::flk() => '5061',
            Country::fji() => '6032',
            Country::phl() => '5027',
            Country::fin() => '6002',
            Country::fra() => '5002',
            Country::atf() => '0',
            Country::guf() => '5062',
            Country::pyf() => '6054',
            Country::gab() => '6048',
            Country::gmb() => '7008',
            Country::geo() => '6064',
            Country::gha() => '5024',
            Country::gib() => '6055',
            Country::grd() => '8008',
            Country::grc() => '6003',
            Country::grl() => '5065',
            Country::glp() => '5066',
            Country::gum() => '8001',
            Country::gtm() => '6004',
            Country::ggy() => '8034',
            Country::gin() => '7040',
            Country::gnb() => '5027',
            Country::guy() => '6025',
            Country::hti() => '6041',
            Country::hmd() => '0',
            Country::hnd() => '7017',
            Country::hun() => '5017',
            Country::hkg() => '7036',
            Country::irl() => '6007',
            Country::isl() => '6011',
            Country::ind() => '7046',
            Country::idn() => '6024',
            Country::irq() => '5043',
            Country::irn() => '5012',
            Country::isr() => '6034',
            Country::ita() => '7044',
            Country::civ() => '5030',
            Country::jam() => '6017',
            Country::jpn() => '7035',
            Country::yem() => '5048',
            Country::jey() => '8034',
            Country::jor() => '6042',
            Country::cym() => '7092',
            Country::cpv() => '8025',
            Country::cmr() => '5035',
            Country::kaz() => '5099',
            Country::ken() => '7002',
            Country::kgz() => '6021',
            Country::kir() => '8027',
            Country::umi() => '0',
            Country::kwt() => '7045',
            Country::xkx() => 'KOS',
            Country::hrv() => '5051',
            Country::lao() => '5025',
            Country::lso() => '7016',
            Country::lva() => '7064',
            Country::lbn() => '7043',
            Country::lbr() => '5019',
            Country::lby() => '6006',
            Country::lie() => '6012',
            Country::ltu() => '7066',
            Country::lux() => '6018',
            Country::mac() => '5065',
            Country::mdg() => '9010',
            Country::mwi() => '5005',
            Country::mdv() => '7041',
            Country::mys() => '7026',
            Country::mli() => '5029',
            Country::mlt() => '7003',
            Country::imn() => '8035',
            Country::mar() => '5022',
            Country::mhl() => '9056',
            Country::mtq() => '5069',
            Country::mrt() => '6020',
            Country::mus() => '5044',
            Country::myt() => '5084',
            Country::mex() => '7006',
            Country::fsm() => '9094',
            Country::mda() => '6000',
            Country::mco() => '5032',
            Country::mng() => '7052',
            Country::mne() => '5103',
            Country::msr() => '8015',
            Country::moz() => '5070',
            Country::mmr() => '5047',
            Country::nam() => '9023',
            Country::nru() => '7057',
            Country::nld() => '6030',
            Country::npl() => '6035',
            Country::nic() => '7018',
            Country::ncl() => '7099',
            Country::nzl() => '5013',
            Country::ner() => '6040',
            Country::nga() => '6005',
            Country::niu() => '9091',
            Country::mnp() => '8009',
            Country::prk() => '6049',
            Country::mkd() => '5100',
            Country::nor() => '6027',
            Country::nfk() => '8016',
            Country::uga() => '7001',
            Country::ukr() => '6038',
            Country::uzb() => '6050',
            Country::omn() => '7051',
            Country::aut() => '5009',
            Country::tls() => '5101',
            Country::pak() => '7020',
            Country::plw() => '8044',
            Country::pse() => '7060',
            Country::pan() => '7037',
            Country::png() => '8021',
            Country::pry() => '5038',
            Country::per() => '7049',
            Country::pcn() => '5071',
            Country::pol() => '7028',
            Country::prt() => '7050',
            Country::pri() => '8020',
            Country::qat() => '9037',
            Country::reu() => '5073',
            Country::rou() => '7047',
            Country::rus() => '5053',
            Country::rwa() => '6009',
            Country::blm() => '0',
            Country::kna() => '8037',
            Country::lca() => '8029',
            Country::spm() => '5074',
            Country::vct() => '5092',
            Country::slb() => '8022',
            Country::wsm() => '6062',
            Country::smr() => '6028',
            Country::sau() => '5018',
            Country::stp() => '6059',
            Country::sen() => '7021',
            Country::srb() => '5102',
            Country::syc() => '8026',
            Country::sle() => '6051',
            Country::sgp() => '5037',
            Country::shn() => '6061',
            Country::maf() => '5110',
            Country::sxm() => '5110',
            Country::svn() => '5049',
            Country::svk() => '6067',
            Country::sdn() => '9961',
            Country::som() => '6013',
            Country::esp() => '6037',
            Country::sjm() => '5093',
            Country::lka() => '7033',
            Country::sur() => '5007',
            Country::swz() => '9036',
            Country::syr() => '7009',
            Country::tjk() => '6057',
            Country::twn() => '5252',
            Country::tza() => '7031',
            Country::tha() => '7042',
            Country::tgo() => '5023',
            Country::tkl() => '7098',
            Country::ton() => '5076',
            Country::tto() => '6044',
            Country::tcd() => '6019',
            Country::cze() => '6066',
            Country::tun() => '5008',
            Country::tur() => '6043',
            Country::tkm() => '6063',
            Country::tca() => '8019',
            Country::tuv() => '8028',
            Country::ury() => '7038',
            Country::vut() => '9090',
            Country::vat() => '5045',
            Country::ven() => '6010',
            Country::are() => '4011',
            Country::usa() => '6014',
            Country::gbr() => '6039',
            Country::vnm() => '8024',
            Country::wlf() => '5077',
            Country::esh() => '9093',
            Country::blr() => '9950',
            Country::zmb() => '5028',
            Country::zwe() => '8031',
            Country::zaf() => '5014',
            Country::sgs() => '0',
            Country::kor() => '6036',
            Country::ssd() => '9960',
            Country::swe() => '5039',
            Country::che() => '5003',
            default => null
        };
    }
}
