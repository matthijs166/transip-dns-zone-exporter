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
);

# Configure domains to export
$domains = [];
if(!empty($config['tags'])){
    $domains = array_merge($domains, $api->domains()->getByTagNames($config['tags']));
}
foreach($config['domainNames'] as $domainName){
    // Skip if domain is already in the list
    if(in_array($domainName, array_map(function($domain){ return $domain->getName(); }, $domains))){
        continue;
    }

    $domains[] = new Transip\Api\Library\Entity\Domain(['name' => $domainName]);
}

# Process each domain
foreach($domains as $domain) {
    echo 'Processing ' . $domain->getName() . PHP_EOL;

    $domainName = $domain->getName() ;
    $dnsentries = $api->domainDns()->getByDomainName( $domainName );

    if($config['exportAuthorizationCodes']) {
        try {
            $auth_code = $api->domainAuthCode()->getByDomainName($domainName);

            if (!is_dir('authcodes/')) {
                mkdir('authcodes/');
            }

            file_put_contents('authcodes/' . $domainName . '.auth', $auth_code);
        } catch (Exception $e) {
            // Do nothing; Can happen if the domain is not transferable or has no auth code
            echo 'No auth code found for ' . $domainName . PHP_EOL;
        }
    }

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
            case DnsEntry::TYPE_SRV:
                list($priority, $weight, $port, $target) = explode(' ', $dnsEntry->getContent());
                $rr->setRdata(Factory::SRV($priority, $weight, $port, $target));
                break;
            case DnsEntry::TYPE_SSHFP:
                list($algorithm, $fingerprintType, $fingerprint) = explode(' ', $dnsEntry->getContent());
                $rr->setRdata(Factory::SSHFP($algorithm, $fingerprintType, $fingerprint));
                break;
            case DnsEntry::TYPE_CAA:
                list($flag, $tag, $value) = explode(' ', $dnsEntry->getContent());
                $rr->setRdata(Factory::CAA($flag, $tag, $value));
                break;
            case DnsEntry::TYPE_NS:
                $rr->setRdata(Factory::Ns($dnsEntry->getContent()));
                break;
            //TODO: Implement the rest of the types
            default:
                throw new \RuntimeException( $dnsEntry->getType() . ' records are not implemented');
        }
        $zone->addResourceRecord($rr);
    }

    $alignBuilder = new AlignedBuilder();
    $zoneFile = $alignBuilder->build($zone);

    if (!is_dir('zonefiles/')) {
        mkdir('zonefiles/');
    }

    //save the zone file
    file_put_contents('zonefiles/' . $domainName . '.zone', $zoneFile);
}
