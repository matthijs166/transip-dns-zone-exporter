<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

global $config;

use Transip\Api\Library\TransipAPI;
use Transip\Api\Library\Entity\Domain\DnsEntry as DnsEntry;

use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\AlignedBuilder;

$api = new TransipAPI(
    $config['login'],
    $config['privateKey'],
    $config['generateWhitelistOnlyTokens']
);
$domainName = $config['domainName'];
$dnsentries = $api->domainDns()->getByDomainName( $domainName );

// Create a new zone for exporting
$zone = new Zone($domainName . '.');
$zone->setDefaultTtl(1);

// Add the DNS entries to the zone
foreach( $dnsentries as $dnsEntry) {

    $rr = new ResourceRecord;
    $rr->setName($dnsEntry->getName());

    switch($dnsEntry->getType()) {
        case DnsEntry::TYPE_A:
            $rr->setRdata(Factory::A($dnsEntry->getContent()));
            break;
        case DnsEntry::TYPE_AAAA:
            $rr->setRdata(Factory::Aaaa($dnsEntry->getContent()));
            break;
        case DnsEntry::TYPE_CNAME:
            $rr->setRdata(Factory::Cname($dnsEntry->getContent()));
            break;
        case DnsEntry::TYPE_MX:
            list($pref,$cont) = explode(' ', $dnsEntry->getContent());
            $rr->setRdata(Factory::Mx($pref, $cont));
            break;
        case DnsEntry::TYPE_TXT:
            $rr->setRdata(Factory::Txt($dnsEntry->getContent()));
            break;
        //TODO: Implement the rest of the types
        default:
            throw new \RuntimeException( $dnsEntry->type . ' records are not implemented');
    }
    $zone->addResourceRecord($rr);
}

$alignBuilder = new AlignedBuilder();
$zoneFile = $alignBuilder->build($zone);

//save the zone file
file_put_contents($domainName . '.zone', $zoneFile);