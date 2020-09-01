<?php

namespace Valet;

use DomainException;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

class Site
{
    public $config;
    public $cli;
    public $files;

    /**
     * Create a new Site instance.
     *
     * @param Configuration $config
     * @param CommandLine   $cli
     * @param Filesystem    $files
     */
    public function __construct(Configuration $config, CommandLine $cli, Filesystem $files)
    {
        $this->cli = $cli;
        $this->files = $files;
        $this->config = $config;
    }

    /**
     * Get the real hostname for the given path, checking links.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function host($path)
    {
        foreach ($this->files->scandir($this->sitesPath()) as $link) {
            if ($resolved = realpath($this->sitesPath().'/'.$link) === $path) {
                return $link;
            }
        }

        return basename($path);
    }

    /**
     * Link the current working directory with the given name.
     *
     * @param string $target
     * @param string $link
     *
     * @return string
     */
    public function link($target, $link)
    {
        $this->files->ensureDirExists(
            $linkPath = $this->sitesPath(), user()
        );

        $this->config->prependPath($linkPath);

        $this->files->symlinkAsUser($target, $linkPath.'/'.$link);

        return $linkPath.'/'.$link;
    }

    /**
     * Unlink the given symbolic link.
     *
     * @param string $name
     *
     * @return void
     */
    public function unlink($name)
    {
        $name = $this->getRealSiteName($name);

        if ($this->files->exists($path = $this->sitesPath().'/'.$name)) {
            $this->files->unlink($path);
        }

        return $name;
    }

    /**
     * Get the name of the site.
     *
     * @param  string|null $name
     * @return string
     */
    protected function getRealSiteName($name)
    {
        if (! is_null($name)) {
            return $name;
        }

        if (is_string($link = $this->getLinkNameByCurrentDir())) {
            return $link;
        }

        return basename(getcwd());
    }

    /**
     * Get link name based on the current directory.
     *
     * @return null|string
     */
    protected function getLinkNameByCurrentDir()
    {
        $count = count($links = $this->links()->where('path', getcwd()));

        if ($count == 1) {
            return $links->shift()['site'];
        }

        if ($count > 1) {
            throw new DomainException("There are {$count} links related to the current directory, please specify the name: valet unlink <name>.");
        }
    }

    /**
     * Pretty print out all links in Valet.
     *
     * @return \Illuminate\Support\Collection
     */
    public function links()
    {
        $certsPath = VALET_HOME_PATH.'/Certificates';

        $this->files->ensureDirExists($certsPath, user());

        $certs = $this->getCertificates($certsPath);

        return $this->getLinks($this->sitesPath(), $certs);
    }

    /**
     * Get all certificates from config folder.
     *
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCertificates($path)
    {
        return collect($this->files->scanDir($path))->filter(function ($value, $key) {
            return ends_with($value, '.crt');
        })->map(function ($cert) {
            $certWithoutSuffix = substr($cert, 0, -4);

            return substr($certWithoutSuffix, 0, strrpos($certWithoutSuffix, '.'));
        })->flip();
    }

    /**
     * Get list of links and present them formatted.
     *
     * @param string                         $path
     * @param \Illuminate\Support\Collection $certs
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getLinks($path, $certs)
    {
        $config = $this->config->read();

        return collect($this->files->scanDir($path))->mapWithKeys(function ($site) use ($path) {
            return [$site => $this->files->readLink($path.'/'.$site)];
        })->map(function ($path, $site) use ($certs, $config) {
            $secured = $certs->has($site);
            $url = ($secured ? 'https' : 'http').'://'.$site.'.'.$config['tld'];

            return [
                'site' => $site,
                'secured' => $secured ? ' X' : '',
                'url' => $url,
                'path' => $path,
            ];
        });
    }

    /**
     * Remove all broken symbolic links.
     *
     * @return void
     */
    public function pruneLinks()
    {
        $this->files->ensureDirExists($this->sitesPath(), user());

        $this->files->removeBrokenLinksAt($this->sitesPath());
    }

    /**
     * Resecure all currently secured sites with a fresh tld.
     *
     * @param string $oldTld
     * @param string $tld
     *
     * @return void
     */
    public function resecureForNewTld($oldTld, $tld)
    {
        if (! $this->files->exists($this->certificatesPath())) {
            return;
        }

        $secured = $this->secured();

        foreach ($secured as $url) {
            $this->unsecure($url);
        }

        foreach ($secured as $url) {
            $this->secure(str_replace('.'.$oldTld, '.'.$tld, $url));
        }
    }

    /**
     * Get all of the URLs that are currently secured.
     *
     * @return array
     */
    public function secured()
    {
        return collect($this->files->scandir($this->certificatesPath()))
                    ->map(function ($file) {
                        return str_replace(['.key', '.csr', '.crt', '.conf'], '', $file);
                    })->unique()->values()->all();
    }

    /**
     * Secure the given host with TLS.
     *
     * @param string $url
     *
     * @return void
     */
    public function secure($url)
    {
        $this->unsecure($url);

        $this->files->ensureDirExists($this->certificatesPath(), user());

        $this->createCertificate($url);

        $this->files->putAsUser(
            VALET_HOME_PATH."/Nginx/$url.conf", $this->buildSecureNginxServer($url)
        );
    }

    /**
     * Get the port of the given host.
     *
     * @param string $url
     *
     * @return int
     */
    public function port($url)
    {
        if ($this->files->exists($path = VALET_HOME_PATH."/Nginx/$url.conf")) {
            if (strpos($this->files->get($path), '443') !== false) {
                return 443;
            }
        }

        return 80;
    }

    /**
     * Create and trust a certificate for the given URL.
     *
     * @param string $url
     *
     * @return void
     */
    public function createCertificate($url)
    {
        $keyPath = $this->certificatesPath().'/'.$url.'.key';
        $csrPath = $this->certificatesPath().'/'.$url.'.csr';
        $crtPath = $this->certificatesPath().'/'.$url.'.crt';

        $this->createPrivateKey($keyPath);
        $this->createSigningRequest($url, $keyPath, $csrPath);
        $this->createSignedCertificate($keyPath, $csrPath, $crtPath);

        $this->trustCertificate($crtPath);
    }

    /**
     * Create the private key for the TLS certificate.
     *
     * @param string $keyPath
     *
     * @return void
     */
    public function createPrivateKey($keyPath)
    {
        $key = (new RSA())->createKey(2048);

        $this->files->putAsUser($keyPath, $key['privatekey']);
    }

    /**
     * Create the signing request for the TLS certificate.
     *
     * @param string $keyPath
     *
     * @return void
     */
    public function createSigningRequest($url, $keyPath, $csrPath)
    {
        $privKey = new RSA();
        $privKey->loadKey($this->files->get($keyPath));

        $x509 = new X509();
        $x509->setPrivateKey($privKey);
        $x509->setDNProp('commonname', $url);

        $x509->loadCSR($x509->saveCSR($x509->signCSR()));

        $x509->setExtension('id-ce-subjectAltName', [
            ['dNSName' => $url],
            ['dNSName' => "*.$url"],
        ]);

        $csr = $x509->saveCSR($x509->signCSR());

        $this->files->putAsUser($csrPath, $csr);
    }

    /**
     * Create the signed TLS certificate.
     *
     * @param string $keyPath
     * @param string $csrPath
     * @param string $crtPath
     *
     * @return void
     */
    public function createSignedCertificate($keyPath, $csrPath, $crtPath)
    {
        $privKey = new RSA();
        $privKey->loadKey($this->files->get($keyPath));

        $subject = new X509();
        $subject->loadCSR($this->files->get($csrPath));

        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDN($subject->getDN());

        $x509 = new X509();
        $x509->makeCA();
        $x509->setStartDate('-1 day');

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        $certificate = $x509->saveX509($result);

        $this->files->putAsUser($crtPath, $certificate);
    }

    /**
     * Trust the given certificate file in the Mac Keychain.
     *
     * @param string $crtPath
     *
     * @return void
     */
    public function trustCertificate($crtPath)
    {
        $this->cli->run(sprintf('cmd "/C certutil -addstore "Root" "%s""', $crtPath));
    }

    /**
     * Build the TLS secured Nginx server for the given URL.
     *
     * @param string $url
     *
     * @return string
     */
    public function buildSecureNginxServer($url)
    {
        $path = $this->certificatesPath();

        return str_replace(
            ['VALET_HOME_PATH', 'VALET_SERVER_PATH', 'VALET_STATIC_PREFIX', 'VALET_SITE', 'VALET_CERT', 'VALET_KEY', 'HOME_PATH'],
            [VALET_HOME_PATH, VALET_SERVER_PATH, VALET_STATIC_PREFIX, $url, $path.'/'.$url.'.crt', $path.'/'.$url.'.key', $_SERVER['HOME']],
            $this->files->get(__DIR__.'/../stubs/secure.valet.conf')
        );
    }

    /**
     * Unsecure the given URL so that it will use HTTP again.
     *
     * @param string $url
     *
     * @return void
     */
    public function unsecure($url)
    {
        if ($this->files->exists($this->certificatesPath().'/'.$url.'.crt')) {
            $this->files->unlink(VALET_HOME_PATH."/Nginx/$url.conf");

            $this->files->unlink($this->certificatesPath().'/'.$url.'.key');
            $this->files->unlink($this->certificatesPath().'/'.$url.'.csr');
            $this->files->unlink($this->certificatesPath().'/'.$url.'.crt');

            $this->cli->run(sprintf('cmd "/C certutil -delstore "Root" "%s""', $url));
        }
    }

    /**
     * Get the path to the linked Valet sites.
     *
     * @return string
     */
    public function sitesPath()
    {
        return VALET_HOME_PATH.'/Sites';
    }

    /**
     * Get the path to the Valet TLS certificates.
     *
     * @return string
     */
    public function certificatesPath()
    {
        return VALET_HOME_PATH.'/Certificates';
    }
}
